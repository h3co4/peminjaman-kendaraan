<?php
session_start();
if (!isset($_SESSION['npk'])) {
    echo json_encode(['status' => 'error', 'message' => 'User belum login']);
    exit();
}

include 'config.php';
header("Content-Typ: application/json");

// Cek koneksi
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed']);
    exit();
}

if (!isset($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}

// Cek koneksi
if ($connKendaraan->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed']);
    exit();
}

if ($_POST['type'] === "requested") {
    $id = intval($_POST['id']);
    $status = intval($_POST['status']);

    $query = "SELECT * FROM pemohon WHERE id = ?";
    $stmtSelectect = $connKendaraan->prepare($query);
    $stmtSelectect->bind_param('i', $id);
    $stmtSelectect->execute();
    $result = $stmtSelectect->get_result();

    // Mengambil data jika ditemukan
    $data = $result->num_rows > 0 ? $result->fetch_assoc() : null;

    // Update status permohonan
    $query = "UPDATE pemohon SET `status` = ? WHERE id = ?";
    $stmt = $connKendaraan->prepare($query);
    $stmt->bind_param('ii', $status, $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Ambil NPK KaDept dan no telepon KaDept
        $npkKaDept = getNPKKaDept($conn, $_SESSION['dept']);
        $noTelpKaDept = getNoHP($connIsd, $npkKaDept);

        $tanggal = date("Y-m-d", strtotime($data['tanggal'])); // Format tanggal untuk keberangkatan
        $tanggal_pulang = date("Y-m-d", strtotime($data['tanggal_pulang'])); // Format tanggal untuk kepulangan
        $jampergi = $data['jampergi']; // Waktu keberangkatan
        $tujuan = $data['tujuan']; // Tujuan keberangkatan
        $keperluan = $data['keperluan']; // Keperluan perjalanan
        $jampulang = $data['jampulang']; // Waktu pulang
        $pengemudi = $data['pengemudi']; // Nama pengemudi

        // Data pemohon untuk notifikasi
        $dataPemohon = $_SESSION['npk'] . " - " . $_SESSION['full_name'];
        $message = "Pemberitahuan Permohonan Peminjaman Kendaraan\nDengan hormat,\n\nKami ingin memberitahukan bahwa ada permohonan peminjaman kendaraan dari $dataPemohon.\n\nBerikut adalah rincian peminjaman kendaraan tersebut:\nTujuan : $tujuan.\nKeperluan : $keperluan.\nKeberangkatan : $tanggal pukul $jampergi.\nWaktu Pulang : $tanggal_pulang pukul $jampulang.\n\nPermohonan ini membutuhkan persetujuan Anda agar dapat diproses lebih lanjut.\nKami mengucapkan Terimakasih atas perhatian dan waktu yang Anda berikan.";

        // Kirim notifikasi push
        insertNotificationPush($connKendaraan, $noTelpKaDept, $message);

        // Kirim respons JSON
        echo json_encode(['status' => 'success', 'message' => 'Status berhasil diUpdate menjadi Requested dan telah mengirim Message WhatsApp ke Kadept.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal meng-update status permohonan.']);
    }
    exit();
    $stmtSelectect->close();
} elseif ($_POST['type'] === "approveKadept") {
    // Mendapatkan ID dari URL
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $typeKendaraan = isset($_POST['typeKendaraan']) ? intval($_POST['typeKendaraan']) : 0;
    $tanggal = isset($_POST['tanggal']) ? date("Y-m-d", strtotime($_POST['tanggal'])) : 0;
    $jampergi = isset($_POST['jampergi']) ? $_POST['jampergi'] : 0;
    $jampulang = isset($_POST['jampulang']) ? $_POST['jampulang'] : 0;
    $tanggal_pulang = isset($_POST['tanggal_pulang']) ? date("Y-m-d", strtotime($_POST['tanggal_pulang'])) : 0;
    $npk = $_SESSION['npk'];

    // Ambil data pengguna berdasarkan NPK
    $query = "SELECT npk, full_name, dept FROM ct_users_hash WHERE npk = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $npk);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $npk = $user['npk'];
        $fullName = $user['full_name'];
        $dept = $user['dept'];

        // Misalkan $role diambil dari session
        $role = $_SESSION['role'];

        if ($role == 'kadep') {
            $updateQuery = "UPDATE pemohon SET status = 2 WHERE id = ?";
        } elseif ($role == 'direksi') {
            $updateQuery = "UPDATE pemohon SET status = 3 WHERE id = ?";
        } elseif ($role == 'ga pool') {
            $updateQuery = "UPDATE pemohon SET status = 4, type = ? WHERE id = ?";

            $stmtjadwal = $connKendaraan->prepare("INSERT INTO jadwalmobil (pemohon_id, mobil_id, tanggal, jampergi, jampulang, tanggal_pulang) VALUES (?, ?, ?, ?, ?, ?)");
        } else {
            // Penanganan untuk role lain jika perlu
            die("Role tidak dikenal.");
        }

        $updateStmt = $connKendaraan->prepare($updateQuery);
        if ($typeKendaraan == 0) {
            $updateStmt->bind_param('i', $id);
        } else {
            $updateStmt->bind_param('ii', $typeKendaraan, $id);

            $stmtjadwal->bind_param("iissss", $id, $typeKendaraan, $tanggal, $jampergi, $jampulang, $tanggal_pulang);
            $stmtjadwal->execute();
            $stmtjadwal->close();
        }
        $updateStmt->execute();

        if ($updateStmt->affected_rows > 0) {
            // Ambil NPK pemohon berdasarkan ID pemohon
            $pemohon = getNPKPemohonByIdPemohon($connKendaraan, $id);
            $pemohonName = getNameFromUsers($conn, $pemohon);

            // Menyusun query dan statement berdasarkan peran pengguna
            if ($role == 'kadep') {
                // Ambil NPK pemohon berdasarkan ID pemohon
                $pemohon = getNPKPemohonByIdPemohon($connKendaraan, $id);

                // Cek apakah pemohon urgent atau tidak
                $isUrgent = getPemohonById($connKendaraan, $id); // Mengambil nilai urgent (1 atau 0)

                if ($isUrgent === null) {
                    echo json_encode(['status' => 'error', 'message' => 'Data urgent tidak ditemukan!']);
                    exit();
                }

                // Definisikan pesan yang akan digunakan untuk log
                $message = '';
                $messagePemohon = ''; // Pesan untuk pemohon

                // Jika pemohon urgent, kirim notifikasi ke Direksi
                if ($isUrgent == 1) {
                    // Mendapatkan NPK dan nomor telepon Direksi
                    $npkDireksi = getNPKByDept($conn, 'BOD');
                    if ($npkDireksi === null) {
                        echo json_encode(['status' => 'error', 'message' => 'NPK Direksi tidak ditemukan!']);
                        exit();
                    }

                    $noTelpDireksi = getNoHP($connIsd, $npkDireksi);

                    // Data pemohon untuk pesan notifikasi
                    $message = "Pemberitahuan Permohonan Peminjaman Kendaraan\nDengan hormat,\n\nKami ingin memberitahukan bahwa ada permohonan peminjaman kendaraan dari $pemohon $pemohonName.\n\nBerikut adalah rincian peminjaman kendaraan tersebut (Urgent):\nKeberangkatan : $tanggal pukul $jampergi.\nWaktu Pulang : $tanggal_pulang pukul $jampulang.\n\nPermohonan ini membutuhkan persetujuan Anda agar dapat diproses lebih lanjut.\nKami mengucapkan Terimakasih atas perhatian dan waktu yang Anda berikan.";

                    // Kirim notifikasi ke Direksi
                    if (!insertNotificationPush($connKendaraan, $noTelpDireksi, $message)) {
                        echo json_encode(['status' => 'error', 'message' => 'Gagal mengirim notifikasi ke Direksi.']);
                        exit();
                    }

                    // Kirim notifikasi ke pemohon bahwa permohonannya sedang diproses
                    $noTelpPemohon = getNoHP($connIsd, $pemohon);
                    $messagePemohon = "Pemberitahuan\nPermohonan peminjaman kendaraan Anda saat ini sedang dalam proses dan memerlukan persetujuan dari pihak Direksi (Urgent).";
                    insertNotificationPush($connKendaraan, $noTelpPemohon, $messagePemohon);
                } else {
                    // Jika pemohon tidak urgent, kirim notifikasi ke GA Pool
                    $npkGa = getNPKByDept($conn, 'GA'); // Menyesuaikan dept GA Pool
                    if ($npkGa === null) {
                        echo json_encode(['status' => 'error', 'message' => 'NPK GA Pool tidak ditemukan!']);
                        exit();
                    }

                    $noTelpGa = getNoHP($connIsd, $npkGa);

                    // Data pemohon untuk pesan notifikasi
                    $message = "Pemberitahuan Permohonan Peminjaman Kendaraan\nDengan hormat,\n\nKami ingin memberitahukan bahwa ada permohonan peminjaman kendaraan dari $pemohon $pemohonName.\n\nBerikut adalah rincian peminjaman kendaraan tersebut:\nKeberangkatan : $tanggal pukul $jampergi.\nWaktu Pulang : $tanggal_pulang pukul $jampulang.\n\nPermohonan ini membutuhkan persetujuan Anda agar dapat diproses lebih lanjut.\nKami mengucapkan Terimakasih atas perhatian dan waktu yang Anda berikan.";

                    // Kirim notifikasi ke GA Pool
                    insertNotificationPush($connKendaraan, $noTelpGa, $message);

                    // Kirim notifikasi ke pemohon bahwa permohonannya sedang diproses
                    $noTelpPemohon = getNoHP($connIsd, $pemohon);
                    $messagePemohon = "Pemberitahuan\nPermohonan peminjaman kendaraan Anda saat ini sedang dalam proses dan memerlukan persetujuan dari pihak GA Pool.";
                    insertNotificationPush($connKendaraan, $noTelpPemohon, $messagePemohon);
                }

                // Kirim respons JSON ke frontend
                echo json_encode(['status' => 'success', 'message' => 'Status berhasil di-update dan telah mengirim Message WhatsApp.']);
            } elseif ($role == 'direksi') {

                // Ambil nomor telepon pemohon
                $noTelpPemohon = getNoHP($connIsd, $pemohon);

                // Cek apakah nomor telepon pemohon ditemukan
                if ($noTelpPemohon) {
                    $messagePemohon = "Pemberitahuan\nPermohonan peminjaman kendaraan Anda telah mendapatkan persetujuan dari Direksi dan saat ini sedang diproses oleh GA Pool.";
                    insertNotificationPush($connKendaraan, $noTelpPemohon, $messagePemohon);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Tidak dapat mengirim ke Pemohon.']);
                    exit();
                }

                // === Tambahkan Logika Notifikasi ke GA ===
                $npkGa = getNPKByDept($conn, 'GA'); // Ambil NPK GA Pool
                if ($npkGa) {
                    $noTelpGa = getNoHP($connIsd, $npkGa); // Ambil nomor telepon GA Pool

                    // Kirim notifikasi ke GA Pool
                    if ($noTelpGa) {
                        $messageGa = "Pemberitahuan Permohonan Peminjaman Kendaraan\nDengan hormat,\n\nKami ingin memberitahukan bahwa ada permohonan peminjaman kendaraan dari $pemohon $pemohonName.\n\nBerikut adalah rincian peminjaman kendaraan tersebut (Urgent):\nKeberangkatan : $tanggal pukul $jampergi.\nWaktu Pulang : $tanggal_pulang pukul $jampulang.\n\nPermohonan ini membutuhkan persetujuan Anda agar dapat diproses lebih lanjut.\nKami mengucapkan Terimakasih atas perhatian dan waktu yang Anda berikan.";
                        insertNotificationPush($connKendaraan, $noTelpGa, $messageGa);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Tidak dapat mengirim ke GA Pool.']);
                    exit();
                }

                // Kirim respons JSON ke frontend
                echo json_encode(['status' => 'success', 'message' => 'Status berhasil di-update dan telah mengirim Message WhatsApp ke GA-Pool dan Pemohon.']);
            } elseif ($role == 'ga pool') {
                $noTelpPemohon = getNoHP($connIsd, $pemohon); // Asumsikan $id adalah pemohon_id yang digunakan untuk mencari nomor telepon pemohon
                $mobilData = getMobilData($typeKendaraan, $connKendaraan);

                // Cek apakah nomor telepon pemohon ditemukan
                if ($noTelpPemohon) {
                    // Format pesan untuk pemohon
                    $messagePemohon = "Pemberitahuan\n\nDengan hormat,\nKami ingin memberitahukan bahwa persetujuan Anda untuk peminjaman kendaraan telah disetujui oleh {$_SESSION['npk']} - {$_SESSION['full_name']}.\nMobil yang akan digunakan untuk perjalanan Anda adalah $mobilData.\n\nKami mengharapkan perjalanan yang lancar dan aman.\nJangan lupa untuk berdoa terlebih dahulu dan Selamat sampai tujuan.";

                    // Kirim notifikasi ke pemohon
                    insertNotificationPush($connKendaraan, $noTelpPemohon, $messagePemohon);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Tidak dapat mengirim ke Pemohon.']);
                    exit();
                }

                echo json_encode(['status' => 'success', 'message' => 'Status berhasil di-update dan telah mengirim Message WhatsApp ke Pemohon.']);
            } else {
                die("Role tidak dikenali.");
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan log persetujuan.']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal meng-update status permohonan.']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Data pengguna tidak ditemukan.']);
    exit();
}


// Tutup koneksi setelah selesai
$conn->close();
$connKendaraan->close();
