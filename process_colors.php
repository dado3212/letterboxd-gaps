<?php
require_once("secret.php");
require_once("tmdb.php");

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

$PDO = getDatabase();
# How did we get 3 with no poster?
$stmt = $PDO->prepare("SELECT id, poster FROM movies WHERE primary_color IS NULL AND poster IS NOT NULL LIMIT 250");
$stmt->execute();
$posters = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($posters) === 0) {

  die('No more to process!');
}

$new_colors = [];
foreach ($posters as $poster) {
  $id = $poster['id'];
  $poster = $poster['poster'];
  $rgb = getDominantColor($poster);
  $hsl = rgbToHsl($rgb['r'], $rgb['g'], $rgb['b']);

  $new_colors[$id] = $hsl;
}

$ids = implode(',', array_keys($new_colors));
$sql = "UPDATE movies SET primary_color = CASE id ";

$combine = [];
foreach ($new_colors as $id => $hsl) {
  $sql .= "WHEN $id THEN ? ";
  $combine[] = json_encode($hsl);
}

$sql .= "END WHERE id IN ($ids)";

$stmt = $PDO->prepare($sql);
$stmt->execute($combine);

var_export($new_colors);
var_export($sql);