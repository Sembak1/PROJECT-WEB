<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Hanya admin
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Proses update stok
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['variant_id'], $_POST['add_qty'])) {
    $variant_id = (int) $_POST['variant_id'];
    $add_qty = (int) $_POST['add_qty'];

    if ($add_qty > 0) {
        $stmt = $pdo->prepare("UPDATE inventory SET stock = stock + ? WHERE variant_id = ?");
        $stmt->execute([$add_qty, $variant_id]);
        $success = "Stok berhasil ditambahkan!";
    } else {
        $error = "Jumlah stok harus lebih dari 0.";
    }
}

// Ambil data stok produk
$stmt = $pdo->query("
    SELECT pv.id AS variant_id, p.name AS product_name, pv.variant_name, i.stock, i.low_stock_threshold
    FROM product_variants pv
    JOIN products p ON pv.product_id = p.id
    JOIN inventory i ON pv.id = i.variant_id
    ORDER BY p.name
");
$stocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/header.php';
?>

<div class="container section">
  <h2>📦 Manajemen Stok Produk</h2>

  <?php if (!empty($success)): ?>
    <div style="background:#dcfce7;color:#166534;border:1px solid #bbf7d0;padding:10px;border-radius:8px;margin:1rem 0;">
      <?= htmlspecialchars($success) ?>
    </div>
  <?php elseif (!empty($error)): ?>
    <div style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;padding:10px;border-radius:8px;margin:1rem 0;">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <table style="width:100%;border-collapse:collapse;margin-top:1rem;">
    <thead style="background:#f3f4f6;">
      <tr>
        <th style="padding:.6rem;border:1px solid #ddd;">Produk</th>
        <th style="padding:.6rem;border:1px solid #ddd;">Varian</th>
        <th style="padding:.6rem;border:1px solid #ddd;">Stok Sekarang</th>
        <th style="padding:.6rem;border:1px solid #ddd;">Tambah Stok</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($stocks as $item): ?>
        <tr>
          <td style="padding:.6rem;border:1px solid #ddd;"><?= htmlspecialchars($item['product_name']) ?></td>
          <td style="padding:.6rem;border:1px solid #ddd;"><?= htmlspecialchars($item['variant_name']) ?></td>
          <td style="padding:.6rem;border:1px solid #ddd;">
            <?php 
              if ($item['stock'] <= $item['low_stock_threshold']) {
                  echo "<span style='color:#dc2626;font-weight:bold;'>{$item['stock']} (Rendah)</span>";
              } else {
                  echo htmlspecialchars($item['stock']);
              }
            ?>
          </td>
          <td style="padding:.6rem;border:1px solid #ddd;">
            <form method="post" style="display:flex;gap:.5rem;align-items:center;">
              <input type="hidden" name="variant_id" value="<?= $item['variant_id'] ?>">
              <input type="number" name="add_qty" min="1" placeholder="Jumlah" required
                     style="width:80px;padding:.4rem;border:1px solid #ddd;border-radius:8px;">
              <button type="submit" style="padding:.4rem .8rem;border:none;border-radius:8px;background:#ec4899;color:#fff;cursor:pointer;">
                + Tambah
              </button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p style="margin-top:1.5rem;"><a href="admin.php" style="color:#ec4899;">⬅ Kembali ke Dashboard</a></p>
</div>

<?php include __DIR__ . '/footer.php'; ?>
