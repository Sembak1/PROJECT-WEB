<?php
// Mengimpor sistem autentikasi (untuk cek login/role)
require_once __DIR__ . '/../inti/autentikasi.php';

// Mengimpor fungsi umum seperti rupiah(), keranjang, dll
require_once __DIR__ . '/../inti/fungsi.php';


/* =====================================================
   CEK ID PRODUK DI URL
   Jika tidak ada ID, redirect kembali ke daftar produk
===================================================== */
if (!isset($_GET['id'])) {
    header("Location: daftar_produk.php");
    exit;
}

$id = (int) $_GET['id']; // Sanitize ID


/* =====================================================
   MENGAMBIL DATA PRODUK DARI DATABASE
===================================================== */
$stmt = $pdo->prepare("
    SELECT *
    FROM products
    WHERE id = ? AND is_active = 1
    LIMIT 1
");
$stmt->execute([$id]);
$product = $stmt->fetch();

// Jika produk tidak ditemukan → tampilkan pesan
if (!$product) {
    include __DIR__ . '/../header.php';
    echo "<h2>Produk tidak ditemukan.</h2>";
    include __DIR__ . '/../footer.php';
    exit;
}


/* =====================================================
   AMBIL GAMBAR UTAMA PRODUK
===================================================== */
$imgStmt = $pdo->prepare("
    SELECT url
    FROM product_images
    WHERE product_id = ?
    ORDER BY is_primary DESC, sort_order ASC
    LIMIT 1
");
$imgStmt->execute([$id]);
$row = $imgStmt->fetch();

$image = $row ? $row['url'] : "aset/uploads/default.png";
// Jika produk tidak punya foto → pakai default


// Load header (navbar + HTML awal)
include __DIR__ . '/../header.php';
?>



<!-- ========================= CSS UNTUK HALAMAN DETAIL ========================= -->
<style>
    body { font-family: "Inter", sans-serif; }

    /* Wrapper seluruh konten detail */
    .detail-wrapper { 
        display: flex; 
        gap: 40px; 
        margin-top: 40px; 
    }

    /* Gambar produk */
    .detail-gambar img {
        width: 420px;
        aspect-ratio: 4 / 5;
        object-fit: cover;
        border-radius: 14px;
    }

    /* Judul nama produk */
    .detail-info h1 { 
        font-size: 1.9rem; 
        font-weight: 800; 
        color:#db2777; 
    }

    /* Harga produk */
    .harga { 
        font-size: 24px; 
        font-weight: 900; 
        color: #ec4899; 
        margin:10px 0 18px; 
    }

    /* Box info stok */
    .stok-box {
        margin-bottom: 14px;
        font-size: 1.1rem;
        font-weight: 600;
        color: #db2777;
        padding: 6px 12px;
        background: #fce7f3;
        display: inline-block;
        border-radius: 10px;
    }

    /* Jika stok habis */
    .stok-habis {
        background: #ffe1e1;
        color: #b91c1c;
    }

    /* Tombol pink */
    .btn-pink {
        background: #ec4899;
        border: none;
        padding: 11px 20px;
        font-size: 16px;
        font-weight: 600;
        color: white;
        border-radius: 12px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(236, 72, 153, 0.25);
        transition: .25s;
        margin-top: 12px;
        display: inline-block;
    }

    /* Jika tombol disabled */
    .btn-disabled {
        background: #cfcfcf;
        box-shadow: none;
        cursor: not-allowed;
    }
</style>



<!-- ========================= WRAPPER DETAIL PRODUK ========================= -->
<div class="detail-wrapper">

    <!-- BAGIAN FOTO PRODUK -->
    <div class="detail-gambar">
        <img src="/glowify/<?= htmlspecialchars($image); ?>">
    </div>


    <!-- BAGIAN INFO PRODUK -->
    <div class="detail-info">

        <!-- Nama Produk -->
        <h1><?= htmlspecialchars($product['name']); ?></h1>

        <!-- Harga Produk -->
        <div class="harga"><?= rupiah($product['base_price']); ?></div>


        <!-- ===================== STOK ===================== -->
        <?php if ($product['stock'] > 0): ?>
            <!-- Jika stok masih ada -->
            <div class="stok-box">Stok: <?= $product['stock'] ?></div>
        <?php else: ?>
            <!-- Jika stok habis -->
            <div class="stok-box stok-habis">Stok Habis</div>
        <?php endif; ?>


        <!-- Deskripsi Produk -->
        <h3 style="color:#db2777;margin-top:14px;">Deskripsi Produk</h3>
        <p><?= nl2br(htmlspecialchars($product['description'])); ?></p>


        <!-- ===================== FORM TAMBAH KERANJANG ===================== -->
        <form action="keranjang.php" method="post" style="margin-top:20px;">

            <!-- Aksi untuk file keranjang.php -->
            <input type="hidden" name="aksi" value="tambah">

            <!-- ID Produk -->
            <input type="hidden" name="product_id" value="<?= $product['id']; ?>">

            <?php if ($product['stock'] > 0): ?>

                <!-- Input jumlah beli -->
                <label>Jumlah:</label><br>
                <input 
                    type="number" 
                    name="qty" 
                    value="1" 
                    min="1" 
                    max="<?= $product['stock'] ?>"
                    style="width:80px;padding:6px;border-radius:8px;border:1px solid #f3b3d4;"
                >
                <br><br>

                <!-- Tombol Masukkan ke Keranjang -->
                <button type="submit" class="btn-pink">Masukkan ke Keranjang</button>

            <?php else: ?>

                <!-- Tombol disabled jika stok habis -->
                <button type="button" class="btn-pink btn-disabled">Stok Habis</button>

            <?php endif; ?>

        </form>

    </div>
</div>



<!-- FOOTER -->
<?php include __DIR__ . '/../footer.php'; ?>
