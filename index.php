<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Ambil semua produk aktif dari database
$stmt = $pdo->query("
  SELECT p.id, p.name, p.slug, p.base_price, 
         COALESCE(pi.url, '/assets/img/default.jpg') AS image_url
  FROM products p
  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
  WHERE p.is_active = 1
  ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/header.php';
?>

<!-- Hero Section -->
<section class="hero" style="text-align:center;padding:3rem 1rem;background:linear-gradient(135deg,#fdf2f8,#fff);">
  <h1 style="font-size:2rem;margin-bottom:.5rem;color:#111827;">✨ Selamat Datang di Glowify Beauty ✨</h1>
  <p style="color:#6b7280;font-size:1rem;">Temukan koleksi skincare dan makeup terbaik kami untuk kecantikanmu 💄</p>
  <a href="products.php" class="btn" style="margin-top:1rem;">Belanja Sekarang</a>
</section>

<!-- Produk Terbaru -->
<div class="container section">
  <h2 style="margin-bottom:1.5rem;text-align:center;">🛍️ Produk Terbaru</h2>

  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.5rem;">
    <?php foreach ($products as $p): ?>
      <div style="border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.05);background:#fff;transition:.2s;">
        <a href="product.php?id=<?php echo $p['id']; ?>" style="text-decoration:none;color:inherit;">
          <img src="<?php echo htmlspecialchars($p['image_url']); ?>" 
               alt="<?php echo htmlspecialchars($p['name']); ?>" 
               style="width:100%;height:200px;object-fit:cover;">
          <div style="padding:1rem;">
            <h3 style="font-size:1.1rem;color:#111827;margin-bottom:.25rem;">
              <?php echo htmlspecialchars($p['name']); ?>
            </h3>
            <p style="color:#ec4899;font-weight:bold;">Rp <?php echo number_format($p['base_price'], 0, ',', '.'); ?></p>
            <button style="margin-top:.5rem;background:#ec4899;color:#fff;border:none;padding:.5rem 1rem;border-radius:10px;cursor:pointer;">
              Lihat Detail
            </button>
          </div>
        </a>
      </div>
    <?php endforeach; ?>

    <?php if (count($products) === 0): ?>
      <p style="grid-column:1/-1;text-align:center;color:#6b7280;">Belum ada produk yang tersedia.</p>
    <?php endif; ?>
  </div>
</div>

<!-- Tentang -->
<section style="background:#f9fafb;padding:3rem 1rem;text-align:center;margin-top:3rem;">
  <h2 style="margin-bottom:1rem;">🌸 Tentang Glowify</h2>
  <p style="max-width:640px;margin:0 auto;color:#4b5563;">
      hahahahahahha Glowify Beauty adalah toko kecantikan online terpercaya yang menyediakan berbagai produk skincare dan makeup berkualitas tinggi.
    Kami berkomitmen untuk membuat setiap pelanggan  tampil percaya diri dan bersinar setiap hari!
  </p>
</section>

<?php include __DIR__ . '/footer.php'; ?>
