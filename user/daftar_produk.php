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

<!-- ===================== CSS KHUSUS KATALOG (TIDAK SENTUH HEADER) ===================== -->
<style>
.page-container{
    max-width:1100px;
    margin:20px auto;
    padding:0 16px;
}

/* --- SEARCH BAR --- */
.header-search{
    display:flex;
    gap:.5rem;
    margin:1rem 0 1.5rem;
}
.header-search input{
    padding:.55rem .75rem;
    border-radius:10px;
    border:1px solid #ddd;
    width:260px;
}

/* --- GRID PRODUK --- */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:1.25rem;
}

/* --- CARD PRODUK --- */
.card{
    background:white;
    border-radius:12px;
    border:1px solid #e5e7eb;
    box-shadow:0 2px 8px rgba(0,0,0,.05);
    overflow:hidden;
    transition:.15s ease;
}
.card:hover{ transform:translateY(-3px); }

/* --- FOTO KOTAK PERFECT --- */
.card img{
    width:100%;
    aspect-ratio:1 / 1;   /* INI YANG MEMBUAT FOTO KOTAK */
    object-fit:cover;     /* Biar gak gepeng */
    object-position:center;
}

/* --- CONTENT TEXT --- */
.card .content{
    padding:10px 12px;
}
.card strong{
    display:block;
    font-size:1rem;
    margin-bottom:5px;
}
.price{
    font-weight:bold;
    margin-bottom:8px;
}

/* --- TOMBOL --- */
.btn.small{
    padding:.45rem .7rem;
    font-size:.85rem;
    border-radius:8px;
    background:#ec4899;
    color:white;
    display:inline-block;
}
.btn.small:hover{ background:#db2777; }

/* --- NO DATA --- */
.no-data{
    text-align:center;
    background:#f5f5f5;
    color:#777;
    padding:1rem;
    border-radius:10px;
}
</style>

<!-- ===================== KONTEN ===================== -->
<div class="page-container">

    <h2 style="margin-bottom:.5rem;">Katalog Produk</h2>

    <!-- FORM SEARCH -->
    <form method="get" class="header-search">
        <input type="text" name="q" placeholder="Cari produk..." value="<?= htmlspecialchars($q) ?>">
        <button class="btn small" type="submit">Cari</button>
    </form>

    <!-- LIST PRODUK -->
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
