<?php
session_start();
require_once __DIR__ . '/../inti/koneksi_database.php';
require_once __DIR__ . '/../inti/fungsi.php';

/* ==========================================================
   CEK ADMIN LOGIN
========================================================== */
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /glowify/akun/masuk.php');
    exit;
}

$error       = "";
$success     = "";
$previewFile = "";

/* ==========================================================
   UPLOAD GAMBAR PREVIEW
========================================================== */
if (isset($_POST['upload_preview'])) {

    if (!empty($_FILES['gambar_preview']['name'])) {

        $dir = __DIR__ . "/../aset/preview/";
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $ext     = strtolower(pathinfo($_FILES['gambar_preview']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed)) {
            $error = "Format gambar harus JPG, JPEG, PNG, atau WEBP.";
        } else {

            $previewFile = "preview_" . time() . "_" . rand(1000, 9999) . "." . $ext;
            $path = $dir . $previewFile;

            if (!move_uploaded_file($_FILES['gambar_preview']['tmp_name'], $path)) {
                $error = "Gagal mengunggah gambar.";
            }
        }
    }
}

/* Jika gambar sudah ada sebelumnya */
if (!empty($_POST['nama_file_preview'])) {
    $previewFile = $_POST['nama_file_preview'];
}

/* ==========================================================
   SIMPAN PRODUK BARU (DENGAN STOK)
========================================================== */
if (isset($_POST['simpan_produk'])) {

    $nama      = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);
    $rawPrice  = str_replace('.', '', $_POST['harga']);
    $harga     = (int)$rawPrice;
    $stock     = (int)$_POST['stock'];

    if (empty($_POST['nama_file_preview'])) {
        $error = "Silakan upload gambar produk.";
    } elseif ($nama === "" || $harga <= 0) {
        $error = "Nama dan harga wajib diisi.";
    } else {

        /* Pindahkan file preview menuju folder uploads */
        $src = __DIR__ . "/../aset/preview/" . $_POST['nama_file_preview'];
        $dst = __DIR__ . "/../aset/uploads/" . $_POST['nama_file_preview'];

        if (!is_dir(__DIR__ . "/../aset/uploads/")) mkdir(__DIR__ . "/../aset/uploads/", 0777, true);

        @rename($src, $dst);

        /* Buat slug */
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $nama)));

        /* Simpan produk ke database */
        $q = $pdo->prepare("
            INSERT INTO products (name, slug, base_price, description, stock, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, 1, NOW())
        ");

        $q->execute([$nama, $slug, $harga, $deskripsi, $stock]);

        $pid = $pdo->lastInsertId();

        /* Simpan gambar utama */
        $img = $pdo->prepare("
            INSERT INTO product_images (product_id, url, is_primary)
            VALUES (?, ?, 1)
        ");

        $img->execute([$pid, "aset/uploads/" . $_POST['nama_file_preview']]);

        $success     = "Produk berhasil ditambahkan!";
        $previewFile = "";
    }
}

include __DIR__ . '/../header.php';
?>


<!-- ==========================================================
        CSS PREMIUM GLOWIFY
========================================================== -->
<style>
    body { font-family: "Inter", sans-serif; }

    h2 {
        font-size: 1.7rem;
        font-weight: 700;
        color: #db2777;
        margin-bottom: 20px;
    }

    .form-box {
        background: white;
        padding: 22px;
        border-radius: 16px;
        border: 1px solid #fbcfe8;
        box-shadow: 0 8px 20px rgba(236,72,153,.12);
        max-width: 680px;
    }

    label {
        font-weight: 600;
        margin-top: 14px;
        display: block;
        color: #db2777;
    }

    input[type="file"],
    input[type="text"],
    input[type="number"],
    textarea {
        width: 100%;
        padding: 12px;
        border-radius: 12px;
        border: 1px solid #f2b4d4;
        margin-top: 6px;
        font-size: 1rem;
        background: #fff;
        transition: 0.2s;
    }

    input:focus,
    textarea:focus {
        border-color: #ec4899;
        box-shadow: 0 0 0 4px #fce7f3;
        outline: none;
    }

    /* Preview Image */
    .preview-box img {
        width: 240px;
        height: 240px;
        object-fit: cover;
        border-radius: 14px;
        box-shadow: 0 5px 14px rgba(0,0,0,.18);
    }

    /* Tombol Premium Pink */
    .btn-pink {
        background: #ec4899;
        border: none;
        padding: 12px 20px;
        font-size: 16px;
        font-weight: 600;
        color: white;
        border-radius: 12px;
        cursor: pointer;
        box-shadow: 0 6px 14px rgba(236,72,153,.25);
        transition: .25s;
        margin-top: 18px;
    }

    .btn-pink:hover {
        transform: translateY(-2px);
        opacity: .9;
    }
</style>



<h2>Tambah Produk Baru</h2>

<?php if ($error): ?>
    <div class="alert error"><?= htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert success"><?= htmlspecialchars($success); ?></div>
<?php endif; ?>


<div class="form-box">

<!-- ==========================================================
        PREVIEW GAMBAR
========================================================== -->
<?php if ($previewFile): ?>
    <div class="preview-box" style="margin-bottom:20px;">
        <p style="font-weight:600;color:#db2777;margin-bottom:8px;">Preview Gambar:</p>
        <img src="/glowify/aset/preview/<?= htmlspecialchars($previewFile); ?>" alt="Preview">
    </div>
<?php endif; ?>


<!-- ==========================================================
        FORM UPLOAD GAMBAR
========================================================== -->
<form method="post" enctype="multipart/form-data" style="margin-bottom:30px;">
    <input type="hidden" name="upload_preview" value="1">
    <input type="hidden" name="nama_file_preview" value="<?= htmlspecialchars($previewFile); ?>">

    <label>Pilih Gambar Produk:</label>
    <input type="file" name="gambar_preview" required onchange="this.form.submit()">
</form>


<!-- ==========================================================
        FORM DATA PRODUK
========================================================== -->
<form method="post">
    <input type="hidden" name="simpan_produk" value="1">
    <input type="hidden" name="nama_file_preview" value="<?= htmlspecialchars($previewFile); ?>">

    <label>Nama Produk:</label>
    <input type="text" name="nama" required>

    <label>Harga Produk:</label>
    <input type="text" name="harga" placeholder="70.000" required>

    <label>Stok Produk:</label>
    <input type="number" name="stock" value="0" min="0" required>

    <label>Deskripsi Produk:</label>
    <textarea name="deskripsi" rows="4"></textarea>

    <button class="btn-pink" type="submit">Simpan Produk</button>
</form>

</div>

<?php include __DIR__ . '/../footer.php'; ?>
