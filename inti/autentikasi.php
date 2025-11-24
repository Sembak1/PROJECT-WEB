<?php
// inti/autentikasi.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/koneksi_database.php';

function cek_login()
{
    if (empty($_SESSION['user'])) {
        $_SESSION['redirect_setelah_login'] = $_SERVER['REQUEST_URI'] ?? '/glowify/user/beranda.php';
        header('Location: /glowify/akun/masuk.php');
        exit;
    }
}

function cek_admin()
{
    cek_login();
    if ($_SESSION['user']['role'] !== 'admin') {
        header('Location: /glowify/user/beranda.php');
        exit;
    }
}

// Cek status aktif langsung ke DB
if (!empty($_SESSION['user'])) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $aktif = $stmt->fetchColumn();

    if ($aktif != 1) {
        session_unset();
        session_destroy();
        header('Location: /glowify/akun/masuk.php?inactive=1');
        exit;
    }
}
