<?php
// Mengimpor sistem login (untuk cek role user jika perlu)
require_once __DIR__ . '/../inti/autentikasi.php';

// Mengimpor fungsi umum: rupiah(), keranjang_item(), dll
require_once __DIR__ . '/../inti/fungsi.php';


// Ambil keyword pencarian jika ada
$q = isset($_GET['q']) ? trim($_GET['q']) : '';



/* ======================================================
   QUERY PRODUK + GAMBAR UTAMA (DENGAN FITUR SEARCH)
====================================================== */
if ($q !== '') {
    // Jika user mengetik pencarian → buat wildcard
    $like = '%' . $q . '%';

    // Prepare query agar aman dari SQL Injection
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.name,
            p.base_price,
            (
                SELECT url 
                FROM product_images
                WHERE product_id = p.id
                ORDER BY is_primary DESC, sort_order ASC
                LIMIT 1
            ) AS image_url
        FROM products p
        WHERE p.is_active = 1
          AND (p.name LIKE :q OR p.description LIKE :q)
        ORDER BY p.created_at DESC
    ");

    $stmt->execute([':q' => $like]);

} else {
    // Jika tidak ada pencarian → tampilkan semua produk
    $stmt = $pdo->query("
        SELECT 
            p.id,
            p.name,
            p.base_price,
            (
                SELECT url 
                FROM product_images
                WHERE product_id = p.id
                ORDER BY is_primary DESC, sort_order ASC
                LIMIT 1
            ) AS image_url
        FROM products p
        WHERE p.is_active = 1
        ORDER BY p.created_at DESC
    ");
}

// Hasil query dalam bentuk array
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Memanggil header (navbar + HTML awal)
include __DIR__ . '/../header.php';
?>



<!-- ======================== CSS INLINE HALAMAN KATALOG ======================== -->
<style>

.page-container{
    max-width:1100px;
    margin:40px auto;
    padding:0 16px;
    font-family:'Inter',sans-serif;
}

/* Search bar */
.header-search{
    display:flex;
    gap:.7rem;
    margin:1rem 0 2rem;
}

/* Input pencarian */
.header-search input{
    padding:.75rem 1rem;
    border-radius:14px;
    border:1px solid #f9a8d4;
    width:300px;
    background:white;
    font-size:1rem;
    transition:.2s;
    box-shadow:0 5px 14px rgba(236,72,153,.15);
}

.header-search input:focus{
    border-color:#ec4899;
    box-shadow:0 0 0 4px #fce7f3;
}

/* Tombol pink */
.btn.small{
    padding:.6rem 1.2rem;
    background:#ec4899;
    border:none;
    font-size:.9rem;
    border-radius:12px;
    color:white;
    font-weight:600;
    cursor:pointer;
    box-shadow:0 6px 14px rgba(236,72,153,.32);
    transition:.2s;
}
.btn.small:hover{
    transform:translateY(-2px);
    opacity:.9;
}

/* Grid katalog */
.grid{
    display:flex;
    flex-wrap:wrap;
    gap:1.5rem;
    justify-content:flex-start;
    margin-top:1.5rem;
}

/* Card produk */
.card{
    width:100%;
    max-width:320px;
    background:white;
    border-radius:18px;
    border:1px solid #fbcfe8;
    box-shadow:0 8px 22px rgba(236,72,153,.10);
    overflow:hidden;
    transition:.25s ease;
    padding-bottom:12px;
}

.card:hover{
    transform:translateY(-6px);
    box-shadow:0 12px 28px rgba(236,72,153,.18);
}

/* Gambar produk */
.card img{
    width:100%;
    height:auto;
    object-fit:cover;
    display:block;
    border-radius:18px 18px 0 0;
}

.card .content{
    padding:14px 16px;
}

/* Nama produk */
.card strong{
    font-size:1.05rem;
    font-weight:700;
    color:#111;
    margin-bottom:6px;
    display:block;
}

/* Harga */
.price{
    font-weight:800;
    font-size:1.05rem;
    color:#db2777;
    margin-bottom:14px;
}

/* Tombol detail */
.card .btn.small{
    width:100%;
    display:block;
    text-align:center;
    padding:.6rem 0;
    border-radius:12px;
    font-weight:600;
}

/* Jika tidak ada produk */
.no-data{
    text-align:center;
    padding:1rem;
    border-radius:14px;
    background:#fff1f7;
    border:1px solid #fbcfe8;
    color:#db2777;
    font-weight:500;
    margin-top:.5rem;
}

</style>



<!-- ===================== TAMPILAN KATALOG PRODUK ===================== -->
<div class="page-container">

    <h2 style="font-size:1.7rem;margin-bottom:.5rem;color:#db2777;font-weight:800;">
        Katalog Produk
    </h2>

    <!-- Form pencarian -->
    <form method="get" class="header-search">
        <input type="text" name="q" placeholder="Cari produk..." 
               value="<?= htmlspecialchars($q) ?>">
        <button class="btn small" type="submit">Cari</button>
    </form>


    <!-- Jika tidak ada produk -->
    <?php if (!$products): ?>
        
        <p class="no-data">Tidak ada produk ditemukan.</p>

    <?php else: ?>

        <!-- GRID PRODUK -->
        <div class="grid">

            <?php foreach ($products as $p): ?>

                <?php
                    // Ambil gambar utama / fallback ke default
                    $image = $p['image_url']
                        ? "/glowify/" . $p['image_url']
                        : "/glowify/aset/gambar/default.png";
                ?>

                <div class="card">

                    <!-- Gambar produk → Klik menuju detail -->
                    <a href="/glowify/user/detail_produk.php?id=<?= $p['id'] ?>">
                        <img src="<?= htmlspecialchars($image); ?>" 
                             alt="<?= htmlspecialchars($p['name']); ?>">
                    </a>

                    <div class="content">

                        <!-- Nama produk -->
                        <strong><?= htmlspecialchars($p['name']); ?></strong>

                        <!-- Harga produk -->
                        <div class="price"><?= rupiah($p['base_price']); ?></div>

                        <!-- Tombol menuju detail produk -->
                        <a href="/glowify/user/detail_produk.php?id=<?= $p['id'] ?>" 
                           class="btn small">
                            Lihat Detail
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

<!-- FOOTER -->
<?php include __DIR__ . '/../footer.php'; ?>
