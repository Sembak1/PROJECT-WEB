<?php
session_start();
// Memulai session untuk mengelola status login pengguna.

require_once __DIR__ . '/../inti/koneksi_database.php';
// Mengambil koneksi database PDO.



/* ==========================================================
   CEK JIKA USER SUDAH LOGIN → LANGSUNG REDIRECT
========================================================== */
if (!empty($_SESSION['user'])) {

    $role = $_SESSION['user']['role']; // Ambil role user

    // Jika admin → dashboard admin
    // Jika customer → beranda user
    header("Location: " . ($role === 'admin'
        ? "/glowify/admin/dashboard_admin.php"
        : "/glowify/user/beranda.php"
    ));
    exit;
}

$error = ""; // Variabel untuk menyimpan pesan error



/* ==========================================================
   PROSES LOGIN (SAAT TOMBOL SUBMIT DITEKAN)
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');      // Input email
    $password = trim($_POST['password'] ?? '');   // Input password

    // Validasi field kosong
    if ($email === "" || $password === "") {
        $error = "Email dan password wajib diisi!";
    } 
    else {

        // Cek email dalam database
        $stmt = $pdo->prepare("
            SELECT id, name, email, password_hash, role, is_active
            FROM users
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {

            // Jika akun dinonaktifkan
            if ($user['is_active'] != 1) {
                $error = "Akun telah dinonaktifkan.";
            }
            // Jika password salah
            elseif (!password_verify($password, $user['password_hash'])) {
                $error = "Email atau password salah!";
            }
            // Jika login berhasil
            else {

                // Simpan user ke session
                $_SESSION['user'] = [
                    'id'    => $user['id'],
                    'name'  => $user['name'],
                    'email' => $user['email'],
                    'role'  => $user['role']
                ];

                session_regenerate_id(true); // Keamanan session



                /* ========================================================
                   CEK APAKAH USER DARI HALAMAN LAIN YANG MEMBUTUHKAN LOGIN
                ======================================================== */
                if (!empty($_SESSION['redirect_setelah_login'])) {
                    $go = $_SESSION['redirect_setelah_login'];
                    unset($_SESSION['redirect_setelah_login']);
                    header("Location: $go");
                    exit;
                }

                // Redirect default berdasarkan role
                header("Location: " . ($user['role'] === 'admin'
                    ? "/glowify/admin/dashboard_admin.php"
                    : "/glowify/user/beranda.php"
                ));
                exit;
            }
        } 
        // Jika email tidak ditemukan
        else {
            $error = "Email tidak ditemukan!";
        }
    }
}
?>



<!-- ========================================================
       HTML + CSS LOGIN PAGE (TAMPILAN HALAMAN LOGIN)
======================================================== -->
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Glowify Beauty</title>

<style>
    /* ================================
       RESET & FIX AGAR TIDAK ADA SCROLL
    ================================ */
    html, body {
        height: 100%;
        margin: 0;
        overflow: hidden; /* Mengunci scroll agar tampilan bersih */
        font-family: 'Segoe UI', sans-serif;
        background: #f9fafb; /* Warna background lembut */
    }

    /* ================================
       WRAPPER AGAR FORM DI TENGAH LAYAR
    ================================ */
    .auth-wrapper {
        height: 100vh;
        display: flex;
        justify-content: center; /* Tengah horizontal */
        align-items: center;     /* Tengah vertikal */
    }

    /* ================================
       KOTAK LOGIN
    ================================ */
    .auth-container {
        width: 400px;
        background: white;
        padding: 2rem;
        border-radius: 14px;
        box-shadow: 0 4px 12px rgba(0,0,0,.1);
        text-align: center; /* Semua teks di tengah */
    }

    h1 {
        color: #ec4899; /* Pink Glowify */
        margin: 0 0 .5rem;
        font-size: 1.8rem;
    }

    h2 {
        margin-bottom: 1.8rem;
        color: #444;
        font-size: 1.2rem;
    }

    /* ================================
       LABEL & INPUT FIELD
    ================================ */
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

    /* ================================
       TOMBOL LOGIN
    ================================ */
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
        transition: .2s;
    }

    .btn:hover {
        background: #db2777; /* Pink gelap saat hover */
    }

    /* ================================
       ALERT ERROR
    ================================ */
    .error {
        background: #fee2e2;
        color: #991b1b;
        padding: .7rem;
        border-radius: 10px;
        margin-bottom: 1rem;
    }

    /* ================================
       LINK KE HALAMAN REGISTER
    ================================ */
    a {
        color: #ec4899;
        text-decoration: none;
        font-weight: 600;
    }
</style>
</head>
<body>


<!-- ========================================================
       TAMPILAN FORM LOGIN
======================================================== -->
<div class="auth-wrapper">

    <div class="auth-container">

        <!-- Judul Website -->
        <h1>Glowify Beauty</h1>
        <h2>Masuk ke Akun</h2>

        <!-- Menampilkan pesan error jika ada -->
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- FORM LOGIN -->
        <form method="POST">

            <!-- Input Email -->
            <label>Email</label>
            <input type="email" name="email" placeholder="email@example.com" required>

            <!-- Input Password -->
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>

            <!-- Tombol Masuk -->
            <button type="submit" class="btn">Masuk</button>
        </form>

        <!-- Link ke Register -->
        <p style="margin-top:1.1rem;">
            Belum punya akun? 
            <a href="daftar.php">Daftar di sini</a>
        </p>

    </div>

</div>

</body>
</html>
