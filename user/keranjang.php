<?php
// Mengimpor sistem autentikasi (untuk memastikan session aktif)
require_once __DIR__ . '/../inti/autentikasi.php';

// Mengimpor fungsi keranjang + rupiah()
require_once __DIR__ . '/../inti/fungsi.php';

// ============================
// CEK ROLE ADMIN
// Admin tidak boleh akses keranjang customer
// ============================
if (!empty($_SESSION['user']) &&
    isset($_SESSION['user']['role']) &&
    $_SESSION['user']['role'] === 'admin') {

    include __DIR__ . '/../header.php';
    echo '<div class="alert warning">Anda login sebagai Admin. Halaman ini hanya untuk pelanggan.</div>';
    include __DIR__ . '/../footer.php';
    exit;
}


/* ============================================================
   TAMBAH PRODUK KE KERANJANG (POST dari detail_produk.php)
============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {

    $pid = (int)($_POST['product_id'] ?? 0);
    $qty = max(1, (int)($_POST['qty'] ?? 1));

    // Ambil data produk
    $stmt = $pdo->prepare("
        SELECT p.id, p.name, p.base_price,
            (SELECT url FROM product_images 
             WHERE product_id = p.id 
             ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS img
        FROM products p
        WHERE p.id = ? AND p.is_active = 1
    ");
    $stmt->execute([$pid]);
    $prod = $stmt->fetch();

    if ($prod) {
        keranjang_tambah(
            $pid,
            $qty,
            $prod['base_price'],
            $prod['name'],
            '-'
        );

        // Tambah gambar produk pada keranjang
        $_SESSION['keranjang'][$pid]['gambar'] = $prod['img'] ?: "aset/uploads/default.png";
    }

    header("Location: keranjang.php");
    exit;
}


/* ============================================================
   UPDATE JUMLAH PRODUK DALAM KERANJANG
============================================================ */
if (isset($_POST['update'])) {
    foreach ($_POST['qty'] ?? [] as $vid => $qty) {
        $vid = (int)$vid;
        $qty = (int)$qty;

        if ($qty <= 0) {
            unset($_SESSION['keranjang'][$vid]);
        } elseif (isset($_SESSION['keranjang'][$vid])) {
            $_SESSION['keranjang'][$vid]['qty'] = $qty;
        }
    }
}


/* ============================================================
   KOSONGKAN KERANJANG
============================================================ */
if (isset($_POST['kosongkan'])) {
    $_SESSION['keranjang'] = [];
}


/* ============================================================
   DATA TAMPILAN
============================================================ */
$items    = keranjang_item();
$subtotal = keranjang_subtotal();


// HEADER
include __DIR__ . '/../header.php';
?>


<!-- ======================= CSS STYLE ======================= -->
<style>
.keranjang-container {
    max-width: 900px;
    margin: auto;
    padding: 20px;
}

.keranjang-card {
    display: grid;
    grid-template-columns: 90px 1fr 120px;
    background: #fff;
    border-radius: 16px;
    padding: 15px;
    margin-bottom: 12px;
    align-items: center;
    box-shadow: 0 4px 12px rgba(255, 0, 102, 0.15);
    border: 1px solid #ffd1e8;
}

.keranjang-img {
    width: 75px;
    height: 75px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #ff92c8;
}

.keranjang-nama {
    font-size: 18px;
    font-weight: bold;
    color: #d80071;
}

.keranjang-harga {
    font-size: 15px;
    font-weight: 600;
    color: #444;
    margin-top: 2px;
}

.qty-box {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 6px;
}

.qty-btn {
    background: #ff6fb0;
    border: none;
    color: white;
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
}

.qty-input {
    width: 48px;
    text-align: center;
    padding: 6px;
    border: 1.5px solid #ffa6d8;
    border-radius: 8px;
}

.keranjang-subtotal {
    font-size: 17px;
    font-weight: bold;
    color: #d80071;
    text-align: right;
}

.total-box {
    background: #fff0fa;
    padding: 15px;
    border-radius: 14px;
    margin-top: 15px;
    text-align: right;
    font-size: 20px;
    font-weight: bold;
    border: 1px solid #ffbde4;
}

.btn-glowify {
    background: linear-gradient(135deg, #ff62a1, #ff3787);
    color: white !important;
    padding: 12px 18px;
    border-radius: 14px;
    font-size: 16px;
    border: none;
    cursor: pointer;
    transition: 0.2s;
}

.btn-danger {
    background: #ff477e;
}

.action-buttons {
    margin-top: 10px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
</style>


<h2>Keranjang Belanja</h2>

<?php if (!$items): ?>

    <p>Keranjang kosong. <a href="daftar_produk.php">Belanja sekarang</a>.</p>

<?php else: ?>

<form method="post">

<div class="keranjang-container">

    <?php foreach ($items as $vid => $it): ?>
    <div class="keranjang-card">

        <img src="/glowify/<?= htmlspecialchars($it['gambar']); ?>" class="keranjang-img">

        <div>
            <div class="keranjang-nama"><?= htmlspecialchars($it['nama']); ?></div>
            <div class="keranjang-harga"><?= rupiah($it['harga']); ?></div>

            <div class="qty-box">
                <button type="button" class="qty-btn" onclick="ubahQty(<?= $vid ?>, -1)">-</button>

                <input type="number"
                       name="qty[<?= (int)$vid ?>]"
                       value="<?= (int)$it['qty'] ?>"
                       min="0"
                       class="qty-input"
                       id="qty_<?= $vid ?>">

                <button type="button" class="qty-btn" onclick="ubahQty(<?= $vid ?>, 1)">+</button>
            </div>
        </div>

        <div class="keranjang-subtotal">
            <?= rupiah($it['harga'] * $it['qty']); ?>
        </div>

    </div>
    <?php endforeach; ?>

    <div class="total-box">
        Total: <?= rupiah($subtotal); ?>
    </div>

    <div class="action-buttons">
        <button type="submit" name="update" value="1" class="btn-glowify">Perbarui</button>
        <button type="submit" name="kosongkan" value="1" class="btn-glowify btn-danger">Kosongkan</button>
        <a href="checkout.php" class="btn-glowify">Checkout</a>
    </div>

</div>

</form>

<script>
function ubahQty(id, val) {
    let box = document.getElementById("qty_" + id);
    let newVal = parseInt(box.value) + val;
    if (newVal < 0) newVal = 0;
    box.value = newVal;
}
</script>

<?php endif; ?>

<?php include __DIR__ . '/../footer.php'; ?>
