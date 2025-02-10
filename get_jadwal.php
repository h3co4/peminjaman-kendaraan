<?php
include 'config.php'; // Sesuaikan dengan nama file koneksi Anda

header("Content-Type: application/json");

if (isset($_GET['mobil_id'])) {
    $mobil_id = intval($_GET['mobil_id']);

    // Query untuk mengambil data jadwal berdasarkan mobil_id
    $query = "SELECT j.tanggal, j.tanggal_pulang, j.jampergi, j.jampulang, p.tujuan, p.pemohon, p.id AS pemohon_id
              FROM jadwalmobil j
              INNER JOIN pemohon p ON j.pemohon_id = p.id
              WHERE j.mobil_id = ?";

    $stmtjadwal = $connKendaraan->prepare($query);
    $stmtjadwal->bind_param("i", $mobil_id);
    $stmtjadwal->execute();
    $result = $stmtjadwal->get_result();

    $data = array();
    while ($row = $result->fetch_assoc()) {
        // Tambahkan data jadwal ke array
        $jadwal = [
            'tanggal' => $row['tanggal'],
            'tanggal_pulang' => $row['tanggal_pulang'],
            'jampergi' => $row['jampergi'],
            'jampulang' => $row['jampulang'],
            'tujuan' => $row['tujuan'],
            'pemohon' => $row['pemohon'],
            'pemohonName' => getNameFromUsers($conn, $row['pemohon']),
            'penumpang' => [] // Placeholder untuk penumpang
        ];

        // Query untuk mendapatkan data penumpang berdasarkan pemohon_id
        $queryPenumpang = "SELECT npk_penumpang FROM penumpang WHERE pemohon_id = ?";
        $stmtPenumpang = $connKendaraan->prepare($queryPenumpang);
        $stmtPenumpang->bind_param("i", $row['pemohon_id']);
        $stmtPenumpang->execute();
        $resultPenumpang = $stmtPenumpang->get_result();

        while ($rowPenumpang = $resultPenumpang->fetch_assoc()) {
            $jadwal['penumpang'][] = $rowPenumpang['npk_penumpang'] . " - " . getNameFromUsers($conn, $rowPenumpang['npk_penumpang']); // Tambahkan penumpang ke jadwal
        }

        $stmtPenumpang->close();

        // Tambahkan jadwal ke data utama
        $data[] = $jadwal;
    }

    $stmtjadwal->close();

    // Jika tidak ada data, kembalikan pesan
    if (empty($data)) {
        $data = ['message' => 'Tidak ada data ditemukan'];
    }

    echo json_encode($data);
} else {
    // Mengembalikan pesan error jika mobil_id tidak disediakan
    echo json_encode(['error' => 'Parameter mobil_id tidak disediakan']);
}

// Tutup koneksi ke database
$connKendaraan->close();
