<?php
session_start();
require 'config.php';  // Pastikan file ini terhubung dengan benar

header('Content-Type: application/json');

if (isset($_POST['pemohon_id'])) {
    $pemohon_id = $_POST['pemohon_id'];

    // Validasi hak akses, misalnya hanya role tertentu yang bisa menghapus
    $allowedRoles = array('karyawan');
    if (in_array($_SESSION['role'], $allowedRoles)) {

        // Debug input: Pastikan data tidak kosong
        if (empty($pemohon_id)) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
            exit;
        }

        // Sanitasi input dan pastikan hanya integer yang diizinkan
        $idToDelete = intval($pemohon_id);

        // Query untuk menghapus data dari tabel `penumpang` terlebih dahulu
        $stmtPenumpang = $connKendaraan->prepare("DELETE FROM penumpang WHERE pemohon_id=?");
        if ($stmtPenumpang) {
            $stmtPenumpang->bind_param("i", $idToDelete);
            if ($stmtPenumpang->execute()) {
                $stmtPenumpang->close();

                // Jika penghapusan dari tabel `penumpang` berhasil, lanjutkan ke tabel `pemohon`
                $stmtPemohon = $connKendaraan->prepare("DELETE FROM pemohon WHERE id=?");
                if ($stmtPemohon) {
                    $stmtPemohon->bind_param("i", $idToDelete);
                    if ($stmtPemohon->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Query gagal pada pemohon: ' . $stmtPemohon->error]);
                    }
                    $stmtPemohon->close();
                } else {
                    echo json_encode(['success' => false, 'message' => 'Prepare statement gagal untuk pemohon: ' . $connKendaraan->error]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Query gagal pada penumpang: ' . $stmtPenumpang->error]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Prepare statement gagal untuk penumpang: ' . $connKendaraan->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Anda tidak memiliki akses untuk menghapus data ini.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
}

// Tutup koneksi database
$connKendaraan->close();
