<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Ambil produk terbaru
$stmt = $pdo->query("
    SELECT p.id, p.name, p.base_price,
        (
            SELECT url 
            FROM product_images 
            WHERE product_id = p.id 
            ORDER BY is_primary DESC, sort_order ASC 
            LIMIT 1
        ) AS image_url
    FROM products p
    WHERE p.is_active = 1
    ORDER BY p.created_at DESC
    LIMIT 12
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/header.php';
?>

<!-- ===================== HERO SECTION ===================== -->
<section class="hero section" style="text-align:center;background:linear-gradient(135deg,#fdf2f8,#ffffff);padding:3rem 1rem;">
    <h1 style="font-size:2rem;font-weight:800;margin-bottom:.5rem;color:#111827;">
        ✨ Selamat Datang di Glowify Beauty ✨
    </h1>

    <p style="color:#6b7280;max-width:600px;margin:0 auto 1rem;font-size:1rem;">
        Temukan koleksi skincare & makeup terbaik untuk kecantikan alami kamu.
    </p>

    <a href="products.php" class="btn" style="margin-top:1rem;">Belanja Sekarang</a>
</section>

<!-- ===================== PRODUK TERBARU ===================== -->
<div class="container section">
    <h2 style="text-align:center;margin-bottom:1.5rem;font-weight:700;font-size:1.4rem;">
        🛍️ Produk Terbaru
    </h2>

    <div class="grid">

        <?php foreach ($products as $p): ?>

            <?php
                // cek gambar
                $img = (!empty($p['image_url']) && file_exists(__DIR__ . '/' . $p['image_url']))
                    ? $p['image_url']
                    : 'uploads/default.png';
            ?>

            <div class="card pop">

                <a href="product.php?id=<?= $p['id'] ?>">
                    <img src="<?= htmlspecialchars($img); ?>" 
                         alt="<?= htmlspecialchars($p['name']); ?>">
                </a>

                <div class="content">
                    <strong><?= htmlspecialchars($p['name']); ?></strong>

                    <div class="price">
                        Rp <?= number_format($p['base_price'], 0, ',', '.'); ?>
                    </div>

                    <a href="product.php?id=<?= $p['id'] ?>" class="btn">
                        Lihat Detail
                    </a>
                </div>

            </div>

        <?php endforeach; ?>

        <?php if (count($products) === 0): ?>
            <p style="grid-column:1/-1;text-align:center;color:#6b7280;">
                Belum ada produk tersedia.
            </p>
        <?php endif; ?>

    </div>
</div>

<!-- ===================== TENTANG GLOWIFY ===================== -->
<section class="section" style="background:#f9fafb;text-align:center;margin-top:3rem;padding:3rem 1rem;">
    <h2 style="margin-bottom:1rem;font-weight:700;">🌸 Tentang Glowify</h2>

    <p style="max-width:640px;margin:0 auto;color:#4b5563;font-size:1rem;line-height:1.7;">
        Glowify Beauty adalah toko kecantikan online terpercaya yang menyediakan produk skincare dan makeup berkualitas.
        Kami berkomitmen menghadirkan kecantikan alami untuk setiap wanita, agar tampil percaya diri setiap hari!
    </p>
</section>

<?php include __DIR__ . '/footer.php'; ?>
