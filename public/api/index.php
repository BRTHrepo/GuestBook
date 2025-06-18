<?php
echo "<script>console.log(22222222222222222222222222222222222222222222222222222222222);</script>";
use controllers\MessageController;

header('Content-Type: application/json');

require_once '../../src/models/Database.php';
require_once '../../src/helpers/LanguageHelper.php';



$requestUri = $_SERVER['REQUEST_URI'];
$filePath = __DIR__ . $requestUri;
echo "<script>console.log('Request URI: $requestUri');</script>";
echo "<script>console.log('File path: $filePath');</script>";
if (file_exists($filePath)) {
    echo "<script>console.log('File exists: $filePath');</script>";
} else {
    echo "<script>console.log('File does not exist: $filePath');</script>";
}
if (is_file($filePath)) {
    echo "<script>console.log('Is file: $filePath');</script>";
} else {
    echo "<script>console.log('Is not a file: $filePath');</script>";
}


if (file_exists($filePath) && is_file($filePath)) {
    // Kérés átirányítása a megfelelő statikus fájlra
    $mimeType = mime_content_type($filePath);
    echo "<script>console.log('MIME type: $mimeType');</script>";
    header("Content-Type: $mimeType");
    readfile($filePath);
    exit;
}
//echo "<script>console.log('---------------------------------------: $requestUri');</script>";

$controller = new MainController($requestUri);
$controller->handleRequest();