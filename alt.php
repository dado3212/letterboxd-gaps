<?php

require_once("secret.php");
require_once("tmdb.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<html>
    <head>
      <style>
        span {
          width: 30px;
          height: 30px;
          display: inline-block;
        }
        </style>
    </head>
    <body>
      <div class="title">Letterboxd</div>
      <?php

$numPosters = 5;

$PDO = getDatabase();
$stmt = $PDO->prepare("SELECT poster, primary_color, tmdb_id, movie_name FROM movies LIMIT $numPosters"); // ORDER BY RAND()
$stmt->execute();
$posters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Actually use squared euclidian distance, because we really just need a sense of distance
// (and this is faster)
function squaredEuclideanDistance($point1, $point2) {
  $dx = $point1[0] - $point2[0];
  $dy = $point1[1] - $point2[1];
  $dz = $point1[2] - $point2[2];

  return $dx * $dx + $dy * $dy + $dz * $dz;
}

function meanShift($points, $radius = 30, $maxIterations = 10) {
  $shiftedPoints = $points;
  
  for ($iteration = 0; $iteration < $maxIterations; $iteration++) {
    $newPoints = [];
    $changes = 0;

    foreach ($shiftedPoints as $point) {
      $neighbors = [];

      foreach ($points as $neighbor) {
        if (squaredEuclideanDistance($point, $neighbor) < $radius * $radius) {
          $neighbors[] = $neighbor;
        }
      }

      if (count($neighbors) > 0) {
        $mean = array_reduce($neighbors, function ($carry, $item) {
          return [$carry[0] + $item[0], $carry[1] + $item[1], $carry[2] + $item[2]];
        }, [0, 0, 0]);

        $mean = [$mean[0] / count($neighbors), $mean[1] / count($neighbors), $mean[2] / count($neighbors)];

        if (squaredEuclideanDistance($mean, $point) > 1) {
          $changes++;
        }

        $newPoints[] = $mean;
      } else {
        $newPoints[] = $point;
      }
    }

    $shiftedPoints = $newPoints;

    if ($changes == 0) break; // Converged
  }

  return extractClusterCenters($shiftedPoints, $radius);
}

function extractClusterCenters($points, $radius) {
  $centers = [];

  foreach ($points as $point) {
    $found = false;

    foreach ($centers as &$center) {
      if (squaredEuclideanDistance($center, $point) < $radius * $radius) {
        $center = [
          ($center[0] + $point[0]) / 2,
          ($center[1] + $point[1]) / 2,
          ($center[2] + $point[2]) / 2
        ];
        $found = true;
        break;
      }
    }

    if (!$found) {
      $centers[] = $point;
    }
  }

  return array_map(fn($c) => array_map('round', $c), $centers); // Round RGB values
}

// Perform Mean Shift Clustering


function getDominantColor($imagePath) {
  $image = imagecreatefromjpeg($imagePath);
$width = imagesx($image);
$height = imagesy($image);

// Target height (e.g., 50px)
$newHeight = 50;
$newWidth = intval(($width / $height) * $newHeight);

// Resize the image
$resizedImage = imagecreatetruecolor($newWidth, $newHeight);
imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

// Extract colors from resized image
  $colors = [];

  for ($x = 0; $x < $newWidth; $x++) {
    for ($y = 0; $y < $newHeight; $y++) {
      $rgb = imagecolorat($resizedImage, $x, $y);
      $r = ($rgb >> 16) & 0xFF;
      $g = ($rgb >> 8) & 0xFF;
      $b = $rgb & 0xFF;

      $colors[] = [$r, $g, $b];
    }
  }

  // Free memory
  imagedestroy($image);
  imagedestroy($resizedImage);

  echo count($colors);

  echo '<br>';

  $clusters = meanShift($colors, 50, 25);

  echo var_export($clusters);

  echo count($clusters);

  foreach ($clusters as $cluster) {
    echo "<span style='background-color: rgb($cluster[0], $cluster[1], $cluster[2]);'></span>";
  }

  echo "<img src='$imagePath'>";

  return [
    // 'r' => round($rTotal / $pixelCount),
    // 'g' => round($gTotal / $pixelCount),
    // 'b' => round($bTotal / $pixelCount)
  ];
}

foreach ($posters as $poster) {
  echo var_export(getDominantColor($poster['poster']));
  // $hsl = json_decode($poster['primary_color'], true);
  // $tmdb_id = $poster['tmdb_id'];
  // $movie_name = $poster['movie_name'];
  // $poster = $poster['poster'];

  // $angle = ($hsl['h']) / 360.0 * 2 * M_PI; // Convert Hue to radians
  // $radius = (1 - $hsl['s'] / 0.927); // (1 - $hsl['s']) * (1 + 0.2 * (1 - $hsl['l']));    // Inverse of Lightness
  // echo '<div class="poster" '.
  // 'style="background-color: hsl(' . $hsl['h'] . ', '  . $hsl['s'] * 100 . '%, '  . $hsl['l'] * 100 . '%)" '.
  // 'data-angle="' . $angle . '" '.
  // 'data-radius="' . $radius . '" '.
  // 'data-hue="' . $hsl['h'] . '" '.
  // 'data-saturation="' . $hsl['s'] . '" '.
  // 'data-lightness="' . $hsl['l'] . '" '.
  // '>';
  // echo '<img src="' . $poster .'" alt="' . $movie_name . '" /><br>';
  // echo $tmdb_id;

  // echo '</div>';
}
?>
    </body>
    </html>