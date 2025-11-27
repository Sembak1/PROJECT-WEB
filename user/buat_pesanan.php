<?php
// Mulai session agar bisa membaca user & keranjang
session_start();

// Import koneksi database
require_once __DIR__ . '/../inti/koneksi_database.php';

// Import fungsi cek login & role
require_once __DIR__ . '/../inti/autentikasi.php';

// Import fungsi keranjang, rupiah, dll
require_once __DIR__ . '/../inti/fungsi.php';


// Ambil data user login
$user  = $_SESSION['user'] ?? null;

// Ambil semua item keranjang
$items = keranjang_item();


// Jika user belum login atau keranjang kosong → kembalikan ke checkout
if (!$user || !$items) {
    header("Location: checkout.php");
    exit;
}


/* ==========================================================
   NORMALISASI DATA KERANJANG
   → memastikan harga valid, stok mencukupi, dan data aman
========================================================== */

$subtotal   = 0;         // Total harga sebelum ongkir
$normalized = [];        // array berisi data produk yang sudah divalidasi

foreach ($items as $pid => $item) {

    $pid = (int)$pid;                        // pastikan ID produk integer
    $qty = max(1, (int)$item['qty']);        // minimal qty = 1

    // Ambil data produk dari database untuk validasi harga & stok
    $q = $pdo->prepare("
        SELECT id, name, base_price, stock 
        FROM products 
        WHERE id = ? AND is_active = 1
        LIMIT 1
    ");
    $q->execute([$pid]);
    $p = $q->fetch();

    // Jika produk tidak ditemukan → skip
    if (!$p) continue;

    $price = (int)$p['base_price'];          // harga asli dari database
    $subtotal += $price * $qty;              // update subtotal

    // Simpan data ke array normalisasi
    $normalized[$pid] = [
        'nama'  => $p['name'],
        'qty'   => $qty,
        'price' => $price,
        'stock' => $p['stock']
    ];
}

// Jika tidak ada item valid → kembali ke keranjang
if (empty($normalized)) {
    header("Location: keranjang.php");
    exit;
}


try {

    // Mulai transaksi database
    $pdo->beginTransaction();

    /* ==========================================================
       1. SIMPAN DATA PESANAN UTAMA KE TABEL orders
    =========================================================== */

    $insOrder = $pdo->prepare("
        INSERT INTO orders (user_id, total_price, status, created_at)
        VALUES (?, ?, 'pending', NOW())
    ");

    // Simpan data pesanan utama
    $insOrder->execute([(int)$user['id'], $subtotal]);

    // Ambil ID pesanan yang baru dibuat
    $orderId = (int)$pdo->lastInsertId();


    /* ==========================================================
       2. PREPARE QUERY UNTUK UPDATE STOK & SIMPAN ITEM
    =========================================================== */

    // Query untuk mengurangi stok
    $updateStok = $pdo->prepare("
        UPDATE products 
        SET stock = stock - :qty 
        WHERE id = :pid 
          AND stock >= :qty
    ");

    // Query untuk menyimpan item pesanan ke tabel order_items
    $insItem = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (:oid, :pid, :qty, :price)
    ");


    /* ==========================================================
       3. LOOP TIAP PRODUK → SIMPAN ITEM & KURANGI STOK
    =========================================================== */

    foreach ($normalized as $pid => $data) {

        // Kurangi stok di database
        $updateStok->execute([
            ':qty' => $data['qty'],
            ':pid' => $pid
        ]);

        // Jika stok kurang → batalkan seluruh pesanan
        if ($updateStok->rowCount() === 0) {

            // Batalkan transaksi
            $pdo->rollBack();

            // Tampilkan pesan stok tidak cukup
            include __DIR__ . '/../header.php';

            echo "
            <div class='alert error' style='padding:14px; border-radius:12px; background:#ffe1e1; color:#b91c1c;'>
                Stok tidak cukup untuk produk <strong>" . htmlspecialchars($data['nama']) . "</strong>.<br>
                Silakan kurangi jumlah di keranjang.
            </div>

            <p><a class='btn' href='keranjang.php'>Kembali ke Keranjang</a></p>
            ";

            include __DIR__ . '/../footer.php';
            exit;
        }

        // Masukkan data item ke tabel order_items
        $insItem->execute([
            ':oid'   => $orderId,
            ':pid'   => $pid,
            ':qty'   => $data['qty'],
            ':price' => $data['price']
        ]);
    }


    /* ==========================================================
       4. JIKA SEMUA BERHASIL → COMMIT TRANSAKSI
    =========================================================== */

    $pdo->commit();

    // Kosongkan keranjang setelah pesanan dibuat
    $_SESSION['keranjang'] = [];

    // Tampilkan pesan sukses
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

    // Jika error dan transaksi masih berjalan → rollback
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Tampilkan pesan error umum
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
