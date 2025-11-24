<?php
session_start();
require_once __DIR__ . '/../inti/koneksi_database.php';

// Jika user sudah login
if (!empty($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    header("Location: " . ($role === 'admin' 
        ? "/glowify/admin/dashboard_admin.php" 
        : "/glowify/user/beranda.php"
    ));
    exit;
}

$error = "";

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === "" || $password === "") {
        $error = "Email dan password wajib diisi!";
    } else {
        $stmt = $pdo->prepare("
            SELECT id, name, email, password_hash, role, is_active 
            FROM users 
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['is_active'] != 1) {
                $error = "Akun telah dinonaktifkan.";
            } elseif (!password_verify($password, $user['password_hash'])) {
                $error = "Email atau password salah!";
            } else {

                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];

                session_regenerate_id(true);

                // Redirect sebelumnya
                if (!empty($_SESSION['redirect_setelah_login'])) {
                    $go = $_SESSION['redirect_setelah_login'];
                    unset($_SESSION['redirect_setelah_login']);
                    header("Location: $go");
                    exit;
                }

                header("Location: " . ($user['role'] === 'admin'
                    ? "/glowify/admin/dashboard_admin.php"
                    : "/glowify/user/beranda.php"
                ));
                exit;
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
    /* --- FIX AGAR TIDAK ADA SCROLL --- */
    html, body {
        height: 100%;
        margin: 0;
        overflow: hidden; /* ⛔ Hapus scroll vertikal/horizontal */
        font-family: 'Segoe UI', sans-serif;
        background: #f9fafb;
    }

    /* --- Container full screen dan center --- */
    .auth-wrapper {
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .auth-container {
        width: 400px;
        background: white;
        padding: 2rem;
        border-radius: 14px;
        box-shadow: 0 4px 12px rgba(0,0,0,.1);
        text-align: center;
    }

    h1 {
        color: #ec4899;
        margin: 0 0 .5rem;
        font-size: 1.8rem;
    }

    h2 {
        margin-bottom: 1.8rem;
        color: #444;
        font-size: 1.2rem;
    }

    label {
        display: block;
        text-align: left;
        font-weight: 600;
        margin-bottom: .4rem;
        margin-top: .7rem;
    }

    input {
        width: 100%;
        padding: .55rem;
        font-size: 1rem;
        border-radius: 10px;
        border: 1px solid #ddd;
        margin-bottom: .3rem;
    }

    .btn {
        width: 100%;
        padding: .7rem;
        border: none;
        background: #ec4899;
        color: white;
        border-radius: 10px;
        cursor: pointer;
        font-size: 1rem;
        margin-top: 1rem;
    }

    .btn:hover {
        background: #db2777;
    }

    .error {
        background: #fee2e2;
        color: #991b1b;
        padding: .7rem;
        border-radius: 10px;
        margin-bottom: 1rem;
    }

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

        <h1>Glowify Beauty</h1>
        <h2>Masuk ke Akun</h2>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" placeholder="email@example.com" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>

            <button type="submit" class="btn">Masuk</button>
        </form>

        <p style="margin-top:1.1rem;">
            Belum punya akun? <a href="daftar.php">Daftar di sini</a>
        </p>

    </div>

</div>

</body>
</html>
