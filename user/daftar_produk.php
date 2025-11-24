<?php
require_once __DIR__ . '/../inti/autentikasi.php';
require_once __DIR__ . '/../inti/fungsi.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

/* ================================
   QUERY PRODUK + GAMBAR UTAMA
================================ */
if ($q !== '') {
    $like = '%' . $q . '%';

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

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* HEADER */
include __DIR__ . '/../header.php';
?>

<style>

/* ======================== GLOWIFY PREMIUM PINK ======================== */
.page-container{
    max-width:1100px;
    margin:40px auto;
    padding:0 16px;
    font-family:'Inter',sans-serif;
}

/* -------------------- SEARCH BAR -------------------- */
.header-search{
    display:flex;
    gap:.7rem;
    margin:1rem 0 2rem;
}

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

/* -------------------- GRID -------------------- */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:1.5rem;
}

/* -------------------- CARD PRODUK -------------------- */
.card{
    background:white;
    border-radius:18px;
    border:1px solid #fbcfe8;
    box-shadow:0 8px 22px rgba(236,72,153,.10);
    overflow:hidden;
    transition:.25s ease;
    padding-bottom:10px;
}

.card:hover{
    transform:translateY(-6px);
    box-shadow:0 12px 28px rgba(236,72,153,.18);
}

/* -------------------- FOTO KECIL (200px) -------------------- */
.card img{
    width:100%;
    height:200px;     /* FOTO DIPERKECIL */
    object-fit:cover;
    border-radius:18px 18px 0 0;
}

/* -------------------- CONTENT -------------------- */
.card .content{
    padding:14px 16px;
}

.card strong{
    font-size:1.05rem;
    font-weight:700;
    color:#111;
    margin-bottom:6px;
    display:block;
}

/* Harga pink */
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
}

/* -------------------- NO DATA -------------------- */
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

<!-- ===================== KONTEN ===================== -->
<div class="page-container">

    <h2 style="font-size:1.7rem;margin-bottom:.5rem;color:#db2777;font-weight:800;">
        Katalog Produk
    </h2>

    <!-- FORM SEARCH -->
    <form method="get" class="header-search">
        <input type="text" name="q" placeholder="Cari produk..." value="<?= htmlspecialchars($q) ?>">
        <button class="btn small" type="submit">Cari</button>
    </form>

    <?php if (!$products): ?>
        
        <p class="no-data">Tidak ada produk ditemukan.</p>

    <?php else: ?>

        <div class="grid">

            <?php foreach ($products as $p): ?>

                <?php
                    $image = $p['image_url']
                        ? "/glowify/" . $p['image_url']
                        : "/glowify/aset/gambar/default.png";
                ?>

                <div class="card">

                    <a href="/glowify/user/detail_produk.php?id=<?= $p['id'] ?>">
                        <img src="<?= htmlspecialchars($image); ?>" alt="<?= htmlspecialchars($p['name']); ?>">
                    </a>

                    <div class="content">

                        <strong><?= htmlspecialchars($p['name']); ?></strong>

                        <div class="price"><?= rupiah($p['base_price']); ?></div>

                        <a href="/glowify/user/detail_produk.php?id=<?= $p['id'] ?>" class="btn small">
                            Lihat Detail
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

<?php include __DIR__ . '/../footer.php'; ?>
