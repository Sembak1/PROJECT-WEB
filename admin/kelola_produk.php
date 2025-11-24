<?php
session_start();
require_once __DIR__ . '/../inti/koneksi_database.php';
require_once __DIR__ . '/../inti/fungsi.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: /glowify/akun/masuk.php");
    exit;
}

$error = "";
$success = "";

/* ==========================================================
   UPDATE PRODUK
========================================================== */
if (isset($_POST['update_produk'])) {
    $id      = (int)($_POST['product_id'] ?? 0);
    $nama    = trim($_POST['nama'] ?? '');
    $harga   = (float)($_POST['harga'] ?? 0);
    $desk    = trim($_POST['deskripsi'] ?? '');
    $stock   = (int)($_POST['stock'] ?? 0);

    if ($id <= 0 || $nama === '' || $harga <= 0) {
        $error = "Data produk tidak valid.";
    } else {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nama)));

        try {
            $stmt = $pdo->prepare("
                UPDATE products
                SET name = ?, slug = ?, base_price = ?, description = ?, stock = ?
                WHERE id = ?
            ");
            $stmt->execute([$nama, $slug, $harga, $desk, $stock, $id]);

            $success = "Produk berhasil diperbarui!";
        } catch (Throwable $e) {
            $error = "Gagal memperbarui produk.";
        }
    }
}

/* ==========================================================
   HAPUS PRODUK
========================================================== */
if (isset($_POST['hapus_produk'])) {
    $id = (int)($_POST['product_id'] ?? 0);

    if ($id <= 0) {
        $error = "Produk tidak valid.";
    } else {
        try {
            $q = $pdo->prepare("SELECT url FROM product_images WHERE product_id = ?");
            $q->execute([$id]);
            $files = $q->fetchAll();

            foreach ($files as $f) {
                if (!empty($f['url'])) {
                    $rel  = ltrim($f['url'], '/');
                    $path = __DIR__ . '/../' . $rel;

                    if (file_exists($path)) {
                        @unlink($path);
                    }
                }
            }

            $pdo->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);

            $success = "Produk berhasil dihapus!";
        } catch (Throwable $e) {
            $error = "Gagal menghapus produk.";
        }
    }
}

/* ==========================================================
   AMBIL DATA PRODUK + STOK
========================================================== */
$stmt = $pdo->query("
    SELECT 
        p.id, p.name, p.base_price, p.description, p.stock,
        COALESCE(img.url, 'aset/uploads/default.png') AS image
    FROM products p
    LEFT JOIN product_images img
        ON img.product_id = p.id AND img.is_primary = 1
    ORDER BY p.id DESC
");

$products = $stmt->fetchAll();

include __DIR__ . '/../header.php';
?>

<h2>Manajemen Produk</h2>

<?php if ($error): ?>
    <div class="alert error"><?= htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert success"><?= htmlspecialchars($success); ?></div>
<?php endif; ?>


<table class="tabel">
<tbody>

<?php foreach ($products as $p): ?>
<tr style="vertical-align:middle;">

    <!-- FOTO PRODUK -->
    <td style="width:420px; padding:20px;">
        <img src="/glowify/<?= htmlspecialchars($p['image']); ?>"
            style="
                width: 450px;
                height: 450px;
                object-fit: cover;
                border-radius: 14px;
            ">
    </td>

    <!-- FORM EDIT PRODUK -->
    <td style="padding:20px; width:500px; vertical-align:middle;">

        <form method="post" style="margin-bottom:25px;">

            <input type="hidden" name="product_id" value="<?= (int)$p['id']; ?>">

            <!-- NAMA -->
            <label style="font-size:16px; font-weight:700; margin-bottom:6px; display:block;">
                Nama Produk
            </label>
            <input type="text" name="nama"
                value="<?= htmlspecialchars($p['name']); ?>"
                required
                style="
                    width:100%; padding:12px; font-size:16px;
                    margin-bottom:16px; border-radius:8px;
                ">

            <!-- HARGA -->
            <label style="font-size:16px; font-weight:700; margin-bottom:6px; display:block;">
                Harga Produk
            </label>
            <input type="number" name="harga"
                value="<?= htmlspecialchars($p['base_price']); ?>"
                step="100" required
                style="
                    width:100%; padding:12px; font-size:16px;
                    margin-bottom:16px; border-radius:8px;
                ">

            <!-- STOK -->
            <label style="font-size:16px; font-weight:700; margin-bottom:6px; display:block;">
                Stok Produk
            </label>
            <input type="number" name="stock"
                value="<?= htmlspecialchars($p['stock']); ?>"
                min="0"
                style="
                    width:100%; padding:12px; font-size:16px;
                    margin-bottom:16px; border-radius:8px;
                ">

            <!-- DESKRIPSI -->
            <label style="font-size:16px; font-weight:700; margin-bottom:6px; display:block;">
                Deskripsi Produk
            </label>
            <textarea name="deskripsi" rows="4"
                style="
                    width:100%; padding:12px; font-size:16px;
                    margin-bottom:20px; border-radius:8px;
                "><?= htmlspecialchars($p['description']); ?></textarea>

            <button class="btn" name="update_produk" value="1"
                style="width:100%; padding:12px; font-size:16px;">
                Update
            </button>

        </form>

        <!-- HAPUS PRODUK -->
        <form method="post" onsubmit="return confirm('Hapus produk ini?');">
            <input type="hidden" name="product_id" value="<?= (int)$p['id']; ?>">
            <button class="btn secondary" name="hapus_produk" value="1"
                style="width:100%; padding:12px; font-size:16px;">
                Hapus
            </button>
        </form>

    </td>

</tr>
<?php endforeach; ?>

</tbody>
</table>


<?php include __DIR__ . '/../footer.php'; ?>
