<?php
include 'config.php';  // Pastikan file config.php di-include

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
} else {
    echo "Koneksi berhasil!";
}
?>
