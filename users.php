<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Hanya admin yang boleh mengakses
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle aktif/nonaktif user
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE users SET is_active = IF(is_active=1,0,1) WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: users.php');
    exit;
}

include __DIR__ . '/header.php';

// Ambil semua pengguna
$stmt = $pdo->query("SELECT id, name, email, phone, role, is_active, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container section">
  <h2>👥 Manajemen Pengguna</h2>
  <p>Total Pengguna: <strong><?php echo count($users); ?></strong></p>

  <table style="width:100%;border-collapse:collapse;margin-top:1rem;">
    <thead style="background:#f3f4f6;">
      <tr>
        <th style="padding:.6rem;border:1px solid #ddd;">ID</th>
        <th style="padding:.6rem;border:1px solid #ddd;">Nama</th>
        <th style="padding:.6rem;border:1px solid #ddd;">Email</th>
        <th style="padding:.6rem;border:1px solid #ddd;">Telepon</th>
        <th style="padding:.6rem;border:1px solid #ddd;">Role</th>
        <th style="padding:.6rem;border:1px solid #ddd;">Status</th>
        <th style="padding:.6rem;border:1px solid #ddd;">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $user): ?>
        <tr>
          <td style="padding:.6rem;border:1px solid #ddd;"><?php echo $user['id']; ?></td>
          <td style="padding:.6rem;border:1px solid #ddd;"><?php echo htmlspecialchars($user['name']); ?></td>
          <td style="padding:.6rem;border:1px solid #ddd;"><?php echo htmlspecialchars($user['email']); ?></td>
          <td style="padding:.6rem;border:1px solid #ddd;"><?php echo htmlspecialchars($user['phone']); ?></td>
          <td style="padding:.6rem;border:1px solid #ddd;"><?php echo ucfirst($user['role']); ?></td>
          <td style="padding:.6rem;border:1px solid #ddd;">
            <?php echo $user['is_active'] ? '✅ Aktif' : '❌ Nonaktif'; ?>
          </td>
          <td style="padding:.6rem;border:1px solid #ddd;">
            <?php if ($user['id'] !== $_SESSION['user']['id']): // jangan nonaktifkan diri sendiri ?>
              <a href="users.php?toggle=<?php echo $user['id']; ?>" 
                 style="color:#ec4899;text-decoration:none;"
                 onclick="return confirm('Ubah status pengguna ini?')">
                 <?php echo $user['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?>
              </a>
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p style="margin-top:1.5rem;"><a href="admin.php" style="color:#ec4899;">⬅ Kembali ke Dashboard</a></p>
</div>

<?php include __DIR__ . '/footer.php'; ?>
