<?php
// inti/koneksi_database.php
// File ini digunakan untuk membuat koneksi ke database menggunakan PDO.



/* ==========================================================
   KONFIGURASI DATABASE
   - Sesuaikan dengan server / hosting Anda
========================================================== */
$DB_HOST = 'localhost';              // Host database (umumnya localhost)
$DB_NAME = 'ecommerce_kecantikan';   // Nama database
$DB_USER = 'root';                   // Username MySQL
$DB_PASS = 'FAIZ12345';              // Password MySQL (ganti sesuai server)



/* ==========================================================
   MEMBUAT KONEKSI PDO
   - Menggunakan try-catch agar error bisa ditangani dengan aman
========================================================== */
try {

    // Membuat objek koneksi PDO
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", 
        // DSN → menentukan host, nama database, dan charset UTF-8

        $DB_USER,    // Username database
        $DB_PASS,    // Password database

        // Opsi tambahan PDO
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // Menampilkan error sebagai exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // Hasil fetch berupa array asosiatif
        ]
    );

} catch (PDOException $e) {

    // Jika koneksi gagal, hentikan seluruh program
    // Pesan tidak menampilkan error detail untuk keamanan
    die("Koneksi database gagal.");
}
