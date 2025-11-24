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
    $phone    = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm']);

    if ($nama === '' || $email === '' || $phone === '' || $password === '' || $confirm === '') {
        $error = "Semua field wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (!preg_match('/^[0-9]+$/', $phone)) {
        $error = "Nomor HP hanya boleh angka.";
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
                INSERT INTO users (name, email, phone, password_hash, role, is_active, created_at)
                VALUES (?, ?, ?, ?, 'admin', 1, NOW())
            ");
            $stmt->execute([$nama, $email, $phone, $hash]);

            $success = "Admin baru berhasil dibuat!";
        }
    }
}

include __DIR__ . '/../header.php';
?>

<!-- ======================= STYLE PREMIUM ======================= -->
<style>
.page-wrapper {
    max-width: 450px;
    margin: 40px auto;
    padding: 0 20px;
    font-family: 'Inter', sans-serif;
}

.title {
    font-size: 1.7rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 24px;
}

/* CARD */
.form-card {
    background: #ffffff;
    padding: 28px;
    border-radius: 18px;
    box-shadow: 0 8px 24px rgba(236, 72, 153, 0.15);
}

/* Input */
.form-card input {
    width: 100%;
    padding: 12px;
    font-size: 15px;
    margin-bottom: 16px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    outline: none;
    transition: .2s;
}
.form-card input:focus {
    border-color: #ec4899;
    box-shadow: 0 0 0 4px #fbe7f3;
}

/* Labels */
.form-card label {
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
}

/* ALERT */
.alert {
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 18px;
    font-weight: 600;
}
.alert.error { background:#fee2e2; border:1px solid #fecaca; color:#b91c1c; }
.alert.success { background:#dcfce7; border:1px solid #bbf7d0; color:#166534; }

/* BUTTON */
.btn-primary {
    width: 100%;
    padding: 12px 0;
    font-size: 16px;
    background: #ec4899;
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: .2s;
    box-shadow: 0 4px 14px rgba(236,72,153,.3);
}
.btn-primary:hover {
    background: #db2777;
}

/* LINK KEMBALI */
.back-link {
    margin-top: 14px;
    display: inline-block;
    text-decoration: none;
    color: #ec4899;
    font-weight: 600;
    text-align: center;
    width: 100%;
}
</style>

<div class="page-wrapper">

    <h2 class="title">Buat Admin Baru</h2>

    <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="form-card">

        <form method="post">

            <label>Nama Admin:</label>
            <input type="text" name="nama" required>

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Nomor HP:</label>
            <input type="text" name="phone" placeholder="08xxxxxxxxxx" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <label>Konfirmasi Password:</label>
            <input type="password" name="confirm" required>

            <button type="submit" class="btn-primary">Buat Admin</button>

        </form>

    </div>

    <a href="/glowify/admin/dashboard_admin.php" class="back-link">Kembali ke Dashboard</a>

</div>

<?php include __DIR__ . '/../footer.php'; ?>
