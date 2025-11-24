<?php
session_start();
require_once __DIR__ . '/../inti/koneksi_database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm'] ?? '');
    $role     = 'customer';

    if ($name === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif ($password !== $confirm) {
        $error = 'Password dan konfirmasi tidak sama.';
    } else {
        // Cek email sudah ada
        $cek = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $cek->execute([$email]);

        if ($cek->fetch()) {
            $error = 'Email sudah terdaftar.';
        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, phone, password_hash, role, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, 1, NOW())
            ");
            $stmt->execute([$name, $email, $phone, $hash, $role]);

            $success = 'Akun berhasil dibuat! Silakan masuk.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Akun - Glowify Beauty</title>

<style>
    /* Fix tampilan agar tidak ada scroll */
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
        background: #f9fafb;
        font-family: 'Segoe UI', sans-serif;
    }

    /* Full center layout */
    .auth-wrapper {
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .auth-container {
        width: 420px;
        background: white;
        padding: 2rem;
        border-radius: 14px;
        box-shadow: 0 4px 12px rgba(0,0,0,.1);
        text-align: center;
    }

    h1 {
        color: #ec4899;
        margin-top: 0;
    }

    h2 {
        color: #444;
        margin-bottom: 1.7rem;
    }

    label {
        display: block;
        text-align: left;
        margin-top: .8rem;
        font-weight: 600;
    }

    input {
        width: 100%;
        padding: .55rem;
        font-size: 1rem;
        border: 1px solid #ddd;
        border-radius: 10px;
        margin-top: .2rem;
    }

    .btn {
        width: 100%;
        padding: .7rem;
        margin-top: 1.4rem;
        border: none;
        border-radius: 10px;
        background: #ec4899;
        color: white;
        font-size: 1rem;
        cursor: pointer;
        transition: .2s;
    }

    .btn:hover {
        background: #db2777;
    }

    .alert {
        padding: .75rem;
        border-radius: 10px;
        margin-bottom: 1rem;
    }

    .error { background:#fee2e2; color:#991b1b; }
    .success { background:#d1fae5; color:#065f46; }

    a {
        color: #ec4899;
        text-decoration: none;
        font-weight: 600;
    }
</style>

</head>
<body>

<div class="auth-wrapper">

    <div class="auth-container">

        <h1>🌸 Glowify Beauty 🌸</h1>
        <h2>Buat Akun Baru</h2>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error); ?></div>
        <?php elseif ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="post">

            <label>Nama Lengkap</label>
            <input type="text" name="name" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>No. HP</label>
            <input type="text" name="phone">

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Konfirmasi Password</label>
            <input type="password" name="confirm" required>

            <button type="submit" class="btn">Daftar</button>
        </form>

        <p style="margin-top:1.1rem;">
            Sudah punya akun?
            <a href="masuk.php">Masuk di sini</a>
        </p>

    </div>

</div>

</body>
</html>
