<?php
session_start();

if (!isset($_SESSION['npk']) || $_SESSION['role'] !== "karyawan") {
    header('Location: /kendaraan/index.php'); // Mengarahkan ke halaman login
    exit(); // Hentikan eksekusi
}

include 'config.php';
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form GA Department</title>
    <link href="public/img/kyb-icon.ico" rel="icon">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="public/bootstrap/css/bootstrap.min.css">

    <!-- Bootstrap-select CSS -->
    <link rel="stylesheet" href="public/library/bootstrap-select/dist/css/bootstrap-select.min.css">

    <!-- jQuery UI CSS (optional) -->
    <link rel="stylesheet" href="jquery-ui/jquery-ui.min.css">

    <!-- Link CSS untuk SweetAlert2 -->
    <link rel="stylesheet" href="public/library/sweetalert2/dist/sweetalert2.min.css">

    <!-- JS SweetAlert2 -->
    <script src="public/library/sweetalert2/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="public/library/flatpicker/flatpicker.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary mb-4 sticky-top">
        <?php include_once 'public/header.php'; ?>
    </nav>

    <div class="d-flex justify-content-center align-items-center min-vh-100">
        <div class="container p-4 shadow-lg rounded bg-white" style="max-width: 600px;">
            <h3 class="mb-4 text-center">Form Permohonan Kendaraan</h3>

            <!-- Form Container -->
            <form method="POST">

                <!-- Tanggal -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="tanggal" class="form-label">Tanggal Berangkat</label>
                        <input type="text" class="form-control" name="tanggal" id="tanggal" placeholder="Pilih Tanggal" required autocomplete="off">
                    </div>
                    <div class="col-md-6">
                        <label for="tanggal_pulang" class="form-label">Tanggal Pulang</label>
                        <input type="text" class="form-control" name="tanggal_pulang" id="tanggal_pulang" placeholder="Pilih Tanggal" required autocomplete="off">
                    </div>
                </div>

                <!-- Jam Pergi dan Pulang -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="jamPergi" class="form-label">Jam Pergi</label>
                        <input type="time" class="form-control without_ampm" name="jampergi" id="jamPergi" required autocomplete="off" placeholder="00:00">
                    </div>
                    <div class="col-md-6">
                        <label for="jamPulang" class="form-label">Jam Pulang</label>
                        <input type="time" class="form-control without_ampm" name="jampulang" id="jamPulang" required autocomplete="off" placeholder="00:00" maxlength="2">
                    </div>
                </div>

                <!-- Tujuan -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="tujuan" class="form-label">Tujuan</label>
                        <input type="text" class="form-control" name="tujuan" id="tujuan" placeholder="Masukkan Tujuan" required autocomplete="off" maxlength="50">
                    </div>

                    <!-- Keperluan -->
                    <div class="col-md-6">
                        <label for="keperluan" class="form-label">Keperluan</label>
                        <input type="text" class="form-control" maxlength="55" name="keperluan" id="keperluan" placeholder="Masukkan Keperluan" required autocomplete="off">
                    </div>
                </div>

                <!-- Kendaraan dan Jenis Kendaraan -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="kendaraan" class="form-label">Kendaraan</label>
                        <select class="form-control selectpicker" name="kendaraan" id="kendaraan" required>
                            <option value="" disabled selected>Pilih Kendaraan</option>
                            <option value="Avanza Reborn">Avanza Reborn</option>
                            <option value="Inova Zenix">Inova Zenix</option>
                            <option value="Suzuki Swift">Suzuki Swift</option>
                            <option value="Yaris Cros">Yaris Cros</option>
                            <option value="Veloz Byon">Veloz Byon</option>
                            <option value="Jeep 4X4">Jeep 4X4</option>
                            <option value="Agya GR SS">Agya GR SS</option>
                            <option value="Mini Buss">Mini Buss</option>
                            <option value="Mazda RX7">Mazda RX7</option>
                            <option value="Motor">Motor</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="urgent" class="form-label">(Jika urgent)</label>
                        <select class="form-control selectpicker" name="urgent" id="urgent" required>
                            <option value="0">Tidak</option>
                            <option value="1">Urgent</option>
                        </select>
                    </div>
                </div>

                <!-- Penumpang -->
                <div class="mb-3">
                    <label for="penumpang" class="form-label">Penumpang</label>
                    <br>
                    <select class="form-control selectpicker" id="penumpang" name="penumpang[]" data-live-search="true" multiple>
                        <option value="" disabled>Pilih Penumpang (bisa pilih lebih dari satu)</option>
                        <?php
                        $sqlPenumpang = "SELECT npk, full_name FROM ct_users_hash";
                        $resultPenumpang = mysqli_query($conn, $sqlPenumpang);
                        if ($resultPenumpang && $resultPenumpang->num_rows > 0) {
                            while ($row = $resultPenumpang->fetch_assoc()) {
                                echo "<option value='" . $row['npk'] . "'>" . $row['npk'] . " - "  . $row['full_name'] . "</option>";
                            }
                        } else {
                            echo "<option disabled>Data penumpang tidak ditemukan</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Pengemudi -->
                <div class="mb-3">
                    <label for="pengemudi" class="form-label">Pengemudi</label>
                    <select class="form-control selectpicker" name="pengemudi" id="pengemudi" data-live-search="true" required>
                        <option value="" disabled selected>Pilih Pengemudi</option>
                        <?php
                        $sqlPengemudi = "SELECT npk, full_name FROM ct_users_hash";
                        $resultPengemudi = mysqli_query($conn, $sqlPengemudi);
                        if ($resultPengemudi && $resultPengemudi->num_rows > 0) {
                            while ($row = $resultPengemudi->fetch_assoc()) {
                                echo "<option value='" . $row['npk'] . "'>" . $row['npk'] . " - "  . $row['full_name'] . "</option>";
                            }
                        } else {
                            echo "<option disabled>Data pengemudi tidak ditemukan</option>";
                        }
                        ?>
                    </select>
                </div>

                <button type="submit" name="formpemohon" class="btn btn-primary">Ajukan Permohonan</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="public/library/jquery/dist/jquery.min.js"></script>

    <script src="public/library/flatpicker/flatpicker.min.js"></script>

    <script src="public/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Bootstrap-select JS -->
    <script src="public/library/bootstrap-select/dist/js/bootstrap-select.min.js"></script>

    <!-- jQuery UI JS -->
    <script src="jquery-ui/jquery-ui.min.js"></script>

    <!-- Datepicker -->
    <script>
        function validateDates() {
            let tanggalBiasa = $("#tanggal").datepicker("getDate");
            let tanggalPulang = $("#tanggal_pulang").datepicker("getDate");

            if (tanggalBiasa && tanggalPulang) {
                if (tanggalBiasa > tanggalPulang) {
                    Swal.fire({
                        icon: "error",
                        title: "Tanggal Tidak Valid",
                        text: "Tanggal biasa tidak boleh lebih dari tanggal pulang!",
                    });
                    $("#tanggal").datepicker("setDate", null);
                } else if (tanggalPulang < tanggalBiasa) {
                    Swal.fire({
                        icon: "error",
                        title: "Tanggal Tidak Valid",
                        text: "Tanggal pulang tidak boleh lebih dari tanggal biasa!",
                    });
                    $("#tanggal_pulang").datepicker("setDate", null);
                }
            }
        }

        $(document).ready(function() {
            $("#tanggal").datepicker({
                dateFormat: "dd-mm-yy",
                minDate: 0,
                onSelect: function(selectedDate) {
                    $("#tanggal_pulang").datepicker("option", "minDate", selectedDate);
                }
            });

            $(".selectpicker").selectpicker();
        });

        $(document).ready(function() {
            $("#tanggal_pulang").datepicker({
                dateFormat: "dd-mm-yy",
                minDate: 0,
                onSelect: function(selectedDate) {
                    $("#tanggal").datepicker("option", "maxDate", selectedDate);
                }

            });

            $(".selectpicker").selectpicker();
        });

        $("#jamPulang").flatpickr({
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true
        });

        $("#jamPergi").flatpickr({
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true
        });
    </script>

    <script>
        document.getElementById('logoutBtn').addEventListener('click', function(event) {
            event.preventDefault(); // Mencegah default action
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: 'Apakah Anda yakin ingin keluar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, keluar!',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika pengguna menekan "Ya, keluar!", arahkan ke logout.php
                    window.location.href = 'logout.php';
                }
            });
        });
    </script>

    <script>
        function toggleUrgent() {
            const button = document.getElementById('jikaurgent');

            // Check the button's status
            if (button.classList.contains('btn-outline-primary')) {
                button.classList.remove('btn-outline-primary');
                button.classList.add('btn-success');
                button.textContent = 'Urgent'; // Change the text to 'On'

                // Show a success message using SweetAlert
                Swal.fire({
                    icon: 'success',
                    title: 'Urgent Status Changed',
                    text: 'The urgent status has been turned on.'
                });
            } else {
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-primary');
                button.textContent = 'Tidak'; // Change the text to 'Off'

                // Show a warning message using SweetAlert
                Swal.fire({
                    icon: 'warning',
                    title: 'Urgent Status Changed',
                    text: 'The urgent status has been turned off.'
                });
            }
        }
    </script>

</body>

</html>

<?php
if (isset($_POST['formpemohon'])) {
    // Mengambil data dari form
    $tanggal = isset($_POST['tanggal']) ? $_POST['tanggal'] : '';
    $tanggal_pulang = isset($_POST['tanggal_pulang']) ? $_POST['tanggal_pulang'] : '';
    $jampergi = isset($_POST['jampergi']) ? $_POST['jampergi'] : '';
    $jampulang = isset($_POST['jampulang']) ? $_POST['jampulang'] : '';
    $tujuan = isset($_POST['tujuan']) ? $_POST['tujuan'] : '';
    $keperluan = isset($_POST['keperluan']) ? $_POST['keperluan'] : '';
    $kendaraan = isset($_POST['kendaraan']) ? $_POST['kendaraan'] : '';
    $penumpang = isset($_POST['penumpang']) ? $_POST['penumpang'] : [];
    $pengemudi = isset($_POST['pengemudi']) ? $_POST['pengemudi'] : '';
    $pemohon = $_SESSION['npk'];
    $dept_pemohon = $_SESSION['dept'];
    $status = 0; // Status permohonan
    date_default_timezone_set('Asia/Jakarta');
    $tanggal_dibuat = date("Y-m-d H:i:s");
    $urgent = isset($_POST['urgent']) ? $_POST['urgent'] : 0;

    // Validasi input
    if (empty($tanggal) || empty($tanggal_pulang) || empty($jampergi) || empty($jampulang) || empty($tujuan) || empty($keperluan) || empty($kendaraan) || empty($pengemudi)) {
        echo "<script>Swal.fire('Oops!', 'Semua kolom harus diisi!', 'error');</script>";
    } elseif ($tanggal > $tanggal_pulang || $tanggal_pulang < $tanggal) {
        echo "<script>Swal.fire('Oops!', 'Tanggal tidak valid!', 'error');()</script>";
    } else {
        // Konversi tanggal menjadi format Y-m-d
        $tanggal = date('Y-m-d', strtotime($tanggal));
        $tanggal_pulang = date('Y-m-d', strtotime($tanggal_pulang));

        // Persiapkan query untuk memasukkan data
        $stmt = $connKendaraan->prepare("INSERT INTO pemohon (tanggal, jampergi, jampulang, tujuan, keperluan, kendaraan, pengemudi, pemohon, dept_pemohon, `status`, tanggal_dibuat, urgent, tanggal_pulang) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if ($stmt === false) {
            die("Kesalahan pada query SQL: " . $connKendaraan->error);
        }

        // Bind parameter untuk query
        $stmt->bind_param(
            "sssssssssisis",
            $tanggal,
            $jampergi,
            $jampulang,
            $tujuan,
            $keperluan,
            $kendaraan,
            $pengemudi,
            $pemohon,
            $dept_pemohon,
            $status,
            $tanggal_dibuat,
            $urgent,
            $tanggal_pulang
        );

        // Eksekusi query dan cek apakah berhasil
        if ($stmt->execute()) {
            $permohonan_id = $stmt->insert_id; // Ambil ID permohonan yang baru saja dimasukkan

            // Menyimpan penumpang jika ada
            if (!empty($penumpang)) {
                foreach ($penumpang as $npkPenumpang) {
                    $sqlPenumpang = "INSERT INTO penumpang (pemohon_id, npk_penumpang) 
                                     VALUES ('$permohonan_id', '$npkPenumpang')";
                    if (!mysqli_query($connKendaraan, $sqlPenumpang)) {
                        echo "Error: " . mysqli_error($connKendaraan); // Tangkap error jika query gagal
                    }
                }
                echo "<script>
                     Swal.fire('Berhasil!', 'Permohonan kendaraan dan penumpang berhasil disimpan!', 'success')
                    .then(() => {
                      window.location.href = 'dashboard.php';
                     });
                    </script>";
            } else {
                // Jika tidak ada penumpang, hanya tampilkan sukses permohonan kendaraan
                echo "<script>
                     Swal.fire('Berhasil!', 'Permohonan kendaraan berhasil disimpan tanpa penumpang!', 'success')
                    .then(() => {
                      window.location.href = 'dashboard.php';
                     });
                    </script>";
            }
        } else {
            echo "<script>Swal.fire('Gagal!', 'Gagal mengajukan permohonan: " . $stmt->error . "', 'error');</script>";
        }

        // Tutup statement setelah selesai
        $stmt->close();
    }
}
?>