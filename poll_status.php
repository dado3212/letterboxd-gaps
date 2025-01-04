<?php
require_once("tmdb.php");
require_once("secret.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
  $PDO = getDatabase();
  // Fetch which movie uploads we're tracking
  $sql = "SELECT uploaded FROM upload_tracking WHERE id = :id";
  $stmt = $PDO->prepare($sql);
  $stmt->bindValue(":id", $_GET['id'], PDO::PARAM_INT);
  $stmt->execute();
  $uploaded = json_decode($stmt->fetch(PDO::FETCH_ASSOC)['uploaded'], true);

  $sql = "SELECT count(*) as num FROM movies WHERE id IN (" . implode(",", $uploaded) . ") AND status='done'";
  $stmt = $PDO->prepare($sql);
  $stmt->execute();
  $numDone = $stmt->fetch(PDO::FETCH_ASSOC);

  header('Content-Type: application/json');
  echo json_encode([
    'total' => count($uploaded),
    'done' => $numDone['num'],
  ]);
} else {
  http_response_code(400);
  header('Content-Type: application/json');
  echo json_encode(['error' => 'ID not supplied.']);
}

?>