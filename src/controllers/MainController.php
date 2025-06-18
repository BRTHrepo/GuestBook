<?php

namespace controllers;
require_once  BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/src/controllers/MessageController.php';

use helpers\LanguageHelper;

class MainController
{

    public static $requestUri;

    public function __construct($requestUri)
    {

        // Nyelv inicializálása
        LanguageHelper::init();
        self::$requestUri = $action = preg_replace('/(&|\?)lang=[^&]*/', '', $requestUri);
    }

    // Az alapértelmezett oldal betöltése
    private function index()
    {
        require_once BASE_PATH . '/src/views/home/index.php';

    }

    // Szabályok oldal betöltése
    public function admin()
    {
        require_once BASE_PATH . '/src/views/admin/index.php';
    }

    public function newMessage()
    {
        require_once BASE_PATH . '/src/views/new_message/index.php';
    }

    private function confirm($token)
    {
        // A $token változó elérhető lesz a view-ban
        $controller = new MessageController();
        $result = $controller->confirm($token);
        require_once BASE_PATH . '/src/views/confirm/index.php';
    }
    private function approve( $approve, $token)
    {
        $controller = new MessageController();
        $result = $controller->approve($approve,$token);
        require_once BASE_PATH . '/src/views/confirm/index.php';
    }
    private function sanitizeAction($action): string
    {
        // Régi, elavult megoldás helyett:
        // $action = filter_var($action, FILTER_SANITIZE_STRING);
        // Távolítsuk el a lang paramétert az URL-ből
        $action = preg_replace('/(&|\?)lang=[^&]*/', '', $action);
        // Új, biztonságos megoldás:
        echo "<script>console.log('" . addslashes($action) . "');</script>";
        $action = strip_tags(trim($action));
        $allowedActions = ['admin', 'new_message', 'edit', 'home', 'confirm', 'dont_approve', 'approve'];

        return in_array($action, $allowedActions) ? $action : 'home';
    }

    public function handleRequest()
    {
        $action = isset($_GET['action']) ? $this->sanitizeAction($_GET['action']) : 'home';

        echo "<script>console.log('action: $action');</script>";
        switch ($action) {
            case 'admin':
                $this->admin();
                break;
            case 'new_message':
                $this->newMessage();
                break;
            case 'confirm':
                $token = $_GET['token'];
                $this->confirm($token);
                break;
            case 'dont_approve':
                $token = $_GET['token'];
                $this->approve(false,$token);
                break;
            case 'approve':
                $token = $_GET['token'];
                $this->approve(true,$token);
                break;
            default:
                $this->index();
                break;
        }
    }




}