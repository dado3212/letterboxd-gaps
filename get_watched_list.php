<?php
require_once "tmdb.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
  $file = $_FILES['file'];

  if (pathinfo($file['name'], PATHINFO_EXTENSION) === 'csv') {
    $error = handleWatchlist($file);
    if ($error === null) {
      http_response_code(400);
      echo 'The CSV file is empty or invalid.';
      exit;
    }
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

    $headers = fgetcsv($handle);

    if ($headers === false) {
      return null;
    }

    $type = null; // watchlist, diary, reviews, list
    $list_name = null;
    // Watchlist, watched, and likes/films
    if ($headers === ['Date', 'Name', 'Year', 'Letterboxd URI']) {
      $type = 'watchlist';
    } else if ($headers === [
      'Date',
      'Name',
      'Year',
      'Letterboxd URI', // review URI, so we don't want this
      'Rating',
      'Rewatch',
      'Tags',
      'Watched Date'
    ]) {
      $type = 'diary';
    } else if ($headers === [
      'Date',
      'Name',
      'Year',
      'Letterboxd URI', // review URI, so we don't want this
      'Rating',
      'Rewatch',
      'Review',
      'Tags',
      'Watched Date'
    ]) {
      $type = 'reviews';
    } else if ($headers === ['Letterboxd list export v7']) {
      $type = 'list';
      fgetcsv($handle);
      $list_name = fgetcsv($handle)[1]; // Get the list name
      fgetcsv($handle);
      $headers = fgetcsv($handle);
    } else {
      // Malformed
      return null;
    }

    // Process each row of the CSV
    while (($row = fgetcsv($handle)) !== false) {
      $data[] = array_combine($headers, $row);
    }

    fclose($handle);

    // Example: Return JSON response
    header('Content-Type: application/json');
    echo json_encode(handleMovies($data, $type, $list_name));
    return true;
  } else {
    return null;
  }
}

function handleZip($file) {
  // Open the .zip file directly from the uploaded file stream
  $zip = new ZipArchive();
  if ($zip->open($file['tmp_name']) === true) {
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

// TODO: diary/reviews is slow because name/id isn't indexed
// $type = watchlist, diary, reviews, list
function handleMovies($watchlistMovies, $type, $list_name = null) {
  if (count($watchlistMovies) === 0) {
    return ['movies' => [], 'upload_id' => null, 'upload_count' => 0];
  }

  // We have the link to the review, not to the movie. Don't try and process
  // these and upload them, because it's expensive to scrape. We'll do best
  // effort based on the name + year combination. For the most part this should
  // only be hit as part of the .zip upload, so these should be uploaded by 
  // watched, making this ignorable
  if ($type == 'diary' || $type == 'reviews') {
    $params = [];
    foreach ($watchlistMovies as $movie) {
      $params[] = $movie['Name'];
      $params[] =  $movie['Year'];
    }
    // Check if the URL has been uploaded to the database already
    $PDO = getDatabase();
    $placeholders = implode(' OR ', array_fill(0, count($watchlistMovies), '(movie_name = ? AND year = ?)'));
    $stmt = $PDO->prepare("SELECT * FROM movies WHERE $placeholders");
    $stmt->execute($params);

    $rawMovieInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $serverMovieInfo = [];
    foreach ($rawMovieInfo as $rawMovie) {
      $serverMovieInfo[$rawMovie['movie_name'] . '-' . $rawMovie['year']] = $rawMovie;
    }

    $movies = [];
    foreach ($watchlistMovies as $movie) {
      $key = $movie['Name'] . '-' . $movie['Year'];
      if (array_key_exists($key, $serverMovieInfo)) {
        $movieInfo = $serverMovieInfo[$key];
        $movieInfo['countries'] = json_decode($movieInfo['countries']);
        $movies[] = $movieInfo;
      } else {
        // If it's NOT in the database, we're not adding it, see above
        $movies[] = [
          'movie_name' => $movie['Name'],
          'year' => $movie['Year'],
          'letterboxd_url' => $movie['Letterboxd URI'], // this is the review URL, but I think it's fine
          'status' => 'pending',
        ];
      }
    }

    usort($movies, function ($a, $b) {
      return (float) json_decode($a['primary_color'] ?? "{'h': 0}", true)['h'] <=> (float) json_decode($b['primary_color'] ?? "{'h': 0}", true)['h'];
    });

    return ['movies' => $movies, 'upload_id' => null, 'upload_count' => 0];
  }

  // If this is a list, map it accordingly
  if ($type === 'list') {
    foreach ($watchlistMovies as $key => $movie) {
      $watchlistMovies[$key]['Letterboxd URI'] = $movie['URL'];
      unset($watchlistMovies[$key]['URL']);
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

  usort($movies, function ($a, $b) {
    return (float) json_decode($a['primary_color'] ?? "{'h': 0}", true)['h'] <=> (float) json_decode($b['primary_color'] ?? "{'h': 0}", true)['h'];
  });

  return ['movies' => $movies, 'upload_id' => $upload_id, 'upload_count' => count($new_ids)];
}
