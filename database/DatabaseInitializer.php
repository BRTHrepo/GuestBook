<?php

// database/DatabaseInitializer.php
namespace database;

use models\Database;
use PDOException;

class DatabaseInitializer {


    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function initialize(): bool
    {
        try {

            $sql = file_get_contents(__DIR__ . '/schema.sql');
            if ($sql === false || empty(trim($sql))) {
                throw new PDOException("A schema.sql fájl nem található, vagy üres.");
            }
            $this->db->exec($sql);
            return true;
        } catch(PDOException $e) {
            error_log("Adatbázis inicializálási hiba: " . $e->getMessage());
            return false;
        }
    }

    public function checkTables(): bool
    {
        try {
            $result = $this->db->query("SHOW TABLES LIKE '".DB_NAME."'");
            return $result->rowCount() > 0;
        } catch(Exception $e) {
            error_log("(checkTables)Adatbázis inicializálási hiba: " . $e->getMessage());
            return false;
        }
    }
}
