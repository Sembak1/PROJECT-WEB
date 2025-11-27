<?php
// Mengimpor sistem autentikasi untuk memastikan user login
require_once __DIR__ . '/../inti/autentikasi.php';

// Mengimpor fungsi keranjang, fungsi rupiah(), dll
require_once __DIR__ . '/../inti/fungsi.php';


// Jika user adalah admin → tidak boleh akses halaman customer
if ($_SESSION['user']['role'] === 'admin') {
    include __DIR__ . '/../header.php';
    echo '<div class="alert warning">Anda login sebagai Admin. Halaman ini hanya untuk pelanggan.</div>';
    include __DIR__ . '/../footer.php';
    exit;
}


/* ============================================================
   TAMBAH PRODUK KE KERANJANG
   Dipanggil dari detail_produk.php melalui metode POST
============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {

    $pid = (int)$_POST['product_id']; // ID produk
    $qty = max(1, (int)$_POST['qty']); // Minimal qty = 1

    // Ambil data lengkap produk
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

    // Jika produk valid → masukkan ke keranjang
    if ($prod) {
        keranjang_tambah(
            $variant_id   = $pid,          // ID produk dimasukkan sebagai ID varian
            $qty,                           // Jumlah
            $price        = $prod['base_price'], // Harga
            $name         = $prod['name'],       // Nama produk
            $variant_name = '-'             // Glowify tidak memakai varian
        );

        // Simpan gambar produk ke session keranjang
        $_SESSION['keranjang'][$pid]['gambar'] = $prod['img'] ?: "aset/uploads/default.png";
    }

    // Setelah menambah → redirect kembali ke keranjang
    header("Location: keranjang.php");
    exit;
}



/* ============================================================
   UPDATE QTY KERANJANG
   Dipanggil saat pengguna klik tombol "Perbarui"
============================================================ */
if (isset($_POST['update'])) {

    foreach ($_POST['qty'] ?? [] as $vid => $qty) {

        $vid = (int)$vid;
        $qty = (int)$qty;

        if ($qty <= 0) {
            // Jika qty 0 → hapus item dari keranjang
            unset($_SESSION['keranjang'][$vid]);
        } elseif (isset($_SESSION['keranjang'][$vid])) {
            // Update qty
            $_SESSION['keranjang'][$vid]['qty'] = $qty;
        }
    }
}



/* ============================================================
   KOSONGKAN KERANJANG
============================================================ */
if (isset($_POST['kosongkan'])) {
    $_SESSION['keranjang'] = []; // Hapus seluruh isi keranjang
}



/* ============================================================
   DATA KERANJANG UNTUK DITAMPILKAN
============================================================ */
$items    = keranjang_item();     // Ambil semua item di keranjang
$subtotal = keranjang_subtotal(); // Hitung total harga



// Import header
include __DIR__ . '/../header.php';
?>


<!-- ======================= CSS STYLE ======================= -->
<style>
/* Container keranjang */
.keranjang-container {
    max-width: 900px;
    margin: auto;
    padding: 20px;
}

/* Box item keranjang */
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

/* Gambar produk */
.keranjang-img {
    width: 75px;
    height: 75px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #ff92c8;
}

/* Nama produk */
.keranjang-nama {
    font-size: 18px;
    font-weight: bold;
    color: #d80071;
}

/* Harga produk */
.keranjang-harga {
    font-size: 15px;
    font-weight: 600;
    color: #444;
    margin-top: 2px;
}

/* Box qty */
.qty-box {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 6px;
}

/* Tombol + dan - */
.qty-btn {
    background: #ff6fb0;
    border: none;
    color: white;
    padding: 6px 10px;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
}

/* Input qty */
.qty-input {
    width: 48px;
    text-align: center;
    padding: 6px;
    border: 1.5px solid #ffa6d8;
    border-radius: 8px;
}

/* Subtotal per item */
.keranjang-subtotal {
    font-size: 17px;
    font-weight: bold;
    color: #d80071;
    text-align: right;
}

/* Total semua item */
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

/* Tombol Glowify */
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

/* Tombol kosongkan */
.btn-danger {
    background: #ff477e;
}

/* Area tombol aksi */
.action-buttons {
    margin-top: 10px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
</style>



<h2>Keranjang Belanja</h2>

<?php if (!$items): ?>

    <!-- Jika keranjang kosong -->
    <p>Keranjang kosong. <a href="daftar_produk.php">Belanja sekarang</a>.</p>

<?php else: ?>

<!-- FORM UPDATE KERANJANG -->
<form method="post">

<div class="keranjang-container">

    <!-- Perulangan setiap produk di keranjang -->
    <?php foreach ($items as $vid => $it): ?>
    <div class="keranjang-card">

        <!-- Gambar produk -->
        <img src="/glowify/<?= htmlspecialchars($it['gambar']); ?>" class="keranjang-img">

        <!-- Nama + harga + qty -->
        <div>
            <div class="keranjang-nama"><?= htmlspecialchars($it['nama']); ?></div>
            <div class="keranjang-harga"><?= rupiah($it['harga']); ?></div>

            <!-- Input jumlah produk -->
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

        <!-- Subtotal -->
        <div class="keranjang-subtotal">
            <?= rupiah($it['harga'] * $it['qty']); ?>
        </div>

    </div>
    <?php endforeach; ?>

    <!-- TOTAL KESELURUHAN -->
    <div class="total-box">
        Total: <?= rupiah($subtotal); ?>
    </div>

    <!-- Tombol aksi -->
    <div class="action-buttons">
        <button type="submit" name="update" value="1" class="btn-glowify">Perbarui</button>
        <button type="submit" name="kosongkan" value="1" class="btn-glowify btn-danger">Kosongkan</button>
        <a href="checkout.php" class="btn-glowify">Checkout</a>
    </div>

</div>

</form>


<!-- JS untuk tombol + dan - pada qty -->
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
