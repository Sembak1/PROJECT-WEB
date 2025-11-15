<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/**
 * (Opsional) Jika ingin menampilkan peringatan stok habis untuk admin.
 * Tidak digunakan di navbar, tapi disiapkan untuk nanti.
 */
$showAdminButton = false;
try {
  $stmt = $pdo->query("SELECT COUNT(*) FROM inventory WHERE stock <= 0");
  $showAdminButton = ((int)$stmt->fetchColumn() > 0);
} catch (Throwable $e) {
  $showAdminButton = false;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Glowify Beauty</title>

  <!-- CSS & JS -->
  <link rel="stylesheet" href="style.css">
</head>

<body>
<header class="navbar">
  <div class="container wrap">
    <a href="index.php" class="brand">Glowify Beauty</a>

    <nav class="navlinks">
      <a href="index.php">Beranda</a>
      <a href="products.php">Produk</a>

      <?php if (!empty($_SESSION['user'])): ?>
        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
          <!-- TOMBOL ADMIN -->
          <a href="admin.php">Halaman Admin</a>
          <?php if ($showAdminButton): ?>
            <span style="color:#dc2626;">(⚠️ Ada stok habis)</span>
          <?php endif; ?>
        <?php else: ?>
          <!-- UNTUK USER BIASA -->
          <a href="cart.php">Keranjang
            (<?php echo array_sum(array_map(fn($i) => $i['qty'], cart_items())); ?>)
          </a>
        <?php endif; ?>

        <a href="logout.php" class="btn secondary">Keluar</a>
      <?php else: ?>
        <!-- Hanya tampil jika belum login -->
        <a href="login.php">Masuk</a>
        <a href="register.php" class="btn">Daftar</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<!-- Konten halaman -->
<main class="section container">
