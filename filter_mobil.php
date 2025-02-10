<?php
// Sambungkan ke database
include 'config.php';
date_default_timezone_set("Asia/Jakarta");

// Ambil tanggal dari parameter GET, atau gunakan tanggal saat ini
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d H:i:s');
$tanggal = date('Y-m-d H:i:s', strtotime($tanggal));

// Pastikan koneksi berhasil
if (!$connKendaraan) {
    die(json_encode(['error' => 'Koneksi database gagal: ' . mysqli_connect_error()]));
}

// Query utama untuk mengambil daftar mobil
$query = "
    SELECT 
        t.id, 
        t.type, 
        t.platmobil, 
        t.warna, 
        CASE 
            WHEN EXISTS (
                SELECT 1 
                FROM jadwalmobil j 
                WHERE j.mobil_id = t.id 
                  AND '$tanggal' BETWEEN CONCAT(j.tanggal, ' ', j.jampergi) 
                                      AND CONCAT(j.tanggal_pulang, ' ', j.jampulang)
                LIMIT 1
            ) THEN 'Tidak Tersedia'
            ELSE 'Tersedia'
        END AS status
    FROM typemobil t
";

$result = mysqli_query($connKendaraan, $query);
if (!$result) {
    die(json_encode(['error' => 'Query gagal: ' . mysqli_error($connKendaraan)]));
}

// Inisialisasi array data
$data = [];

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $mobil = [
            'id' => $row['id'],
            'type' => $row['type'],
            'platmobil' => $row['platmobil'],
            'warna' => $row['warna'],
            'status' => $row['status'],
            'pemohon' => null,
            'tujuan' => null
        ];

        if ($row['status'] === 'Tidak Tersedia') {
            $mobil_id = $row['id'];
            $queryJadwal = "
                SELECT 
                    j.tanggal, 
                    j.tanggal_pulang, 
                    j.jampergi, 
                    j.jampulang, 
                    p.tujuan, 
                    p.pemohon, 
                    p.id AS pemohon_id
                FROM jadwalmobil j
                INNER JOIN pemohon p ON j.pemohon_id = p.id
                WHERE j.mobil_id = ?
                  AND ? BETWEEN CONCAT(j.tanggal, ' ', j.jampergi) 
                            AND CONCAT(j.tanggal_pulang, ' ', j.jampulang)
            ";
            $stmtjadwal = $connKendaraan->prepare($queryJadwal);
            $stmtjadwal->bind_param("is", $mobil_id, $tanggal);
            $stmtjadwal->execute();
            $resultJadwal = $stmtjadwal->get_result();

            while ($rowJadwal = $resultJadwal->fetch_assoc()) {
                $mobil['pemohon'] = htmlspecialchars($rowJadwal['pemohon']);
                $mobil['tujuan'] = htmlspecialchars($rowJadwal['tujuan']);
            }
            $stmtjadwal->close();
        }

        $data[] = $mobil;
    }
} else {
    $data[] = ['message' => 'Tidak ada data mobil untuk tanggal ini.'];
}

header('Content-Type: application/json');
echo json_encode($data);
