<?php

class DB {
    private static $connection;

    public static function getConnection() {
        if (!self::$connection) {
            $host = 'localhost';
            $dbname = 'seminariophp';
            $user = 'root';
            $pass = '';

            try {
                self::$connection = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die(json_encode(['error' => $e->getMessage()]));
            }
        }

        return self::$connection;
    }
}
