<?php
require_once __DIR__ . '/../inti/autentikasi.php';
require_once __DIR__ . '/../inti/fungsi.php';

/* ----------------------------------------------------------
   CEK ID PRODUK
---------------------------------------------------------- */
if (!isset($_GET['id'])) {
    header("Location: daftar_produk.php");
    exit;
}

$id = (int) $_GET['id'];

/* ----------------------------------------------------------
   AMBIL DATA PRODUK
---------------------------------------------------------- */
$stmt = $pdo->prepare("
    SELECT *
    FROM products
    WHERE id = ? AND is_active = 1
    LIMIT 1
");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    include __DIR__ . '/../header.php';
    echo "<h2>Produk tidak ditemukan.</h2>";
    include __DIR__ . '/../footer.php';
    exit;
}

/* ----------------------------------------------------------
   AMBIL GAMBAR PRODUK UTAMA
---------------------------------------------------------- */
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

include __DIR__ . '/../header.php';
?>

<!-- =========================================================
     HALAMAN DETAIL PRODUK — GAMBAR DI SISI KIRI
========================================================= -->
<style>
    .detail-wrapper {
        display: flex;
        gap: 30px;
        margin-top: 20px;
        align-items: flex-start;
    }

    .detail-gambar img {
        width: 420px;
        height: 420px;
        object-fit: cover;
        border-radius: 12px;
    }

    .detail-info {
        flex: 1;
    }

    .detail-info h1 {
        margin-bottom: 10px;
    }

    .detail-info .harga {
        font-size: 22px;
        font-weight: bold;
        margin-bottom: 15px;
        color: #e6007e; /* Glowify Pink */
    }

    .detail-info form input[type="number"] {
        width: 80px;
        padding: 6px;
        margin-bottom: 10px;
    }

    .detail-info button {
        margin-top: 10px;
        width: 100%;
        padding: 12px;
        font-size: 16px;
    }
</style>

<div class="detail-wrapper">

    <!-- GAMBAR KIRI -->
    <div class="detail-gambar">
        <img src="/glowify/<?= htmlspecialchars($image); ?>" 
             alt="<?= htmlspecialchars($product['name']); ?>">
    </div>

    <!-- INFORMASI PRODUK DI KANAN -->
    <div class="detail-info">

        <h1><?= htmlspecialchars($product['name']); ?></h1>

        <div class="harga">
            <?= rupiah($product['base_price']); ?>
        </div>

        <h3>Deskripsi Produk</h3>
        <p><?= nl2br(htmlspecialchars($product['description'])); ?></p>

        <!-- FORM TAMBAH KE KERANJANG -->
        <form action="keranjang.php" method="post">

            <input type="hidden" name="aksi" value="tambah">
            <input type="hidden" name="product_id" value="<?= $product['id']; ?>">

            <label>Jumlah:</label><br>
            <input type="number" name="qty" value="1" min="1">

            <button type="submit" class="btn" style="padding:10px 18px; font-size:16px; width:auto; display:inline-block;">
                Masukkan ke Keranjang
            </button>

        </form>

    </div>

</div>

<?php include __DIR__ . '/../footer.php'; ?>
