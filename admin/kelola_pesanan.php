<?php
session_start(); 
// Memulai sesi untuk mengelola login admin.

require_once __DIR__ . '/../inti/koneksi_database.php';
// Mengimpor koneksi database PDO.

require_once __DIR__ . '/../inti/autentikasi.php';
// Mengimpor fungsi autentikasi (cek admin).

require_once __DIR__ . '/../inti/fungsi.php';
// Mengimpor fungsi tambahan seperti format rupiah.

cek_admin();
// Mengecek apakah user login sebagai admin.

$error = "";
$success = "";
// Variabel untuk menyimpan pesan error / sukses.


/* ===================================
   HAPUS PESANAN
=================================== */
if (isset($_POST['hapus_pesanan'])) {
    // Mengecek apakah tombol hapus diklik.

    $orderId = (int)$_POST['order_id'];
    // Mengambil ID pesanan yang ingin dihapus.

    try {
        $pdo->beginTransaction();
        // Memulai transaksi agar penghapusan aman.

        // Hapus daftar produk di pesanan tersebut.
        $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$orderId]);

        // Hapus pesanan utama.
        $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$orderId]);

        $pdo->commit();
        // Jika berhasil, commit transaksi.

        $success = "Pesanan berhasil dihapus.";
    } catch (Throwable $e) {
        $pdo->rollBack();
        // Jika gagal, kembalikan transaksi.

        $error = "Gagal menghapus pesanan.";
    }
}


/* ===================================
   AMBIL LIST PESANAN
=================================== */
$stmt = $pdo->query("
    SELECT 
        o.id,
        o.total_price AS total,
        o.created_at,
        u.name AS nama_user
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    ORDER BY o.id DESC
");
// Query mengambil daftar pesanan beserta nama user.

$orders = $stmt->fetchAll();
// Simpan semua data pesanan ke array.

include __DIR__ . '/../header.php';
// Memanggil header HTML.
?>

<!-- ================== STYLE PREMIUM GLOWIFY ================== -->
<style>
/* Wrapper halaman */
.page {
    max-width: 1050px;
    margin: 35px auto;
    padding: 0 20px;
    font-family: 'Inter', sans-serif;
}

/* Judul halaman */
.title {
    font-size: 1.7rem;
    font-weight: 700;
    margin-bottom: 18px;
    color: #333;
    text-align: center;
}

/* ALERT (pesan error & sukses) */
.alert {
    padding: 14px 18px;
    border-radius: 12px;
    font-weight: 600;
    margin-bottom: 20px;
}
.alert.error { background:#fee2e2; border:1px solid #fecaca; color:#b91c1c; }
.alert.success { background:#dcfce7; border:1px solid #bbf7d0; color:#166534; }

/* BOX TABEL */
.table-box {
    background: #ffffff;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(236, 72, 153, 0.12);
}

/* TABEL PREMIUM */
.table-premium {
    width: 100%;
    border-collapse: collapse;
}

/* Header tabel */
.table-premium thead {
    background: #ec4899;
    color: white;
}

/* Kolom header */
.table-premium th {
    padding: 14px;
    font-weight: 600;
    text-align: center;
    white-space: nowrap;
}

/* Kolom body tabel */
.table-premium td {
    padding: 14px;
    border-bottom: 1px solid #f3f4f6;
    font-size: 15px;
    white-space: nowrap;
    text-align: center;
}

/* Lebar kolom fix agar tabel rapi */
.col-id     { width: 80px; }
.col-name   { width: 260px; text-align: left !important; padding-left: 24px !important; }
.col-total  { width: 150px; }
.col-date   { width: 230px; }
.col-action { width: 120px; }

/* Tombol hapus pesanan */
.btn-del {
    padding: 7px 16px;
    border: 1px solid #ec4899;
    border-radius: 10px;
    background: white;
    color: #ec4899;
    font-weight: 600;
    cursor: pointer;
    transition: .2s;
}
.btn-del:hover {
    background: #ffe0f2;
}

/* Tombol 'Tutup' kembali ke dashboard */
.btn-tutup {
    margin-top: 24px;
    display: inline-block;
    padding: 12px 24px;
    background: #ec4899;
    border-radius: 12px;
    color: white;
    font-weight: 600;
    font-size: 16px;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(236,72,153,0.3);
}
.btn-tutup:hover { background:#db2777; }
</style>

<!-- ================== HALAMAN HTML ================== -->
<div class="page">

    <h2 class="title">Lihat Pesanan</h2>
    <!-- Judul utama halaman -->

    <?php if ($error): ?>
        <!-- Jika ada error, tampilkan alert merah -->
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <!-- Jika sukses, tampilkan alert hijau -->
        <div class="alert success"><?= $success ?></div>
    <?php endif; ?>

    <div class="table-box">
        <!-- Box pembungkus tabel -->

        <table class="table-premium">
            <!-- Tabel tampilan pesanan -->

            <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th class="col-name">Pelanggan</th>
                    <th class="col-total">Total</th>
                    <th class="col-date">Tanggal</th>
                    <th class="col-action">Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($orders as $order): ?>
                <!-- Looping menampilkan setiap pesanan -->

                <tr>
                    <td>#<?= $order['id']; ?></td>
                    <!-- Menampilkan ID pesanan -->

                    <td class="col-name"><?= htmlspecialchars($order['nama_user']); ?></td>
                    <!-- Menampilkan nama pelanggan -->

                    <td><?= rupiah($order['total']); ?></td>
                    <!-- Menampilkan total harga dalam format rupiah -->

                    <td><?= $order['created_at']; ?></td>
                    <!-- Menampilkan tanggal pesanan -->

                    <td>
                        <!-- Form hapus pesanan -->
                        <form method="post" 
                              onsubmit="return confirm('Hapus pesanan ini?');" 
                              style="display:inline;">

                            <!-- Mengirimkan ID pesanan secara hidden -->
                            <input type="hidden" name="order_id" value="<?= $order['id']; ?>">

                            <!-- Tombol hapus -->
                            <button class="btn-del" name="hapus_pesanan">Hapus</button>
                        </form>
                    </td>
                </tr>

                <?php endforeach; ?>
            </tbody>

        </table>
    </div>

    <!-- Tombol kembali ke dashboard -->
    <a href="/glowify/admin/dashboard_admin.php" class="btn-tutup">Tutup</a>
</div>

<?php include __DIR__ . '/../footer.php'; ?>
<!-- Menutup HTML dengan footer -->
