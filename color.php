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
    <div id="container"></div>
<script type="module">

import * as d3 from "https://cdn.jsdelivr.net/npm/d3@7/+esm";

function createCanvas(width, height, dpi) {
  if (dpi == null) dpi = devicePixelRatio;
  var canvas = document.createElement("canvas");
  canvas.width = width * dpi;
  canvas.height = height * dpi;
  canvas.style.width = width + "px";
  var context = canvas.getContext("2d");
  context.scale(dpi, dpi);
  return context;
}

let width = window.innerWidth;
let height = window.innerHeight;

function getData() {
  const k = width / 500;
  <?php

  $PDO = getDatabase();
  $stmt = $PDO->prepare("SELECT poster, primary_color FROM movies ORDER BY RAND() LIMIT 500");
  $stmt->execute();
  $posters = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $posterImages = [];

  foreach ($posters as $poster) {
    $hsl = json_decode($poster['primary_color'], true);
    $angle = ($hsl['h']) / 360.0 * 2 * M_PI; // Convert Hue to radians
    $radius = (1 - $hsl['s'] / 0.927); // (1 - $hsl['s']) * (1 + 0.2 * (1 - $hsl['l']));    // Inverse of Lightness

    $posterImages[] = '{r: k * 5, p: "https://image.tmdb.org/t/p/w92' . $poster['poster'] . '", h: "' . $hsl['h'] . '", a: "' . $angle . '", rad: "' . $radius . '"}';
  }
  echo 'let data = [' . implode(',', $posterImages) . '];';
  ?>

  let radiusScale, centerX, centerY;
  if (width > height) {
    radiusScale = (height / 2) -50;
  } else {
    radiusScale = (width / 2) - 50;
  }

  centerX = 0; // (width / 2) - 41;
  centerY = 0; // (height / 2) - 51;

  data.forEach(function(d) {
    let radius = radiusScale * d.rad;
    let angle = d.a;
    const x = centerX + radius * Math.cos(angle);
    const y = centerY + radius * Math.sin(angle);
    d.gX = x;
    d.gY = y;
  });

  return data;

  // const k = width / 200;
  // const r = d3.randomUniform(k, k * 4);
  // const n = 4;
  // return Array.from({length: 200}, (_, i) => ({r: k * 3, group: i && (i % n + 1)}));
}

function preloadImages(nodes) {
  return Promise.all(
    nodes.map(d => {
      return new Promise(resolve => {
        const img = new Image();
        img.src = d.p; // Use the .p property for the poster URL
        img.onload = () => resolve({ src: d.p, img }); // Resolve when the image is loaded
        img.onerror = () => resolve({ src: d.p, img: null }); // Handle failed loads
      });
    })
  );
}

async function drawChart(container, data){
  const color = d3.scaleOrdinal(d3.schemeTableau10);
  const context = createCanvas(width, height, 2);

  // Preload images
  const preloadedImages = await preloadImages(data);

  const nodes = data.map(Object.create);
  const nodeImageMap = new Map(
    preloadedImages.map(({ src, img }) => [src, img])
  );

  const simulation = d3.forceSimulation(nodes)
      .alphaTarget(0.2) // stay hot
      .velocityDecay(0.4) // low friction
      .force("x", d3.forceX().x(function(d){
        return d.gX;
      }).strength(0.05))
      .force("y", d3.forceY().y(function(d){
        return d.gY;
      }).strength(0.05))
      .force("collide", d3.forceCollide().radius(d => d.r + 1).iterations(3))
      .force("charge", d3.forceManyBody().strength(2))
      .on("tick", ticked);

  // d3.select(context.canvas)
  //     .on("touchmove", event => event.preventDefault());
      // .on("pointermove", pointermoved);

  // invalidation.then(() => simulation.stop());

  // function pointermoved(event) {
  //   const [x, y] = d3.pointer(event);
  //   nodes[0].fx = x - width / 2;
  //   nodes[0].fy = y - height / 2;
  // }

  function ticked() {
    const aspectRatio = 92 / 138; // Width-to-height ratio of the posters

    context.clearRect(0, 0, width, height);
    context.save();
    context.translate(width / 2, height / 2);
    nodes.forEach(d => {
      const img = nodeImageMap.get(d.p);
      if (img) {
        const posterHeight = d.r * 2; // Diameter of the node determines image size
        const posterWidth = posterHeight * aspectRatio;
        context.drawImage(img, d.x - posterWidth / 2, d.y - posterHeight / 2, posterWidth, posterHeight);
      } else {
        // Fallback: draw a rectangle placeholder
        context.beginPath();
        const rectWidth = d.r * 2 * aspectRatio;
        const rectHeight = d.r * 2;
        context.rect(d.x - rectWidth / 2, d.y - rectHeight / 2, rectWidth, rectHeight);
        context.fillStyle = "gray";
        context.fill();
      }
    });
    context.restore();
  }

  return context.canvas;
}
const container = document.getElementById("container");
container.appendChild(await drawChart(container, getData()));

</script>
      
      <?php

// $PDO = getDatabase();
// $stmt = $PDO->prepare("SELECT poster, primary_color FROM movies ORDER BY RAND() LIMIT 500");
// $stmt->execute();
// $posters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// foreach ($posters as $poster) {
//   $hsl = json_decode($poster['primary_color'], true);
//   $poster = 'https://image.tmdb.org/t/p/w92' . $poster['poster'];

//   $angle = ($hsl['h'] + mt_rand() / mt_getrandmax() * 180) / 360.0 * 2 * M_PI; // Convert Hue to radians
//   $radius = (1 - $hsl['s'] / 0.927); // (1 - $hsl['s']) * (1 + 0.2 * (1 - $hsl['l']));    // Inverse of Lightness

//   echo '<div class="poster" style="background-color: hsl(' . $hsl['h'] . ', '  . $hsl['s'] * 100 . '%, '  . $hsl['l'] * 100 . '%)" data-angle="' . $angle . '" data-radius="' . $radius . '">';
//   echo '<img src="' . $poster .'" /><br>';
//   echo '</div>';
// }
?>
</body></html>