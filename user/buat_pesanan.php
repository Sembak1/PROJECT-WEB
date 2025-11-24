<?php
session_start();
require_once __DIR__ . '/../inti/koneksi_database.php';
require_once __DIR__ . '/../inti/autentikasi.php';
require_once __DIR__ . '/../inti/fungsi.php';

$user  = $_SESSION['user'] ?? null;
$items = keranjang_item();

// Jika belum login atau keranjang kosong
if (!$user || !$items) {
    header('Location: checkout.php');
    exit;
}

// =============================
// Normalisasi item & hitung total
// =============================
$subtotal   = 0.0;
$normalized = [];

foreach ($items as $vid => $item) {

    $vid = (int)$vid;
    $qty = max(1, (int)$item['qty']);

    // Ambil data varian + produk
    $qry = $pdo->prepare("
        SELECT 
            pv.id,
            pv.variant_name,
            pv.additional_price,
            p.name AS product_name,
            p.base_price
        FROM product_variants pv
        JOIN products p ON p.id = pv.product_id
        WHERE pv.id = ? 
          AND pv.is_active = 1 
          AND p.is_active = 1
        LIMIT 1
    ");
    $qry->execute([$vid]);
    $row = $qry->fetch();

    if (!$row) continue;

    $unitPrice = (float)$row['base_price'] + (float)$row['additional_price'];
    $subtotal += $unitPrice * $qty;

    $normalized[$vid] = [
        'qty'         => $qty,
        'price'       => $unitPrice,
        'nama'        => $row['product_name'],
        'nama_varian' => $row['variant_name']
    ];
}

if (empty($normalized)) {
    header('Location: keranjang.php');
    exit;
}

try {

    // =============================
    // Mulai transaksi
    // =============================
    $pdo->beginTransaction();

    // Simpan pesanan utama
    $insOrder = $pdo->prepare("
        INSERT INTO orders (user_id, total, status, created_at)
        VALUES (?, ?, 'pending', NOW())
    ");
    $insOrder->execute([(int)$user['id'], $subtotal]);

    $orderId = (int)$pdo->lastInsertId();

    // Update stok
    $updateStok = $pdo->prepare("
        UPDATE inventory
        SET stock = stock - :qty
        WHERE variant_id = :vid AND stock >= :qty
    ");

    // Insert detail order
    $insItem = $pdo->prepare("
        INSERT INTO order_items (order_id, variant_id, quantity, price)
        VALUES (:oid, :vid, :qty, :price)
    ");

    foreach ($normalized as $vid => $data) {

        // Kurangi stok
        $updateStok->execute([
            ':qty' => $data['qty'],
            ':vid' => $vid
        ]);

        // Jika stok tidak cukup → gagalkan transaksi
        if ($updateStok->rowCount() === 0) {

            $pdo->rollBack();
            include __DIR__ . '/../header.php';

            echo "
            <div class='alert error'>
                Stok tidak cukup untuk varian: 
                <strong>" . htmlspecialchars($data['nama_varian']) . "</strong>.
                Silakan kurangi jumlah di keranjang.
            </div>
            <p><a class='btn' href='keranjang.php'>Kembali ke Keranjang</a></p>
            ";

            include __DIR__ . '/../footer.php';
            exit;
        }

        // Simpan barang dalam pesanan
        $insItem->execute([
            ':oid'   => $orderId,
            ':vid'   => $vid,
            ':qty'   => $data['qty'],
            ':price' => $data['price']
        ]);
    }

    // =============================
    // Selesai → commit
    // =============================
    $pdo->commit();

    // Bersihkan keranjang
    $_SESSION['keranjang'] = [];

    include __DIR__ . '/../header.php';

    echo "
    <div class='alert success'>
        Pesanan berhasil dibuat!<br>
        ID Pesanan: <strong>#$orderId</strong>
    </div>

    <p>
        <a class='btn' href='beranda.php'>Kembali ke Beranda</a>
        <a class='btn secondary' href='daftar_produk.php'>Lanjut Belanja</a>
    </p>
    ";

    include __DIR__ . '/../footer.php';

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    include __DIR__ . '/../header.php';

    echo "
    <div class='alert error'>
        Terjadi kesalahan saat membuat pesanan. Silakan coba lagi.
    </div>

    <p><a class='btn' href='checkout.php'>Kembali ke Checkout</a></p>
    ";

    include __DIR__ . '/../footer.php';
}
