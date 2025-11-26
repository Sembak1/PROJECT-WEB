<?php
session_start(); 
// Memulai sesi agar data login user bisa digunakan.

require_once __DIR__ . '/../inti/koneksi_database.php';
// Mengimpor file koneksi database (PDO).

require_once __DIR__ . '/../inti/autentikasi.php';
// Mengimpor file autentikasi untuk pengecekan role & login.

cek_admin();
// Mengecek apakah user adalah admin. Jika tidak, diarahkan keluar.

include __DIR__ . '/../header.php';
// Menyertakan file header tampilan website (navbar, setup HTML).


// ===============================
//      QUERY DATA STATISTIK
// ===============================

// Mengambil total pengguna dari tabel users.
$totalPengguna  = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Mengambil total produk dari tabel products.
$totalProduk    = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

// Mengambil total pesanan dari tabel orders.
$totalPesanan   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// Mengambil jumlah pesanan yang masih pending.
$pesananPending = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
?>

<!-- ================== GLOWIFY NEO INLINE CSS ================== -->
<style>
/* Variabel warna dan styling utama */
:root{
  --bg:#ffffff;--surface:#ffffff;--text:#0f172a;--muted:#6b7280;
  --border:#e5e7eb;--primary:#ec4899;--primary-600:#db2777;
  --ring:#fbcfe8;--shadow:0 6px 24px rgba(17,24,39,.08);
  --radius:14px;
}

/* Reset ukuran box */
*{box-sizing:border-box}

/* Styling dasar body */
body{
  margin:0;
  font:15px/1.6 Inter,ui-sans-serif,system-ui,Segoe UI,Roboto,Arial;
  color:var(--text);
  background:var(--bg);
}

/* Styling judul */
h1,h2,h3{margin:0 0 .5rem;color:var(--text);}


/* ===================== GRID KOTAK STATISTIK ===================== */
.grid-info{
    display:grid; /* Membuat layout grid */
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); 
    /* Responsive: minimal 200px per card */
    gap:1.25rem; /* Jarak antar card */
    margin:1.5rem 0;
}

/* Tampilan kartu statistik */
.info-card{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:var(--radius);
    padding:1rem;
    text-align:center;
    box-shadow:var(--shadow);
    transition:.15s ease;
}

/* Efek hover card */
.info-card:hover{
    transform:translateY(-3px);
    box-shadow:0 12px 36px rgba(17,24,39,.12);
}

/* Angka besar pada card */
.info-card h3{
    margin:0;
    font-size:1.6rem;
    color:var(--primary);
}

/* Keterangan kecil pada card */
.info-card p{
    margin:.25rem 0 0;
    font-size:.85rem;
    color:var(--muted);
}


/* ===================== MENU ADMIN ===================== */
.admin-menu{
    list-style:none;  /* Menghilangkan bullet list */
    padding:0;
    margin:1rem 0 2rem;
    display:flex;      /* Membuat menu horizontal */
    gap:.65rem;        /* Jarak antar tombol */
    flex-wrap:wrap;    /* Agar tetap rapi di layar kecil */
}

/* Style tombol menu admin */
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

/* Efek hover */
.admin-menu a:hover{
    transform:translateY(-2px);
    background:var(--primary-600);
}


/* ===================== CONTAINER ===================== */
.admin-container{
    max-width:1100px;   /* Lebar maksimum container */
    margin:20px auto;   /* Posisi tengah */
    padding:0 16px;     /* Spasi kiri kanan */
}
</style>


<!-- ================== CONTENT ================== -->
<div class="admin-container pop">
    <!-- Wrapper utama dashboard admin -->

    <h2>Dashboard Admin</h2>
    <!-- Judul halaman -->

    <p style="margin:0 0 1rem;color:var(--muted);">
        Selamat datang, 
        <strong><?= htmlspecialchars($_SESSION['user']['name']); ?></strong>!
        <!-- Menampilkan nama admin yang sedang login -->
    </p>

    <!-- ========== Statistik Dashboard ========== -->
    <div class="grid-info">
        <!-- Kotak statistik dalam bentuk grid -->

        <div class="info-card">
            <!-- Card jumlah pengguna -->
            <h3><?= $totalPengguna ?></h3>
            <p>Pengguna Terdaftar</p>
        </div>

        <div class="info-card">
            <!-- Card jumlah produk -->
            <h3><?= $totalProduk ?></h3>
            <p>Produk Tersedia</p>
        </div>

        <div class="info-card">
            <!-- Card total pesanan -->
            <h3><?= $totalPesanan ?></h3>
            <p>Total Pesanan</p>
        </div>

        <div class="info-card">
            <!-- Card pesanan pending -->
            <h3><?= $pesananPending ?></h3>
            <p>Pesanan Masuk</p>
        </div>
    </div>

    <h3 style="margin-bottom:.5rem;">Menu Admin</h3>
    <!-- Judul menu admin -->

    <!-- ========== Menu Admin ========== -->
    <ul class="admin-menu">
        <!-- Menu navigasi admin -->
        <li><a href="kelola_produk.php">Kelola Produk</a></li>
        <li><a href="tambah_produk.php">Tambah Produk</a></li>
        <li><a href="kelola_pesanan.php">Lihat Pesanan</a></li>
        <li><a href="buat_admin.php">Tambah Admin</a></li>
    </ul>

</div>

<?php include __DIR__ . '/../footer.php'; ?>
<!-- Menyertakan footer HTML -->
