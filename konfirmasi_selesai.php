<?php
// Include your database connection file
include 'config.php';

// Periksa apakah metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Metode permintaan salah. Harus menggunakan POST."
    ]);
    exit;
}

// Periksa apakah 'id' ada dalam request
if (!isset($_POST['id'])) {
    echo json_encode([
        "success" => false,
        "message" => "ID pemohon tidak ditemukan dalam request."
    ]);
    exit;
}

// Ambil ID pemohon dan validasi
$pemohonId = intval($_POST['id']); // Konversi ke integer untuk keamanan
if ($pemohonId <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "ID pemohon tidak valid."
    ]);
    exit;
}

// Update jadwalmobil untuk tanggal_pulang dan jam_pulang
$idJadwal = $pemohonId; // Gunakan pemohonId untuk konsistensi
$query = "UPDATE jadwalmobil SET tanggal_pulang = NOW(), jampulang = CURTIME() WHERE pemohon_id = ?";
$stmt = $connKendaraan->prepare($query);
$stmt->bind_param("i", $idJadwal);

if (!$stmt->execute() || $stmt->affected_rows <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Gagal memperbarui data tanggal dan jam pulang."
    ]);
    $stmt->close();
    $connKendaraan->close();
    exit;
}

// Update pemohon untuk status, tanggal_pulang, dan jampulang
$newStatus = 6; // Status selesai
$query = "UPDATE pemohon SET status = ?, tanggal_pulang = NOW(), jampulang = CURTIME() WHERE id = ?";
$stmt = $connKendaraan->prepare($query);
$stmt->bind_param("ii", $newStatus, $pemohonId);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Status berhasil diperbarui menjadi Finish."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal memperbarui status. Silakan coba lagi."
    ]);
}

// Tutup statement dan koneksi database
$stmt->close();
$connKendaraan->close();
exit;
