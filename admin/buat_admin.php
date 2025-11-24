<?php
session_start();
require_once __DIR__ . '/../inti/koneksi_database.php';
require_once __DIR__ . '/../inti/autentikasi.php';
require_once __DIR__ . '/../inti/fungsi.php';

cek_admin();

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm']);

    if ($nama === '' || $email === '' || $password === '' || $confirm === '') {
        $error = "Semua field wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif ($password !== $confirm) {
        $error = "Password tidak cocok.";
    } else {
        $cek = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $cek->execute([$email]);

        if ($cek->fetch()) {
            $error = "Email sudah digunakan.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password_hash, role, is_active, created_at)
                VALUES (?, ?, ?, 'admin', 1, NOW())
            ");
            $stmt->execute([$nama, $email, $hash]);

            $success = "Admin baru berhasil dibuat!";
        }
    }
}

include __DIR__ . '/../header.php';
?>

<h2>Buat Admin Baru</h2>

<?php if ($error): ?>
  <div class="alert error"><?= htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
  <div class="alert success"><?= htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="post" style="max-width:400px;">
    <label>Nama Admin:</label>
    <input type="text" name="nama" required>

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Password:</label>
    <input type="password" name="password" required>

    <label>Konfirmasi Password:</label>
    <input type="password" name="confirm" required>

    <button type="submit" class="btn">Buat Admin</button>
</form>

<?php include __DIR__ . '/../footer.php'; ?>
