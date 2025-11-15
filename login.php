<?php
session_start();
require_once __DIR__ . '/db.php';

// Jika sudah login → arahkan
if (!empty($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$error = "";

// Bila form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === "" || $password === "") {
        $error = "Email dan password wajib diisi!";
    } else {

        // Ambil user berdasarkan email
        $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role, is_active FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {

            if ($user['is_active'] != 1) {
                $error = "Akun dinonaktifkan.";
            } elseif (password_verify($password, $user['password_hash'])) {

                // Simpan session user
                $_SESSION['user'] = [
                    'id'    => $user['id'],
                    'name'  => $user['name'],
                    'email' => $user['email'],
                    'role'  => $user['role'],
                ];

                session_regenerate_id(true);

                // Jika admin → ke admin panel
                if ($user['role'] === 'admin') {
                    header("Location: admin.php");
                    exit;
                }

                // Jika customer → ke beranda
                header("Location: index.php");
                exit;

            } else {
                $error = "Email atau password salah!";
            }

        } else {
            $error = "Email tidak ditemukan!";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Glowify Beauty</title>

<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background: #f9fafb;
        margin: 0;
        padding: 0;
    }
    .container {
        max-width: 420px;
        margin: 80px auto;
        background: #fff;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,.1);
    }
    h1 {
        text-align: center;
        color: #ec4899;
        margin-bottom: .5rem;
    }
    h2 {
        text-align: center;
        margin-bottom: 1.5rem;
    }
    label { font-weight: 500; margin-bottom: .3rem; display:block; }
    input {
        width: 100%;
        padding: .55rem;
        border-radius: 10px;
        border: 1px solid #ddd;
        margin-bottom: 1rem;
    }
    .btn {
        width: 100%;
        background: #ec4899;
        color: white;
        padding: .6rem;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 1rem;
    }
    .error{
        padding: .7rem;
        text-align:center;
        background:#fee2e2;
        color:#991b1b;
        border-radius: 10px;
        margin-bottom: 1rem;
    }
    a { color:#ec4899; text-decoration:none; }
</style>

</head>
<body>

<div class="container">
    <h1>🌸 Glowify Beauty 🌸</h1>
    <h2>Masuk ke Akun</h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Email:</label>
        <input type="email" name="email" placeholder="email@example.com" required>

        <label>Password:</label>
        <input type="password" name="password" placeholder="••••••••" required>

        <button type="submit" class="btn">Masuk</button>
    </form>

    <p style="text-align:center;margin-top:1rem;">
        Belum punya akun? <a href="register.php">Daftar disini</a>
    </p>
</div>

</body>
</html>
