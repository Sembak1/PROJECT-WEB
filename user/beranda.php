<?php
// Mulai session untuk membaca status login user
session_start();

require_once __DIR__ . '/../inti/koneksi_database.php';  
// Import koneksi database (PDO)

require_once __DIR__ . '/../inti/autentikasi.php';
// Import fungsi cek_login / cek_admin jika dibutuhkan

require_once __DIR__ . '/../inti/fungsi.php';
// Import fungsi umum seperti rupiah(), gambar_produk, keranjang, dll



/* ============================================================
   AMBIL PRODUK TERBARU (12 PRODUK)
============================================================ */
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
    LIMIT 12
");

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Hasil query disimpan dalam array asosiatif

// Import header (navbar + struktur HTML awal)
include __DIR__ . '/../header.php';
?>



<!-- ============================================================
     HERO SECTION
     Bagian banner utama di halaman beranda
============================================================ -->
<section style="
    padding:80px 20px;
    text-align:center;
    background:linear-gradient(135deg,#fff0f7,#ffffff);
    border-bottom:1px solid #fce7f3;
">
    <h1 style="
        font-size:2.6rem;
        font-weight:800;
        color:#db2777;
        margin-bottom:10px;
    ">
        Selamat Datang di Glowify Beauty ✨
    </h1>

    <p style="
        font-size:1.1rem;
        max-width:640px;
        margin:0 auto 25px;
        color:#6b7280;
    ">
        Temukan koleksi skincare & makeup terbaik untuk kecantikan alami kamu.
    </p>

    <!-- Tombol menuju daftar produk -->
    <a href='/glowify/user/daftar_produk.php' style="
        display:inline-block;
        padding:14px 32px;
        background:#ec4899;
        color:white;
        font-weight:600;
        border-radius:16px;
        box-shadow:0 6px 18px rgba(236,72,153,0.35);
        transition:.2s;
    " onmouseover="this.style.transform='translateY(-3px)'"
      onmouseout="this.style.transform='translateY(0)'">
        Belanja Sekarang
    </a>
</section>



<!-- ============================================================
     PRODUK TERBARU (GRID KATALOG)
============================================================ -->
<div style="max-width:1100px;margin:50px auto;padding:0 16px;">
    
    <h2 style="
        font-size:1.8rem;
        font-weight:700;
        margin-bottom:22px;
    ">
        Produk Terbaru
    </h2>

    <!-- GRID LIST PRODUK -->
    <div style="
        display:grid;
        grid-template-columns:repeat(auto-fill,minmax(230px,1fr));
        gap:22px;
    ">

        <?php if (!empty($products)): ?>
            <?php foreach ($products as $p): ?>

                <?php
                    // Menentukan gambar utama produk
                    $image = $p['image_url'] 
                        ? "/glowify/" . $p['image_url']
                        : "/glowify/aset/gambar/default.png";
                ?>

                <!-- CARD PRODUK -->
                <div style="
                    background:white;
                    border-radius:18px;
                    padding:14px;
                    border:1px solid #f3f4f6;
                    box-shadow:0 4px 14px rgba(17,24,39,0.08);
                    transition:.2s;
                " 
                onmouseover="this.style.transform='translateY(-6px)';this.style.boxShadow='0 8px 20px rgba(17,24,39,0.12)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 14px rgba(17,24,39,0.08)'">

                    <!-- LINK KE HALAMAN DETAIL PRODUK -->
                    <a href="/glowify/user/detail_produk.php?id=<?= $p['id'] ?>">
                        <img src="<?= htmlspecialchars($image); ?>" 
                            alt="<?= htmlspecialchars($p['name']); ?>"
                            style="
                                width:100%;
                                height:230px;
                                object-fit:cover;
                                border-radius:14px;
                                margin-bottom:12px;
                            ">
                    </a>

                    <!-- NAMA + HARGA + TOMBOL -->
                    <div>
                        <!-- Nama produk -->
                        <div style="
                            font-weight:700;
                            font-size:1rem;
                            margin-bottom:6px;
                            color:#111;
                        ">
                            <?= htmlspecialchars($p['name']); ?>
                        </div>

                        <!-- Harga produk -->
                        <div style="
                            font-weight:800;
                            color:#db2777;
                            margin-bottom:14px;
                        ">
                            <?= rupiah($p['base_price']); ?>
                        </div>

                        <!-- Tombol lihat detail -->
                        <a href="/glowify/user/detail_produk.php?id=<?= $p['id'] ?>" 
                           style="
                               display:block;
                               text-align:center;
                               padding:10px;
                               background:#ec4899;
                               color:white;
                               border-radius:12px;
                               font-weight:600;
                               transition:.2s;
                           "
                           onmouseover="this.style.opacity='0.85'"
                           onmouseout="this.style.opacity='1'">
                            Lihat Detail
                        </a>
                    </div>

                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <!-- Jika belum ada produk -->
            <p style="color:#6b7280;font-style:italic;">Belum ada produk tersedia.</p>
        <?php endif; ?>

    </div>
</div>



<!-- ============================================================
     SECTION TENTANG GLOWIFY
============================================================ -->
<section style="
    padding:60px 20px;
    text-align:center;
    background:#faf5ff;
    margin-top:40px;
">
    <h2 style="
        font-size:1.9rem;
        font-weight:700;
        margin-bottom:12px;
        color:#7e22ce;
    ">Tentang Glowify</h2>

    <p style="
        max-width:720px;
        margin:0 auto;
        font-size:1.05rem;
        line-height:1.7;
        color:#4b5563;
    ">
        Glowify Beauty adalah toko kecantikan online terpercaya yang menyediakan produk skincare dan makeup berkualitas.
        Kami berkomitmen menghadirkan kecantikan alami untuk setiap wanita, agar selalu tampil percaya diri setiap hari!
    </p>
</section>



<!-- FOOTER -->
<?php include __DIR__ . '/../footer.php'; ?>
