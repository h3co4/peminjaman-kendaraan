<?php
include 'config.php';
date_default_timezone_set('Asia/Jakarta');
// Query untuk mengambil data pemohon yang status 5 dan tanggal/jam pulang sudah lewat
$query = "SELECT * FROM pemohon WHERE CONCAT(tanggal_pulang, ' ', jampulang) < NOW() AND status = 4";

// Menjalankan query
$result = $connKendaraan->query($query);

header("Content-Type: application/json");
// Mengecek apakah ada hasil
if ($result->num_rows > 0) {
    // Menampilkan data setiap pemohon yang ditemukan
    while ($row = $result->fetch_assoc()) {
        $noHpPemohon = getNoHP($connIsd, $row['pemohon']);

        insertNotificationPush($connKendaraan, $noHpPemohon, "PEMBERITAHUAN PENTING.\nDurasi peminjaman kendaraan anda sudah melewati batas waktu rencana Peminjaman.\nHarap selesaikan peminjaman di Sistem Peminjaman-kendaraan dan Membuat permohonan peminjaman baru dengan data yang sama.\nSelanjut nya diubah hanya tanggal pulang dan jam pulang.\nTerimakasih atas perhatian anda.");
    }
    echo json_encode("Terkirim");
} else {
    echo json_encode("Tidak ada pemohon yang memenuhi kriteria.");
}


// Menutup koneksi
$connKendaraan->close();
