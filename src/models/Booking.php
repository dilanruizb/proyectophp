<?php

// DB imported on index.php
// require_once __DIR__ . '/DB.php';

class Booking {
    public static function create($data) {
        $pdo = new PDO("mysql:host=localhost;dbname=seminariophp;charset=utf8", "root", "");
        $stmt = $pdo->prepare("INSERT INTO bookings (created_by, court_id, booking_datetime, duration_blocks)
                               VALUES (:created_by, :court_id, :booking_datetime, :duration_blocks)");
        $stmt->execute([
            ':created_by'      => $data['created_by'],
            ':court_id'        => $data['court_id'],
            ':booking_datetime'=> $data['booking_datetime'],
            ':duration_blocks' => $data['duration_blocks']
        ]);
        return $pdo->lastInsertId();
    }

    public static function getByCourtAndTime($court_id, $datetime) {
        $pdo = new PDO("mysql:host=localhost;dbname=seminariophp;charset=utf8", "root", "");
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE court_id = :court_id AND booking_datetime = :datetime");
        $stmt->execute([':court_id' => $court_id, ':datetime' => $datetime]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
