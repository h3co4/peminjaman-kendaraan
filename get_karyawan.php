<?php
// Menghubungkan ke database
include 'config.php'; // Sesuaikan dengan nama file koneksi database Anda

$sql = "SELECT npk, full_name FROM ct_users_hash";
$resultPengemudi = mysqli_query($conn, $sql);

$pengemudiList = array();

if ($resultPengemudi->num_rows > 0) {
    while ($row = $resultPengemudi->fetch_assoc()) {
        if (!empty($row['npk']) && !is_null($row['full_name'])) {
            $pengemudiList[] = [
                'npk' => $row['npk'],
                'full_name' => $row['full_name'],
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($pengemudiList);
