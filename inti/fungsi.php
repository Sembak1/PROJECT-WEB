<?php
// inti/fungsi.php
// File ini berisi fungsi-fungsi umum yang digunakan di seluruh aplikasi.


/* ==========================================================
   PASTIKAN SESSION AKTIF
   (fungsi keranjang membutuhkan session)
========================================================== */
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
    // Jika session belum aktif → mulai session baru
}



/* ==========================================================
   FUNGSI: rupiah()
   → Mengubah angka menjadi format mata uang Rupiah
   Contoh: 15000 → "Rp 15.000"
========================================================== */
function rupiah($angka)
{
    // number_format:
    // - tanpa desimal
    // - pemisah ribuan menggunakan titik
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}



/* ==========================================================
   FUNGSI: gambar_utama_produk()
   → Mengambil gambar utama suatu produk di database
   → Jika tidak ada gambar, gunakan default.png
========================================================== */
function gambar_utama_produk(PDO $pdo, int $product_id): string
{
    // Ambil gambar utama berdasarkan is_primary dan sort_order
    $stmt = $pdo->prepare("
        SELECT url
        FROM product_images
        WHERE product_id = ?
        ORDER BY is_primary DESC, sort_order ASC
        LIMIT 1
    ");
    $stmt->execute([$product_id]);
    $row = $stmt->fetch();

    // Jika gambar ditemukan → kembalikan URL-nya
    // Jika tidak → ambil default.png
    return $row && !empty($row['url']) 
        ? $row['url'] 
        : 'aset/uploads/default.png';
}



/* ==========================================================
   BAGIAN: FUNGSI KERANJANG
   sistem keranjang disimpan dalam session:
   $_SESSION['keranjang'][variant_id] = [
       qty, harga, nama, nama_varian
   ]
========================================================== */


/* ----------------------------------------------------------
   FUNGSI: keranjang_tambah()
   → Menambah item ke keranjang berdasarkan variant_id
---------------------------------------------------------- */
function keranjang_tambah($variant_id, $qty, $price, $name, $variant_name)
{
    // Jika keranjang belum dibuat, buat array kosong
    if (!isset($_SESSION['keranjang'])) {
        $_SESSION['keranjang'] = [];
    }

    // Jika varian belum ada di keranjang, buat entry baru
    if (!isset($_SESSION['keranjang'][$variant_id])) {
        $_SESSION['keranjang'][$variant_id] = [
            'qty'          => 0,                // jumlah awal 0
            'harga'        => (float)$price,    // harga satuan
            'nama'         => $name,            // nama produk
            'nama_varian'  => $variant_name,    // nama varian
        ];
    }

    // Tambahkan jumlah barang (min 1 pcs)
    $_SESSION['keranjang'][$variant_id]['qty'] += max(1, (int)$qty);
}



/* ----------------------------------------------------------
   FUNGSI: keranjang_item()
   → Mengambil seluruh item dalam keranjang
---------------------------------------------------------- */
function keranjang_item(): array
{
    // Jika keranjang kosong, return array kosong
    return $_SESSION['keranjang'] ?? [];
}



/* ----------------------------------------------------------
   FUNGSI: keranjang_subtotal()
   → Menghitung total harga seluruh barang di keranjang
---------------------------------------------------------- */
function keranjang_subtotal(): float
{
    $total = 0;

    // Loop semua item keranjang
    foreach (keranjang_item() as $i) {
        // subtotal per item = qty * harga
        $total += ((int)$i['qty']) * ((float)$i['harga']);
    }

    return $total; // Kembalikan subtotal total
}
