<?php
// get_catatan.php
session_start();
include 'config.php'; // Pastikan koneksi database sudah benar

header('Content-Type: application/json');

if (!$connKendaraan) {
    echo json_encode(["error" => "Database connection failed."]);
    exit();
}

if (isset($_SESSION['npk'])) {
    $pemohon_id = $_GET['id'];

    // Ambil remark berdasarkan pemohon_id
    $remarkQuery = "SELECT remark_kadept, remark_direksi, remark_ga FROM approval_pemohon WHERE pemohon_id = ?";
    $remarkStmt = $connKendaraan->prepare($remarkQuery);
    $remarkStmt->bind_param("i", $pemohon_id); // Pastikan tipe data sesuai
    $remarkStmt->execute();
    $remarkResult = $remarkStmt->get_result();
    $remarks = $remarkResult->fetch_assoc();

    if ($remarks) {
        echo json_encode($remarks);
    } else {
        echo json_encode(["error" => "Remarks not found"]);
    }
} else {
    echo json_encode(["error" => "User not logged in"]);
}
