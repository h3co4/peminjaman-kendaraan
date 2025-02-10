<?php
// Fungsi untuk membuat koneksi database
function createConnection($servername, $username, $password, $dbname)
{
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Koneksi ke database $dbname gagal: " . $conn->connect_error);
    }
    return $conn;
}

// Informasi koneksi database
$servername = "localhost";
$username = "root";
$password = "";
$dbnameLembur = "lembur";
$dbnameKendaraan = "kendaraan";
$dbIsd = "isd";

// Mencegah akses langsung ke file ini
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die("Akses langsung ke file ini tidak diperbolehkan.");
}


// Koneksi ke database lembur dan kendaraan
$conn = createConnection($servername, $username, $password, $dbnameLembur);
$connKendaraan = createConnection($servername, $username, $password, $dbnameKendaraan);
$connIsd = createConnection($servername, $username, $password, $dbIsd);

function getNameFromUsers($conn, $npk)
{
    $sql = "SELECT full_name FROM ct_users_hash WHERE npk = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $npk);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['full_name']; // Kembalikan nama pengguna
        } else {
            return "Nama tidak ditemukan untuk NPK: " . htmlspecialchars($npk);
        }
    } else {
        return "Terjadi kesalahan pada query: " . htmlspecialchars($conn->error);
    }
}

/// Fungsi untuk mendapatkan NPK berdasarkan dept
function getNPKKaDept($conn, $dept)
{
    $sql = "SELECT cuh.npk FROM ct_users_hash cuh
            JOIN hrd_so hso ON cuh.npk = hso.npk
            WHERE cuh.dept = ?
            AND hso.tipe = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $dept);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['npk']; // Langsung mengembalikan nilai NPK sebagai string
    } else {
        return null; // Kembalikan null jika tidak ada data ditemukan
    }
}

function getNPKByDept($conn, $dept)
{
    $sql = "SELECT npk FROM ct_users_hash
            WHERE dept = ?;";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $dept);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['npk']; // Langsung mengembalikan nilai NPK sebagai string
    } else {
        return null; // Kembalikan null jika tidak ada data ditemukan
    }
}

function getNPKPemohonByIdPemohon($contol, $id)
{
    $sql = "SELECT pemohon FROM pemohon WHERE id = ?";

    // Cek apakah query berhasil dipersiapkan
    $stmt = $contol->prepare($sql);
    if ($stmt === false) {
        die('Error preparing the query: ' . $contol->error); // Menampilkan kesalahan jika query gagal
    }

    // Bind parameter dan eksekusi query
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Ambil hasil
    $result = $stmt->get_result();

    // Periksa apakah ada hasil yang ditemukan
    if ($row = $result->fetch_assoc()) {
        return $row['pemohon']; // Mengembalikan nilai NPK pemohon
    } else {
        return null; // Jika tidak ditemukan
    }
}


function getPemohonById($conn, $id)
{
    $sql = "SELECT urgent FROM pemohon WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id); // Binding parameter untuk id
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['urgent']; // Mengembalikan nilai urgent (1 atau 0)
    } else {
        return null; // Mengembalikan null jika tidak ada data ditemukan
    }
}

// Fungsi untuk mendapatkan no_hp berdasarkan npk
function getNoHP($connIsd, $npk)
{
    $sql = "SELECT no_hp FROM hp WHERE npk = ?";
    $stmt = $connIsd->prepare($sql);
    $stmt->bind_param("s", $npk);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['no_hp']; // Langsung mengembalikan nilai NPK sebagai string
    } else {
        return null; // Kembalikan null jika tidak ada data ditemukan
    }
}

// Fungsi untuk memperbarui pesan di notification_push
function insertNotificationPush($connKendaraan, $no_hp, $message)
{
    if (empty($no_hp) || empty($message)) {
        error_log("No phone number or message is empty");
        return false; // Tidak lanjut jika data tidak lengkap
    }

    $sql = "INSERT INTO `notification_push`(`phone_number`, `message`, `flags`, `insert_date`) VALUES (?,?, 'queue', NOW())";
    $stmt = $connKendaraan->prepare($sql);
    if ($stmt === false) {
        error_log('Prepare statement failed: ' . $connKendaraan->error);
        return false;  // Jika gagal prepare statement
    }
    $stmt->bind_param("ss", $no_hp, $message);
    return $stmt->execute();
}

function getMobilData($idMobil, $conn)
{
    $sql = "SELECT type, platmobil, warna FROM typemobil WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idMobil); // Binding parameter untuk id
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['type'] . " - " . $row['platmobil'] . " - " .  $row['warna']; // Mengembalikan nilai urgent (1 atau 0)
    } else {
        return null; // Mengembalikan null jika tidak ada data ditemukan
    }
}

function checkPemohonIdAtRemark($id, $conn)
{
    $sql = "SELECT COUNT(id) AS total FROM approval_pemohon WHERE pemohon_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id); // Binding parameter untuk id
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['total'] > 0; // Mengembalikan true jika ada data
    } else {
        return false; // Mengembalikan false jika tidak ada data
    }
}
