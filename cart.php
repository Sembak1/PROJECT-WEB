<?php require_once __DIR__ . '/auth.php'; ?>
<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
include __DIR__ . '/header.php';

// Pastikan sudah login
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Jika role adalah admin → tidak boleh akses keranjang
if ($_SESSION['user']['role'] === 'admin') {
    echo '<div class="container" style="margin-top:2rem;">
            <div style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;padding:1rem;border-radius:10px;">
              🔒 Anda login sebagai <strong>Admin</strong>. Halaman keranjang tidak tersedia untuk admin.
            </div>
            <p style="margin-top:1rem;">
              <a href="products.php" class="btn" style="background:#ec4899;color:#fff;border:none;padding:.5rem 1rem;border-radius:8px;text-decoration:none;">
                Kembali ke Produk
              </a>
            </p>
          </div>';
    include __DIR__ . '/footer.php';
    exit;
}

// Jika bukan admin, lanjutkan seperti biasa
// ==== Update / clear keranjang ====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $vid => $qty) {
            $vid = (int)$vid;
            $qty = (int)$qty;
            if ($qty <= 0) {
                if (isset($_SESSION['cart'][$vid])) unset($_SESSION['cart'][$vid]);
            } else {
                if (isset($_SESSION['cart'][$vid])) $_SESSION['cart'][$vid]['qty'] = $qty;
            }
        }
    }
    if (isset($_POST['clear'])) {
        $_SESSION['cart'] = [];
    }
}

$items = cart_items();
$subtotal = cart_subtotal();
?>

<div class="container">
  <h2>Keranjang</h2>

  <?php if (!$items): ?>
    <p>Keranjang kosong. <a href="products.php" style="color:#ec4899;">Belanja sekarang</a>.</p>
  <?php else: ?>
  <form method="post">
    <table class="table" style="width:100%;border-collapse:collapse;">
      <thead>
        <tr>
          <th style="border-bottom:1px solid #eee;padding:.75rem;text-align:left;">Produk</th>
          <th style="border-bottom:1px solid #eee;padding:.75rem;text-align:left;">Varian</th>
          <th style="border-bottom:1px solid #eee;padding:.75rem;text-align:left;">Harga</th>
          <th style="border-bottom:1px solid #eee;padding:.75rem;text-align:left;">Qty</th>
          <th style="border-bottom:1px solid #eee;padding:.75rem;text-align:left;">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $vid => $it): ?>
          <tr>
            <td style="border-bottom:1px solid #eee;padding:.75rem;"><?php echo htmlspecialchars($it['name']); ?></td>
            <td style="border-bottom:1px solid #eee;padding:.75rem;"><?php echo htmlspecialchars($it['variant_name']); ?></td>
            <td style="border-bottom:1px solid #eee;padding:.75rem;"><?php echo rupiah($it['price']); ?></td>
            <td style="border-bottom:1px solid #eee;padding:.75rem;">
              <input type="number" name="qty[<?php echo (int)$vid; ?>]" value="<?php echo (int)$it['qty']; ?>" min="0" style="width:80px;">
            </td>
            <td style="border-bottom:1px solid #eee;padding:.75rem;"><?php echo rupiah($it['qty'] * $it['price']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" class="total" style="text-align:right;font-weight:700;padding:.75rem;">Total</td>
          <td style="padding:.75rem;"><?php echo rupiah($subtotal); ?></td>
        </tr>
      </tfoot>
    </table>
    <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:.75rem;">
      <button class="btn" name="update" value="1" type="submit">Perbarui</button>
      <button class="btn" name="clear" value="1" type="submit" style="background:#6b7280;border-color:#6b7280;">Kosongkan</button>
      <a class="btn" href="checkout.php">Checkout</a>
    </div>
  </form>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
