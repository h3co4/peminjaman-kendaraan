<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id']; // Pastikan ID adalah integer

    // Query pertama: Ambil data dari tabel pemohon
    $stmt = $connKendaraan->prepare("SELECT * FROM pemohon WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Query kedua: Ambil data NPK dari tabel penumpang
    $stmtPenumpang = $connKendaraan->prepare("SELECT npk_penumpang FROM penumpang WHERE pemohon_id = ?");
    $stmtPenumpang->bind_param("i", $id);
    $stmtPenumpang->execute();
    $resultPenumpang = $stmtPenumpang->get_result();

    if ($result->num_rows > 0) {
        $data = array();

        // Ambil data pemohon (hasil dari query pertama)
        $data['pemohon'] = $result->fetch_assoc(); // Simpan data pemohon dalam kunci 'pemohon'

        // Ambil data penumpang (hasil dari query kedua)
        $data['penumpang'] = array(); // Inisialisasi array penumpang
        while ($rowPenumpang = $resultPenumpang->fetch_assoc()) {
            $npk_penumpang = $rowPenumpang['npk_penumpang']; // Ambil NPK penumpang
            $nama_penumpang = getNameFromUsers($conn, $npk_penumpang); // Panggil fungsi untuk mendapatkan nama

            // Simpan NPK dan nama penumpang dalam array
            $data['penumpang'][] = array(
                'npk_penumpang' => $npk_penumpang,
                'nama_penumpang' => $nama_penumpang
            );
        }

        // Ambil nama pengemudi berdasarkan NPK pengemudi dari data pemohon
        $data['namaPengemudi'] = getNameFromUsers($conn, $data['pemohon']['pengemudi']);

        // Kirim data dalam bentuk JSON
        echo json_encode($data);
    } else {
        // Jika tidak ada data, kirim array kosong
        echo json_encode(array());
    }
} else {
    // Jika tidak ada parameter 'id', kirim array kosong
    echo json_encode(array());
}
