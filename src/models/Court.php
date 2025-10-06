<?php

// DB imported on index.php
// require_once __DIR__ . '/DB.php';

class Court {
    public static function getAll() {
        $pdo = new PDO("mysql:host=localhost;dbname=seminariophp;charset=utf8", "root", "");
        $stmt = $pdo->query("SELECT * FROM courts");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $pdo = new PDO("mysql:host=localhost;dbname=seminariophp;charset=utf8", "root", "");
        $stmt = $pdo->prepare("INSERT INTO courts (name, location, type) VALUES (:name, :location, :type)");
        $stmt->execute([
            ':name' => $data['name'],
            ':location' => $data['location'],
            ':type' => $data['type']
        ]);
        return self::findById($pdo->lastInsertId());
    }

    public static function findById($id) {
        $pdo = new PDO("mysql:host=localhost;dbname=seminariophp;charset=utf8", "root", "");
        $stmt = $pdo->prepare("SELECT * FROM courts WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function update($id, $data) {
        $pdo = new PDO("mysql:host=localhost;dbname=seminariophp;charset=utf8", "root", "");
        $stmt = $pdo->prepare("UPDATE courts SET name = :name, location = :location, type = :type WHERE id = :id");
        $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':location' => $data['location'],
            ':type' => $data['type']
        ]);
        return self::findById($id);
    }

    public static function delete($id) {
        $pdo = new PDO("mysql:host=localhost;dbname=seminariophp;charset=utf8", "root", "");
        $stmt = $pdo->prepare("DELETE FROM courts WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
