<?php
session_start();
require_once __DIR__ . '/../inti/koneksi_database.php';
require_once __DIR__ . '/../inti/autentikasi.php';
cek_admin();

include __DIR__ . '/../header.php';

$totalPengguna  = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalProduk    = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalPesanan   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pesananPending = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
?>

<!-- ================== GLOWIFY NEO INLINE CSS ================== -->
<style>
:root{
  --bg:#ffffff;--surface:#ffffff;--text:#0f172a;--muted:#6b7280;
  --border:#e5e7eb;--primary:#ec4899;--primary-600:#db2777;
  --ring:#fbcfe8;--shadow:0 6px 24px rgba(17,24,39,.08);
  --radius:14px;
}
*{box-sizing:border-box}
body{
  margin:0;
  font:15px/1.6 Inter,ui-sans-serif,system-ui,Segoe UI,Roboto,Arial;
  color:var(--text);
  background:var(--bg);
}

h1,h2,h3{margin:0 0 .5rem;color:var(--text);}

/* ====== GRID STATS ====== */
.grid-info{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:1.25rem;
    margin:1.5rem 0;
}

.info-card{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:var(--radius);
    padding:1rem;
    text-align:center;
    box-shadow:var(--shadow);
    transition:.15s ease;
}
.info-card:hover{
    transform:translateY(-3px);
    box-shadow:0 12px 36px rgba(17,24,39,.12);
}
.info-card h3{
    margin:0;
    font-size:1.6rem;
    color:var(--primary);
}
.info-card p{
    margin:.25rem 0 0;
    font-size:.85rem;
    color:var(--muted);
}

/* ====== ADMIN MENU ====== */
.admin-menu{
    list-style:none;
    padding:0;
    margin:1rem 0 2rem;
    display:flex;
    gap:.65rem;
    flex-wrap:wrap;
}
.admin-menu a{
    display:inline-block;
    background:var(--primary);
    padding:.55rem 1rem;
    border-radius:var(--radius);
    color:white;
    font-weight:600;
    box-shadow:var(--shadow);
    border:1px solid var(--primary);
    transition:.15s ease;
}
.admin-menu a:hover{
    transform:translateY(-2px);
    background:var(--primary-600);
}

/* ====== CONTAINER ====== */
.admin-container{
    max-width:1100px;
    margin:20px auto;
    padding:0 16px;
}
</style>

<!-- ================== CONTENT ================== -->
<div class="admin-container pop">

    <h2>Dashboard Admin</h2>
    <p style="margin:0 0 1rem;color:var(--muted);">
        Selamat datang, 
        <strong><?= htmlspecialchars($_SESSION['user']['name']); ?></strong>!
    </p>

    <!-- ========== Statistik ========== -->
    <div class="grid-info">
        <div class="info-card">
            <h3><?= $totalPengguna ?></h3>
            <p>Pengguna Terdaftar</p>
        </div>

        <div class="info-card">
            <h3><?= $totalProduk ?></h3>
            <p>Produk Tersedia</p>
        </div>

        <div class="info-card">
            <h3><?= $totalPesanan ?></h3>
            <p>Total Pesanan</p>
        </div>

        <div class="info-card">
            <h3><?= $pesananPending ?></h3>
            <p>Pesanan Pending</p>
        </div>
    </div>

    <h3 style="margin-bottom:.5rem;">Menu Admin</h3>

    <!-- ========== Menu Admin ========== -->
    <ul class="admin-menu">
        <li><a href="kelola_produk.php">Kelola Produk</a></li>
        <li><a href="tambah_produk.php">Tambah Produk</a></li>
        <li><a href="kelola_pesanan.php">Lihat Pesanan</a></li>
        <li><a href="buat_admin.php">Tambah Admin</a></li>
    </ul>

</div>

<?php include __DIR__ . '/../footer.php'; ?>
