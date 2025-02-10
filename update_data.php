<?php
include 'config.php';

header('Content-Type: application/json'); // Set header untuk JSON response

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi input
    if (!isset($_POST['id'], $_POST['tanggal'], $_POST['tanggal_pulang'], $_POST['jampergi'], $_POST['jampulang'], $_POST['tujuan'], $_POST['keperluan'], $_POST['kendaraan'], $_POST['pengemudi'])) {
        // Kirim response error dalam format JSON
        echo json_encode([
            'success' => false,
            'message' => 'Data tidak lengkap. Pastikan semua kolom terisi.'
        ]);
        exit();
    }

    // Debug untuk melihat data POST (Opsional)
    // var_dump($_POST); exit();

    // Sanitasi input jika diperlukan
    $id = (int) $_POST['id'];
    $tanggal = htmlspecialchars(date("Y-m-d", strtotime($_POST['tanggal'])));
    $tanggal_pulang = htmlspecialchars(date("Y-m-d", strtotime($_POST['tanggal_pulang'])));
    $jampergi = htmlspecialchars($_POST['jampergi']);
    $jampulang = htmlspecialchars($_POST['jampulang']);
    $tujuan = htmlspecialchars($_POST['tujuan']);
    $keperluan = htmlspecialchars($_POST['keperluan']);
    $kendaraan = htmlspecialchars($_POST['kendaraan']);
    $pengemudi = htmlspecialchars($_POST['pengemudi']);
    $urgent = htmlspecialchars($_POST['edit-urgent']);

    // Query update
    $stmt = $connKendaraan->prepare("UPDATE pemohon SET tanggal=?, tanggal_pulang=?, jampergi=?, jampulang=?, tujuan=?, keperluan=?, kendaraan=?, pengemudi=?, urgent=? WHERE id=?");
    $stmt->bind_param("ssssssssis", $tanggal, $tanggal_pulang, $jampergi, $jampulang, $tujuan, $keperluan, $kendaraan, $pengemudi, $urgent, $id);

    if (isset($_POST['penumpang']) && !empty($_POST['penumpang'])) {
        foreach ($_POST['penumpang'] as $npkPenumpang) {
            $sqlPenumpang = "INSERT INTO penumpang (pemohon_id, npk_penumpang) 
                                 VALUES ('$id', '$npkPenumpang')";
            if (!mysqli_query($connKendaraan, $sqlPenumpang)) {
                echo "Error: " . mysqli_error($connKendaraan);
            }
        }
    }

    if (!$connKendaraan) {
        echo json_encode(array(
            'status' => 'success',
            'message' => 'Koneksi ke database gagal'
        ));
        exit();
    }
    // Eksekusi query
    if ($stmt->execute()) {
        // Jika berhasil, kirim response sukses dalam JSON
        echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil diperbarui'
        ]);
    } else {
        // Jika gagal, kirim response error dalam JSON
        echo json_encode([
            'status' => 'fail',
            'message' => 'Gagal memperbarui data: ' . $stmt->error
        ]);
    }

    $stmt->close();
}
