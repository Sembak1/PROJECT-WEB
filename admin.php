<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Pastikan user login dan memiliki role admin
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

include __DIR__ . '/header.php';

// Ambil data ringkasan dari database
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
?>

<div class="container section">
    <h2>👑 Dashboard Admin</h2>
    <p>Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['user']['name']); ?></strong>!</p>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-top:2rem;">
        <div style="background:#f9fafb;padding:1rem;border-radius:12px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.08);">
            <h3><?php echo $totalUsers; ?></h3>
            <p>Pengguna Terdaftar</p>
        </div>

        <div style="background:#f9fafb;padding:1rem;border-radius:12px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.08);">
            <h3><?php echo $totalProducts; ?></h3>
            <p>Produk Tersedia</p>
        </div>

        <div style="background:#f9fafb;padding:1rem;border-radius:12px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.08);">
            <h3><?php echo $totalOrders; ?></h3>
            <p>Total Pesanan</p>
        </div>

        <div style="background:#f9fafb;padding:1rem;border-radius:12px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.08);">
            <h3><?php echo $pendingOrders; ?></h3>
            <p>Pesanan Pending</p>
        </div>
    </div>

    <div style="margin-top:2rem;">
        <h3>Menu Admin</h3>
        <ul style="line-height:1.8;">
            <li><a href="stock.php" style="color:#ec4899;">Kelola Produk</a></li>
            <li><a href="orders.php" style="color:#ec4899;">Lihat Pesanan</a></li>
            <li><a href="users.php" style="color:#ec4899;">Manajemen Pengguna</a></li>
            <li><a href="logout.php" style="color:#ec4899;">Logout</a></li>
            <li><a href="add_product.php" style="color:#ec4899;">Tambah Produk Baru</a></li>
        </ul>

    </div>
</div>



<?php include __DIR__ . '/footer.php'; ?>