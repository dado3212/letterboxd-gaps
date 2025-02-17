<?php
require_once("secret.php");
require_once("tmdb.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

return;

?>
<html>
    <head>
      <style>
      .title {
        font-size: 2em;
        font-family: Georgia, serif;
        position: relative;
        left: 29px;
        top: -6px;
      }
        body {
          margin: 0;
          display: flex;
          justify-content: center;
          align-items: center;
        }
        .poster {
          display: inline-block;
          position: absolute;
          color: white;
          font-size: 0px;

          span {
            height: 10px;
            display: inline-block;
          }
        }
        </style>
</head>
    <body>
      <div class="title">Letterboxd</div>
      <?php

$numPosters = 1800;

$PDO = getDatabase();
$stmt = $PDO->prepare("SELECT poster, primary_color, tmdb_id, movie_name FROM movies LIMIT $numPosters"); // ORDER BY RAND()
$stmt->execute();
$posters = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($posters as $poster) {
  $hsl = json_decode($poster['primary_color'], true);
  $tmdb_id = $poster['tmdb_id'];
  $movie_name = $poster['movie_name'];
  $poster = $poster['poster'];

  $angle = ($hsl['h']) / 360.0 * 2 * M_PI; // Convert Hue to radians
  $radius = (1 - $hsl['s'] / 0.927); // (1 - $hsl['s']) * (1 + 0.2 * (1 - $hsl['l']));    // Inverse of Lightness
  echo '<div class="poster" '.
  'style="background-color: hsl(' . $hsl['h'] . ', '  . $hsl['s'] * 100 . '%, '  . $hsl['l'] * 100 . '%)" '.
  'data-angle="' . $angle . '" '.
  'data-radius="' . $radius . '" '.
  'data-hue="' . $hsl['h'] . '" '.
  'data-saturation="' . $hsl['s'] . '" '.
  'data-lightness="' . $hsl['l'] . '" '.
  '>';
  echo '<img src="' . $poster .'" alt="' . $movie_name . '" /><br>';
  echo $tmdb_id;

  echo '</div>';
}
?>
<script>

function hslToLab(h, s, l) {
  // Step 1: Convert HSL to RGB
  function hslToRgb(h, s, l) {
    s /= 100;
    l /= 100;

    const k = n => (n + h / 30) % 12;
    const a = s * Math.min(l, 1 - l);
    const f = n => l - a * Math.max(-1, Math.min(k(n) - 3, Math.min(9 - k(n), 1)));

    return [f(0), f(8), f(4)].map(v => Math.round(v * 255));
  }

  const [r, g, b] = hslToRgb(h, s, l);

  // Step 2: Convert RGB to XYZ
  function rgbToXyz(r, g, b) {
    const normalize = v => {
      v /= 255;
      return v <= 0.04045 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
    };

    const [rn, gn, bn] = [normalize(r), normalize(g), normalize(b)];

    return [
      rn * 0.4124564 + gn * 0.3575761 + bn * 0.1804375,
      rn * 0.2126729 + gn * 0.7151522 + bn * 0.0721750,
      rn * 0.0193339 + gn * 0.1191920 + bn * 0.9503041,
    ];
  }

  const [x, y, z] = rgbToXyz(r, g, b).map(v => v / 100);

  // Step 3: Convert XYZ to LAB
  function xyzToLab(x, y, z) {
    const refX = 0.95047;
    const refY = 1.00000;
    const refZ = 1.08883;

    const f = t =>
      t > 0.008856 ? Math.cbrt(t) : (7.787 * t) + (16 / 116);

    const l = (116 * f(y / refY)) - 16;
    const a = 500 * (f(x / refX) - f(y / refY));
    const b = 200 * (f(y / refY) - f(z / refZ));

    return [l, a, b];
  }

  return xyzToLab(x, y, z);
}

  function calculateBestFit(w, h, n, ratio) {
    let bestWidth = 0;
    let bestColumns = 0;
    let bestRows = 0;

    for (let c = 1; c <= n; c++) {
      let r = Math.ceil(n / c); // Calculate rows for current column count
      const x = Math.min(w / (c * 1), h / (r * ratio)); // Calculate the scale-independent width x
      const y = x * ratio; // Corresponding height
      if (c * x <= w && r * y <= h) { // Check if the configuration fits
        if (x > bestWidth) { // Update if this scale is better
          bestWidth = x;
          bestColumns = c;
          bestRows = r;
        }
      }
    }

    return {
      imageWidth: bestWidth,
      numRows: bestRows,
      numCols: bestColumns,
    };
  }

  const numPosters = <?php echo count($posters); ?>;
  let centerX, centerY, radiusScale;
  const width = window.innerWidth;
  const height = window.innerHeight;
  const ratio = 138/92;

  // +5 for the letterboxd logo
  const { imageWidth, numRows, numCols } = calculateBestFit(width, height, numPosters + 5, ratio);
  const imageHeight = imageWidth * ratio;
  const borderWidth = 0;

  var styleSheet = document.createElement("style");
  styleSheet.textContent = `
  .poster {
    width: ${imageWidth}px;
    height: ${imageHeight}px;
  }
  img {
      // display: none;
      width: 100%;
} 
  `;
  document.head.appendChild(styleSheet);
  
  if (width > height) {
    radiusScale = (height / 2 - imageHeight / 2) -50;
  } else {
    radiusScale = (width / 2 - imageWidth / 2) - 50;
  }

  centerX = (width / 2) - (imageWidth + borderWidth) / 2;
  centerY = (height / 2) - (imageHeight + borderWidth * 2) / 2;

  // For each poster calculate its desired position
  // Then iterate over the locations, choose the best poster, and place it
  let posters = [];

  // Preprocess each poster to get targeted location and get a sense for volume by hue
  const numBuckets = 60;
  let hueBuckets = {};
  let hueRange = [];
  document.querySelectorAll('.poster').forEach(poster => {
    let radius = radiusScale * poster.getAttribute('data-radius');
    let angle = poster.getAttribute('data-angle');
    const x = centerX + radius * Math.cos(angle);
    const y = centerY + radius * Math.sin(angle);

    const hue = poster.getAttribute('data-hue');
    const bucketizedHue = Math.floor(hue / 360 * numBuckets) * 360 / numBuckets;
    hueRange.push(bucketizedHue);
    const f = {
      x,
      y,
      poster,
      h: poster.getAttribute('data-hue'),
      s: poster.getAttribute('data-saturation'),
      l: poster.getAttribute('data-lightness'),
      lstar: hslToLab(poster.getAttribute('data-hue'), poster.getAttribute('data-saturation') * 100, poster.getAttribute('data-lightness') * 100),
      radius,
      angle,
    };
    if (bucketizedHue in hueBuckets) {
      hueBuckets[bucketizedHue].push(f);
    } else {
      hueBuckets[bucketizedHue] = [f];
    }
    posters.push(f);
  });
  hueRange = hueRange.sort((a, b) => a - b);
  
  // Figure out what area each bucket needs to take by radially dividing 
  
  // First create a grid of all of the available cells
  const grid = Object.fromEntries(
    Array.from({ length: numRows }, (_, i) => [i, Array.from({ length: numCols }, (_, i) => {return {};})])
  );
  let allAngles = [];
  for (var r = 0; r < numRows; r++) {
    for (var c = 0; c < numCols; c++) {
      const targetX = c * imageWidth;
      const targetY = r * imageHeight;

      grid[r][c].x = targetX;
      grid[r][c].y = targetY;
      let targetAngle = Math.atan2(targetY - centerY, targetX - centerX);
      if (targetAngle < 0) {
        targetAngle += 2 * Math.PI;
      }
      grid[r][c].angle = targetAngle;
      grid[r][c].distance = Math.sqrt((targetY - centerY) ** 2 + (targetX - centerX)**2) / Math.sqrt((width / 2)**2 + (height / 2) ** 2);
      allAngles.push([targetAngle, r, c]);
    }
  }
  allAngles = allAngles.sort((a, b) => a[0] - b[0]);

  const currentHueRange = 0;
  for (var i = 0; i < hueRange.length; i++) {
    grid[allAngles[i][1]][allAngles[i][2]].bucket = hueRange[i];
  }

  console.log(grid);
  console.log(hueBuckets);

  function getBestAngle(arr, r, c) {
    if (arr.length === 0) return null; // Handle empty array case

    let targetAngle = grid[r][c].angle;
    // Get everything in the corresponding hue bucket
    let options = hueBuckets[grid[r][c].bucket];
    if (options === undefined) {
      console.log(r, c, grid[r][c].bucket);
      return null;
    }
    // console.log(grid[r][c].bucket, options.length, options);

    let bestSaturation = null;
    bestIndex = i;
    for (let i = 0; i < options.length; i++) {
      let score = options[i].l; // Math.sqrt(options[i].s) * (1 - options[i].l);
      if (bestSaturation == null || score > bestSaturation) {
        bestSaturation = score;
        bestIndex = i;
      }
    }

    selectedPoster = options.splice(bestIndex, 1)[0]; // Returns the removed element
    // selectedPoster.poster.innerHTML += grid[r][c].bucket;
    return selectedPoster;
  }
  
  // The current poster position that we're trying to fill
  let row = Math.floor(numRows / 2);
  let col = Math.floor(numCols / 2); // + 4;
  // The current direction that we're traveling
  let direction = 'EAST';
  // Each time we rotate, we increase
  let nextVertical = 1;
  let nextHorizontal = 1; // 5;
  let switchCounter = 1;

  // For 500 people
  // 230 (first turn off the bottom)
  // 243 (first turn off the top)
  // 256 (second turn off the bottom)
  // 290
  for (var i = 0; i < numPosters; i++) {
    let poster = getBestAngle(posters, row, col);

    if (poster) {
      poster.poster.style.left = `${grid[row][col].x}px`;
      poster.poster.style.top = `${grid[row][col].y}px`;
      // poster.poster.innerHTML = getSegment(grid[row][col].angle); // Math.round(grid[row][col].angle * 100) / 100;
    }

    // Check if we should swap directions
    switchCounter -= 1;
    if (switchCounter == 0) {
      switch (direction) {
        case 'EAST':
          direction = 'SOUTH';
          nextHorizontal += 1;
          switchCounter = nextVertical;
          break;
        case 'SOUTH':
          direction = 'WEST';
          nextVertical += 1;
          switchCounter = nextHorizontal;
          break;
        case 'WEST':
          direction = 'NORTH';
          nextHorizontal += 1;
          switchCounter = nextVertical;
          break;
        case 'NORTH':
          direction = 'EAST';
          nextVertical += 1;
          switchCounter = nextHorizontal;
          break;
      }
    }

    switch (direction) {
      case 'EAST':
        col += 1;
        break;
      case 'WEST':
        col -= 1;
        break;
      case 'NORTH':
        row -= 1;
        break;
      case 'SOUTH':
        row += 1;
        break;
    }

    // If you've gone off the grid, we need to teleport
    // Off the bottom
    if (row == numRows) {
      // Swap back to going north
      direction = 'NORTH';
      // Reset yourself to the bottom position
      row = numRows - 1;
      col -= nextHorizontal;
      // Need this so that we don't overwrite
      switchCounter = nextVertical + 1;
      // For next time!
      nextHorizontal += 1;
    } else if (row < 0) {
      direction = 'SOUTH';

      row = 0
      col += nextHorizontal;

      switchCounter = nextVertical + 1;

      nextHorizontal += 1;
    }
  }

  // posters.forEach(p => {
  //   p.poster.remove();
  // })
  
</script>
</body></html>