<?php
session_start(); 
// Memulai session untuk menyimpan data login.

require_once __DIR__ . '/../inti/koneksi_database.php';
// Menghubungkan ke database menggunakan PDO.

// Variabel untuk menyimpan pesan
$error = '';
$success = '';

/* ==========================================================
   PROSES REGISTER CUSTOMER
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mengecek apakah form dikirim via POST

    $name     = trim($_POST['name'] ?? '');      // Nama lengkap
    $email    = trim($_POST['email'] ?? '');     // Email pengguna
    $phone    = trim($_POST['phone'] ?? '');     // Nomor HP (opsional)
    $password = trim($_POST['password'] ?? '');  // Password
    $confirm  = trim($_POST['confirm'] ?? '');   // Konfirmasi password
    $role     = 'customer';                      // Role default untuk user baru

    // Validasi: semua field wajib diisi (kecuali phone)
    if ($name === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'Semua field wajib diisi.';
    }
    // Validasi email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    }
    // Password dan konfirmasi harus sama
    elseif ($password !== $confirm) {
        $error = 'Password dan konfirmasi tidak sama.';
    }
    else {
        // Mengecek apakah email sudah terdaftar sebelumnya
        $cek = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $cek->execute([$email]);

        if ($cek->fetch()) {
            $error = 'Email sudah terdaftar.';
        } else {
            // Hash password agar lebih aman
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Menyimpan akun baru ke tabel users
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
    /* =============================================
       RESET & BODY
       ============================================= */
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;                  /* Hilangkan scroll */
        background: #f9fafb;               /* Warna dasar lembut */
        font-family: 'Segoe UI', sans-serif;
    }

    /* =============================================
       WRAPPER TENGAH LAYAR
       ============================================= */
    .auth-wrapper {
        height: 100vh;
        display: flex;
        justify-content: center;           /* Tengah horizontal */
        align-items: center;               /* Tengah vertikal */
    }

    /* =============================================
       KOTAK FORM REGISTER
       ============================================= */
    .auth-container {
        width: 420px;
        background: white;
        padding: 2rem;
        border-radius: 14px;
        box-shadow: 0 4px 12px rgba(0,0,0,.1);
        text-align: center;
    }

    h1 {
        color: #ec4899;        /* Pink Glowify */
        margin-top: 0;
    }

    h2 {
        color: #444;
        margin-bottom: 1.7rem;
    }

    /* =============================================
       LABEL DAN INPUT
       ============================================= */
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

    /* =============================================
       TOMBOL SUBMIT
       ============================================= */
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
        background: #db2777; /* Pink lebih gelap saat hover */
    }

    /* =============================================
       ALERT ERROR & SUCCESS
       ============================================= */
    .alert {
        padding: .75rem;
        border-radius: 10px;
        margin-bottom: 1rem;
    }

    .error { background:#fee2e2; color:#991b1b; }
    .success { background:#d1fae5; color:#065f46; }

    /* =============================================
       LINK KE HALAMAN LOGIN
       ============================================= */
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

        <!-- BRAND -->
        <h1>🌸 Glowify Beauty 🌸</h1>
        <h2>Buat Akun Baru</h2>

        <!-- TAMPILKAN PESAN ERROR/SUKSES -->
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error); ?></div>
        <?php elseif ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- FORM REGISTER -->
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
