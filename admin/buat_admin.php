<?php
session_start(); 
// Memulai session agar bisa menggunakan data login user.

require_once __DIR__ . '/../inti/koneksi_database.php';
// Mengimpor file koneksi database (PDO).

require_once __DIR__ . '/../inti/autentikasi.php';
// Mengimpor fungsi autentikasi (cek login & role).

require_once __DIR__ . '/../inti/fungsi.php';
// Mengimpor fungsi tambahan yang digunakan di aplikasi.

cek_admin();
// Mengecek apakah user saat ini adalah admin. Jika bukan, diarahkan keluar.

$error = "";    
// Variabel untuk menampung pesan error.

$success = "";  
// Variabel untuk menampung pesan sukses.


// ==========================================================
//   BAGIAN PROSES FORM (MENYIMPAN ADMIN BARU)
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cek jika form disubmit dengan POST.

    $nama     = trim($_POST['nama']);      // Input nama
    $email    = trim($_POST['email']);     // Input email
    $phone    = trim($_POST['phone']);     // Input nomor HP
    $password = trim($_POST['password']);  // Input password
    $confirm  = trim($_POST['confirm']);   // Input confirm password

    // ======================= VALIDASI =======================
    if ($nama === '' || $email === '' || $phone === '' || $password === '' || $confirm === '') {
        // Jika ada field yang kosong
        $error = "Semua field wajib diisi.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Validasi format email
        $error = "Format email tidak valid.";

    } elseif (!preg_match('/^[0-9]+$/', $phone)) {
        // Validasi nomor HP hanya angka
        $error = "Nomor HP hanya boleh angka.";

    } elseif ($password !== $confirm) {
        // Password harus sama dengan konfirmasi
        $error = "Password tidak cocok.";

    } else {

        // Cek apakah email sudah ada
        $cek = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $cek->execute([$email]);

        if ($cek->fetch()) {
            // Email sudah dipakai user lain
            $error = "Email sudah digunakan.";

        } else {

            // Hash password sebelum disimpan
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Query untuk memasukkan admin baru
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, phone, password_hash, role, is_active, created_at)
                VALUES (?, ?, ?, ?, 'admin', 1, NOW())
            ");
            $stmt->execute([$nama, $email, $phone, $hash]);

            $success = "Admin baru berhasil dibuat!";
        }
    }
}

// Memanggil header HTML utama
include __DIR__ . '/../header.php';
?>

<!-- ==========================================================
    BAGIAN STYLE (CSS)
   ========================================================== -->
<style>
/* Wrapper halaman utama */
.page-wrapper {
    max-width: 450px; /* Lebar kontainer */
    margin: 40px auto; /* Tengah secara horizontal */
    padding: 0 20px;   /* Spasi kiri-kanan */
    font-family: 'Inter', sans-serif;
}

/* Judul halaman */
.title {
    font-size: 1.7rem;
    font-weight: 700;
    text-align: center;
    margin-bottom: 24px;
}

/* Kartu form */
.form-card {
    background: #ffffff;
    padding: 28px;
    border-radius: 18px;
    box-shadow: 0 8px 24px rgba(236, 72, 153, 0.15); /* Bayangan pink */
}

/* Input field */
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

/* Efek fokus input (highlight warna pink) */
.form-card input:focus {
    border-color: #ec4899;
    box-shadow: 0 0 0 4px #fbe7f3;
}

/* Label input */
.form-card label {
    font-weight: 600;
    margin-bottom: 6px;
    display: block;
}

/* Style alert error & success */
.alert {
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 18px;
    font-weight: 600;
}
.alert.error { background:#fee2e2; border:1px solid #fecaca; color:#b91c1c; }
.alert.success { background:#dcfce7; border:1px solid #bbf7d0; color:#166534; }

/* Tombol Buat Admin */
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

/* Hover tombol */
.btn-primary:hover {
    background: #db2777;
}

/* Link kembali ke dashboard */
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


<!-- ==========================================================
    BAGIAN TAMPILAN (HTML)
   ========================================================== -->

<div class="page-wrapper">
    <!-- Wrapper konten agar berada di tengah -->

    <h2 class="title">Buat Admin Baru</h2>
    <!-- Judul halaman -->

    <?php if ($error): ?>
        <!-- Menampilkan pesan error jika ada -->
        <div class="alert error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <!-- Menampilkan pesan sukses jika admin berhasil dibuat -->
        <div class="alert success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="form-card">
        <!-- Kartu putih tempat form -->

        <form method="post">
            <!-- Form menggunakan POST -->

            <!-- Input Nama Admin -->
            <label>Nama Admin:</label>
            <input type="text" name="nama" required>

            <!-- Input Email -->
            <label>Email:</label>
            <input type="email" name="email" required>

            <!-- Input Nomor HP -->
            <label>Nomor HP:</label>
            <input type="text" name="phone" placeholder="08xxxxxxxxxx" required>

            <!-- Input Password -->
            <label>Password:</label>
            <input type="password" name="password" required>

            <!-- Konfirmasi Password -->
            <label>Konfirmasi Password:</label>
            <input type="password" name="confirm" required>

            <!-- Tombol submit -->
            <button type="submit" class="btn-primary">Buat Admin</button>

        </form>
        <!-- End form -->
    </div>

    <!-- Link kembali ke dashboard -->
    <a href="/glowify/admin/dashboard_admin.php" class="back-link">Kembali ke Dashboard</a>

</div>

<?php include __DIR__ . '/../footer.php'; ?>
<!-- Memanggil file footer -->
