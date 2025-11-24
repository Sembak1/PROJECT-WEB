<?php
// inti/fungsi.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function rupiah($angka)
{
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function gambar_utama_produk(PDO $pdo, int $product_id): string
{
    $stmt = $pdo->prepare("
        SELECT url
        FROM product_images
        WHERE product_id = ?
        ORDER BY is_primary DESC, sort_order ASC
        LIMIT 1
    ");
    $stmt->execute([$product_id]);
    $row = $stmt->fetch();

    return $row && !empty($row['url']) ? $row['url'] : 'aset/uploads/default.png';
}

/* ====== FUNGSI KERANJANG ====== */

function keranjang_tambah($variant_id, $qty, $price, $name, $variant_name)
{
    if (!isset($_SESSION['keranjang'])) {
        $_SESSION['keranjang'] = [];
    }
    if (!isset($_SESSION['keranjang'][$variant_id])) {
        $_SESSION['keranjang'][$variant_id] = [
            'qty'          => 0,
            'harga'        => (float)$price,
            'nama'         => $name,
            'nama_varian'  => $variant_name,
        ];
    }
    $_SESSION['keranjang'][$variant_id]['qty'] += max(1, (int)$qty);
}

function keranjang_item(): array
{
    return $_SESSION['keranjang'] ?? [];
}

function keranjang_subtotal(): float
{
    $total = 0;
    foreach (keranjang_item() as $i) {
        $total += ((int)$i['qty']) * ((float)$i['harga']);
    }
    return $total;
}
