<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

// Jika belum login → arahkan ke login
if (empty($_SESSION['user'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/Project/index.php';
    header('Location: /Project/login.php');
    exit;
}

// Ambil status aktif user langsung dari database
$stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$is_active = $stmt->fetchColumn();

// Jika user dinonaktifkan → paksa logout
if ($is_active != 1) {
    session_unset();
    session_destroy();
    header('Location: /Project/login.php?inactive=1');
    exit;
}
