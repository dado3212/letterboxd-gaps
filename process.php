<?php
error_reporting(E_ALL);
ini_set('display_errors', 1); 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
  $file = $_FILES['file'];
  
  // Ensure the file is a .zip
  if (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'zip') {
    http_response_code(400);
    echo 'Invalid file type. Please upload a .zip file.';
    exit;
  }

  var_export($file);

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
} else {
  http_response_code(400);
  echo 'No file uploaded.';
}