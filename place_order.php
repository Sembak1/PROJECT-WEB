<?php
require_once __DIR__ . '/auth.php';      // wajib login customer
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$user = $_SESSION['user'] ?? null;
$items = cart_items();
if (!$user || !$items) {
    header('Location: checkout.php');
    exit;
}

// hitung ulang total (aman dari manipulasi harga di client)
$subtotal = 0.0;
$normalized = []; // variant_id => ['qty'=>, 'price'=>, 'name'=>, 'variant_name'=>]
foreach ($items as $vid => $it) {
    $vid = (int)$vid;
    $qty = max(1, (int)$it['qty']);

    // ambil harga terbaru dari DB
    $qry = $pdo->prepare("SELECT pv.id, pv.variant_name, pv.additional_price, p.name AS product_name, p.base_price
                          FROM product_variants pv
                          JOIN products p ON p.id = pv.product_id
                          WHERE pv.id=? AND pv.is_active=1 AND p.is_active=1
                          LIMIT 1");
    $qry->execute([$vid]);
    if (!$row = $qry->fetch()) {
        // varian tidak valid: skip item ini
        continue;
    }
    $unit = (float)$row['base_price'] + (float)$row['additional_price'];
    $subtotal += $unit * $qty;
    $normalized[$vid] = [
        'qty' => $qty,
        'price' => $unit,
        'name' => $row['product_name'],
        'variant_name' => $row['variant_name']
    ];
}

if (!$normalized) {
    header('Location: cart.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Buat order
    $insOrder = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'pending')");
    $insOrder->execute([(int)$user['id'], $subtotal]);
    $orderId = (int)$pdo->lastInsertId();

    // Untuk tiap item: kurangi stok atomik + insert order_items
    $updInv = $pdo->prepare("UPDATE inventory SET stock = stock - :qty
                             WHERE variant_id = :vid AND stock >= :qty");
    $insItem = $pdo->prepare("INSERT INTO order_items (order_id, variant_id, quantity, price)
                              VALUES (:oid, :vid, :qty, :price)");

    foreach ($normalized as $vid => $data) {
        // kurangi stok jika cukup
        $updInv->execute([
            ':qty' => $data['qty'],
            ':vid' => $vid
        ]);

        if ($updInv->rowCount() === 0) {
            // stok tidak cukup → rollback
            $pdo->rollBack();
            echo '<!doctype html><html><head><meta charset="utf-8"><link rel="stylesheet" href="style.css"></head><body>';
            echo '<div class="container section"><div class="notice" style="border-color:#fecaca;background:#fef2f2;color:#991b1b;">';
            echo 'Stok tidak mencukupi untuk varian: <strong>' . htmlspecialchars($data['variant_name']) . '</strong>.';
            echo ' Silakan kurangi jumlah di keranjang.</div>';
            echo '<p><a class="btn" href="cart.php">Kembali ke Keranjang</a></p></div></body></html>';
            exit;
        }

        // catat item
        $insItem->execute([
            ':oid'   => $orderId,
            ':vid'   => $vid,
            ':qty'   => $data['qty'],
            ':price' => $data['price']
        ]);
    }

    $pdo->commit();

    // kosongkan keranjang
    $_SESSION['cart'] = [];

    // tampilkan sukses
    echo '<!doctype html><html><head><meta charset="utf-8"><link rel="stylesheet" href="style.css"></head><body>';
    echo '<div class="container section">';
    echo '<div class="notice">Pesanan berhasil dibuat. ID Pesanan: <strong>#' . $orderId . '</strong></div>';
    echo '<p><a class="btn" href="index.php">Kembali ke Beranda</a> ';
    echo '<a class="btn secondary" href="products.php">Lanjut Belanja</a></p>';
    echo '</div></body></html>';

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo '<!doctype html><html><head><meta charset="utf-8"><link rel="stylesheet" href="style.css"></head><body>';
    echo '<div class="container section"><div class="notice" style="border-color:#fecaca;background:#fef2f2;color:#991b1b;">';
    echo 'Terjadi kesalahan saat membuat pesanan. Coba lagi.</div>';
    echo '<p><a class="btn" href="checkout.php">Kembali ke Checkout</a></p></div></body></html>';
}
