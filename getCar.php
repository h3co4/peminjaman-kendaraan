<?php
// get_mobil_data.php
include('config.php'); // Pastikan koneksi sudah terhubung

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $connKendaraan->query("SELECT * FROM typemobil WHERE id = $id");
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode($data); // Mengembalikan data dalam format JSON
    } else {
        echo json_encode(['error' => 'Data tidak ditemukan']);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $type = $_POST['type'];
    $warna = $_POST['warna'];
    $platmobil = $_POST['platmobil'];

    $query = "UPDATE typemobil SET type = ?, warna = ?, platmobil = ? WHERE id = ?";
    $stmt = $connKendaraan->prepare($query);
    $stmt->bind_param("sssi", $type, $warna, $platmobil, $id);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
}
