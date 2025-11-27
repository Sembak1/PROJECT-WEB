<?php
// inti/autentikasi.php
// File ini berisi fungsi-fungsi untuk mengecek login & hak akses user.


/* ---------------------------------------------------------
   PASTIKAN SESSION SUDAH AKTIF
--------------------------------------------------------- */
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
    // Jika session belum dimulai, mulai session baru.
}


/* ---------------------------------------------------------
   IMPORT KONEKSI DATABASE
--------------------------------------------------------- */
require_once __DIR__ . '/koneksi_database.php';
// Mengambil variabel $pdo untuk query database.



/* =========================================================
   FUNGSI cek_login()
   → Dipakai untuk memastikan user sudah login.
========================================================= */
function cek_login()
{
    // Jika tidak ada data user di session = belum login
    if (empty($_SESSION['user'])) {

        // Simpan URL halaman yang ingin diakses sebelum login
        // Agar setelah login langsung kembali ke halaman tersebut
        $_SESSION['redirect_setelah_login'] = $_SERVER['REQUEST_URI'] ?? '/glowify/user/beranda.php';

        // Arahkan ke halaman login
        header('Location: /glowify/akun/masuk.php');
        exit;
    }
}



/* =========================================================
   FUNGSI cek_admin()
   → Memastikan user sudah login DAN role-nya adalah admin.
========================================================= */
function cek_admin()
{
    cek_login(); 
    // Pertama cek apakah user memang sudah login

    if ($_SESSION['user']['role'] !== 'admin') {
        // Jika login tapi role bukan admin, arahkan ke beranda user
        header('Location: /glowify/user/beranda.php');
        exit;
    }
}

/* =========================================================
   CEK STATUS AKTIF USER SECARA REAL-TIME KE DATABASE
   (Anti bypass: meskipun user masih punya session,
    jika di DB dinonaktifkan → langsung logout)
========================================================= */
if (!empty($_SESSION['user'])) {

    global $pdo;  
    // Menggunakan variabel PDO global untuk query

    // Ambil status aktif user dari database
    $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $aktif = $stmt->fetchColumn();

    // Jika akun dinonaktifkan
    if ($aktif != 1) {

        // Hapus semua session agar logout total
        session_unset();
        session_destroy();

        // Redirect ke halaman login + parameter inactive=1
        header('Location: /glowify/akun/masuk.php?inactive=1');
        exit;
    }
}
