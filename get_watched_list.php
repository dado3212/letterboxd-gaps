<?php
require_once("tmdb.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
  $file = $_FILES['file'];

  if (pathinfo($file['name'], PATHINFO_EXTENSION) === 'csv') {
    handleWatchlist($file);
    exit;
  } else if (pathinfo($file['name'], PATHINFO_EXTENSION) === 'zip') {
    handleZip($file);
  } else {
    http_response_code(400);
    echo 'Invalid file type. Please upload a .zip file.';
    exit;
  }
} else {
    http_response_code(400);
    echo 'No file uploaded.';
}

function handleWatchlist($file) {
    if (($handle = fopen($file['tmp_name'], 'r')) !== false) {
        $data = []; // Array to store the CSV data as a dictionary
    
        // Get the header row
        $headers = fgetcsv($handle);

        // It's a custom list, read to the proper header
        if (count($headers) == 1) {
          $headers = fgetcsv($handle);
          $headers = fgetcsv($handle);
          $headers = fgetcsv($handle);
          $headers = fgetcsv($handle);
        }
    
        if ($headers === false) {
          http_response_code(400);
          echo 'The CSV file is empty or invalid.';
          fclose($handle);
          exit;
        }
    
        // Process each row of the CSV
        while (($row = fgetcsv($handle)) !== false) {
          $data[] = array_combine($headers, $row);
        }
    
        fclose($handle);

        // Example: Return JSON response
        header('Content-Type: application/json');
        echo json_encode(handleMovies($data));
      } else {
        http_response_code(500);
        echo 'Failed to open uploaded file.';
      }
}

function handleZip($file) {
  // Open the .zip file directly from the uploaded file stream
  $zip = new ZipArchive();
  if ($zip->open($file['tmp_name']) === TRUE) {
    // Iterate over files in the .zip
    $files = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
      $fileName = $zip->getNameIndex($i);
      $fileContent = $zip->getFromIndex($i);
      
      // Store file data in an associative array
      $files[] = [
        'name' => $fileName,
        'content' => $fileContent,
      ];
    }
    $zip->close();

    // Process the extracted files in memory
    foreach ($files as $file) {
      echo "File: " . $file['name'] . "\n";
      // Example: Output first 100 characters of file content
      echo "Content (truncated): " . substr($file['content'], 0, 100) . "\n\n";
    }
  } else {
    http_response_code(500);
    echo 'Failed to open .zip file.';
  }
}

function handleMovies($watchlistMovies) {
  if (count($watchlistMovies) === 0) {
    return null;
  }

  // If this is a list, map it accordingly
  if (array_key_exists('URL', $watchlistMovies[0])) {
    foreach ($watchlistMovies as &$movie) {
      $movie['Letterboxd URI'] = $movie['URL'];
      unset($movie['URL']);
    }
  }

  $letterboxdUrls = [];
  foreach ($watchlistMovies as $movie) {
    $letterboxdUrls[] = $movie['Letterboxd URI'];
  }

  // Check if the URL has been uploaded to the database already
	$PDO = getDatabase();
  $placeholders = implode(',', array_fill(0, count($letterboxdUrls), '?'));
	$stmt = $PDO->prepare("SELECT * FROM movies WHERE letterboxd_url IN ($placeholders)");
  $stmt->execute($letterboxdUrls);

  $rawMovieInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $serverMovieInfo = [];
  foreach ($rawMovieInfo as $rawMovie) {
    $serverMovieInfo[$rawMovie['letterboxd_url']] = $rawMovie;
  }

  $toUpload = [];
  $movies = [];
  $new_ids = [];
  foreach ($watchlistMovies as $movie) {
    // If it's already in the database then we can return the data directly
    if (array_key_exists($movie['Letterboxd URI'], $serverMovieInfo)) {
      $movieInfo = $serverMovieInfo[$movie['Letterboxd URI']];
      // But if it's already in the database but it's still pending, then add it
      // to the new ids list for the purpose of polling. Should only apply to 
      // disconnects, but if there was more site traffic there could be more 
      // overlap in people uploading things. Hoping that with enough seed data
      // the amount of live fetching we're doing is minimal.
      if ($movieInfo['status'] == 'pending') {
        $new_ids[] = $movieInfo['id'];
      } else {
        $movieInfo['countries'] = json_decode($movieInfo['countries']);
      }
      $movies[] = $movieInfo;
    } else {
      // If it's NOT in the database, it's a little more complicated. We'll create
      // basic rows for each of the new ones, and kick off populating that data
      // In the meantime we'll send back the information that we have from the 
      // watchlist. Additionally we'll send down an ID for the progress that will
      // monitor these movies.
      $toUpload[] = [$movie['Letterboxd URI'], $movie['Name'], $movie['Year']];
      $movies[] = [
        'movie_name' => $movie['Name'],
        'year' => $movie['Year'],
        'letterboxd_url' => $movie['Letterboxd URI'],
        'status' => 'pending',
      ];
    }
  }

  if (count($toUpload) > 0) {
    $placeholders = [];
    $bindValues = [];
    foreach ($toUpload as $index => $info) {
      $placeholders[] = '(' . implode(',', array_fill(0, count($info), '?')) . ')';
      $bindValues = array_merge($bindValues, $info);
    }
    
    $sql = "INSERT INTO letterboxd.movies 
    (letterboxd_url, movie_name, `year`) 
    VALUES " . implode(', ', $placeholders);
    $stmt = $PDO->prepare($sql);
    $stmt->execute($bindValues);

    // Keep track of all of the rows that we're now processing
    $first_id = $PDO->lastInsertId();
    // Assume they're all sequential?
    for ($i = 0; $i < count($toUpload); $i++) {
      $new_ids[] = $first_id + $i;
    }
  }
  // If any of the other IDs
  $upload_id = null;
  if (!empty($new_ids)) {
    $sql = "INSERT INTO letterboxd.upload_tracking 
    (uploaded) 
    VALUES (?)";
    $stmt = $PDO->prepare($sql);
    $stmt->execute([json_encode($new_ids)]);

    $upload_id = $PDO->lastInsertId();
  }

  usort($movies, function($a, $b) {
    return (float)json_decode($a['primary_color'] ?? "{'h': 0}", true)['h'] <=> (float)json_decode($b['primary_color'] ?? "{'h': 0}", true)['h'];
  });

  return ['movies' => $movies, 'upload_id' => $upload_id, 'upload_count' => count($new_ids)];
}