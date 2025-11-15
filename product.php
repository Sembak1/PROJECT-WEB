<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Cek produk
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1 LIMIT 1");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='container section'><h2>Produk tidak ditemukan.</h2></div>";
    exit;
}

// Ambil gambar utama
$imgStmt = $pdo->prepare("
    SELECT url 
    FROM product_images 
    WHERE product_id = ?
    ORDER BY is_primary DESC, sort_order ASC
    LIMIT 1
");
$imgStmt->execute([$id]);
$row = $imgStmt->fetch();

$image = $row ? $row['url'] : "uploads/default.png";

include __DIR__ . '/header.php';
?>

<div class="container section" style="max-width:980px;">

    <div style="display:grid;grid-template-columns:1fr 1.1fr;gap:2rem;align-items:start;">

        <!-- FOTO PRODUK -->
        <div>
            <img src="<?= htmlspecialchars($image) ?>" 
                 style="width:100%;border-radius:16px;border:1px solid #e5e7eb;box-shadow:0 4px 18px rgba(0,0,0,.08);">
        </div>

        <!-- DETAIL PRODUK -->
        <div style="padding:1rem 0;">

            <!-- Nama Produk -->
            <h1 style="margin-top:0;font-size:2rem;font-weight:800;color:#111827;">
                <?= htmlspecialchars($product['name']) ?>
            </h1>

            <!-- Harga -->
            <div style="
                font-size:1.6rem;
                font-weight:800;
                color:#ec4899;
                margin:.5rem 0 1.2rem;
            ">
                <?= rupiah($product['base_price']) ?>
            </div>

            <!-- Deskripsi Title -->
            <h3 style="margin-bottom:.3rem;color:#374151;">Deskripsi Produk</h3>

            <!-- Deskripsi -->
            <p style="line-height:1.7;font-size:1rem;color:#4b5563;white-space:pre-line;">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </p>

        </div>

    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
