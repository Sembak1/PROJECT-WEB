<?php
session_start();
require_once __DIR__ . '/../inti/koneksi_database.php';
require_once __DIR__ . '/../inti/autentikasi.php';
require_once __DIR__ . '/../inti/fungsi.php';

cek_admin();

$error = "";
$success = "";

/* ===================== UPDATE STATUS ===================== */
if (isset($_POST['ubah_status'])) {
    $orderId = (int)$_POST['order_id'];
    $status  = trim($_POST['status']);

    $allowed = ['pending', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];

    if (!in_array($status, $allowed)) {
        $error = "Status tidak valid.";
    } else {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);
        $success = "Status pesanan berhasil diperbarui.";
    }
}

/* ===================== HAPUS PESANAN ===================== */
if (isset($_POST['hapus_pesanan'])) {
    $orderId = (int)$_POST['order_id'];

    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$orderId]);
        $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$orderId]);
        $pdo->commit();
        $success = "Pesanan berhasil dihapus.";
    } catch (Throwable $e) {
        $pdo->rollBack();
        $error = "Gagal menghapus pesanan.";
    }
}

/* ===================== AMBIL SEMUA PESANAN ===================== */
$stmt = $pdo->query("
    SELECT 
        o.id,
        o.user_id,
        o.total_price AS total,
        o.status,
        o.created_at,
        u.name AS nama_user
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    ORDER BY o.id DESC
");
$orders = $stmt->fetchAll();

/* HEADER TETAP DIPANGGIL — TIDAK DIUTAK–ATIK */
include __DIR__ . '/../header.php';
?>

<!-- =============== CSS UNTUK KONTEN SAJA (HEADER TIDAK TERSENTUH) =============== -->
<style>
.admin-container{
    max-width:1100px;margin:20px auto;padding:0 16px;
}

/* ALERT */
.alert{
    padding:.9rem 1rem;border-radius:10px;font-weight:600;margin:1rem 0;
}
.alert.error{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca}
.alert.success{background:#dcfce7;color:#166534;border:1px solid #bbf7d0}

/* TABLE */
.tabel{
    width:100%;border-collapse:collapse;border-radius:12px;
    overflow:hidden;background:white;box-shadow:0 2px 10px rgba(0,0,0,.05)
}
.tabel th{
    background:#ec4899;color:white;padding:.9rem;font-size:.9rem;text-align:left;
}
.tabel td{
    padding:.8rem;border-bottom:1px solid #e5e7eb;
}

/* BUTTON */
.btn{
    padding:.5rem .9rem;border-radius:10px;border:none;
    background:#ec4899;color:white;font-weight:600;cursor:pointer;
}
.btn:hover{background:#db2777}
.btn.secondary{
    background:white;border:1px solid #ec4899;color:#ec4899;
}

/* SELECT */
select{
    padding:.45rem .6rem;border-radius:10px;border:1px solid #ddd;width:100%;margin-top:6px;
}

/* MODAL */
.modal{
    position:fixed;top:0;left:0;width:100%;height:100%;
    background:rgba(0,0,0,.45);display:none;
    align-items:center;justify-content:center;padding:1rem;
}
.modal-content{
    background:white;padding:1.2rem;border-radius:14px;max-width:520px;width:100%;
}
</style>

<!-- ==================== CONTENT ==================== -->
<div class="admin-container">

    <h2 style="margin-bottom:.5rem;">Kelola Pesanan</h2>

    <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <table class="tabel">
        <thead>
            <tr>
                <th>ID</th>
                <th>Pelanggan</th>
                <th>Total</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td>#<?= $order['id']; ?></td>
                <td><?= htmlspecialchars($order['nama_user']); ?></td>
                <td><?= rupiah($order['total']); ?></td>
                <td><?= ucfirst($order['status']); ?></td>
                <td><?= $order['created_at']; ?></td>

                <td>

                    <!-- DETAIL -->
                    <button class="btn" onclick="document.getElementById('detail<?= $order['id']; ?>').style.display='flex'">
                        Detail
                    </button>

                    <!-- UBAH STATUS -->
                    <form method="post" style="margin-top:5px;">
                        <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                        <select name="status">
                            <?php $statuses=['pending','diproses','dikirim','selesai','dibatalkan']; ?>
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?= $s ?>" <?= $order['status']==$s?'selected':'' ?>>
                                    <?= ucfirst($s) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn" name="ubah_status">Simpan</button>
                    </form>

                    <!-- HAPUS -->
                    <form method="post" style="margin-top:5px;" onsubmit="return confirm('Hapus pesanan ini?')">
                        <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                        <button class="btn secondary" name="hapus_pesanan">Hapus</button>
                    </form>

                </td>
            </tr>

            <!-- ===================== MODAL DETAIL ===================== -->
            <div class="modal" id="detail<?= $order['id']; ?>">
                <div class="modal-content">

                    <h3>Detail Pesanan #<?= $order['id']; ?></h3>

                    <?php
                    $q = $pdo->prepare("
                        SELECT oi.quantity, oi.price, pv.variant_name, p.name AS product_name
                        FROM order_items oi
                        JOIN product_variants pv ON pv.id = oi.variant_id
                        JOIN products p ON p.id = pv.product_id
                        WHERE oi.order_id = ?
                    ");
                    $q->execute([$order['id']]);
                    $items = $q->fetchAll();
                    ?>

                    <table class="tabel" style="margin-top:1rem;">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Varian</th>
                                <th>Qty</th>
                                <th>Harga</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td><?= htmlspecialchars($it['product_name']); ?></td>
                                <td><?= htmlspecialchars($it['variant_name']); ?></td>
                                <td><?= $it['quantity']; ?></td>
                                <td><?= rupiah($it['price']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <p style="margin-top:1rem;font-weight:600;">
                        Total Pesanan: <?= rupiah($order['total']); ?>
                    </p>

                    <button class="btn" style="margin-top:.8rem"
                        onclick="document.getElementById('detail<?= $order['id']; ?>').style.display='none'">
                        Tutup
                    </button>

                </div>
            </div>
            <!-- ===================== END MODAL ===================== -->

        <?php endforeach; ?>
        </tbody>
    </table>

</div>

<?php include __DIR__ . '/../footer.php'; ?>
