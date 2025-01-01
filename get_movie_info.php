<?php

require_once("tmdb.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['name'] && $_POST['year']) {
    header('Content-Type: application/json');
    $data = getMovieInfo($_POST['name'], $_POST['year']);
    if (!$data) {
        http_response_code(400);
        echo '{"error": "Could not find movie."}';
    } else {
        echo json_encode($data);
    }
} else {
    var_export($_POST);
    http_response_code(400);
    echo 'Malformed request.';
}