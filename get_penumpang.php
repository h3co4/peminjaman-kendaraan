<?php
// Koneksi ke database
$conn = new mysqli('localhost', 'root', '', 'nama_database');

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query untuk mengambil npk dan full_name
$query = "SELECT npk, full_name FROM ct_users_hash ORDER BY full_name ASC";
$result = $conn->query($query);

$penumpang = [];

if ($result->num_rows > 0) {
    // Simpan hasil dalam array
    while ($row = $result->fetch_assoc()) {
        $penumpang[] = $row;
    }
}

// Kembalikan data dalam format JSON
echo json_encode($penumpang);

$conn->close();
