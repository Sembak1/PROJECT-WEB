<?php require_once __DIR__ . '/auth.php'; ?>
<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
include __DIR__ . '/header.php';

// Hanya pengguna yang login yang bisa melihat produk
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$products = [];

if ($q !== '') {
    // Pencarian aman tanpa FULLTEXT
    $like = '%' . $q . '%';
    $stmt = $pdo->prepare("SELECT id, name, slug, base_price FROM products
                           WHERE is_active=1 AND (name LIKE :q OR description LIKE :q)
                           ORDER BY created_at DESC LIMIT 48");
    $stmt->execute([':q' => $like]);
    $products = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("SELECT id, name, slug, base_price FROM products WHERE is_active=1
                         ORDER BY created_at DESC LIMIT 48");
    $products = $stmt->fetchAll();
}
?>
<div class="container">

  <div class="navbar" style="border:none;padding:0;margin-bottom:1rem;">
    <h2 style="margin:0;">Katalog Produk</h2>
    <form class="header-search" method="get" style="display:flex;gap:.5rem;">
      <input type="text" name="q" placeholder="Cari skincare, makeup..." 
             value="<?php echo htmlspecialchars($q); ?>" 
             style="padding:.55rem;border:1px solid #ddd;border-radius:10px;min-width:260px;">
      <button class="btn" type="submit">Cari</button>
    </form>
  </div>

  <?php if ($_SESSION['user']['role'] === 'admin'): ?>
    <div style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;padding:10px;border-radius:8px;margin-bottom:1rem;">
      🔒 Anda login sebagai <strong>Admin</strong>. Anda hanya dapat melihat produk, tidak bisa membeli.
    </div>
  <?php endif; ?>

  <?php if (!$products): ?>
    <p>Tidak ada produk ditemukan.</p>
  <?php else: ?>
  <div class="grid">
    <?php foreach ($products as $p): ?>
      <div class="card">
        <?php $img = primary_image($pdo, $p['id']); ?>
        <a href="product.php?id=<?php echo (int)$p['id']; ?>">
          <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
        </a>
        <div class="content">
          <a href="product.php?id=<?php echo (int)$p['id']; ?>">
            <strong><?php echo htmlspecialchars($p['name']); ?></strong>
          </a>
          <div class="price"><?php echo rupiah($p['base_price']); ?></div>

          <?php if ($_SESSION['user']['role'] !== 'admin'): ?>
            <a class="btn" href="product.php?id=<?php echo (int)$p['id']; ?>">Lihat Detail</a>
          <?php else: ?>
            <button class="btn" disabled style="background:#e5e7eb;color:#6b7280;cursor:not-allowed;">Lihat Detail</button>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
