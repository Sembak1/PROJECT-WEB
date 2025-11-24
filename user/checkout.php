<?php
session_start();
require_once __DIR__ . '/../inti/koneksi_database.php';
require_once __DIR__ . '/../inti/fungsi.php';

$items    = keranjang_item();
$subtotal = keranjang_subtotal();
$ongkir   = $subtotal > 0 ? 15000 : 0;
$grand    = $subtotal + $ongkir;

include __DIR__ . '/../header.php';
?>

<style>
/* ===================== Premium Glowify ===================== */

body {
    font-family: "Inter", system-ui, sans-serif;
}

.checkout-wrapper {
    max-width: 900px;
    margin: 34px auto;
    padding: 0 16px;
}

.checkout-title {
    font-size: 1.9rem;
    font-weight: 800;
    color: #db2777;
    margin-bottom: 22px;
}

/* ===================== Table Produk ===================== */

.table-produk {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 26px rgba(236,72,153,0.12);
    margin-bottom: 28px;
}

.table-produk thead {
    background: #fdf2f8;
}

.table-produk th {
    padding: 14px 16px;
    font-size: .95rem;
    font-weight: 700;
    border-bottom: 1px solid #f3d0df;
    color: #4b5563;
    text-align: left;
}

.table-produk td {
    padding: 14px 16px;
    border-bottom: 1px solid #e5e7eb;
    font-size: .95rem;
}

.table-produk th:nth-child(1),
.table-produk td:nth-child(1) { width: 45%; }
.table-produk th:nth-child(2),
.table-produk td:nth-child(2) { width: 10%; text-align: center; }
.table-produk th:nth-child(3),
.table-produk td:nth-child(3) { width: 22%; text-align: right; }
.table-produk th:nth-child(4),
.table-produk td:nth-child(4) { width: 22%; text-align: right; }

.table-produk tbody tr:last-child td {
    border-bottom: none;
}

/* ===================== Card Total ===================== */

.card-total {
    background: #fff0f7;
    border: 1px solid #fbcfe8;
    padding: 20px 22px;
    border-radius: 18px;
    box-shadow: 0 8px 20px rgba(236,72,153,0.12);
    margin-bottom: 24px;
}

.card-total-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 1rem;
}

.card-total-item:last-child {
    margin-bottom: 0;
}

.card-total .label {
    color: #6b7280;
}

.card-total .value {
    font-weight: 700;
    color: #4b5563;
}

.card-total-grand {
    font-size: 1.25rem;
    font-weight: 800;
    color: #db2777;
    display: flex;
    justify-content: space-between;
    margin-top: 14px;
    padding-top: 12px;
    border-top: 2px dashed #f5a3c2;
}

/* ===================== Buttons ===================== */

.checkout-actions {
    display: flex;
    gap: .9rem;
    margin-top: 10px;
}

.btn-main {
    padding: 12px 22px;
    border: none;
    background: #ec4899;
    color: white;
    border-radius: 14px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 6px 16px rgba(236,72,153,.35);
    transition: .2s;
}

.btn-main:hover {
    transform: translateY(-2px);
}

.btn-secondary {
    padding: 12px 22px;
    border-radius: 14px;
    border: 2px solid #ec4899;
    background: white;
    color: #ec4899;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
}

.btn-secondary:hover {
    background: #fdf2f8;
}
</style>

<div class="checkout-wrapper">

    <h2 class="checkout-title">Checkout</h2>

    <?php if (!$items): ?>
        <div class="alert-box">
            Keranjang kamu masih kosong.
            <a href="daftar_produk.php" style="color:#db2777;font-weight:700;">Belanja sekarang</a>.
        </div>

    <?php else: ?>

        <!-- TABLE PRODUK -->
        <table class="table-produk">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= htmlspecialchars($it['nama']); ?></td>
                    <td><?= (int)$it['qty']; ?></td>
                    <td><?= rupiah($it['harga']); ?></td>
                    <td><?= rupiah($it['harga'] * $it['qty']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- CARD TOTAL PEMBAYARAN -->
        <div class="card-total">

            <div class="card-total-item">
                <span class="label">Subtotal</span>
                <span class="value"><?= rupiah($subtotal); ?></span>
            </div>

            <div class="card-total-item">
                <span class="label">Ongkir</span>
                <span class="value"><?= rupiah($ongkir); ?></span>
            </div>

            <div class="card-total-grand">
                <span>Grand Total</span>
                <span><?= rupiah($grand); ?></span>
            </div>

        </div>

        <!-- BUTTON -->
        <form method="post" action="buat_pesanan.php" class="checkout-actions">
            <button class="btn-main" type="submit">Buat Pesanan</button>
            <a href="keranjang.php" class="btn-secondary">Kembali</a>
        </form>

    <?php endif; ?>

</div>

<?php include __DIR__ . '/../footer.php'; ?>
