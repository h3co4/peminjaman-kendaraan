<?php
session_start();
require 'config.php';  // Pastikan file ini terhubung dengan benar

header('Content-Type: application/json');

if (isset($_POST['npk_penumpang']) && isset($_POST['pemohon_id'])) {
    $npk_penumpang = $_POST['npk_penumpang'];
    $pemohon_id = $_POST['pemohon_id'];

    // Debug input
    if (empty($npk_penumpang) || empty($pemohon_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
        exit;
    }

    // Query untuk menghapus penumpang
    $sql = "DELETE FROM penumpang WHERE npk_penumpang = ? AND pemohon_id = ?";
    if ($stmt = $connKendaraan->prepare($sql)) {
        $stmt->bind_param("si", $npk_penumpang, $pemohon_id);  // si -> string dan integer
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Query gagal: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Prepare statement gagal: ' . $connKendaraan->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak valid']);
}

// Tutup koneksi database
$connKendaraan->close();
