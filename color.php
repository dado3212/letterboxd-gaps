<?php
require_once("secret.php");
require_once("tmdb.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

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

$numPosters = 3000;

$PDO = getDatabase();
$stmt = $PDO->prepare("SELECT poster, primary_color FROM movies LIMIT $numPosters"); // ORDER BY RAND()
$stmt->execute();
$posters = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($posters as $poster) {
  $hsl = json_decode($poster['primary_color'], true);
  $poster = 'https://image.tmdb.org/t/p/w92' . $poster['poster'];

  $angle = ($hsl['h']) / 360.0 * 2 * M_PI; // Convert Hue to radians
  $radius = (1 - $hsl['s'] / 0.927); // (1 - $hsl['s']) * (1 + 0.2 * (1 - $hsl['l']));    // Inverse of Lightness

  echo '<div class="poster" style="background-color: hsl(' . $hsl['h'] . ', '  . $hsl['s'] * 100 . '%, '  . $hsl['l'] * 100 . '%)" data-angle="' . $angle . '" data-radius="' . $radius . '">';
  echo '<img src="' . $poster .'" /><br>';
  echo '</div>';
}
?>
<script>

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
  img {
    width: ${imageWidth}px;
    height: ${imageHeight}px;
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

  document.querySelectorAll('.poster').forEach(poster => {
    let radius = radiusScale * poster.getAttribute('data-radius');
    let angle = poster.getAttribute('data-angle');
    const x = centerX + radius * Math.cos(angle);
    const y = centerY + radius * Math.sin(angle);
    posters.push({x, y, poster});
  });

  function getBestPosition(arr, targetX, targetY) {
    if (arr.length === 0) return null; // Handle empty array case

    let scoreFunc = (poster) => {
      return Math.sqrt((poster.x - targetX) ** 2 + (poster.y - targetY) ** 2);
    };
    
    // Find the index of the element with the smallest score
    let smallestIndex = 0;
    let smallestScore = scoreFunc(arr[0]);

    for (let i = 1; i < arr.length; i++) {
      const currentScore = scoreFunc(arr[i]);
      if (currentScore < smallestScore) {
        smallestScore = currentScore;
        smallestIndex = i;
      }
    }

    // Remove the element with the smallest score
    return arr.splice(smallestIndex, 1)[0]; // Returns the removed element
  }
  
  // The current poster position that we're trying to fill
  let row = Math.floor(numRows / 2);
  let col = Math.floor(numCols / 2) + 4;
  // The current direction that we're traveling
  let direction = 'EAST';
  // Each time we rotate, we increase
  let nextVertical = 1;
  let nextHorizontal = 5;
  let switchCounter = 1;

  // 230 (first turn off the bottom)
  // 243 (first turn off the top)
  // 256 (second turn off the bottom)
  // 290
  for (var i = 0; i < numPosters; i++) {
    const targetX = col * imageWidth;
    const targetY = row * imageHeight;

    let poster = getBestPosition(posters, targetX, targetY);

    poster.poster.style.left = `${targetX}px`;
    poster.poster.style.top = `${targetY}px`;
    // poster.poster.innerHTML = i;

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

  posters.forEach(p => {
    p.poster.remove();
  })
  
</script>
</body></html>