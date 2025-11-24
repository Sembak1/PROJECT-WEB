<?php
require_once __DIR__ . '/../inti/autentikasi.php';
require_once __DIR__ . '/../inti/fungsi.php';

/* =====================================================
   CEK ID PRODUK
===================================================== */
if (!isset($_GET['id'])) {
    header("Location: daftar_produk.php");
    exit;
}

$id = (int) $_GET['id'];

/* =====================================================
   AMBIL DATA PRODUK
===================================================== */
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

/* =====================================================
   AMBIL GAMBAR UTAMA
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

include __DIR__ . '/../header.php';
?>


<style>
    body { font-family: "Inter", sans-serif; }
    .detail-wrapper { display: flex; gap: 40px; margin-top: 40px; }
    .detail-gambar img {
        width: 420px;
        aspect-ratio: 4 / 5;
        object-fit: cover;
        border-radius: 14px;
    }
    .detail-info h1 { font-size: 1.9rem; font-weight: 800; color:#db2777; }
    .harga { font-size: 24px; font-weight: 900; color: #ec4899; margin:10px 0 18px; }

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
    .stok-habis {
        background: #ffe1e1;
        color: #b91c1c;
    }

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
    .btn-disabled {
        background: #cfcfcf;
        box-shadow: none;
        cursor: not-allowed;
    }
</style>


<div class="detail-wrapper">

    <!-- FOTO PRODUK -->
    <div class="detail-gambar">
        <img src="/glowify/<?= htmlspecialchars($image); ?>">
    </div>

    <!-- INFO PRODUK -->
    <div class="detail-info">

        <h1><?= htmlspecialchars($product['name']); ?></h1>

        <div class="harga"><?= rupiah($product['base_price']); ?></div>

        <!-- ===================== STOK PRODUK ===================== -->
        <?php if ($product['stock'] > 0): ?>
            <div class="stok-box">Stok: <?= $product['stock'] ?></div>
        <?php else: ?>
            <div class="stok-box stok-habis">Stok Habis</div>
        <?php endif; ?>

        <h3 style="color:#db2777;margin-top:14px;">Deskripsi Produk</h3>
        <p><?= nl2br(htmlspecialchars($product['description'])); ?></p>

        <!-- ===================== FORM TAMBAH KERANJANG ===================== -->
        <form action="keranjang.php" method="post" style="margin-top:20px;">

            <input type="hidden" name="aksi" value="tambah">
            <input type="hidden" name="product_id" value="<?= $product['id']; ?>">

            <?php if ($product['stock'] > 0): ?>
                <label>Jumlah:</label><br>
                <input type="number" name="qty" value="1" min="1" max="<?= $product['stock'] ?>"
                       style="width:80px;padding:6px;border-radius:8px;border:1px solid #f3b3d4;">
                <br><br>

                <button type="submit" class="btn-pink">Masukkan ke Keranjang</button>
            <?php else: ?>
                <button type="button" class="btn-pink btn-disabled">Stok Habis</button>
            <?php endif; ?>

        </form>

    </div>
</div>


<?php include __DIR__ . '/../footer.php'; ?>
