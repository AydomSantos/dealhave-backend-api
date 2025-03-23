<?php

namespace App\Database;

use PDO;
use PDOException;

class Connection {
    private static $instance = null;

    public static function getDb() {
        if (!self::$instance) {
            try {
                self::$instance = new PDO(
                    "mysql:host=localhost;dbname=dealhave",
                    "root",
                    "",
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                    ]
                );
            } catch (PDOException $e) {
                throw new \Exception("Connection failed: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}