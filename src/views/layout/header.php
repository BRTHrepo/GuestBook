<?php

$styleCssPath = BASE_PATH . "/public/css/style.css";
$styleCssContent = '';
$styleCssContentBootstrap = '';
$styleCssPathBootstrap = BASE_PATH .'/public/css/bootstrap.min.css';
if (file_exists($styleCssPathBootstrap) && is_file($styleCssPathBootstrap)) {
    $styleCssContentBootstrap = file_get_contents($styleCssPathBootstrap);
} else {
    $styleCssPathBootstrap = BASE_PATH . '/public/css/bootstrap.min.css';

    if (file_exists($styleCssPathBootstrap) && is_file($styleCssPathBootstrap)) {
        $styleCssContentBootstrap = file_get_contents($styleCssPathBootstrap);
    }
}

if (file_exists($styleCssPath) && is_file($styleCssPath)) {
    $styleCssContent = file_get_contents($styleCssPath);
} else {

    $styleCssPath = BASE_PATH . '/public/css/style.css';
    if (file_exists($styleCssPath) && is_file($styleCssPath)) {
        $styleCssContent = file_get_contents($styleCssPath);
    }

}



require_once BASE_PATH . '/src/helpers/LanguageHelper.php';

use controllers\MainController;
use helpers\LanguageHelper;

// Nyelv inicializálása
//LanguageHelper::init();
$currentUrl = MainController::$requestUri; // Az aktuális URL lekérése
$currentUrl = addslashes($currentUrl);
// vizsgáljuk, hogy '/' -re végződik-e az URL, ha igel, akkor hozzáadjuk  ? ,ha nem, akkor hozzáadjuk a & -t
if (substr($currentUrl, -1) !== '/') {
    $currentUrl .= '&';
}else {
    $currentUrl .= '?';
}
echo "<script>console.log('" . addslashes($currentUrl) . "');</script>";
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?? 'hu' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>GuestBook</title>
    <style> <?= $styleCssContentBootstrap ?></style>
    <style>  <?= $styleCssContent ?>  </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        .clickable-row {
            cursor: pointer; /* Mutató változtatása kézre */
            transition: background-color 0.3s ease; /* Átmenet a háttérszínhez */
        }

        .clickable-row:hover {
            background-color: #f0f8ff; /* Halvány kék háttérszín hover esetén */
        }

        .table thead th {
            background-color: #e0e0e0; /* Fejléc háttérszíne */
            color: #333; /* Fejléc szövegszíne */
        }

        .table tbody tr:nth-child(even) {
            background-color: #f9f9f9; /* Páros sorok háttérszíne */
        }

        .table tbody tr:nth-child(odd) {
            background-color: #ffffff; /* Páratlan sorok háttérszíne */
        }

        .table td, .table th {
            padding: 8px; /* Cellák paddingje */
            text-align: left; /* Szöveg igazítása balra */
            border-bottom: 1px solid #ddd; /* Alsó szegély */
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg  fixed-top">
    <div class="navbar-nav expand-lg">

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>?action=home"><?= LanguageHelper::get('guestbook') ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>?action=new_message"><?= LanguageHelper::get('new_message') ?></a>
                </li>

            </ul>
            <div class="language-switcher">
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" id="langDropdown"
                            data-bs-toggle="dropdown">
                        <?= LanguageHelper::getLangName($_SESSION['lang'] ?? 'hu') ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item <?= ($_SESSION['lang'] ?? 'hu') === 'hu' ? 'active' : '' ?>"
                               href="<?= $currentUrl ?>lang=hu">Magyar</a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= ($_SESSION['lang'] ?? 'hu') === 'en' ? 'active' : '' ?>"
                               href="<?= $currentUrl ?>lang=en">English</a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= ($_SESSION['lang'] ?? 'hu') === 'de' ? 'active' : '' ?>"
                               href="<?= $currentUrl ?>lang=de">Deutsch</a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= ($_SESSION['lang'] ?? 'hu') === 'es' ? 'active' : '' ?>"
                               href="<?= $currentUrl ?>lang=es">Español</a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= ($_SESSION['lang'] ?? 'hu') === 'fr' ? 'active' : '' ?>"
                               href="<?= $currentUrl ?>lang=fr">Français</a>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</nav>
<main class="content-wrapper">
