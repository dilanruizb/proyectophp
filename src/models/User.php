<?php

// DB imported on index.php
// require_once __DIR__ . '/DB.php';

class User {
    // Get all users from the database

    public static function create($data) {

        $pdo = new PDO("mysql:host=localhost;dbname=seminariophp;charset=utf8", "root", "");
        $db = $pdo;

        $stmt = $db->prepare("INSERT INTO users 
            (email, first_name, last_name, password, token, expired, is_admin) 
            VALUES (:email, :first_name, :last_name, :password, :token, :expired, :is_admin)");

        $stmt->execute([
            ':email'      => $data['email'],
            ':first_name' => $data['first_name'],
            ':last_name'  => $data['last_name'],
            ':password'   => $data['password'],
            ':token'      => $data['token'] ?? null,
            ':expired'    => $data['expired'] ?? null,
            ':is_admin'   => $data['is_admin'] ?? 0
        ]);

        // Retornar el usuario recién creado
        $id = $db->lastInsertId();
        return self::findById($id);
    }

    public static function getAll()
    {
        $pdo = new PDO("mysql:host=localhost;dbname=seminariophp;charset=utf8", "root", "");
        $db = $pdo;
        $stmt = $db->query("SELECT * FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

      public static function findById($id) {
        $pdo = new PDO("mysql:host=localhost;dbname=seminariophp;charset=utf8", "root", "");
        $db = $pdo;
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
