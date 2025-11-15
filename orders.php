<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Akses khusus admin
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Generate CSRF untuk aksi form
if (empty($_SESSION['csrf_admin'])) {
    $_SESSION['csrf_admin'] = bin2hex(random_bytes(32));
}

$flash = ['type' => '', 'msg' => ''];

// Proses aksi pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['action'], $_POST['csrf'])) {

    $orderId = (int) $_POST['order_id'];
    $action  = $_POST['action'];
    $csrf    = $_POST['csrf'];

    // Validasi token
    if (!hash_equals($_SESSION['csrf_admin'], $csrf)) {
        $flash = ['type' => 'error', 'msg' => 'Permintaan tidak sah. Silakan refresh halaman.'];
    } else {

        // Ambil status pesanan
        $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $flash = ['type' => 'error', 'msg' => 'Pesanan tidak ditemukan.'];
        } else {
            $current = $row['status'];
            $next = null;

            if ($action === 'mark_paid' && $current === 'pending') $next = 'paid';
            if ($action === 'ship'      && $current === 'paid')    $next = 'shipped';
            if ($action === 'complete'  && $current === 'shipped') $next = 'completed';
            if ($action === 'cancel'    && in_array($current, ['pending', 'paid'])) $next = 'cancelled';

            // Hapus
            if ($action === 'delete') {
                if ($current === 'shipped') {
                    $del = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                    $del->execute([$orderId]);
                    $flash = ['type' => 'success', 'msg' => "Pesanan #$orderId berhasil dihapus."];
                } else {
                    $flash = ['type' => 'error', 'msg' => 'Pesanan hanya dapat dihapus jika statusnya sudah dikirim.'];
                }
            }
            // Update status
            elseif ($next) {
                $upd = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $upd->execute([$next, $orderId]);

                $labels = [
                    'paid'      => 'ditandai dibayar',
                    'shipped'   => 'dikirim',
                    'completed' => 'diselesaikan',
                    'cancelled' => 'dibatalkan'
                ];

                $flash = ['type' => 'success', 'msg' => "Pesanan #$orderId berhasil {$labels[$next]}."];
            } else {
                $flash = ['type' => 'error', 'msg' => 'Aksi tidak valid untuk status saat ini.'];
            }
        }
    }
}

// Ambil semua pesanan (gunakan kolom total_price → sesuaikan dengan tabelmu)
$stmt = $pdo->query("
    SELECT 
        o.id, 
        o.total_price AS total, 
        o.status, 
        o.created_at, 
        u.name AS customer_name, 
        u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/header.php';
?>

<div class="container section">
    <h2>📦 Daftar Pesanan</h2>

    <?php if ($flash['msg']): ?>
        <div style="margin:.8rem 0;padding:.75rem 1rem;border-radius:10px;
            <?php echo $flash['type']==='success'
                ? 'background:#dcfce7;color:#166534;border:1px solid #bbf7d0;'
                : 'background:#fee2e2;color:#991b1b;border:1px solid #fecaca;'; ?>">
            <?= htmlspecialchars($flash['msg']) ?>
        </div>
    <?php endif; ?>

    <table style="width:100%;border-collapse:collapse;margin-top:1rem;">
        <thead style="background:#f3f4f6;">
            <tr>
                <th style="padding:.6rem;border:1px solid #ddd;">ID</th>
                <th style="padding:.6rem;border:1px solid #ddd;">Pelanggan</th>
                <th style="padding:.6rem;border:1px solid #ddd;">Email</th>
                <th style="padding:.6rem;border:1px solid #ddd;">Total</th>
                <th style="padding:.6rem;border:1px solid #ddd;">Status</th>
                <th style="padding:.6rem;border:1px solid #ddd;">Tanggal</th>
                <th style="padding:.6rem;border:1px solid #ddd;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td style="padding:.6rem;border:1px solid #ddd;"><?= $o['id'] ?></td>
                    <td style="padding:.6rem;border:1px solid #ddd;"><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td style="padding:.6rem;border:1px solid #ddd;"><?= htmlspecialchars($o['email']) ?></td>
                    <td style="padding:.6rem;border:1px solid #ddd;">Rp <?= number_format($o['total'], 0, ',', '.') ?></td>
                    <td style="padding:.6rem;border:1px solid #ddd;"><?= ucfirst($o['status']) ?></td>
                    <td style="padding:.6rem;border:1px solid #ddd;"><?= $o['created_at'] ?></td>

                    <td style="padding:.4rem;border:1px solid #ddd;">
                        <div style="display:flex;gap:.4rem;flex-wrap:wrap;">

                            <?php if ($o['status'] === 'pending'): ?>
                                <!-- Dibayar -->
                                <form method="post" onsubmit="return confirm('Tandai pesanan #<?= $o['id']?> dibayar?')">
                                    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_admin'] ?>">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <input type="hidden" name="action" value="mark_paid">
                                    <button style="padding:.45rem .7rem;border:none;border-radius:8px;background:#0ea5e9;color:#fff;cursor:pointer;">
                                        Tandai Dibayar
                                    </button>
                                </form>
                                <!-- Batal -->
                                <form method="post" onsubmit="return confirm('Batalkan pesanan #<?= $o['id']?>?')">
                                    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_admin'] ?>">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <input type="hidden" name="action" value="cancel">
                                    <button style="padding:.45rem .7rem;border:none;border-radius:8px;background:#ef4444;color:#fff;cursor:pointer;">
                                        Batalkan
                                    </button>
                                </form>

                            <?php elseif ($o['status'] === 'paid'): ?>
                                <!-- Kirim -->
                                <form method="post" onsubmit="return confirm('Kirim pesanan #<?= $o['id']?>?')">
                                    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_admin'] ?>">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <input type="hidden" name="action" value="ship">
                                    <button style="padding:.45rem .7rem;border:none;border-radius:8px;background:#22c55e;color:#fff;cursor:pointer;">
                                        Kirim
                                    </button>
                                </form>
                                <!-- Batal -->
                                <form method="post" onsubmit="return confirm('Batalkan pesanan #<?= $o['id']?>?')">
                                    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_admin'] ?>">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <input type="hidden" name="action" value="cancel">
                                    <button style="padding:.45rem .7rem;border:none;border-radius:8px;background:#ef4444;color:#fff;cursor:pointer;">
                                        Batalkan
                                    </button>
                                </form>

                            <?php elseif ($o['status'] === 'shipped'): ?>
                                <!-- Selesai -->
                                <form method="post" onsubmit="return confirm('Selesaikan pesanan #<?= $o['id']?>?')">
                                    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_admin'] ?>">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <input type="hidden" name="action" value="complete">
                                    <button style="padding:.45rem .7rem;border:none;border-radius:8px;background:#8b5cf6;color:#fff;cursor:pointer;">
                                        Selesai
                                    </button>
                                </form>
                                <!-- Hapus -->
                                <form method="post" onsubmit="return confirm('Hapus pesanan #<?= $o['id']?>?')">
                                    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_admin'] ?>">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button style="padding:.45rem .7rem;border:none;border-radius:8px;background:#6b7280;color:#fff;cursor:pointer;">
                                        Hapus
                                    </button>
                                </form>

                            <?php else: ?>
                                <span style="color:#6b7280;font-size:.9rem;">Tidak ada aksi</span>
                            <?php endif; ?>

                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (count($orders) === 0): ?>
                <tr><td colspan="7" style="text-align:center;padding:1rem;">Belum ada pesanan.</td></tr>
            <?php endif; ?>

        </tbody>
    </table>

    <p style="margin-top:1.5rem;">
        <a href="admin.php" style="color:#ec4899;">⬅ Kembali ke Dashboard</a>
    </p>
</div>

<?php include __DIR__ . '/footer.php'; ?>
