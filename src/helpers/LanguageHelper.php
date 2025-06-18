<?php
namespace helpers;

class LanguageHelper {
    private static $defaultLang = 'hu';
    private static $supportedLangs = ['hu', 'en', 'de', 'es', 'fr'];  // Bővített nyelvek
    private static $messages = [];

    // Nyelv nevek a megjelenítéshez
    private static $langNames = [
        'hu' => 'Magyar',
        'en' => 'English',
        'de' => 'Deutsch',
        'es' => 'Español',
        'fr' => 'Français'
    ];

    public static function init() {
        // Session indítása a fájl legelején

        session_start();
        // Először a GET paramétert nézzük
        if (isset($_GET['lang']) && in_array($_GET['lang'], self::$supportedLangs)) {
            $_SESSION['lang'] = $_GET['lang'];
        }
        // Ha nincs session nyelv beállítva
        elseif (!isset($_SESSION['lang'])) {
            $_SESSION['lang'] = self::$defaultLang;
        }
        echo "<script>console.log('" . $_SESSION['lang'] . "');</script>";
        self::loadLanguageFile($_SESSION['lang']);
    }
    private static function loadLanguageFile($lang) {
        $file = BASE_PATH . '/lang/' . $lang . '/messages.php';
        if (file_exists($file)) {
            self::$messages = require $file;
        }
    }

    public static function get($key) {
        return self::$messages[$key] ?? $key;
    }

    public static function getLangName($langCode): string
    {
        return self::$langNames[$langCode] ?? $langCode;
    }

}
