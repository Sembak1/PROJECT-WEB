<?php
session_start();
require_once __DIR__ . '/../inti/koneksi_database.php';
require_once __DIR__ . '/../inti/autentikasi.php';
require_once __DIR__ . '/../inti/fungsi.php';

$user  = $_SESSION['user'] ?? null;
$items = keranjang_item();

// Jika belum login atau keranjang kosong
if (!$user || !$items) {
    header("Location: checkout.php");
    exit;
}

/* ==========================================================
   NORMALISASI DATA KERANJANG
========================================================== */
$subtotal   = 0;
$normalized = [];

foreach ($items as $pid => $item) {

    $pid = (int)$pid;
    $qty = max(1, (int)$item['qty']);

    // Ambil produk
    $q = $pdo->prepare("
        SELECT id, name, base_price, stock 
        FROM products 
        WHERE id = ? AND is_active = 1
        LIMIT 1
    ");
    $q->execute([$pid]);
    $p = $q->fetch();

    if (!$p) continue;

    $price = (int)$p['base_price'];
    $subtotal += $price * $qty;

    $normalized[$pid] = [
        'nama'  => $p['name'],
        'qty'   => $qty,
        'price' => $price,
        'stock' => $p['stock']
    ];
}

if (empty($normalized)) {
    header("Location: keranjang.php");
    exit;
}

try {

    // Mulai transaksi
    $pdo->beginTransaction();

    /* ==========================================================
       1. SIMPAN DATA PESANAN UTAMA
    =========================================================== */
    $insOrder = $pdo->prepare("
        INSERT INTO orders (user_id, total_price, status, created_at)
        VALUES (?, ?, 'pending', NOW())
    ");
    $insOrder->execute([(int)$user['id'], $subtotal]);

    $orderId = (int)$pdo->lastInsertId();

    /* ==========================================================
       2. PERSIAPAN QUERY
    =========================================================== */
    $updateStok = $pdo->prepare("
        UPDATE products 
        SET stock = stock - :qty 
        WHERE id = :pid 
          AND stock >= :qty
    ");

    $insItem = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (:oid, :pid, :qty, :price)
    ");

    /* ==========================================================
       3. SIMPAN ITEM & UPDATE STOK
    =========================================================== */
    foreach ($normalized as $pid => $data) {

        // Kurangi stok
        $updateStok->execute([
            ':qty' => $data['qty'],
            ':pid' => $pid
        ]);

        // Jika stok tidak cukup → batalkan seluruh transaksi
        if ($updateStok->rowCount() === 0) {

            $pdo->rollBack();
            include __DIR__ . '/../header.php';

            echo "
            <div class='alert error' style='padding:14px; border-radius:12px; background:#ffe1e1; color:#b91c1c;'>
                Stok tidak cukup untuk produk <strong>" . htmlspecialchars($data['nama']) . "</strong>.
                Silakan kurangi jumlah di keranjang.
            </div>

            <p><a class='btn' href='keranjang.php'>Kembali ke Keranjang</a></p>
            ";

            include __DIR__ . '/../footer.php';
            exit;
        }

        // Simpan item order
        $insItem->execute([
            ':oid'   => $orderId,
            ':pid'   => $pid,
            ':qty'   => $data['qty'],
            ':price' => $data['price']
        ]);
    }

    /* ==========================================================
       4. COMMIT TRANSAKSI
    =========================================================== */
    $pdo->commit();

    // Kosongkan keranjang
    $_SESSION['keranjang'] = [];

    include __DIR__ . '/../header.php';

    echo "
    <div class='alert success' 
         style='padding:14px; border-radius:12px; background:#dcfce7; color:#166534;'>
        Pesanan berhasil dibuat!<br>
        ID Pesanan: <strong>#$orderId</strong>
    </div>

    <p style='margin-top:12px; display:flex; gap:.7rem;'>
        <a class='btn' href='beranda.php'>Kembali ke Beranda</a>
        <a class='btn secondary' href='daftar_produk.php'>Belanja Lagi</a>
    </p>
    ";

    include __DIR__ . '/../footer.php';
    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    include __DIR__ . '/../header.php';

    echo "
    <div class='alert error'
         style='padding:14px; border-radius:12px; background:#ffe1e1; color:#b91c1c;'>
        Terjadi kesalahan saat membuat pesanan. Silakan coba lagi.
    </div>

    <p><a class='btn' href='checkout.php'>Kembali ke Checkout</a></p>
    ";

    include __DIR__ . '/../footer.php';
}
