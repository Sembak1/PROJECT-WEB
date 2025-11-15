<?php
session_start();
require_once __DIR__ . '/db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = trim($_POST['password']);

    if ($name === "" || $email === "" || $phone === "" || $password === "") {
        $error = "Semua field wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $error = "Nomor telepon harus 10–15 digit angka.";
    } else {

        // cek email sudah dipakai?
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetchColumn() > 0) {
            $error = "Email sudah terdaftar!";
        } else {

            // hash password
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // insert admin baru
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, phone, password_hash, role, is_active, created_at)
                VALUES (?, ?, ?, ?, 'admin', 1, NOW())
            ");
            $stmt->execute([$name, $email, $phone, $hash]);

            $success = "Admin baru berhasil dibuat!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Admin Baru</title>

<style>
body {
    font-family: Arial, sans-serif;
    background:#f9fafb;
}
.container {
    max-width: 420px;
    margin: 50px auto;
    background: #fff;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,.1);
}
input {
    width: 100%;
    padding: .6rem;
    margin-bottom: 1rem;
    border: 1px solid #ddd;
    border-radius: 10px;
}
button {
    width:100%;
    padding:.7rem;
    background:#ec4899;
    color:white;
    border:none;
    border-radius:10px;
    cursor:pointer;
}
button:hover { background:#db2777; }
.error {
    background:#fee2e2;
    padding:10px;
    border-radius:10px;
    color:#991b1b;
    margin-bottom:1rem;
}
.success {
    background:#dcfce7;
    padding:10px;
    border-radius:10px;
    color:#166534;
    margin-bottom:1rem;
}
</style>

</head>
<body>

<div class="container">
    <h2>Buat Admin Baru</h2>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="name" placeholder="Nama lengkap" required>
        <input type="email" name="email" placeholder="Email admin" required>
        <input type="text" name="phone" placeholder="Nomor telepon" required>
        <input type="password" name="password" placeholder="Password admin" required>

        <button type="submit">Buat Admin</button>
    </form>

    <p style="text-align:center;margin-top:1rem;">
        <a href="login.php">Kembali ke Login</a>
    </p>
</div>

</body>
</html>
