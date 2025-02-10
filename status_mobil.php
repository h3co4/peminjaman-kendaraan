<?php
// Pastikan koneksi ke database sudah tersedia
include 'config.php'; // Sesuaikan dengan file koneksi Anda

// Periksa jika tombol 'selesai' ditekan
if (isset($_POST['selesai'])) {
    $id = $_POST['id']; // Ambil ID kendaraan yang dikirimkan

    // Update status kendaraan menjadi tersedia (0)
    $updateQuery = "UPDATE typemobil SET status = 0 WHERE id = $id";

    // Jalankan query
    if (mysqli_query($connKendaraan, $updateQuery)) {
        // Redirect kembali ke halaman utama atau halaman yang diinginkan setelah berhasil
        header("Location: datamobil.php"); // Ganti dengan halaman yang sesuai
        exit();
    } else {
        // Jika gagal update
        echo "Error: " . mysqli_error($connKendaraan);
    }
}
