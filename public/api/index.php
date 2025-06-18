<?php
// ez a fájl az alwaysdata oldal miatt készült, más a könyvtárstruktúra
require_once '../../config/config.php';
require_once '../../src/controllers/MainController.php';
require_once '../../src/controllers/MessageController.php';
require_once '../../src/models/Database.php';
require_once '../../src/models/MessageManager.php';

// HTTPS ellenőrzés
$https = false;

// Apache és IIS tipikus eset
if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === 1)) {
    $https = true;
}

// Néhány szerver/proxy (pl. AWS, nginx reverse proxy) így jelzi a HTTPS-t
if (
    isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
    strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https'
) {
    $https = true;
}

if (!$https) {
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $redirect);
    exit();
}

// CORS fejlécek hozzáadása
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Alapvető biztonsági fejlécek
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Content Security Policy finomhangolása
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:");

// JSON válasz fejléc
header('Content-Type: application/json');

// Hibakezelés bekapcsolása fejlesztés közben
error_reporting(E_ALL);
ini_set('display_errors', 1);


use controllers\MessageController;

$controller = new MessageController();


$method = $_SERVER['REQUEST_METHOD'];


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$uri = explode('/', trim($uri, '/'));
// Olvasd be a törzset (body) JSON formátumban
//$body = file_get_contents("php://input");
//$data = json_decode($body, true); // true: asszociatív tömböt ad vissza


// Titkosított adat fogadása
$encryptedBody = file_get_contents("php://input");

if ($encryptedBody === false) {
    http_response_code(409);
    echo json_encode([
        'message' => 'Hibás bemenet',
        'ok' => false,
        'fault' => 'Invalid input'
    ]);
    exit;
}
// Visszafejtett JSON dekódolása
$data = json_decode($encryptedBody, true);

$normalData = false;
if ((json_last_error() === JSON_ERROR_NONE) && isset($data)) {
    $apiValue = $data['api'];
    switch ($apiValue) {
        case 'new_message':
        case 'guestbook':
            $normalData = true;
            break;
        default :
            $apiValue = null;
            $data = null;
            break;
    }
}


// Ellenőrizzük, hogy a JSON helyes-e
if ($apiValue === null) {
    // A data tartalmazza a JSON adatokat

    http_response_code(412);
    header('Content-Type: text/plain');
    echo json_encode([
        'message' => 'api: ? ' . $apiValue,
        'ok' => false,
        'fault' => 'Invalid api value'
    ]);

}

if (isset($apiValue)) {


    switch ($apiValue) {
        case  'guestbook':
            //$controller->sendResponse(true, 'Registration or - and login is successful', null, null);
            /*
             * api: "guestbook",
             $apiValue = $data['api'];
            rate: stars,
            search: search,
            from: 0,
             */
            $rate = $data['rate'] ?? null;
            $search = $data['search'] ?? null;
            $from = $data['from'] ?? null;
            $controller->getAllApprovedMessages($rate, $search, $from);
            break;
        case 'new_message':
            if ($method === 'POST') {
                try {
                    $message = $data['message'] ?? null;
                } catch (Exception $e) {
                    $message = null;
                }
                if ($message === null) {
                    $controller->sendResponse(false, 'message: ? ' . $message, 'Invalid message value', null);

                } else {
                    $controller->createMessage($data['message']);
                }
            }
            break;

        default:
            http_response_code(404);
            header('Content-Type: text/plain');
            echo json_encode(['error' => 'Nem támogatott művelet']);

    }

} else {
    http_response_code(499);
    header('Content-Type: text/plain');
    echo json_encode(['error' => 'API key not found']);
}
exit();

