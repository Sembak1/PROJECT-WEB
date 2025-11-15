<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
include __DIR__ . '/header.php';

// Query daftar produk
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $pdo->prepare("
        SELECT id, name, base_price 
        FROM products
        WHERE is_active = 1 AND (name LIKE :q OR description LIKE :q)
        ORDER BY created_at DESC
    ");
    $stmt->execute([':q' => $like]);
} else {
    $stmt = $pdo->query("
        SELECT id, name, base_price 
        FROM products
        WHERE is_active = 1
        ORDER BY created_at DESC
    ");
}

$products = $stmt->fetchAll();
?>

<div class="container">
    <h2>Katalog Produk</h2>

    <form class="header-search" method="get" style="margin-bottom:1rem;">
        <input type="text" name="q" placeholder="Cari produk..." value="<?= htmlspecialchars($q) ?>">
        <button class="btn">Cari</button>
    </form>

    <?php if (!$products): ?>
        <p>Tidak ada produk.</p>

    <?php else: ?>
    <div class="grid">

        <?php foreach ($products as $p): ?>

            <?php
            // Ambil gambar utama dari product_images
            $imgStmt = $pdo->prepare("
                SELECT url 
                FROM product_images 
                WHERE product_id = ? 
                ORDER BY is_primary DESC, sort_order ASC 
                LIMIT 1
            ");
            $imgStmt->execute([$p['id']]);
            $imgRow = $imgStmt->fetch();

            $image = $imgRow ? $imgRow['url'] : "uploads/default.png";
            ?>

            <div class="card">
                <a href="product.php?id=<?= $p['id'] ?>">
                    <img src="<?= htmlspecialchars($image) ?>" 
                         alt="<?= htmlspecialchars($p['name']) ?>">
                </a>

                <div class="content">
                    <strong><?= htmlspecialchars($p['name']) ?></strong>
                    <div class="price"><?= rupiah($p['base_price']) ?></div>

                    <a class="btn" href="product.php?id=<?= $p['id'] ?>">Lihat Detail</a>
                </div>
            </div>

        <?php endforeach; ?>

    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
