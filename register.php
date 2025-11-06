<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm'] ?? '');
    $role = ($_POST['role'] ?? 'customer'); // default customer

    if ($name === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif ($password !== $confirm) {
        $error = 'Password dan konfirmasi tidak sama.';
    } else {
        // Cek apakah email sudah ada
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar.';
        } else {
            // Hash password
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Masukkan ke database
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, role, is_active)
                                   VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->execute([$name, $email, $phone, $hash, $role]);

            $success = 'Akun berhasil dibuat! Silakan login.';
        }
    }
}
?>

<?php include __DIR__ . '/header.php'; ?>

<div class="container section" style="max-width:420px;">
  <h2 style="margin-bottom:1rem;">Daftar Akun Baru</h2>

  <?php if ($error): ?>
    <div class="notice" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;padding:10px;border-radius:10px;margin-bottom:1rem;">
      <?php echo htmlspecialchars($error); ?>
    </div>
  <?php elseif ($success): ?>
    <div class="notice" style="background:#dcfce7;color:#166534;border:1px solid #bbf7d0;padding:10px;border-radius:10px;margin-bottom:1rem;">
      <?php echo htmlspecialchars($success); ?>
    </div>
  <?php endif; ?>

  <form method="post" style="margin-top:1rem;">
    <label>Nama Lengkap:</label><br>
    <input type="text" name="name" required style="width:100%;padding:.55rem;border:1px solid #ddd;border-radius:10px;margin-bottom:1rem;">

    <label>Email:</label><br>
    <input type="email" name="email" required style="width:100%;padding:.55rem;border:1px solid #ddd;border-radius:10px;margin-bottom:1rem;">

    <label>No. HP:</label><br>
    <input type="text" name="phone" style="width:100%;padding:.55rem;border:1px solid #ddd;border-radius:10px;margin-bottom:1rem;">

    <label>Password:</label><br>
    <input type="password" name="password" required style="width:100%;padding:.55rem;border:1px solid #ddd;border-radius:10px;margin-bottom:1rem;">

    <label>Konfirmasi Password:</label><br>
    <input type="password" name="confirm" required style="width:100%;padding:.55rem;border:1px solid #ddd;border-radius:10px;margin-bottom:1rem;">
    <button type="submit" class="btn" style="width:100%;">Daftar</button>
  </form>

  <p style="margin-top:1rem;text-align:center;">Sudah punya akun?
    <a href="login.php" style="color:#ec4899;">Masuk di sini</a>
  </p>
</div>

<?php include __DIR__ . '/footer.php'; ?>
