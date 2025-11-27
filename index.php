<?php
// index.php
// File utama (homepage root) yang digunakan hanya untuk redirect otomatis.

// Mengarahkan pengguna langsung ke halaman beranda user
header('Location: /glowify/user/beranda.php');

// Menghentikan eksekusi script setelah redirect
exit;
