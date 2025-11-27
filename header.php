<?php
// header.php
// File ini adalah bagian header yang digunakan di semua halaman frontend.

// Pastikan session aktif untuk mengecek status login user
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mengambil fungsi-fungsi umum (misalnya keranjang_item(), rupiah(), dll)
require_once __DIR__ . '/inti/fungsi.php';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <!-- Menentukan encoding karakter -->

  <title>Glowify Beauty</title>
  <!-- Judul halaman tampil di tab browser -->

  <meta name="viewport" content="width=device-width,initial-scale=1">
  <!-- Agar tampilan responsif di HP/tablet -->

  <link rel="stylesheet" href="/glowify/aset/css/gaya.css">
  <!-- Menghubungkan file CSS global -->
</head>

<body>

<!-- ==========================================================
     NAVBAR / HEADER UTAMA WEBSITE
========================================================== -->
<header class="navbar">
  <div class="container wrap">
    
    <!-- Logo / Brand -->
    <a href="/glowify/user/beranda.php" class="brand">Glowify Beauty</a>


    <!-- MENU NAVIGASI -->
    <nav class="navlinks">

      <!-- Link standar -->
      <a href="/glowify/user/beranda.php">Beranda</a>
      <a href="/glowify/user/daftar_produk.php">Produk</a>


      <!-- ======================================================
           CEK STATUS LOGIN PENGGUNA
      ====================================================== -->
      <?php if (!empty($_SESSION['user'])): ?>

        <!-- Jika sedang login & role adalah admin -->
        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
          
          <!-- Tampilkan menu dashboard admin -->
          <a href="/glowify/admin/dashboard_admin.php">Admin</a>

        <?php else: ?>

          <!-- Jika login sebagai customer -->
          <a href="/glowify/user/keranjang.php">
            Keranjang
            (
              <?php 
                // Hitung total qty dalam keranjang
                // array_map mengambil semua qty
                // array_sum menjumlahkannya
                echo array_sum(array_map(fn($i) => $i['qty'], keranjang_item())); 
              ?>
            )
          </a>

        <?php endif; ?>

        <!-- TOMBOL LOGOUT -->
        <a href="/glowify/akun/keluar.php" class="btn secondary">Keluar</a>

      
      <?php else: ?>
        <!-- ======================================================
             USER BELUM LOGIN → Tampilkan tombol login & daftar
        ====================================================== -->

        <a href="/glowify/akun/masuk.php">Masuk</a>
        <a href="/glowify/akun/daftar.php" class="btn">Daftar</a>

      <?php endif; ?>

    </nav>
  </div>
</header>


<!-- ==========================================================
     AWAL KONTEN UTAMA HALAMAN
========================================================== -->
<main class="section container">
