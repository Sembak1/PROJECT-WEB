<?php require_once __DIR__ . '/auth.php'; ?>
<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
include __DIR__ . '/header.php';

$items    = cart_items();
$subtotal = cart_subtotal();
$shipping = $subtotal > 0 ? 15000 : 0;
$grand    = $subtotal + $shipping;
?>
<div class="container">
  <h2>Checkout</h2>

  <?php if (!$items): ?>
    <p>Keranjang kosong. <a href="products.php" style="color:#ec4899;">Belanja dulu</a>.</p>
  <?php else: ?>
    <table class="table" style="width:100%;border-collapse:collapse;">
      <thead>
        <tr>
          <th style="border-bottom:1px solid #eee;padding:.75rem;text-align:left;">Produk</th>
          <th style="border-bottom:1px solid #eee;padding:.75rem;text-align:left;">Varian</th>
          <th style="border-bottom:1px solid #eee;padding:.75rem;text-align:left;">Qty</th>
          <th style="border-bottom:1px solid #eee;padding:.75rem;text-align:left;">Harga</th>
          <th style="border-bottom:1px solid #eee;padding:.75rem;text-align:left;">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $vid => $it): ?>
          <tr>
            <td style="border-bottom:1px solid #eee;padding:.75rem;"><?php echo htmlspecialchars($it['name']); ?></td>
            <td style="border-bottom:1px solid #eee;padding:.75rem;"><?php echo htmlspecialchars($it['variant_name']); ?></td>
            <td style="border-bottom:1px solid #eee;padding:.75rem;"><?php echo (int)$it['qty']; ?></td>
            <td style="border-bottom:1px solid #eee;padding:.75rem;"><?php echo rupiah($it['price']); ?></td>
            <td style="border-bottom:1px solid #eee;padding:.75rem;"><?php echo rupiah($it['qty'] * $it['price']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" style="text-align:right;font-weight:700;padding:.75rem;">Subtotal</td>
          <td style="padding:.75rem;"><?php echo rupiah($subtotal); ?></td>
        </tr>
        <tr>
          <td colspan="4" style="text-align:right;font-weight:700;padding:.75rem;">Ongkir (flat)</td>
          <td style="padding:.75rem;"><?php echo rupiah($shipping); ?></td>
        </tr>
        <tr>
          <td colspan="4" style="text-align:right;font-weight:800;padding:.75rem;">Grand Total</td>
          <td style="padding:.75rem;"><?php echo rupiah($grand); ?></td>
        </tr>
      </tfoot>
    </table>

    <!-- tombol buat pesanan (harus di dalam body sebelum footer) -->
    <form method="post" action="place_order.php" style="margin-top:1rem;">
      <button class="btn" type="submit">Buat Pesanan</button>
      <a class="btn secondary" href="cart.php" style="margin-left:.5rem;">Kembali ke Keranjang</a>
    </form>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/footer.php'; ?>
