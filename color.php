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
  $stmt = $PDO->prepare("SELECT poster, primary_color FROM movies ORDER BY RAND() LIMIT 1500");
  $stmt->execute();
  $posters = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $posterImages = [];

  foreach ($posters as $poster) {
    $hsl = json_decode($poster['primary_color'], true);
    $angle = ($hsl['h']) / 360.0 * 2 * M_PI; // Convert Hue to radians
    $radius = (1 - $hsl['s'] / 0.927); // (1 - $hsl['s']) * (1 + 0.2 * (1 - $hsl['l']));    // Inverse of Lightness

    // $posterImages[] = '{r: k * 5, p: "https://image.tmdb.org/t/p/w92' . $poster['poster'] . '", h: "' . $hsl['h'] . '", a: "' . $angle . '", rad: "' . $radius . '"}';
    $posterImages[] = json_encode([
      'r' => 20,
      'p' => 'https://image.tmdb.org/t/p/w92' . $poster['poster'],
      'h' => $hsl['h'],
      'a' => $angle,
      'rad' => $radius,
    ]);
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
    const aspectRatio = 92 / 138; // Width-to-height ratio of the posters
    d.height = d.r * 2; // Diameter of the node determines image size
    d.width = d.height * aspectRatio;
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

  var collisionForce = rectCollide()
    .size(function (d) { return [d.width, d.height] })

var boxForce = boundedBox()
    .bounds([[0, 0], [width, height]])
    .size(function (d) { return [d.width, d.height] })

  const simulation = d3.forceSimulation(nodes)
      // .alphaTarget(0.2) // stay hot
      .velocityDecay(0.4) // low friction
      .force("x", d3.forceX().x(function(d){
        return d.gX;
      }).strength(0.05))
      .force("y", d3.forceY().y(function(d){
        return d.gY;
      }).strength(0.05))
      // .force('box', boxForce)
      .force('collision', collisionForce)
      // .force("collide", d3.forceCollide().radius(d => d.r + 1).iterations(3))
      // .force("charge", d3.forceManyBody().strength(2))
      .on("tick", ticked);

      function constant(_) {
    return function () { return _ }
}

function rectCollide() {
	let nodes, sizes, masses;
	let strength = 1.1;
	let iterations = 1;
	let nodeCenterX;
	let nodeMass;
	let nodeCenterY;

	function force() {
		let node;
		let i = -1;
		while (++i < iterations) {
			iterate();
		}
		function iterate() {
			//let quadtree = d3.quadtree().x(function(d) {return d.x;}).y(function(d) {return d.y;}).addAll(nodes);
			let quadtree = d3.quadtree(nodes, xCenter, yCenter);
			let j = -1;

			while (++j < nodes.length) {
				node = nodes[j];
				nodeMass = masses[j];
				nodeCenterX = xCenter(node);
				nodeCenterY = yCenter(node);
				quadtree.visit(collisionDetection);
			} //end nodes loop
		} //end iterate function

		// forget velocity, all we want is repulsion
		function collisionDetection(quad, x0, y0, x1, y1) {
			let updated = false;
			let data = quad.data;

			if (data) {
				if (data.index <= node.index) return;
				let xSize = (node.width + data.width) / 2;
				let ySize = (node.height + data.height) / 2;

				let dx = nodeCenterX - xCenter(data);
				let dy = nodeCenterY - yCenter(data);
				let xDiff = Math.abs(dx) - xSize;
				let yDiff = Math.abs(dy) - ySize;

				if (xDiff < 0 && yDiff < 0) {
					let distance = Math.sqrt(dx * dx + dy * dy);
					let m = masses[data.index] / (nodeMass + masses[data.index]);

					if (Math.abs(xDiff) < Math.abs(yDiff)) {
						node.vx -= (dx *= (xDiff / distance) * strength) * m;
						data.vx += dx * (1 - m);
					} else {
						node.vy -= (dy *= (yDiff / distance) * strength) * m;
						data.vy += dy * (1 - m);
					}
					updated = true;
				}
			}

			return updated;
		}

		// velocity
		function _collisionDetection(quad, x0, y0, x1, y1) {
			let updated = false;
			let data = quad.data;
			if (data) {
				if (data.index > node.index) {
					let xSize = (node.width + data.width) / 2;
					let ySize = (node.height + data.height) / 2;
					let dataCenterX = xCenter(data);
					let dataCenterY = yCenter(data);
					let dx = nodeCenterX - dataCenterX;
					let dy = nodeCenterY - dataCenterY;
					let absX = Math.abs(dx);
					let absY = Math.abs(dy);
					let xDiff = absX - xSize;
					let yDiff = absY - ySize;

					if (xDiff < 0 && yDiff < 0) {
						//collision has occurred
						//overlap x
						let sx = xSize - absX;
						//overlap y
						let sy = ySize - absY;

						if (sx < sy) {
							//x displacement smaller than y
							if (sx > 0) {
								sy = 0;
							}
						} else {
							//y displacement smaller than x
							if (sy > 0) {
								sx = 0;
							}
						}
						if (dx < 0) {
							//change sign of sx - has collided on the right(?)
							sx = -sx;
						}
						if (dy < 0) {
							//change sign of sy -
							sy = -sy;
						}

						//magnitude of vector
						let distance = Math.sqrt(sx * sx + sy * sy);
						//direction vector/unit vector - normalize each component by the magnitude to get the direction
						let vCollisionNorm = { x: sx / distance, y: sy / distance };
						let vRelativeVelocity = { x: data.vx - node.vx, y: data.vy - node.vy };
						//dot product of relative velocity and collision normal
						let speed =
							vRelativeVelocity.x * vCollisionNorm.x + vRelativeVelocity.y * vCollisionNorm.y;

						if (speed < 0) {
							//negative speed = rectangles moving away
						} else {
							//takes into account mass
							let collisionImpulse = (2 * speed) / (masses[data.index] + masses[node.index]);
							if (Math.abs(xDiff) < Math.abs(yDiff)) {
								//x overlap is less
								data.vx -= collisionImpulse * masses[node.index] * vCollisionNorm.x;
								node.vx += collisionImpulse * masses[data.index] * vCollisionNorm.x;
							} else {
								//y overlap is less
								data.vy -= collisionImpulse * masses[node.index] * vCollisionNorm.y;
								node.vy += collisionImpulse * masses[data.index] * vCollisionNorm.y;
							}

							updated = true;
						}
					}
				}
			}
			return updated;
		}
	} //end force

	function xCenter(d) {
		return d.x + d.vx + sizes[d.index][0] / 2;
	}
	function yCenter(d) {
		return d.y + d.vy + sizes[d.index][1] / 2;
	}

	force.initialize = function (_) {
		sizes = (nodes = _).map(function (d) {
			return [d.width, d.height];
		});
		masses = sizes.map(function (d) {
			return d[0] * d[1];
		});
	};

	force.size = function (_) {
		let size;
		return arguments.length ? ((size = typeof _ === 'function' ? _ : constant(_)), force) : size;
	};

	force.strength = function (_) {
		return arguments.length ? ((strength = +_), force) : strength;
	};

	force.iterations = function (_) {
		return arguments.length ? ((iterations = +_), force) : iterations;
	};

	return force;
} //end rectCollide

function boundedBox() {
	let nodes, sizes;
	let bounds;
	let size = constant([0, 0]);

	function force() {
		let node, size;
		let xi, x0, x1, yi, y0, y1;
		let i = -1;
		while (++i < nodes.length) {
			node = nodes[i];
			size = sizes[i];
			xi = node.x + node.vx;
			x0 = bounds[0][0] - xi;
			x1 = bounds[1][0] - (xi + size[0]);
			yi = node.y + node.vy;
			y0 = bounds[0][1] - yi;
			y1 = bounds[1][1] - (yi + size[1]);
			if (x0 > 0 || x1 < 0) {
				node.x += node.vx;
				node.vx = -node.vx;
				if (node.vx < x0) {
					node.x += x0 - node.vx;
				}
				if (node.vx > x1) {
					node.x += x1 - node.vx;
				}
			}
			if (y0 > 0 || y1 < 0) {
				node.y += node.vy;
				node.vy = -node.vy;
				if (node.vy < y0) {
					node.vy += y0 - node.vy;
				}
				if (node.vy > y1) {
					node.vy += y1 - node.vy;
				}
			}
		}
	}

	force.initialize = function (_) {
		sizes = (nodes = _).map(size);
	};

	force.bounds = function (_) {
		return arguments.length ? ((bounds = _), force) : bounds;
	};

	force.size = function (_) {
		let size = typeof _ === 'function';
		return arguments.length ? (size ? _ : constant(_), force) : size;
	};

	return force;
}

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
    context.clearRect(0, 0, width, height);
    context.save();
    context.translate(width / 2, height / 2);
    nodes.forEach(d => {
      const img = nodeImageMap.get(d.p);
      if (img) {
        context.drawImage(img, d.x - d.width / 2, d.y - d.height / 2, d.width, d.height);
      } else {
        // Fallback: draw a rectangle placeholder
        context.beginPath();
        context.rect(d.x - d.width / 2, d.y - d.height / 2, d.width, d.height);
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