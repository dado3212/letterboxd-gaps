<?php
require_once("secret.php");
require_once("tmdb.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    <body>
      <?php

$PDO = getDatabase();
$stmt = $PDO->prepare("SELECT poster, primary_color FROM movies ORDER BY RAND() LIMIT 500");
$stmt->execute();
$posters = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($posters as $poster) {
  $hsl = json_decode($poster['primary_color'], true);
  $poster = 'https://image.tmdb.org/t/p/w92' . $poster['poster'];

  $angle = ($hsl['h'] + mt_rand() / mt_getrandmax() * 180) / 360.0 * 2 * M_PI; // Convert Hue to radians
  $radius = (1 - $hsl['s'] / 0.927); // (1 - $hsl['s']) * (1 + 0.2 * (1 - $hsl['l']));    // Inverse of Lightness

  echo '<div class="poster" style="background-color: hsl(' . $hsl['h'] . ', '  . $hsl['s'] * 100 . '%, '  . $hsl['l'] * 100 . '%)" data-angle="' . $angle . '" data-radius="' . $radius . '">';
  echo '<img src="' . $poster .'" /><br>';
  echo '</div>';
}
?>
</body></html>