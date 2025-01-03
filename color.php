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

$image_size = 22; // 92
$border_width = 5;

?>
<html>
    <head>
      <style>
        body {
          margin: 0;
        }
        .poster {
          display: inline-block;
          padding: <?php echo $border_width; ?>px;
          position: absolute;

          img {
            width: <?php echo $image_size; ?>px;
          }

          span {
            height: 10px;
            display: inline-block;
          }
        }
        </style>
        <script>
          let centerX, centerY, radiusScale;

          function relocate(poster) {
            let radius = radiusScale * poster.getAttribute('data-radius');
            let angle = poster.getAttribute('data-angle');
            const x = centerX + radius * Math.cos(angle);
            const y = centerY + radius * Math.sin(angle);
            poster.style.left = `${x}px`; // Adjust for poster's center
            poster.style.top = `${y}px`;  // Adjust for poster's center
          }
          
          document.addEventListener('DOMContentLoaded', function() {
            const width = window.innerWidth;
            const height = window.innerHeight;
            
            if (width > height) {
              radiusScale = (height / 2 - <?php echo $image_size * 138 / 92 / 2; ?>) -50;
            } else {
              radiusScale = (width / 2 - <?php echo $image_size / 2; ?>) - 50;
            }

            centerX = (width / 2) - <?php echo ($image_size + $border_width * 2) / 2 ?>;
            centerY = (height / 2) - <?php echo ($image_size * 138 / 92 + $border_width * 2) / 2 ?>;

            document.querySelectorAll('.poster').forEach(poster => {
              relocate(poster);
            });
          });
        </script>
</head>
    <body><?php

$PDO = getDatabase();
$stmt = $PDO->prepare("SELECT poster, primary_color FROM movies ORDER BY RAND() LIMIT 500");
$stmt->execute();
$posters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// include_once("raw_color.php");
// $ex = new GetMostCommonColors();

// for ($h = 0; $h < 360; $h+=5) {
//   for ($s = 0; $s <= 100; $s+=10) {
//     for ($l = 0; $l <= 100; $l+=10) {
//       $hsl = ['h' => $h, 's' => $s / 100.0, 'l' => $l / 100.0];
//       $angle = $hsl['h'] / 360.0 * 2 * M_PI; // Convert Hue to radians
//       $radius = (1 - $hsl['l']); // * (1 + 0.2 * (1 - $hsl['l']));    // Inverse of Lightness

//       echo '<div class="poster" style="background-color: hsl(' . $hsl['h'] . ', '  . $hsl['s'] * 100 . '%, '  . $hsl['l'] * 100 . '%)" data-angle="' . $angle . '" data-radius="' . $radius . '">';
//       echo '</div>';
//     }
//   }
// }

foreach ($posters as $poster) {
  $hsl = json_decode($poster['primary_color'], true);
  $poster = 'https://image.tmdb.org/t/p/w92' . $poster['poster'];

  // $dominant_colors = $ex->Get_Color($poster, 5, true, true, 24);

  // $rgb = getDominantColor($poster);
  // $hsl = rgbToHsl($rgb['r'], $rgb['g'], $rgb['b']);
  $angle = ($hsl['h'] + mt_rand() / mt_getrandmax() * 180) / 360.0 * 2 * M_PI; // Convert Hue to radians
  $radius = (1 - $hsl['s'] / 0.927); // (1 - $hsl['s']) * (1 + 0.2 * (1 - $hsl['l']));    // Inverse of Lightness

  echo '<div class="poster" style="background-color: hsl(' . $hsl['h'] . ', '  . $hsl['s'] * 100 . '%, '  . $hsl['l'] * 100 . '%)" data-angle="' . $angle . '" data-radius="' . $radius . '">';
  echo '<img src="' . $poster .'" /><br>';
  // foreach ($dominant_colors as $dominant_color => $p) {
  //   echo '<span style="background-color: #' . $dominant_color . '; width: ' . $p * 100 . '%"></span>';
  // }
  echo '</div>';
}
?>
</body></html>