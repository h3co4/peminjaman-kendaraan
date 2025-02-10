<?php
session_start();
include 'config.php'; // Pastikan file ini berisi koneksi ke database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mengambil data dari form
    $type = $_POST['type'];
    $platmobil = $_POST['platmobil'];
    $warna = $_POST['warna'];

    // Query untuk menyimpan data
    $stmt = $connKendaraan->prepare("INSERT INTO typemobil (type, platmobil, warna) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $type, $platmobil, $warna);

    if ($stmt->execute()) {
        echo 'success'; // Kirimkan response sukses
    } else {
        echo 'error'; // Kirimkan response error
    }

    $stmt->close();
    $connKendaraan->close();
}
