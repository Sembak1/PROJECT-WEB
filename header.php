<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/inti/fungsi.php';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Glowify Beauty</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="/glowify/aset/css/gaya.css">
</head>
<body>

<header class="navbar">
  <div class="container wrap">
    <a href="/glowify/user/beranda.php" class="brand">Glowify Beauty</a>

    <nav class="navlinks">
      <a href="/glowify/user/beranda.php">Beranda</a>
      <a href="/glowify/user/daftar_produk.php">Produk</a>

      <?php if (!empty($_SESSION['user'])): ?>
        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
          <a href="/glowify/admin/dashboard_admin.php">Admin</a>
        <?php else: ?>
          <a href="/glowify/user/keranjang.php">
            Keranjang
            (<?php echo array_sum(array_map(fn($i) => $i['qty'], keranjang_item())); ?>)
          </a>
        <?php endif; ?>
        <a href="/glowify/akun/keluar.php" class="btn secondary">Keluar</a>
      <?php else: ?>
        <a href="/glowify/akun/masuk.php">Masuk</a>
        <a href="/glowify/akun/daftar.php" class="btn">Daftar</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="section container">
