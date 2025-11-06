<?php require_once __DIR__ . '/auth.php'; ?>
<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
include __DIR__ . '/header.php';

if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Ambil produk berdasarkan id (atau slug)
$id   = isset($_GET['id'])   ? (int)$_GET['id'] : 0;
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if ($id > 0) {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name, b.name AS brand_name
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        LEFT JOIN brands b     ON b.id = p.brand_id
        WHERE p.id = ? AND p.is_active = 1
        LIMIT 1
    ");
    $stmt->execute([$id]);
} elseif ($slug !== '') {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name, b.name AS brand_name
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        LEFT JOIN brands b     ON b.id = p.brand_id
        WHERE p.slug = ? AND p.is_active = 1
        LIMIT 1
    ");
    $stmt->execute([$slug]);
} else {
    $stmt = false;
}

$product = $stmt ? $stmt->fetch() : null;
if (!$product) {
    echo '<div class="container"><p>Produk tidak ditemukan.</p></div>';
    include __DIR__ . '/footer.php';
    exit;
}

// Gambar
$imgs = $pdo->prepare("
    SELECT url, alt_text
    FROM product_images
    WHERE product_id = ?
    ORDER BY is_primary DESC, sort_order ASC
");
$imgs->execute([$product['id']]);
$images = $imgs->fetchAll();

// Variants
$vv = $pdo->prepare("
    SELECT id, variant_name, additional_price
    FROM product_variants
    WHERE product_id = ? AND is_active = 1
    ORDER BY id ASC
");
$vv->execute([$product['id']]);
$variants = $vv->fetchAll();
$default_variant = $variants ? (int)$variants[0]['id'] : 0;

// ==== Handle Add to Cart (untuk user biasa saja) ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['variant_id'], $_POST['qty']) && $_SESSION['user']['role'] !== 'admin') {
    $variant_id = (int)$_POST['variant_id'];
    $qty        = max(1, (int)$_POST['qty']);

    // cek varian & harga
    $vn = $pdo->prepare("
        SELECT variant_name, additional_price
        FROM product_variants
        WHERE id = ? AND product_id = ? AND is_active = 1
        LIMIT 1
    ");
    $vn->execute([$variant_id, $product['id']]);
    $vrow = $vn->fetch();

    if ($vrow) {
        // cek stok saat ini
        $stok = $pdo->prepare("SELECT stock FROM inventory WHERE variant_id = ? LIMIT 1");
        $stok->execute([$variant_id]);
        $rowStok  = $stok->fetch();
        $available = $rowStok ? (int)$rowStok['stock'] : 0;

        if ($available <= 0) {
            echo '<div class="container"><div class="notice" style="border-color:#fecaca;background:#fef2f2;color:#991b1b;">Stok varian ini sedang habis.</div></div>';
        } elseif ($qty > $available) {
            $qty = $available;
            $unit_price = (float)$product['base_price'] + (float)$vrow['additional_price'];
            cart_add($variant_id, $qty, $unit_price, $product['name'], $vrow['variant_name']);
            echo '<div class="container"><div class="notice">Jumlah melebihi stok. Ditambahkan maksimal stok tersedia (' . (int)$available . ' pcs) ke keranjang.</div></div>';
        } else {
            $unit_price = (float)$product['base_price'] + (float)$vrow['additional_price'];
            cart_add($variant_id, $qty, $unit_price, $product['name'], $vrow['variant_name']);
            echo '<div class="container"><div class="notice">Ditambahkan ke keranjang.</div></div>';
        }
    } else {
        echo '<div class="container"><div class="notice" style="border-color:#fecaca;background:#fef2f2;color:#991b1b;">Varian tidak ditemukan / nonaktif.</div></div>';
    }
}
?>

<section class="container" style="display:grid;grid-template-columns:1fr 1.2fr;gap:1.25rem;align-items:start;">
  <div>
    <?php if ($images): ?>
      <img src="<?php echo htmlspecialchars($images[0]['url']); ?>"
           alt="<?php echo htmlspecialchars($images[0]['alt_text'] ?: $product['name']); ?>"
           style="width:100%;border-radius:14px;border:1px solid #eee;">
    <?php else: ?>
      <img src="placeholder.png"
           alt="<?php echo htmlspecialchars($product['name']); ?>"
           style="width:100%;border-radius:14px;border:1px solid #eee;">
    <?php endif; ?>
  </div>

  <div>
    <h2 style="margin-top:0;"><?php echo htmlspecialchars($product['name']); ?></h2>
    <p style="color:#6b7280;">
      <?php echo htmlspecialchars($product['brand_name'] ?: ''); ?>
      <?php echo $product['brand_name'] && $product['category_name'] ? '·' : ''; ?>
      <?php echo htmlspecialchars($product['category_name'] ?: ''); ?>
    </p>
    <div class="price" style="margin:.5rem 0 1rem;"><?php echo rupiah($product['base_price']); ?></div>
    <p><?php echo nl2br(htmlspecialchars($product['description'] ?? '')); ?></p>

    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
      <div style="margin-top:1.5rem;background:#fef3c7;color:#92400e;border:1px solid #fde68a;padding:10px;border-radius:8px;">
        🔒 Anda login sebagai <strong>Admin</strong>. Anda tidak dapat melakukan pembelian produk.
      </div>
    <?php else: ?>
      <?php if ($variants): ?>
        <form method="post" style="margin-top:1rem;">
          <label for="variant_id">Pilih Varian:</label><br>
          <select id="variant_id" name="variant_id"
                  style="padding:.55rem;border:1px solid #ddd;border-radius:10px;width:100%;max-width:320px;">
            <?php foreach ($variants as $v): ?>
              <option value="<?php echo (int)$v['id']; ?>" <?php echo ($v['id'] === $default_variant ? 'selected' : ''); ?>>
                <?php echo htmlspecialchars($v['variant_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>

          <div style="height:.75rem;"></div>
          <label for="qty">Jumlah:</label><br>
          <input type="number" id="qty" name="qty" min="1" value="1"
                 style="padding:.55rem;border:1px solid #ddd;border-radius:10px;width:120px;">

          <div style="height:.75rem;"></div>
          <button class="btn" type="submit">Tambah ke Keranjang</button>
        </form>
      <?php else: ?>
        <p><em>Varian belum tersedia.</em></p>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
