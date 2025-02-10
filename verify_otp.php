<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $npk = $_POST['npk'];
    $otp_input = $_POST['otp'];

    // Mencari OTP di database
    $stmt = $connKendaraan->prepare("SELECT * FROM otp_authentication WHERE npk = ? AND otp = ? AND `expiry_date` > NOW() AND `use` = 2");
    $stmt->bind_param("ss", $npk, $otp_input);
    $stmt->execute();

    if ($stmt->error) {
        die("Error executing statement: " . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $stmtUser = $conn->prepare("SELECT * FROM ct_users_hash WHERE npk = ?");
        $stmtUser->bind_param("s", $npk);
        $stmtUser->execute();
        $resultUser = $stmtUser->get_result();
        $rowUser = $resultUser->fetch_assoc();

        // Set session variables
        $_SESSION['npk'] = $rowUser['npk'];
        $_SESSION['full_name'] = $rowUser['full_name'];
        $_SESSION['golongan'] = $rowUser['golongan'];
        $_SESSION['dept'] = $rowUser['dept'];

        $sql_hrd = "SELECT * FROM hrd_so WHERE npk='$npk'";
        $result_hrd = mysqli_query($conn, $sql_hrd);

        $tipe = ''; // Initialize $tipe variable
        if ($hrd_row = mysqli_fetch_assoc($result_hrd)) {
            $tipe = $hrd_row['tipe']; // Assign value to $tipe
            $_SESSION["tipe"] = $hrd_row["tipe"];

            if ($tipe == '3') {
                $_SESSION['role'] = "Kepala Divisi";
                $redirect_url = "FormMutasi/dashboard.php";
                $_SESSION["redirect_url"] = $redirect_url;
            }
        }

        if ($tipe == 1 &&  $_SESSION['dept'] != 'GA') {
            $_SESSION['role'] = "kadep";
            $redirect_url = "dashboard.php";
        } elseif ($rowUser['golongan'] == 3 && $rowUser['acting'] == 2 &&  $_SESSION['dept'] == 'GA') {
            $_SESSION['role'] = "ga pool";
            $redirect_url = "dashboard.php";
        } elseif ($rowUser['golongan'] == 4 && $rowUser['acting'] == 2 &&  $_SESSION['dept'] == 'GA') {
            $_SESSION['role'] = "ga pool";
            $redirect_url = "dashboard.php";
        } elseif ($_SESSION['dept'] == 'BOD') {
            $_SESSION['role'] = "direksi";
            $redirect_url = "dashboard.php";
        } elseif ($rowUser['golongan'] <= 2) {
            $_SESSION['role'] = "karyawan";
            $redirect_url = "dashboard.php";
        }

        // Role determination logic...
        $redirect_url = "dashboard.php"; // Default redirect URL

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();  // Ambil baris data OTP yang valid

            // Update OTP usage status
            $stmt_update = $connKendaraan->prepare("UPDATE otp_authentication SET `use` = 1, `use_date` = NOW() WHERE id = ?");
            $stmt_update->bind_param("i", $row['id']);
            $stmt_update->execute();

            // Set session and proceed with user roles...
        }


        // Send success response with redirect URL
        echo json_encode([
            'status' => 'success',
            'message' => 'OTP Valid. Selamat datang di halaman Anda!',
            'redirect_url' => isset($redirect_url) ? $_SESSION["redirect_url"] = $redirect_url : ''
        ]);
    } else {
        // OTP invalid or expired
        echo json_encode([
            'status' => 'error',
            'message' => 'OTP tidak Valid atau telah kedaluwarsa.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request.'
    ]);
}
