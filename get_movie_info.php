<?php

require_once("tmdb.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$id = null;
if (
  $_SERVER['REQUEST_METHOD'] === 'GET' &&
  isset($_GET['id']) &&
  ctype_digit($_GET['id']) &&
  strlen($_GET['id']) < 6
) {
  $id = (int)$_GET['id'];
}

if ($id == null) {
  echo 'Malformed.';
  return;
}

exec('php process.php ' . PROCESS_KEY . ' ' . $id, $output);
if ($output[0] == 'more' || $output[0] == 'finished') {
  header('Content-Type: application/json');
  echo json_encode([
    'status' => $output[0],
  ]);
} else {
  header('Content-Type: application/json');
  echo json_encode([
    'status' => 'failed',
    'error' => $output,
  ]);
}