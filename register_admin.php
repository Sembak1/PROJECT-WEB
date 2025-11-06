<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Hanya admin yang boleh membuka halaman ini
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Proses form pendaftaran admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Permintaan tidak sah. Silakan muat ulang halaman.';
    } elseif ($name === '' || $email === '' || $phone === '' || $password === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $error = 'Nomor telepon harus 10–15 digit angka tanpa spasi atau simbol.';
    } else {
        // Cek apakah email sudah terdaftar
        $cek = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $cek->execute([$email]);
        if ($cek->fetchColumn() > 0) {
            $error = 'Email sudah digunakan.';
        } else {
            // Hash password
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Masukkan data admin baru
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, phone, password_hash, role, is_active, created_at)
                VALUES (?, ?, ?, ?, 'admin', 1, NOW())
            ");
            $stmt->execute([$name, $email, $phone, $hash]);
            $success = 'Akun admin baru berhasil dibuat!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Admin - Glowify Beauty</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f9fafb;
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
      margin-bottom: 0.5rem;
    }
    h2 {
      text-align: center;
      margin-bottom: 1.5rem;
    }
    label {
      display: block;
      font-weight: 500;
      margin-bottom: .25rem;
      color: #374151;
    }
    input {
      width: 100%;
      padding: .55rem;
      border: 1px solid #ddd;
      border-radius: 10px;
      margin-bottom: 1rem;
    }
    .btn {
      width: 100%;
      background: #ec4899;
      color: white;
      border: none;
      padding: .6rem;
      border-radius: 10px;
      cursor: pointer;
      font-size: 1rem;
    }
    .btn:hover {
      background: #db2777;
    }
    .notice {
      padding: .75rem;
      border-radius: 10px;
      margin-bottom: 1rem;
      text-align: center;
    }
    .error {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #fecaca;
    }
    .success {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #bbf7d0;
    }
    a {
      color: #ec4899;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="container">
    <h1>🌸 Glowify Beauty 🌸</h1>
    <h2>Daftar Akun Admin</h2>

    <?php if ($error): ?>
      <div class="notice error"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
      <div class="notice success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

      <label for="name">Nama Lengkap:</label>
      <input type="text" id="name" name="name" required>

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" required>

      <label for="phone">Nomor Telepon:</label>
      <input type="text" id="phone" name="phone" placeholder="08xxxxxxxxxx" required>

      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required>

      <button type="submit" class="btn">Buat Akun Admin</button>
    </form>

    <p style="text-align:center;margin-top:1rem;">
      <a href="admin.php">⬅ Kembali ke Dashboard Admin</a>
    </p>
  </div>

</body>
</html>
