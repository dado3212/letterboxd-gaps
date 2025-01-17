<?php
require_once("tmdb.php");
require_once("secret.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

function getDominantColor($imagePath) {
  $image = imagecreatefromjpeg($imagePath);
  $width = imagesx($image);
  $height = imagesy($image);

  $rTotal = $gTotal = $bTotal = $pixelCount = 0;

  for ($x = 0; $x < $width; $x++) {
    for ($y = 0; $y < $height; $y++) {
      $rgb = imagecolorat($image, $x, $y);
      $r = ($rgb >> 16) & 0xFF;
      $g = ($rgb >> 8) & 0xFF;
      $b = $rgb & 0xFF;

      $rTotal += $r;
      $gTotal += $g;
      $bTotal += $b;
      $pixelCount++;
    }
  }

  imagedestroy($image);

  return [
    'r' => round($rTotal / $pixelCount),
    'g' => round($gTotal / $pixelCount),
    'b' => round($bTotal / $pixelCount)
  ];
}

function rgbToHsl($r, $g, $b) {
  $r /= 255;
  $g /= 255;
  $b /= 255;

  $max = max($r, $g, $b);
  $min = min($r, $g, $b);
  $delta = $max - $min;

  $h = 0;
  if ($delta > 0) {
    if ($max === $r) {
      $h = 60 * fmod((($g - $b) / $delta), 6);
    } elseif ($max === $g) {
      $h = 60 * (($b - $r) / $delta + 2);
    } else {
      $h = 60 * (($r - $g) / $delta + 4);
    }
  }

  $l = ($max + $min) / 2;
  $s = $delta == 0 ? 0 : $delta / (1 - abs(2 * $l - 1));

  return [
    'h' => ($h < 0 ? $h + 360 : $h),
    's' => $s,
    'l' => $l
  ];
}

// Get up to 50 that aren't being worked on right now
$PDO = getDatabase();
$sql = "SELECT id, letterboxd_url, movie_name, `year` FROM movies WHERE status = 'pending' LIMIT 50";
$stmt = $PDO->prepare($sql);
$stmt->execute();
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
$PDO = null;

if (empty($movies)) {
  echo 'finished';
  exit;
}

$results = []; // Store results from all threads
$pipes = [];   // For interprocess communication
foreach ($movies as $movie) {
  $pipe = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
  $pid = pcntl_fork();

  if ($pid == -1) {
    die("Could not fork process.\n");
  } elseif ($pid === 0) {
    // Child process
    fclose($pipe[0]); // Close parent pipe in the child process
    $result = getInfo($movie['letterboxd_url'], $movie['movie_name'], $movie['year']);
    $result['id'] = $movie['id'];

    $poster = $result['poster'];
    $rgb = getDominantColor($poster);
    $hsl = rgbToHsl($rgb['r'], $rgb['g'], $rgb['b']);
    $result['primary_color'] = json_encode($hsl);

    fwrite($pipe[1], json_encode($result)); // Send result to the parent
    fclose($pipe[1]);
    exit; // Exit child process
  } else {
    // Parent process
    fclose($pipe[1]); // Close child pipe in the parent process
    $pipes[$pid] = $pipe[0];
  }
}

// Parent process collects results
foreach ($pipes as $pid => $pipe) {
  $data = stream_get_contents($pipe);
  fclose($pipe);
  $results[] = json_decode($data, true); // Decode JSON result
  pcntl_waitpid($pid, $status); // Wait for the child process to exit
}

// Perform a single batch update
// Collect results for batch update
$updates = []; // Array to hold update values
$ids = []; // List of IDs to update

foreach ($results as $movie) {
  $updates[] = [
    'id' => $movie['id'],
    'tmdb_id' => $movie['tmdb_id'],
    'poster' => $movie['poster'],
    'language' => $movie['language'],
    'imdb_id' => $movie['imdb_id'],
    'countries' => json_encode(array_values($movie['production_countries'])),
    'has_female_director' => $movie['has_female_director'] ? 1 : 0,
    'primary_color' => $movie['primary_color'],
  ];
  $ids[] = $movie['id'];
}

// Build the `UPDATE` query with `CASE` statements
$sql = "
  UPDATE letterboxd.movies
  SET
    status = 'done',
    tmdb_id = CASE id
      " . implode("\n", array_map(fn($u) => "WHEN :id_{$u['id']}_1 THEN :tmdb_id_{$u['id']}", $updates)) . "
    END,
    poster = CASE id
      " . implode("\n", array_map(fn($u) => "WHEN :id_{$u['id']}_2 THEN :poster_{$u['id']}", $updates)) . "
    END,
    language = CASE id
      " . implode("\n", array_map(fn($u) => "WHEN :id_{$u['id']}_3 THEN :language_{$u['id']}", $updates)) . "
    END,
    imdb_id = CASE id
      " . implode("\n", array_map(fn($u) => "WHEN :id_{$u['id']}_4 THEN :imdb_id_{$u['id']}", $updates)) . "
    END,
    countries = CASE id
      " . implode("\n", array_map(fn($u) => "WHEN :id_{$u['id']}_5 THEN :countries_{$u['id']}", $updates)) . "
    END,
    has_female_director = CASE id
      " . implode("\n", array_map(fn($u) => "WHEN :id_{$u['id']}_6 THEN :has_female_director_{$u['id']}", $updates)) . "
    END,
    primary_color = CASE id
      " . implode("\n", array_map(fn($u) => "WHEN :id_{$u['id']}_7 THEN :primary_color_{$u['id']}", $updates)) . "
    END
  WHERE id IN (" . implode(', ', array_map(fn($id) => ":id_{$id}_8", $ids)) . ")
";

$PDO = getDatabase();
// Prepare and bind values
$stmt = $PDO->prepare($sql);

foreach ($updates as $u) {
  $stmt->bindValue(":tmdb_id_{$u['id']}", $u['tmdb_id'], PDO::PARAM_INT);
  $stmt->bindValue(":poster_{$u['id']}", $u['poster'], PDO::PARAM_STR);
  $stmt->bindValue(":language_{$u['id']}", $u['language'], PDO::PARAM_STR);
  $stmt->bindValue(":imdb_id_{$u['id']}", $u['imdb_id'], PDO::PARAM_STR);
  $stmt->bindValue(":countries_{$u['id']}", $u['countries'], PDO::PARAM_STR);
  $stmt->bindValue(":has_female_director_{$u['id']}", $u['has_female_director'], PDO::PARAM_INT);
  $stmt->bindValue(":primary_color_{$u['id']}", $u['primary_color'], PDO::PARAM_STR);
}
for ($i = 1; $i <= 8; $i++) {
  foreach ($ids as $id) {
    $stmt->bindValue(":id_{$id}_{$i}", $id, PDO::PARAM_INT);
  }
}

$stmt->execute();
echo 'more';

?>