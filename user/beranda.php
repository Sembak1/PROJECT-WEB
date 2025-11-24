<?php
session_start();
require_once __DIR__ . '/../inti/koneksi_database.php';
require_once __DIR__ . '/../inti/autentikasi.php';
require_once __DIR__ . '/../inti/fungsi.php';

// Jika ingin halaman home hanya untuk user login, aktifkan:
// cekLogin();

// ===================== Ambil Produk Terbaru =====================
$stmt = $pdo->query("
    SELECT 
        p.id, 
        p.name, 
        p.base_price,
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

include __DIR__ . '/../header.php';
?>

<!-- ===================== HERO SECTION ===================== -->
<section class="hero-banner">
    <h1>Selamat Datang di Glowify Beauty</h1>
    <p>Temukan koleksi skincare & makeup terbaik untuk kecantikan alami kamu.</p>

    <a href="/glowify/user/daftar_produk.php" class="btn primary">
        Belanja Sekarang
    </a>
</section>

<!-- ===================== PRODUK TERBARU ===================== -->
<div class="container section">
    <h2 class="section-title">Produk Terbaru</h2>

    <div class="produk-grid">

        <?php if (!empty($products)): ?>
            <?php foreach ($products as $p): ?>
                
                <?php
                    $image = $p['image_url'] 
                        ? "/glowify/" . $p['image_url']
                        : "/glowify/aset/gambar/default.png";
                ?>

                <div class="produk-card">

                    <a href="/glowify/user/detail_produk.php?id=<?= $p['id'] ?>">
                        <img src="<?= htmlspecialchars($image); ?>" 
                            alt="<?= htmlspecialchars($p['name']); ?>" 
                            class="produk-img">
                    </a>

                    <div class="produk-info">
                        <strong class="produk-nama">
                            <?= htmlspecialchars($p['name']); ?>
                        </strong>

                        <div class="produk-harga">
                            <?= rupiah($p['base_price']); ?>
                        </div>

                        <a href="/glowify/user/detail_produk.php?id=<?= $p['id'] ?>" 
                           class="btn small">
                            Lihat Detail
                        </a>
                    </div>

                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-data">Belum ada produk tersedia.</p>
        <?php endif; ?>

    </div>
</div>

<!-- ===================== TENTANG GLOWIFY ===================== -->
<section class="section about">
    <h2>Tentang Glowify</h2>

    <p class="about-text">
        Glowify Beauty adalah toko kecantikan online terpercaya yang menyediakan produk skincare dan makeup berkualitas.
        Kami berkomitmen menghadirkan kecantikan alami untuk setiap wanita, agar selalu tampil percaya diri setiap hari!
    </p>
</section>

<?php include __DIR__ . '/../footer.php'; ?>
