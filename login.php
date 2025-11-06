<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Jika sudah login, arahkan sesuai role
if (!empty($_SESSION['user'])) {
    if ($_SESSION['user']['role'] === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$error = '';
$success = '';

// Buat token CSRF baru jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Simpan halaman asal (supaya setelah login bisa kembali)
if (isset($_SERVER['HTTP_REFERER']) && !str_contains($_SERVER['HTTP_REFERER'], 'login.php')) {
    $_SESSION['redirect_after_login'] = $_SERVER['HTTP_REFERER'];
}

// Jika form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $token = $_POST['csrf_token'] ?? '';

    // Validasi CSRF
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Permintaan tidak sah. Silakan coba lagi.';
    } elseif ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        // Ambil user dari database
        $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role, is_active 
                               FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && (int)$user['is_active'] === 1 && password_verify($password, $user['password_hash'])) {
            // Login berhasil → simpan sesi
            $_SESSION['user'] = [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role']
            ];
            session_regenerate_id(true);

            // Redirect sesuai role atau halaman sebelumnya
            if ($user['role'] === 'admin') {
                header('Location: admin.php');
            } else {
                $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
                unset($_SESSION['redirect_after_login']);
                header("Location: $redirect");
            }
            exit;
        } else {
            $error = 'Email atau password salah, atau akun nonaktif.';
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
      font-size: 1.8rem;
      color: #ec4899;
      margin-bottom: 0.5rem;
      font-weight: 700;
    }
    h2 {
      margin-bottom: 1rem;
      text-align: center;
      color: #111827;
    }
    label {
      display: block;
      margin-bottom: .25rem;
      font-weight: 500;
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
      font-size: .9rem;
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
    p {
      text-align: center;
      margin-top: 1rem;
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
    <h2>Masuk ke Akun</h2>

    <?php if ($error): ?>
      <div class="notice error"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
      <div class="notice success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

      <label for="email">Email:</label>
      <input type="email" id="email" name="email" placeholder="nama@email.com" required>

      <label for="password">Password:</label>
      <input type="password" id="password" name="password" placeholder="••••••••" required>

      <button type="submit" class="btn">Masuk</button>
    </form>

    <p>Belum punya akun?
      <a href="register.php">Daftar di sini</a>
    </p>
  </div>

</body>
</html>
