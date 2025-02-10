<?php

require_once "config.php";

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Query untuk menghapus data berdasarkan ID
    $query = "DELETE FROM typemobil WHERE id = ?";
    $stmt = $connKendaraan->prepare($query);
    $stmt->bind_param("i", $id); // Bind parameter untuk ID

    if ($stmt->execute()) {
        echo 'success'; // Mengirimkan respons sukses jika data berhasil dihapus
    } else {
        echo 'error'; // Mengirimkan respons error jika terjadi kesalahan
    }
} else {
    echo 'error'; // Jika ID tidak diberikan
}
