<?php
require_once "tmdb.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Best effort. If you're too big, sorry :/
ini_set('memory_limit', '512M');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
  $file = $_FILES['file'];

  if (pathinfo($file['name'], PATHINFO_EXTENSION) === 'zip' && str_starts_with($file['name'], 'letterboxd')) {
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
    $all_data = [];
    $diary = [];
    $lists = [];
    $all_new_ids = [];
    $to_upload = [];
    $has_pending = false;
    foreach ($files as $file) {
      /*
        Handle
        watched.csv 
        diary.csv
        watchlist.csv
        lists/<whatever>
      */
      $result = null;
      if (str_starts_with($file['name'], 'lists/')) {
        $result = processCsvContent($file['content']);
        $lists[] = $result;
      } else if ($file['name'] === 'diary.csv') {
        $result = processCsvContent($file['content']);
        // Split by year
        $result['name'] = 'All Time';
        $diary[] = $result;

        $years = [];
        foreach ($result['movies'] as $movie) {
          $watched_year = $movie['watched_year'];
          if (array_key_exists($watched_year, $years)) {
            $years[$watched_year][] = $movie;
          } else {
            $years[$watched_year] = [$movie];
          }
        }
        krsort($years);
        foreach ($years as $year => $movies) {
          $diary[] = [
            'movies' => $movies,
            'name' => $year
          ];
        }
      } else if ($file['name'] === 'watched.csv') {
        $result = processCsvContent($file['content'], null, 'Watched');
        $all_data[] = $result;
      } else if ($file['name'] === 'watchlist.csv') {
        $result = processCsvContent($file['content'], null, 'Watchlist');
        $all_data[] = $result;
      }
      if ($result !== null) {
        $has_pending = $has_pending || ($result['has_pending'] ?? false);
        foreach ($result['new_ids'] ?? [] as $i) {
          $all_new_ids[$i] = 1;
        }
        foreach ($result['to_upload'] ?? [] as $u) {
          $to_upload[$u[1] . '-' . $u[2]] = $u;
        }
      }
    }
    if (count($lists) > 0) {
      $all_data[] = [
        'name' => 'Lists',
        'type' => 'group',
        'sublists' => $lists,
      ];
    }
    if (count($diary) > 0) {
      $all_data[] = [
        'name' => 'Diary',
        'type' => 'group',
        'sublists' => $diary,
      ];
    }
    $countries = getCountryData();
    $languages = getLanguageData();
    $upload_id = uploadData($all_new_ids, $to_upload);
    header('Content-Type: application/json');
    header('Content-Encoding: gzip');
    echo gzencode(json_encode([
      'movies' => $all_data,
      'countries' => $countries,
      'languages' => $languages,
      'upload_id' => $upload_id,
      'should_upload' => count($to_upload) > 0 || $has_pending,
    ]));
    return true;
  } else {
    http_response_code(500);
    echo 'Failed to open .zip file.';
  }
}

function processCsvContent($content, $type = null, $listName = null) {
  // Source is a string
  $lines = array_map('str_getcsv', explode("\n", $content));

  // Separate headers and rows
  $headers = array_shift($lines);

  if ($headers === false) {
    return null; // Malformed CSV
  }

  // Get the proper headers and type
  if ($type === null) {
    // Determine type based on headers
    if ($headers === ['Date', 'Name', 'Year', 'Letterboxd URI']) {
      $type = 'watchlist';
    } else if ($headers === [
      // Letterboxd URI is the review URI, so not useful
      'Date', 'Name', 'Year', 'Letterboxd URI', 'Rating', 'Rewatch', 'Tags', 'Watched Date'
    ]) {
      $type = 'diary';
    } else if ($headers === [
      // Letterboxd URI is the review URI, so not useful
      'Date', 'Name', 'Year', 'Letterboxd URI', 'Rating', 'Rewatch', 'Review', 'Tags', 'Watched Date'
    ]) {
      $type = 'reviews';
    } else if ($headers === ['Letterboxd list export v7']) {
      $type = 'list';
      $listName = $lines[1][1] ?? null;
      // Iterate until you hit the real data
      while ($current = array_shift($lines)) {
        if (count($current) === 5 && $current[0] === 'Position') {
          $headers = $current;
          break;
        }
      }
    } else {
      return null; // Unsupported format
    }
  }

  // Convert rows into associative arrays
  $data = array_map(
    fn($line) => count($line) === count($headers) ? array_combine($headers, $line) : null,
    $lines
  );
  $data = array_filter($data);

  // Pass processed data to handler
  $movies = handleMovies($data, $type, $listName);

  // Unset data that the client doesn't need
  foreach ($movies['movies'] as &$movie) {
    unset($movie['primary_color']);
    unset($movie['status']);
    unset($movie['imdb_id']);
    unset($movie['tmdb_id']);
    unset($movie['id']);
  }

  return $movies;
}

function uploadData($allNewIDs, $toUpload) {
  $PDO = getDatabase();

  $allNewIDs = array_keys($allNewIDs);
  if (count($toUpload) > 0) {
    $placeholders = [];
    $bindValues = [];
    foreach ($toUpload as $_ => $info) {
      $placeholders[] = '(' . implode(',', array_fill(0, count($info), '?')) . ')';
      $bindValues = array_merge($bindValues, $info);
    }

    $sql = "INSERT INTO movies
    (letterboxd_url, movie_name, `year`)
    VALUES " . implode(', ', $placeholders);
    $stmt = $PDO->prepare($sql);
    $stmt->execute($bindValues);

    // Keep track of all of the rows that we're now processing
    $first_id = $PDO->lastInsertId();
    // Assume they're all sequential?
    for ($i = 0; $i < count($toUpload); $i++) {
      $allNewIDs[] = $first_id + $i;
    }
  }

  // If any of the other IDs
  $upload_id = null;
  if (!empty($allNewIDs)) {
    $sql = "INSERT INTO upload_tracking
    (uploaded)
    VALUES (?)";
    $stmt = $PDO->prepare($sql);
    $stmt->execute([json_encode($allNewIDs)]);

    $upload_id = $PDO->lastInsertId();
  }

  return $upload_id;
}

// Gets the cached country data (updated daily by cronjob)
function getCountryData() {
  $PDO = getDatabase();
  $stmt = $PDO->prepare("SELECT * FROM countries");
  $stmt->execute();
  $rawCountries = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $countries = [];
  foreach ($rawCountries as $rawCountry) {
    $countries[$rawCountry['country_code']] = [
      'num_movies' => $rawCountry['num_movies'],
      'url' => $rawCountry['url'],
      'full_name' => $rawCountry['full_name'],
    ];
  }
  return $countries;
}

// Gets the cached country data (updated daily by cronjob)
function getLanguageData() {
  $PDO = getDatabase();
  $stmt = $PDO->prepare("SELECT * FROM languages");
  $stmt->execute();
  $rawLanguages = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $languages = [];
  foreach ($rawLanguages as $rawLanguage) {
    $languages[$rawLanguage['language_code']] = [
      'num_movies' => $rawLanguage['num_movies'],
      'url' => $rawLanguage['url'],
      'full_name' => $rawLanguage['full_name'],
    ];
  }
  return $languages;
}

// $type = watchlist, diary, reviews, list
function handleMovies($watchlistMovies, $type, $list_name = null) {
  if (count($watchlistMovies) === 0) {
    return ['movies' => [], 'new_ids' => null, 'to_upload' => null];
  }

  $color_sorting = function ($a, $b) {
    return fmod($a['primary_color'] + 30, 360) <=> fmod($b['primary_color'] + 30, 360);
  };

  // Dedupe movies (better for uploading and rendering)
  $deduped = [];
  foreach ($watchlistMovies as $movie) {
    $key = $movie['Name'] . '-' . $movie['Year'];
    if (!isset($deduped[$key])) {
      $deduped[$key] = $movie;
    }
  }

  $watchlistMovies = array_values($deduped);

  // These are separately handled because we only have the link to the review,
  // not to the movie. Don't try and upload any as it will be handled by 'watched'.
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

    // Make it indexable
    $serverMovieInfo = [];
    foreach ($rawMovieInfo as $rawMovie) {
      $serverMovieInfo[$rawMovie['movie_name'] . '-' . $rawMovie['year']] = $rawMovie;
    }

    $movies = [];
    foreach ($watchlistMovies as $movie) {
      $key = $movie['Name'] . '-' . $movie['Year'];
      // If it's uploaded (done) then return the data
      if (array_key_exists($key, $serverMovieInfo) && $serverMovieInfo[$key]['status'] == 'done') {
        $movieInfo = $serverMovieInfo[$key];
        $movieInfo['countries'] = json_decode($movieInfo['countries']);
        $movieInfo['watched_year'] = substr($movie['Watched Date'], 0, 4);
        $movies[] = $movieInfo;
      } else {
        // If it's NOT in the database, we're not adding it, see above
        $movies[] = [
          'movie_name' => $movie['Name'],
          'year' => $movie['Year'],
          'letterboxd_url' => $movie['Letterboxd URI'], // this is the review URL, but I think it's fine
          'watched_year' => substr($movie['Watched Date'], 0, 4),
          'status' => 'pending',
        ];
      }
    }

    // One-off color index (to avoid continually calling `json_decode` within usort)
    foreach ($movies as &$movie) {
      $movie['primary_color'] = (float) json_decode($movie['primary_color'] ?? '{"h": 0}', true)['h'];
    }
    usort($movies, $color_sorting);

    return ['movies' => $movies, 'new_ids' => null, 'to_upload' => null, 'name' => $list_name ?? $type];
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
  $newIds = [];
  $hasPending = false;
  foreach ($watchlistMovies as $movie) {
    // If it's already in the database then we can return the data directly
    if (array_key_exists($movie['Letterboxd URI'], $serverMovieInfo)) {
      $movieInfo = $serverMovieInfo[$movie['Letterboxd URI']];
      // But if it's already in the database but it's still pending, then add it
      // to the new ids list for the purpose of polling. Should only apply to
      // disconnects, but if there was more site traffic there could be more
      // overlap in people uploading things. Hoping that with enough seed data
      // the amount of live fetching we're doing is minimal.
      if ($movieInfo['status'] != 'done') {
        $newIds[] = $movieInfo['id'];
        if ($movieInfo['status'] === 'pending') {
          $hasPending = true;
        }
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
      $hasPending = true;
    }
  }

  // One-off color index (to avoid continually calling `json_decode` within usort)
  foreach ($movies as &$movie) {
    $movie['primary_color'] = (float) json_decode($movie['primary_color'] ?? '{"h": 0}', true)['h'];
  }
  usort($movies, $color_sorting);

  return ['movies' => $movies, 'new_ids' => $newIds, 'has_pending' => $hasPending, 'to_upload' => $toUpload, 'name' => $list_name ?? $type];
}
