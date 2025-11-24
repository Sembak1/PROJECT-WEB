<?php
// inti/koneksi_database.php

$DB_HOST = 'localhost';
$DB_NAME = 'ecommerce_kecantikan';
$DB_USER = 'root';
$DB_PASS = 'FAIZ12345'; // sesuaikan

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Koneksi database gagal.");
}
