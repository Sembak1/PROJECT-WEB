<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// hanya admin
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = trim($_POST['name']);
    $price       = (float) $_POST['price'];
    $description = trim($_POST['description']);

    // generate slug otomatis
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

    // Validasi dasar
    if ($name === '' || $price <= 0) {
        $error = "Nama dan harga produk wajib diisi.";
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = "Gambar produk harus di-upload.";
    } else {

        // Siapkan folder uploads
        $uploadDir = __DIR__ . "/uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Ambil ekstensi file
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        // Validasi format gambar
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed)) {
            $error = "Format gambar tidak valid! Gunakan JPG, PNG, atau WEBP.";
        } else {

            $fileName = "product_" . time() . "_" . rand(1000, 9999) . "." . $ext;
            $targetPath = $uploadDir . $fileName;

            // Upload file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {

                // Insert produk ke tabel products
                $stmt = $pdo->prepare("
                    INSERT INTO products (name, slug, base_price, description, is_active, created_at)
                    VALUES (?, ?, ?, ?, 1, NOW())
                ");
                $stmt->execute([$name, $slug, $price, $description]);

                $product_id = $pdo->lastInsertId();

                // Simpan gambar ke tabel product_images (tanpa alt_text)
                $img = $pdo->prepare("
                    INSERT INTO product_images (product_id, url, is_primary)
                    VALUES (?, ?, 1)
                ");
                $img->execute([$product_id, "uploads/" . $fileName]);

                $success = "Produk berhasil ditambahkan!";
            } else {
                $error = "Gagal menyimpan file gambar.";
            }
        }
    }
}

include __DIR__ . '/header.php';
?>

<div class="container section">
    <h2>➕ Tambah Produk Baru</h2>

    <?php if ($error): ?>
        <div class="notice" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="notice" style="background:#dcfce7;color:#166534;border:1px solid #bbf7d0;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" style="margin-top:1rem;">

        <label>Nama Produk:</label>
        <input type="text" name="name" required style="width:100%;padding:.55rem;margin-bottom:1rem;">

        <label>Harga Produk:</label>
        <input type="number" name="price" required style="width:100%;padding:.55rem;margin-bottom:1rem;">

        <label>Deskripsi:</label>
        <textarea name="description" rows="4" style="width:100%;padding:.55rem;margin-bottom:1rem;"></textarea>

        <label>Gambar Produk:</label>
        <input type="file" name="image" required style="margin-bottom:1rem;">

        <button class="btn" type="submit">Simpan Produk</button>

    </form>
</div>

<?php include __DIR__ . '/footer.php'; ?>
