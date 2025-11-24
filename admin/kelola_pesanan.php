<?php
session_start();
require_once __DIR__ . '/../inti/koneksi_database.php';
require_once __DIR__ . '/../inti/autentikasi.php';
require_once __DIR__ . '/../inti/fungsi.php';

cek_admin();

$error = "";
$success = "";

/* ===================================
   HAPUS PESANAN
=================================== */
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

/* ===================================
   AMBIL LIST PESANAN
=================================== */
$stmt = $pdo->query("
    SELECT 
        o.id,
        o.total_price AS total,
        o.created_at,
        u.name AS nama_user
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    ORDER BY o.id DESC
");
$orders = $stmt->fetchAll();

include __DIR__ . '/../header.php';
?>

<!-- ================== STYLE PREMIUM GLOWIFY ================== -->
<style>
.page {
    max-width: 1050px;
    margin: 35px auto;
    padding: 0 20px;
    font-family: 'Inter', sans-serif;
}

.title {
    font-size: 1.7rem;
    font-weight: 700;
    margin-bottom: 18px;
    color: #333;
    text-align: center;
}

/* Alerts */
.alert {
    padding: 14px 18px;
    border-radius: 12px;
    font-weight: 600;
    margin-bottom: 20px;
}
.alert.error { background:#fee2e2; border:1px solid #fecaca; color:#b91c1c; }
.alert.success { background:#dcfce7; border:1px solid #bbf7d0; color:#166534; }

/* Tabel */
.table-box {
    background: #ffffff;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(236, 72, 153, 0.12);
}

.table-premium {
    width: 100%;
    border-collapse: collapse;
}

.table-premium thead {
    background: #ec4899;
    color: white;
}

.table-premium th {
    padding: 14px;
    font-weight: 600;
    text-align: center;
    white-space: nowrap;
}

.table-premium td {
    padding: 14px;
    border-bottom: 1px solid #f3f4f6;
    font-size: 15px;
    white-space: nowrap;
    text-align: center;
}

/* Fixed width agar TABEL SEJAJAR SEMPURNA */
.col-id     { width: 80px; }
.col-name   { width: 260px; text-align: left !important; padding-left: 24px !important; }
.col-total  { width: 150px; }
.col-date   { width: 230px; }
.col-action { width: 120px; }

/* Tombol */
.btn-del {
    padding: 7px 16px;
    border: 1px solid #ec4899;
    border-radius: 10px;
    background: white;
    color: #ec4899;
    font-weight: 600;
    cursor: pointer;
    transition: .2s;
}
.btn-del:hover {
    background: #ffe0f2;
}

/* Tombol Tutup */
.btn-tutup {
    margin-top: 24px;
    display: inline-block;
    padding: 12px 24px;
    background: #ec4899;
    border-radius: 12px;
    color: white;
    font-weight: 600;
    font-size: 16px;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(236,72,153,0.3);
}
.btn-tutup:hover { background:#db2777; }
</style>

<div class="page">

    <h2 class="title">Lihat Pesanan</h2>

    <?php if ($error): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert success"><?= $success ?></div>
    <?php endif; ?>

    <div class="table-box">
        <table class="table-premium">
            <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th class="col-name">Pelanggan</th>
                    <th class="col-total">Total</th>
                    <th class="col-date">Tanggal</th>
                    <th class="col-action">Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?= $order['id']; ?></td>
                    <td class="col-name"><?= htmlspecialchars($order['nama_user']); ?></td>
                    <td><?= rupiah($order['total']); ?></td>
                    <td><?= $order['created_at']; ?></td>

                    <td>
                        <form method="post" 
                              onsubmit="return confirm('Hapus pesanan ini?');" 
                              style="display:inline;">
                            <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                            <button class="btn-del" name="hapus_pesanan">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>

        </table>
    </div>

    <a href="/glowify/admin/dashboard_admin.php" class="btn-tutup">Tutup</a>
</div>

<?php include __DIR__ . '/../footer.php'; ?>
