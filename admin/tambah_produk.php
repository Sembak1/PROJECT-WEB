<?php
session_start();
// Memulai session agar admin dapat mengakses halaman ini.

require_once __DIR__ . '/../inti/koneksi_database.php';
// Mengambil file koneksi database (PDO).

require_once __DIR__ . '/../inti/fungsi.php';
// Mengambil fungsi tambahan (misalnya rupiah, dll).

/* ==========================================================
   CEK ADMIN LOGIN
========================================================== */
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    // Jika belum login atau bukan admin → redirect ke login.
    header('Location: /glowify/akun/masuk.php');
    exit;
}

$error       = "";   // Penyimpanan pesan error jika ada kesalahan.
$success     = "";   // Penyimpanan pesan sukses.
$previewFile = "";   // Menyimpan nama file preview gambar.

/* ==========================================================
   UPLOAD GAMBAR PREVIEW
   (gambar disimpan sementara dulu di folder preview)
========================================================== */
if (isset($_POST['upload_preview'])) {

    if (!empty($_FILES['gambar_preview']['name'])) {

        $dir = __DIR__ . "/../aset/preview/";
        // Folder tempat menyimpan preview

        if (!is_dir($dir)) mkdir($dir, 0777, true);
        // Jika folder belum ada → buat folder

        $ext     = strtolower(pathinfo($_FILES['gambar_preview']['name'], PATHINFO_EXTENSION));
        // Ambil ekstensi file

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        // Ekstensi yang diperbolehkan

        if (!in_array($ext, $allowed)) {
            // Validasi ekstensi
            $error = "Format gambar harus JPG, JPEG, PNG, atau WEBP.";
        } else {

            // Nama file unik
            $previewFile = "preview_" . time() . "_" . rand(1000, 9999) . "." . $ext;
            $path = $dir . $previewFile;

            // Upload file ke folder preview
            if (!move_uploaded_file($_FILES['gambar_preview']['tmp_name'], $path)) {
                $error = "Gagal mengunggah gambar.";
            }
        }
    }
}

/* Jika sebelumnya sudah ada preview file, maka tetap gunakan */
if (!empty($_POST['nama_file_preview'])) {
    $previewFile = $_POST['nama_file_preview'];
}

/* ==========================================================
   SIMPAN PRODUK BARU
   (memindahkan gambar, membuat slug, menyimpan ke DB)
========================================================== */
if (isset($_POST['simpan_produk'])) {

    $nama      = trim($_POST['nama']);         // Nama produk
    $deskripsi = trim($_POST['deskripsi']);    // Deskripsi produk
    $rawPrice  = str_replace('.', '', $_POST['harga']); // Hilangkan titik
    $harga     = (int)$rawPrice;               // Harga integer
    $stock     = (int)$_POST['stock'];         // Stok

    if (empty($_POST['nama_file_preview'])) {
        // Jika gambar belum diupload
        $error = "Silakan upload gambar produk.";

    } elseif ($nama === "" || $harga <= 0) {
        // Validasi nama dan harga
        $error = "Nama dan harga wajib diisi.";

    } else {

        // Pindahkan gambar dari folder preview → uploads
        $src = __DIR__ . "/../aset/preview/" . $_POST['nama_file_preview'];
        $dst = __DIR__ . "/../aset/uploads/" . $_POST['nama_file_preview'];

        if (!is_dir(__DIR__ . "/../aset/uploads/")) mkdir(__DIR__ . "/../aset/uploads/", 0777, true);

        @rename($src, $dst);
        // Memindahkan file

        // Membuat slug dari nama produk
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $nama)));

        /* Simpan produk */
        $q = $pdo->prepare("
            INSERT INTO products (name, slug, base_price, description, stock, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, 1, NOW())
        ");

        $q->execute([$nama, $slug, $harga, $deskripsi, $stock]);

        $pid = $pdo->lastInsertId();
        // Mengambil ID produk terakhir yang baru disimpan

        /* Simpan gambar utama */
        $img = $pdo->prepare("
            INSERT INTO product_images (product_id, url, is_primary)
            VALUES (?, ?, 1)
        ");

        $img->execute([$pid, "aset/uploads/" . $_POST['nama_file_preview']]);

        $success     = "Produk berhasil ditambahkan!";
        $previewFile = ""; // Reset preview
    }
}

include __DIR__ . '/../header.php';
// Memanggil header HTML
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

    /* Preview foto produk */
    .preview-box img {
        width: 240px;
        height: 240px;
        object-fit: cover;
        border-radius: 14px;
        box-shadow: 0 5px 14px rgba(0,0,0,.18);
    }

    /* Tombol pink premium */
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

<!-- Pesan Error -->
<?php if ($error): ?>
    <div class="alert error"><?= htmlspecialchars($error); ?></div>
<?php endif; ?>

<!-- Pesan Sukses -->
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

        <!-- Menampilkan gambar preview -->
        <img src="/glowify/aset/preview/<?= htmlspecialchars($previewFile); ?>" alt="Preview">
    </div>
<?php endif; ?>


<!-- ==========================================================
        FORM UPLOAD GAMBAR (OTO SUBMIT)
========================================================== -->
<form method="post" enctype="multipart/form-data" style="margin-bottom:30px;">
    <input type="hidden" name="upload_preview" value="1">
    <!-- Menyimpan nama preview jika sudah ada -->
    <input type="hidden" name="nama_file_preview" value="<?= htmlspecialchars($previewFile); ?>">

    <label>Pilih Gambar Produk:</label>
    <input type="file" name="gambar_preview" required onchange="this.form.submit()">
    <!-- onchange submit = upload langsung tanpa klik tombol -->
</form>


<!-- ==========================================================
        FORM INPUT DETAIL PRODUK
========================================================== -->
<form method="post">
    <input type="hidden" name="simpan_produk" value="1">
    <input type="hidden" name="nama_file_preview" value="<?= htmlspecialchars($previewFile); ?>">

    <label>Nama Produk:</label>
    <input type="text" name="nama" required>

    <label>Harga Produk:</label>
    <input type="text" name="harga" placeholder="00.000" required>

    <label>Stok Produk:</label>
    <input type="number" name="stock" value="0" min="0" required>

    <label>Deskripsi Produk:</label>
    <textarea name="deskripsi" rows="4"></textarea>

    <!-- Tombol simpan -->
    <button class="btn-pink" type="submit">Simpan Produk</button>
</form>

</div>

<?php include __DIR__ . '/../footer.php'; ?>
<!-- Menutup halaman -->
