<?php
session_start();
include 'config.php'; // Pastikan file ini berisi koneksi ke database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mengambil data dari form
    $id_mobil = $_POST['id_mobil'];
    $type = $_POST['type'];
    $platmobil = $_POST['platmobil'];
    $warna = $_POST['warna'];

    // Query untuk mengupdate data mobil
    $stmt = $connKendaraan->prepare("UPDATE typemobil SET type = ?, platmobil = ?, warna = ? WHERE id = ?");
    $stmt->bind_param("sssi", $type, $platmobil, $warna, $id_mobil);

    if ($stmt->execute()) {
        echo 'success'; // Kirimkan response sukses
    } else {
        echo 'error'; // Kirimkan response error
    }

    $stmt->close();
    $connKendaraan->close();
}
