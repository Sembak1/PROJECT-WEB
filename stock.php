<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

/* -----------------------------
   CEK ADMIN
------------------------------*/
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

/* -----------------------------
   UPDATE PRODUK
------------------------------*/
if (isset($_POST['update_product'])) {
    $id    = (int) $_POST['product_id'];
    $name  = trim($_POST['name']);
    $price = (float) $_POST['price'];
    $desc  = trim($_POST['description']);

    $slug = strtolower(preg_replace("/[^A-Za-z0-9-]/", "-", $name));

    try {
        $stmt = $pdo->prepare("
            UPDATE products 
            SET name = ?, slug = ?, base_price = ?, description = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $slug, $price, $desc, $id]);

        $success = "Produk berhasil diperbarui!";
    } catch (Throwable $e) {
        $error = "Gagal memperbarui produk.";
    }
}

/* -----------------------------
   HAPUS PRODUK
------------------------------*/
if (isset($_POST['delete_product'])) {
    $id = (int) $_POST['product_id'];

    try {
        $q = $pdo->prepare("SELECT url FROM product_images WHERE product_id = ?");
        $q->execute([$id]);
        $files = $q->fetchAll();

        foreach ($files as $f) {
            $path = __DIR__ . "/" . $f['url'];
            if (is_file($path)) unlink($path);
        }

        $del = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $del->execute([$id]);

        $success = "Produk berhasil dihapus!";
    } catch (Throwable $e) {
        $error = "Gagal menghapus produk.";
    }
}

/* -----------------------------
   AMBIL DATA PRODUK
------------------------------*/
$stmt = $pdo->query("
    SELECT p.id, p.name, p.base_price, p.description,
           COALESCE(img.url, 'uploads/default.png') AS image
    FROM products p
    LEFT JOIN product_images img 
        ON img.product_id = p.id AND img.is_primary = 1
    ORDER BY p.id DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* -----------------------------
   HEADER (TETAP SAMA)
------------------------------*/
include __DIR__ . '/header.php';
?>

<style>
/* Table wrapper */
.table-card {
    background: #ffffff;
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-top: 25px;
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: #f3f4f6;
}

th {
    text-align: left;
    padding: 10px;
    font-size: 14px;
    color: #374151;
}

td {
    border-bottom: 1px solid #e5e7eb;
    padding: 12px;
    vertical-align: top;
    font-size: 14px;
}

tbody tr:hover {
    background: #f9fafb;
}

/* Image */
.product-img {
    width: 70px;
    border-radius: 10px;
    border: 2px solid #e5e7eb;
}

/* Form inputs */
input, textarea {
    width: 100%;
    padding: 8px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    margin-bottom: 6px;
    font-size: 14px;
}

input:focus, textarea:focus {
    border-color: #6366f1;
    box-shadow: 0 0 5px rgba(99,102,241,0.4);
    outline: none;
}

/* Alert */
.alert-success {
    background: #d1fae5;
    border-left: 6px solid #10b981;
    padding: 14px;
    border-radius: 8px;
    margin: 10px 0;
    color: #065f46;
}

.alert-error {
    background: #fee2e2;
    border-left: 6px solid #ef4444;
    padding: 14px;
    border-radius: 8px;
    margin: 10px 0;
    color: #991b1b;
}

/* Buttons */
.btn-edit {
    background: #6366f1;
    color: white;
    padding: 8px 10px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
}

.btn-edit:hover {
    background: #4f46e5;
}

.btn-delete {
    background: #ef4444;
    color: white;
    padding: 8px 10px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
}

.btn-delete:hover {
    background: #dc2626;
}

/* Popup overlay */
#confirmOverlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.55);
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

/* Popup box */
#confirmBox {
    background: white;
    width: 320px;
    padding: 22px;
    border-radius: 14px;
    text-align: center;
    box-shadow: 0 8px 30px rgba(0,0,0,.2);
    animation: zoomIn .25s ease;
}

@keyframes zoomIn {
    from { transform: scale(.85); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
}

.confirm-btn {
    padding: 8px 14px;
    border-radius: 6px;
    margin: 6px;
    font-size: 14px;
    cursor: pointer;
    border: none;
    font-weight: 600;
}

.btn-yes { background: #ef4444; color: white; }
.btn-no  { background: #e5e7eb; color: #111827; }
.btn-no:hover { background: #d1d5db; }
.btn-yes:hover { background: #dc2626; }
</style>

<!-- POPUP KONFIRMASI HAPUS -->
<div id="confirmOverlay">
    <div id="confirmBox">
        <h3 style="margin-bottom: 10px;">Hapus Produk?</h3>
        <p style="color:#4b5563;margin-bottom:20px;">Tindakan ini tidak dapat dibatalkan.</p>
        <button id="confirmYes" class="confirm-btn btn-yes">Hapus</button>
        <button id="confirmNo" class="confirm-btn btn-no">Batal</button>
    </div>
</div>

<script>
let deleteForm = null;

function showDeletePopup(form) {
    deleteForm = form;
    document.getElementById("confirmOverlay").style.display = "flex";
}

document.getElementById("confirmNo").onclick = () => {
    document.getElementById("confirmOverlay").style.display = "none";
};

document.getElementById("confirmYes").onclick = () => {
    if (deleteForm) deleteForm.submit();
};
</script>

<!-- MAIN KONTEN -->
<div class="table-card container">

    <h2 style="margin-bottom:15px;">📦 Manajemen Produk</h2>

    <?php if ($error): ?>
        <div class="alert-error"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert-success"><?= $success ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Foto</th>
                <th>Nama</th>
                <th>Harga</th>
                <th>Deskripsi</th>
                <th style="width:180px;">Aksi</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td><img src="<?= htmlspecialchars($p['image']) ?>" class="product-img"></td>

                <td><?= htmlspecialchars($p['name']) ?></td>

                <td><?= rupiah($p['base_price']) ?></td>

                <td style="max-width:260px;"><?= nl2br(htmlspecialchars($p['description'])) ?></td>

                <td>
                    <!-- UPDATE -->
                    <form method="post" style="margin-bottom:8px;">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">

                        <input type="text" name="name" value="<?= htmlspecialchars($p['name']) ?>" required>
                        <input type="number" name="price" value="<?= $p['base_price'] ?>" required>
                        <textarea name="description" rows="2"><?= htmlspecialchars($p['description']) ?></textarea>

                        <button class="btn-edit" name="update_product">Update</button>
                    </form>

                    <!-- DELETE -->
                    <form method="post" onsubmit="event.preventDefault(); showDeletePopup(this);">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <button class="btn-delete" name="delete_product">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</div>

<?php include __DIR__ . '/footer.php'; ?>
