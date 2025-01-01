<?php
require_once("tmdb.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
  $file = $_FILES['file'];

  if (pathinfo($file['name'], PATHINFO_EXTENSION) === 'csv') {
    handleWatchlist($file);
    exit;
  } else if (pathinfo($file['name'], PATHINFO_EXTENSION) === 'zip') {
    handleZip($file);
  } else {
    http_response_code(400);
    echo 'Invalid file type. Please upload a .zip file.';
    exit;
  }
} else {
    http_response_code(400);
    echo 'No file uploaded.';
}

function handleWatchlist($file) {
    if (($handle = fopen($file['tmp_name'], 'r')) !== false) {
        $data = []; // Array to store the CSV data as a dictionary
    
        // Get the header row
        $headers = fgetcsv($handle);
    
        if ($headers === false) {
          http_response_code(400);
          echo 'The CSV file is empty or invalid.';
          fclose($handle);
          exit;
        }
    
        // Process each row of the CSV
        while (($row = fgetcsv($handle)) !== false) {
          $data[] = array_combine($headers, $row);
        }
    
        fclose($handle);
    
        // Example: Return JSON response
        header('Content-Type: application/json');
        echo json_encode($data);
      } else {
        http_response_code(500);
        echo 'Failed to open uploaded file.';
      }
}

function handleZip($file) {
  // Open the .zip file directly from the uploaded file stream
  $zip = new ZipArchive();
  if ($zip->open($file['tmp_name']) === TRUE) {
    // Iterate over files in the .zip
    $files = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
      $fileName = $zip->getNameIndex($i);
      $fileContent = $zip->getFromIndex($i);
      
      // Store file data in an associative array
      $files[] = [
        'name' => $fileName,
        'content' => $fileContent,
      ];
    }
    $zip->close();

    // Process the extracted files in memory
    foreach ($files as $file) {
      echo "File: " . $file['name'] . "\n";
      // Example: Output first 100 characters of file content
      echo "Content (truncated): " . substr($file['content'], 0, 100) . "\n\n";
    }
  } else {
    http_response_code(500);
    echo 'Failed to open .zip file.';
  }
}