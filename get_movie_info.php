<?php

require_once("tmdb.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

exec('php process.php', $output);
if ($output[0] == 'more' || $output[0] == 'finished') {
  header('Content-Type: application/json');
  echo json_encode([
    'status' => $output[0],
  ]);
} else {
  echo implode('<br>', $output);
}


// var_export(getInfo('https://boxd.it/iEEq', 'Free Solo', '2021'));
// $info = getInfo('https://boxd.it/aPvo', 'Frozen', '2021');
// $info = getInfo('https://boxd.it/2o4Y', 'The Vow', '2012');
// $info = getInfo('https://boxd.it/s1Ym', 'The Queen\'s Gambit', '2020');
// $info = getInfo('https://boxd.it/yK2u', 'A Sensorial Ride', '2020');
// echo '<img src="' . $info['poster'] . '">';
// var_export($info);


// if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['name'] && $_POST['year']) {
//     header('Content-Type: application/json');
//     $data = getMovieInfo($_POST['name'], $_POST['year']);
//     if (!$data) {
//         http_response_code(400);
//         echo '{"error": "Could not find movie."}';
//     } else {
//         echo json_encode($data);
//     }
// } else {
//     http_response_code(400);
//     echo 'Malformed request.';
// }