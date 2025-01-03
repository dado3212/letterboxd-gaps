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

let width = 400;

function getData() {
  const k = width / 200;
  const r = d3.randomUniform(k, k * 4);
  const n = 4;
  return Array.from({length: 200}, (_, i) => ({r: r(), group: i && (i % n + 1)}));
}

function drawChart(container, data){
  const height = width;
  const color = d3.scaleOrdinal(d3.schemeTableau10);
  const context = createCanvas(width, height, 2);
  const nodes = data.map(Object.create);

  const simulation = d3.forceSimulation(nodes)
      .alphaTarget(0.3) // stay hot
      .velocityDecay(0.1) // low friction
      .force("x", d3.forceX().strength(0.01))
      .force("y", d3.forceY().strength(0.01))
      .force("collide", d3.forceCollide().radius(d => d.r + 1).iterations(3))
      .force("charge", d3.forceManyBody().strength((d, i) => i ? 0 : -width * 2 / 3))
      .on("tick", ticked);

  d3.select(context.canvas)
      .on("touchmove", event => event.preventDefault())
      .on("pointermove", pointermoved);

  // invalidation.then(() => simulation.stop());

  function pointermoved(event) {
    const [x, y] = d3.pointer(event);
    nodes[0].fx = x - width / 2;
    nodes[0].fy = y - height / 2;
  }

  function ticked() {
    context.clearRect(0, 0, width, height);
    context.save();
    context.translate(width / 2, height / 2);
    for (let i = 1; i < nodes.length; ++i) {
      const d = nodes[i];
      context.beginPath();
      context.moveTo(d.x + d.r, d.y);
      context.arc(d.x, d.y, d.r, 0, 2 * Math.PI);
      context.fillStyle = color(d.group);
      context.fill();
    }
    context.restore();
  }

  return context.canvas;
}
const container = document.getElementById("container");
container.appendChild(drawChart(container, getData()));

</script>
      
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