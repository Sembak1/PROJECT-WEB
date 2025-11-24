<?php
require_once __DIR__ . '/../inti/autentikasi.php';
require_once __DIR__ . '/../inti/fungsi.php';

$items    = keranjang_item();
$subtotal = keranjang_subtotal();
$ongkir   = $subtotal > 0 ? 15000 : 0;
$grand    = $subtotal + $ongkir;

include __DIR__ . '/../header.php';
?>

<h2>Checkout</h2>

<?php if (!$items): ?>
  <p>Keranjang kosong. <a href="daftar_produk.php">Belanja dulu</a>.</p>
<?php else: ?>
  <table class="tabel">
    <thead>
      <tr>
        <th>Produk</th>
        <th>Varian</th>
        <th>Qty</th>
        <th>Harga</th>
        <th>Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $vid => $it): ?>
        <tr>
          <td><?= htmlspecialchars($it['nama']); ?></td>
          <td><?= htmlspecialchars($it['nama_varian']); ?></td>
          <td><?= (int)$it['qty']; ?></td>
          <td><?= rupiah($it['harga']); ?></td>
          <td><?= rupiah($it['qty'] * $it['harga']); ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="4" class="text-right"><strong>Subtotal</strong></td>
        <td><?= rupiah($subtotal); ?></td>
      </tr>
      <tr>
        <td colspan="4" class="text-right"><strong>Ongkir (flat)</strong></td>
        <td><?= rupiah($ongkir); ?></td>
      </tr>
      <tr>
        <td colspan="4" class="text-right"><strong>Grand Total</strong></td>
        <td><?= rupiah($grand); ?></td>
      </tr>
    </tfoot>
  </table>

  <form method="post" action="buat_pesanan.php" style="margin-top:1rem;">
    <button class="btn" type="submit">Buat Pesanan</button>
    <a class="btn secondary" href="keranjang.php">Kembali ke Keranjang</a>
  </form>
<?php endif; ?>

<?php include __DIR__ . '/../footer.php'; ?>
