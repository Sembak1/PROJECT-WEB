<?php
// db.php
$DB_HOST = 'localhost';
$DB_NAME = 'ecommerce_kecantikan';
$DB_USER = 'root';
$DB_PASS = 'FAIZ12345'; // ganti di production

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
    // Hindari bocor detail koneksi; cukup pesan umum untuk user
    die("Koneksi database gagal.");
}
