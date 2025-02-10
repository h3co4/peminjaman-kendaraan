<?php
session_start();

// Periksa apakah pengguna sudah login sebelum mencoba logout
if (isset($_SESSION['npk'])) {
    // Menghapus semua variabel sesi dan menghancurkan sesi
    session_unset();
    session_destroy();

    // Mengirimkan sinyal ke frontend untuk menampilkan SweetAlert
    header('Location: index.php');
    exit();
} else {
    // Jika pengguna belum login, langsung redirect ke halaman login
    header('Location: index.php');
    exit();
}
