<?php
// functions.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function primary_image($pdo, $product_id) {
    $stmt = $pdo->prepare("
        SELECT url
        FROM product_images
        WHERE product_id = ?
        ORDER BY is_primary DESC, sort_order ASC
        LIMIT 1
    ");
    $stmt->execute([$product_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    // Pastikan file placeholder tersedia di folder yang sama.
    return $row && !empty($row['url']) ? $row['url'] : 'placeholder.png';
}

function cart_add($variant_id, $qty, $price, $name, $variant_name) {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    if (!isset($_SESSION['cart'][$variant_id])) {
        $_SESSION['cart'][$variant_id] = [
            'qty' => 0,
            'price' => (float)$price,
            'name' => $name,
            'variant_name' => $variant_name
        ];
    }
    $_SESSION['cart'][$variant_id]['qty'] += max(1, (int)$qty);
}

function cart_items() {
    return $_SESSION['cart'] ?? [];
}

function cart_subtotal() {
    $total = 0;
    foreach (cart_items() as $i) {
        $total += ((int)$i['qty']) * ((float)$i['price']);
    }
    return $total;
}
