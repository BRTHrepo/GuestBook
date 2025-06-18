<?php
// config.php kiegészítése


// Alap URL és útvonal
const BASE_URL = '';
define('BASE_PATH', (dirname(__DIR__))); // Projekt gyökérkönyvtár

require_once BASE_PATH . '/database/DatabaseInitializer.php';
require_once BASE_PATH . '/src/models/Database.php';
require_once BASE_PATH . '/vendor/autoload.php'; // ✅ helyes útvonal


use database\DatabaseInitializer;
use models\Database;

$envFilePath = BASE_PATH . '/.env'; // Feltételezve, hogy a .env a gyökérben van

if (file_exists($envFilePath)) {
    $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line); // Üres szóközök eltávolítása a sor elejéről/végéről

        // Kommentek (#) és üres sorok kihagyása
        if (empty($line) || substr($line, 0, 1) === '#') {
            continue;
        }

        // Ellenőrizzük, hogy a sor tartalmaz-e '=' jelet
        if (strpos($line, '=') === false) {
            continue; // Kihagyjuk, ha nincs kulcs=érték pár
        }

        // Kulcs=Érték szétválasztása
        list($key, $value) = explode('=', $line, 2);

        // Kulcs és érték tisztítása
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'"); // Idézőjelek és whitespace-ek eltávolítása

        // Környezeti változóként beállítás
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
} else {
    trigger_error("A .env fájl nem található! Kérjük, másolja a .env.example fájlt .env névre és töltse ki.", E_USER_ERROR);
    echo "A .env fájl nem található!";
    exit;
}

// Most már a betöltött adatok elérhetők a $_ENV szuperglobális tömbön keresztül
// Meghatározzuk a konstansokat a $_ENV adatokból
// PHP 7.0 előtti verziókban az isset() ellenőrzést használjuk a ?? operátor helyett.
define('DB_HOST', isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'localhost');
define('DB_PORT', isset($_ENV['DB_PORT']) ? $_ENV['DB_PORT'] : '3306');
define('DB_NAME', isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : '');
define('DB_USER', isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : '');
define('DB_PASS', isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : '');


// Automatikus fájlbetöltés
// Autoloader beállítása
spl_autoload_register(function ($class) {
    // Namespace kezelése
    $class = str_replace('\\', '/', $class);

    // Lehetséges mappák listája
    $directories = [
        '/src/controllers/',
        '/src/models/',
        '/src/database/',
        '/public/api/',
        '/database/'

    ];

    // Végigmegyünk a mappákon és betöltjük az első találatot
    foreach ($directories as $directory) {
        $path = BASE_PATH . $directory . $class . '.php';
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Adatbázis inicializálása
$db = Database::getInstance();

// index.php kiegészítése az elején
$dbInit = new DatabaseInitializer();
if (!$dbInit->checkTables()) {
    $dbInit->initialize();
}