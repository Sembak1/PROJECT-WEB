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
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ext     = strtolower(pathinfo($_FILES['gambar_preview']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {

            $previewFile = "preview_" . time() . "_" . rand(1000, 9999) . "." . $ext;
            $path        = $dir . $previewFile;

            if (!move_uploaded_file($_FILES['gambar_preview']['tmp_name'], $path)) {
                $error = "Gagal mengunggah gambar preview.";
            }

        } else {
            $error = "Format gambar tidak didukung.";
        }
    }
}

/* Simpan nama file lama jika tidak upload baru */
if (!empty($_POST['nama_file_preview'])) {
    $previewFile = $_POST['nama_file_preview'];
}

/* ==========================================================
   SIMPAN PRODUK BARU
========================================================== */
if (isset($_POST['simpan_produk'])) {

    $nama      = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);
    $rawPrice  = str_replace('.', '', $_POST['harga']);
    $harga     = (int)$rawPrice;

    if (empty($_POST['nama_file_preview'])) {

        $error = "Silakan upload gambar terlebih dahulu.";

    } elseif ($nama === "" || $harga <= 0) {

        $error = "Nama dan harga wajib diisi.";

    } else {

        /* Pindahkan file preview ke folder uploads */
        $src = __DIR__ . "/../aset/preview/" . $_POST['nama_file_preview'];
        $dst = __DIR__ . "/../aset/uploads/" . $_POST['nama_file_preview'];

        if (!is_dir(__DIR__ . "/../aset/uploads/")) {
            mkdir(__DIR__ . "/../aset/uploads/", 0777, true);
        }

        @rename($src, $dst);

        /* Buat slug */
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $nama)));

        /* Simpan produk */
        $q = $pdo->prepare("
            INSERT INTO products (name, slug, base_price, description, is_active, created_at)
            VALUES (?, ?, ?, ?, 1, NOW())
        ");
        $q->execute([$nama, $slug, $harga, $deskripsi]);

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

<h2>Tambah Produk Baru</h2>

<?php if ($error): ?>
    <div class="alert error"><?= htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert success"><?= htmlspecialchars($success); ?></div>
<?php endif; ?>


<!-- ==========================================================
     PREVIEW GAMBAR (DITAMPILKAN DI ATAS FORM UPLOAD)
========================================================== -->
<?php if ($previewFile): ?>
    <div class="preview-box" style="margin-bottom:20px;">
        <p>Preview Gambar:</p>

        <img src="/glowify/aset/preview/<?= htmlspecialchars($previewFile); ?>"
             alt="Preview"
             style="
                width:260px;
                height:260px;
                object-fit:cover;
                border-radius:12px;
             ">
    </div>
<?php endif; ?>


<!-- ==========================================================
     FORM UPLOAD GAMBAR
========================================================== -->
<form method="post" enctype="multipart/form-data" style="margin-bottom:30px;">
    <input type="hidden" name="upload_preview" value="1">
    <input type="hidden" name="nama_file_preview" value="<?= htmlspecialchars($previewFile); ?>">

    <label>Pilih Gambar Produk:</label>
    <input type="file" name="gambar_preview"
           required
           onchange="this.form.submit()">
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

    <label>Deskripsi Produk:</label>
    <textarea name="deskripsi" rows="4"></textarea>

    <button class="btn" type="submit" style="margin-top:12px;">Simpan Produk</button>
</form>

<?php include __DIR__ . '/../footer.php'; ?>
