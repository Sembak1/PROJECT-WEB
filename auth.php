<?php
// auth.php — guard untuk mewajibkan login sebelum masuk ke web belanja
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Sudah login?
if (empty($_SESSION['user'])) {
    // simpan URL yang diminta, supaya bisa kembali setelah login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/Project/index.php';
    header('Location: /Project/login.php');
    exit;
}

// Optional: nonaktif? tendang
if (isset($_SESSION['user']['is_active']) && (int)$_SESSION['user']['is_active'] !== 1) {
    session_unset();
    session_destroy();
    header('Location: /Project/login.php?inactive=1');
    exit;
}
