<?php
// Koneksi ke database
include 'config.php';
session_start(); // Mulai session untuk mengambil data pengguna yang login

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari AJAX
    $id = $_POST['id']; // ID Pemohon
    $alasan = $_POST['alasan']; // Alasan Penolakan

    // Pastikan npk diambil dari sesi pengguna yang login
    $npk = $_SESSION['npk'];
    $role = $_SESSION['role'];

    // Memulai transaksi
    $connKendaraan->begin_transaction();

    // Query untuk insert atau update sesuai dengan role
    if ($_SESSION['role'] == "kadep") {
        $queryInsert = "INSERT INTO approval_pemohon (pemohon_id, npk_kadept, remark_kadept, approval_date)
                        VALUES (?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE
                        npk_kadept = VALUES(npk_kadept), remark_kadept = VALUES(remark_kadept), approval_date = NOW()";
        $stmt = $connKendaraan->prepare($queryInsert);
        $stmt->bind_param("iss", $pemohon_id, $npk, $remark);
    } else if ($_SESSION['role'] == "ga pool") {
        $queryInsert = "INSERT INTO approval_pemohon (pemohon_id, npk_ga, remark_ga, date_ga)
                        VALUES (?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE
                        npk_ga = VALUES(npk_ga), remark_ga = VALUES(remark_ga), date_ga = NOW()";
        $stmt = $connKendaraan->prepare($queryInsert);
        $stmt->bind_param("iss", $pemohon_id, $npk, $remark);
    } else if ($_SESSION['role'] == "direksi") {
        $queryInsert = "INSERT INTO approval_pemohon (pemohon_id, npk_direksi, remark_direksi, date_direksi)
                        VALUES (?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE
                        npk_direksi = VALUES(npk_direksi), remark_direksi = VALUES(remark_direksi), date_direksi = NOW()";
        $stmt = $connKendaraan->prepare($queryInsert);
        $stmt->bind_param("iss", $pemohon_id, $npk, $remark);
    }

    // Query untuk mengupdate status di tabel pemohon
    $queryUpdate = "UPDATE pemohon SET status = 5 WHERE id = ?";

    // Persiapan statement untuk INSERT ke approval_pemohon
    if ($stmtInsert = $connKendaraan->prepare($queryInsert)) {
        // Bind parameter
        if (strpos($queryInsert, "INSERT") !== false) {
            $stmtInsert->bind_param('iss', $id, $npk, $alasan);
        } else if (strpos($queryInsert, "UPDATE") !== false) {
            $stmtInsert->bind_param('ssi', $npk, $alasan, $id);
        }

        // Eksekusi statement INSERT
        if ($stmtInsert->execute()) {
            // Persiapan statement untuk UPDATE status di tabel pemohon
            if ($stmtUpdate = $connKendaraan->prepare($queryUpdate)) {
                // Bind parameter
                $stmtUpdate->bind_param('i', $id);

                // Eksekusi statement UPDATE
                if ($stmtUpdate->execute()) {
                    // Jika berhasil, commit transaksi
                    $connKendaraan->commit();

                    // Mencari NPK Pemohon berdasarkan ID
                    $npkPemohon = getNPKPemohonByIdPemohon($connKendaraan, $id);

                    if ($npkPemohon) {
                        // Mencari nomor HP berdasarkan NPK pemohon
                        $noTelpPemohon = getNoHP($connIsd, $npkPemohon);

                        if ($noTelpPemohon) {
                            // Menyusun pesan notifikasi
                            $dataPemohon = $_SESSION['npk'] . " - " . $_SESSION['full_name'];
                            $message = "Permintaan persetujuan anda Ditolak dari " . $dataPemohon . ". Perbarui data permohonan anda.";

                            // Mengirimkan notifikasi
                            $insertResult = insertNotificationPush($connKendaraan, $noTelpPemohon, $message);

                            // Cek apakah notifikasi berhasil dikirim
                            if ($insertResult) {
                                echo json_encode(['status' => 'success', 'message' => 'Notifikasi berhasil dikirim.']);
                            } else {
                                echo json_encode(['status' => 'error', 'message' => 'Gagal mengirim notifikasi.']);
                                exit();
                            }
                        } else {
                            echo json_encode(['status' => 'error', 'message' => 'Nomor HP pemohon tidak ditemukan.']);
                            exit();
                        }
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'NPK pemohon tidak ditemukan.']);
                        exit();
                    }

                    // Jika gagal eksekusi UPDATE
                    $response = [
                        'status' => 'error',
                        'message' => 'Gagal memperbarui status: ' . $stmtUpdate->error
                    ];
                    // Rollback jika gagal
                    $connKendaraan->rollback();
                }
                // Tutup statement UPDATE
                $stmtUpdate->close();
                exit();
            } else {
                // Jika gagal prepare statement UPDATE
                $response = [
                    'status' => 'error',
                    'message' => 'Gagal menyiapkan query UPDATE: ' . $connKendaraan->error
                ];
                // Rollback jika gagal
                $connKendaraan->rollback();
                exit();
            }
        } else {
            // Jika gagal eksekusi INSERT
            $response = [
                'status' => 'error',
                'message' => 'Gagal menyimpan data: ' . $stmtInsert->error
            ];
            // Rollback jika gagal
            $connKendaraan->rollback();
        }
        // Tutup statement INSERT
        $stmtInsert->close();
        exit();
    } else {
        // Jika gagal prepare statement INSERT
        $response = [
            'status' => 'error',
            'message' => 'Gagal menyiapkan query INSERT: ' . $connKendaraan->error
        ];
    }

    // Tutup koneksi
    $connKendaraan->close();

    // Kirim response dalam bentuk JSON
    echo json_encode($response);
}
