<?php
session_start();

if (!isset($_SESSION['npk'])) {
    header('Location: /kendaraan/index.php'); // Mengarahkan ke halaman l ogin
    exit(); // Hentikan eksekusi
}

include 'config.php'; // Koneksi database

// Proses Update (jika tidak menggunakan AJAX)
if (isset($_POST['update'])) {
    // Validasi data
    $stmt = $connKendaraan->prepare("UPDATE pemohon SET tanggal=?, tanggal_pulang=?, jampergi=?, jampulang=?, tujuan=?, keperluan=?, kendaraan=?, 'status'=?, pengemudi=? WHERE id=?");
    $stmt->bind_param("ssssssissi", $_POST['tanggal'], $_POST['tanggal_pulang'], $_POST['jampergi'], $_POST['jampulang'], $_POST['tujuan'], $_POST['keperluan'], $_POST['kendaraan'], $_POST['status'], $_POST['pengemudi'], $_POST['id']);
    $stmt->execute();
    $stmt->close();

    header('Location: dashboard.php'); // Mengarahkan ke dashboard.php setelah update
    exit();
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Ga Departement</title>
    <link href="public/img/kyb-icon.ico" rel="icon">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="public/bootstrap/css/bootstrap.min.css">

    <link rel="stylesheet" href="public/library/bootstrap-icons/bootstrap-icons.css">

    <!-- Bootstrap-select CSS -->
    <link rel="stylesheet" href="public/library/bootstrap-select/dist/css/bootstrap-select.min.css">

    <!-- jQuery UI CSS (optional) -->
    <link rel="stylesheet" href="jquery-ui/jquery-ui.min.css">

    <!-- Link CSS untuk SweetAlert2 -->
    <link rel="stylesheet" href="public/library/sweetalert2/dist/sweetalert2.min.css">

    <link rel="stylesheet" href="public/library/flatpicker/flatpicker.min.css">

    <link rel="stylesheet" href="public/library/DataTables/datatables.min.css">


    <!-- JS SweetAlert2 -->
    <script src="public/library/sweetalert2/dist/sweetalert2.all.min.js"></script>
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary mb-4 sticky-top">
        <?php include_once 'public/header.php'; ?>
    </nav>



    <div class="container mt-3">
        <h4>Selamat Datang, <?php echo htmlspecialchars($_SESSION['full_name']) ?>!</h4>

        <?php if ($_SESSION['role'] === "kadep" || $_SESSION['role'] === "direksi") { ?>
            <h6>MENGETAHUI</h6>
        <?php } else if ($_SESSION['role'] === "karyawan") { ?>
            <h6>PEMOHON </h6>
        <?php } else if ($_SESSION['role'] === "ga pool") { ?>
            <h6>MENYETUJUI</h6>
        <?php } ?>

    </div>

    <!-- Konten Utama -->
    <div class="container p-4 border bg-light rounded shadow-sm">
        <h4 class="text-center mb-4">Data Permohonan Kendaraan</h4>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover text-center" id="tabeldashboard">
                <thead style="text-align: center;">
                    <tr>
                        <th style="text-align: center;">ID</th>
                        <th style="text-align: center;">Tanggal Dibuat</th>
                        <th style="text-align: center;">Waktu Berangkat</th>
                        <th style="text-align: center;">Waktu Pulang</th>
                        <th style="text-align: center;">Tujuan</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    // Mengambil data untuk tabel
                    if ($_SESSION['role'] == "kadep") {
                        $dept_user = $_SESSION['dept'];
                        $resultPemohon = $connKendaraan->query("SELECT * FROM pemohon WHERE dept_pemohon = '$dept_user' AND `status` = 1 ORDER BY tanggal_dibuat DESC");
                    } else if ($_SESSION['role'] == "ga pool") {
                        $resultPemohon = $connKendaraan->query("SELECT * FROM pemohon WHERE (`status` = 2 AND urgent = 0) OR (`status` = 3 AND urgent = 1) OR `status` = 4 ORDER BY tanggal_dibuat DESC");
                    } else if ($_SESSION['role'] == "direksi") {
                        $resultPemohon = $connKendaraan->query("SELECT * FROM pemohon WHERE `status` = 2 AND urgent = 1 ORDER BY tanggal_dibuat DESC");
                    } else if ($_SESSION['role'] == "karyawan") {
                        $npk_pemohon =  $_SESSION['npk'];
                        $resultPemohon = $connKendaraan->query("SELECT * FROM pemohon WHERE pemohon = '$npk_pemohon' ORDER BY tanggal_dibuat DESC");
                    }


                    if ($resultPemohon === false) {
                        echo "<tr><td colspan='9' class='text-danger'>Terjadi kesalahan: " . htmlspecialchars($connKendaraan->error) . "</td></tr>";
                    } else {
                        function getStatusBadge($status)
                        {
                            if ($status == 0) {
                                return '<span class="badge bg-secondary text-white border border-dark">Created</span>';
                            } elseif ($status == 1) {
                                return '<span class="badge bg-warning text-dark border border-dark">Requested</span>';
                            } elseif ($status == 2) {
                                return '<span class="badge bg-white text-primary border border-primary">Ka Dept Approved</span>';
                            } elseif ($status == 3) {
                                return '<span class="badge bg-info text-dark border border-dark">Direksi Approved</span>';
                            } elseif ($status == 4) {
                                return '<span class="badge bg-white text-success border border-dark">In Use</span>';
                            } elseif ($status == 5) {
                                return '<span class="badge bg-white text-danger border border-danger">Rejected</span>';
                            } elseif ($status == 6) {
                                return '<span class="badge bg-success text-white border border-drak">Finish</span>';
                            }
                        }

                        function getUrgentBadge($status)
                        {
                            if ($status == 1) {
                                return ' <span class="badge bg-danger text-white border border-dark">Urgent</span>';
                            }
                        }

                        if ($resultPemohon->num_rows > 0) {
                            while ($row = $resultPemohon->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td class='text-sm text-center p-1'>" . htmlspecialchars($row['id']) . "</td>";
                                echo "<td class='text-sm text-center p-1'>" . htmlspecialchars(date('d-m-Y H:i:s', strtotime($row['tanggal_dibuat']))) . "</td>";
                                echo "<td class='text-sm text-center p-1'>" . htmlspecialchars(date('d-m-Y', strtotime($row['tanggal']))) . " " . htmlspecialchars(date('H:i', strtotime($row['jampergi']))) . "</td>";
                                echo "<td class='text-sm text-center p-1'>" . htmlspecialchars(date('d-m-Y', strtotime($row['tanggal_pulang']))) . " " . htmlspecialchars(date('H:i', strtotime($row['jampulang']))) . "</td>";
                                echo "<td class='text-sm text-center p-1'>" . htmlspecialchars($row['tujuan']) . "</td>";
                                echo "<td class='text-sm text-center p-1'>" . getStatusBadge(htmlspecialchars($row['status'])) . getUrgentBadge(htmlspecialchars($row['urgent'])) . "</td>";
                                echo "<td class='text-sm p-1'>";

                                if ($_SESSION['role'] === "direksi" || $_SESSION['role'] === "ga pool" || $_SESSION['role'] === "kadep") {
                                    // Tombol edit
                                    echo '<button type="button" class="btn btn-warning btn-sm editBtn ms-1" data-id="' . htmlspecialchars($row['id']) . '">
                    <i class="bi bi-pencil-square"></i>
                  </button>';

                                    // Tombol cetak/export PDF
                                    echo '<a href="exportpdf.php?id=' . htmlspecialchars($row['id']) . '" class="btn btn-primary btn-sm ms-1" target="_blank">
                    <i class="bi bi-printer-fill"></i>
                  </a>';
                                }

                                if ($_SESSION['role'] === "karyawan") {
                                    if ($row['status'] == 0 || $row['status'] == 5 || $row['status'] == 4) {
                                        // Tombol edit
                                        echo '<button type="button" class="btn btn-warning btn-sm editBtn" data-id="' . htmlspecialchars($row['id']) . '">
                        <i class="bi bi-pencil-square"></i>
                      </button>';
                                    }


                                    if ($row['status'] == 0) {
                                        // Tombol hapus dengan konfirmasi
                                        echo '<button type="button" class="btn btn-danger btn-sm deleteBtn ms-1" data-id="' . htmlspecialchars($row['id']) . '">
                        <i class="bi bi-trash-fill"></i>
                      </button>';
                                    }

                                    // Tombol cetak/export PDF
                                    echo '<a href="exportpdf.php?id=' . htmlspecialchars($row['id']) . '" target="_blank" class="btn btn-primary btn-sm ms-1">
                    <i class="bi bi-printer-fill"></i>
                  </a>';
                                }

                                echo '</td>';
                                echo "</tr>";
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Bootstrap untuk Edit Data -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Detail Data Permohonan Kendaraan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateForm">
                        <input type="hidden" id="edit-id" name="id">

                        <!-- Tanggal -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit-tanggal" class="form-label">Tanggal</label>
                                <input type="text" class="form-control" id="edit-tanggal" name="tanggal" required
                                    <?php if ($_SESSION['role'] === "ga pool" || $_SESSION['role'] === "kadep" || $_SESSION['role'] === "direksi") {
                                        echo 'disabled';
                                    } ?>>
                            </div>

                            <div class="col-md-6">
                                <label for="edit-tanggal_pulang" class="form-label">Tanggal Pulang</label>
                                <input type="text" class="form-control" name="tanggal_pulang" id="edit-tanggal_pulang" required
                                    <?php if ($_SESSION['role'] === "ga pool" || $_SESSION['role'] === "kadep" || $_SESSION['role'] === "direksi") {
                                        echo 'disabled';
                                    } ?>>
                            </div>
                        </div>

                        <!-- Jam Pergi dan Pulang -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit-jampergi" class="form-label">Jam Pergi</label>
                                <input type="time" class="form-control" id="edit-jampergi" name="jampergi" required autocomplete="off"
                                    <?php if ($_SESSION['role'] === "ga pool" || $_SESSION['role'] === "kadep" || $_SESSION['role'] === "direksi") {
                                        echo 'disabled';
                                    } ?>>
                            </div>
                            <div class="col-md-6">
                                <label for="edit-jampulang" class="form-label">Jam Pulang</label>
                                <input type="time" class="form-control" id="edit-jampulang" name="jampulang" required autocomplete="off"
                                    <?php if ($_SESSION['role'] === "ga pool" || $_SESSION['role'] === "kadep" || $_SESSION['role'] === "direksi") {
                                        echo 'disabled';
                                    } ?>>
                            </div>
                        </div>

                        <!-- Tujuan dan Keperluan -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit-tujuan" class="form-label">Tujuan</label>
                                <input type="text" class="form-control" maxlength="50" id="edit-tujuan" name="tujuan" required
                                    <?php if ($_SESSION['role'] === "ga pool" || $_SESSION['role'] === "kadep" || $_SESSION['role'] === "direksi") {
                                        echo 'disabled';
                                    } ?>>
                            </div>
                            <div class="col-md-6">
                                <label for="edit-keperluan" class="form-label">Keperluan</label>
                                <input type="text" class="form-control" maxlength="55" id="edit-keperluan" name="keperluan" required
                                    <?php if ($_SESSION['role'] === "ga pool" || $_SESSION['role'] === "kadep" || $_SESSION['role'] === "direksi") {
                                        echo 'disabled';
                                    } ?>>
                            </div>
                        </div>

                        <!-- Kendaraan -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit-kendaraan" class="form-label">Kendaraan</label>
                                <select class="form-control" name="kendaraan" id="edit-kendaraan" required
                                    <?php if ($_SESSION['role'] === "ga pool" || $_SESSION['role'] === "kadep" || $_SESSION['role'] === "direksi") {
                                        echo 'disabled';
                                    } ?>>
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
                                <label for="edit-urgent" class="form-label">(Jika urgent)</label>
                                <select class="form-control" name="edit-urgent" id="edit-urgent" required
                                    <?php if ($_SESSION['role'] === "ga pool" || $_SESSION['role'] === "kadep" || $_SESSION['role'] === "direksi") {
                                        echo 'disabled';
                                    } ?>>
                                    <option value="0">Tidak</option>
                                    <option value="1">Urgent</option>
                                </select>
                            </div>
                        </div>

                        <!-- Pengemudi -->
                        <div class="mb-3">
                            <label for="edit-pengemudi" class="form-label">Pengemudi</label>
                            <select class="form-control" id="edit-pengemudi" name="pengemudi" required data-live-search="true"
                                <?php if ($_SESSION['role'] === "ga pool" || $_SESSION['role'] === "kadep" || $_SESSION['role'] === "direksi") {
                                    echo 'disabled';
                                } ?>></select>
                        </div>

                        <div class="mb-3">
                            <label for="edit-mobil" class="form-label" id="typemobil">Type Mobil</label>
                            <select class="form-control" id="edit-tipe" name="type" title="Pilih Mobil ditentukan HRD-GA" required data-live-search="true"
                                <?php if ($_SESSION['role'] === "karyawan" || $_SESSION['role'] === "kadep" || $_SESSION['role'] === "direksi") {
                                    echo 'disabled';
                                } ?>>
                            </select>
                        </div>


                        <div class="mb-3">
                            <label for="edit-penumpang" class="form-label">Penumpang</label>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "karyawan"): ?>
                                <button type="button" class="btn btn-sm p-1" id="add-penumpang">
                                    <i class="bi bi-plus-circle-fill text-success"></i>
                                </button>
                            <?php endif; ?>

                            <ul id="penumpang-list" class="mt-2"></ul>

                            <div class="mb-3" id="penumpang-container" style="display: none;">
                                <label for="penumpang" class="form-label">Pilih Penumpang</label>
                                <br>
                                <select class="form-control selectpicker" id="penumpang" name="penumpang[]" data-live-search="true" multiple>
                                    <option value="" disabled>Pilih Penumpang (bisa pilih lebih dari satu)</option>
                                    <?php
                                    // Mengambil data penumpang dari database
                                    $sqlPenumpang = "SELECT npk, full_name FROM ct_users_hash";
                                    $resultPenumpang = mysqli_query($conn, $sqlPenumpang);
                                    if ($resultPenumpang->num_rows > 0) {
                                        while ($row = $resultPenumpang->fetch_assoc()) {
                                            echo "<option value='" . $row['npk'] . "'>" . $row['npk'] . " - " . $row['full_name'] . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="Catatan" class="form-label">Catatan</label>
                            <textarea id="Catatan" class="form-control" rows="3" disabled></textarea>
                        </div>

                        <div class="modal-footer pb-0">

                            <?php
                            if ($_SESSION['role'] === "direksi" || $_SESSION['role'] === "kadep" || $_SESSION['role'] === "ga pool") { ?>
                                <button id="approve-btn" class="btn btn-success">
                                    <i class="fas fa-check"></i> Approve</button>
                                <button type="button" class="btn btn-danger" id="tolak-btn" data-bs-dismiss="modal">Tolak</button>
                            <?php } ?>

                            <?php
                            if ($_SESSION['role'] === "karyawan" || $_SESSION['role'] === "ga pool") {
                            ?>
                                <button id="btn_selesai" class="btn btn-primary">
                                    <i class="fas fa-check"></i> Selesai
                                </button>
                            <?php
                            }
                            ?>


                            <?php if ($_SESSION['role'] === "karyawan") { ?>
                                <button type="button" id="created-btn" class="btn btn-success">Requested</button>
                                <button type="submit" id="simpan-btn" class="btn btn-primary">Simpan Perubahan</button>
                            <?php } ?>

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript dan jQuery -->
    <script src="public/library/jquery/dist/jquery.min.js"></script>

    <script src="public/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="public/library/flatpicker/flatpicker.min.js"></script>

    <!-- Bootstrap-select JS -->
    <script src="public/library/bootstrap-select/dist/js/bootstrap-select.min.js"></script>

    <script src="jquery-ui/jquery-ui.min.js"></script>

    <script src="public/library/DataTables/datatables.min.js"></script>
    <script>
        function convertDateFormatToDmyHis(dateString) {
            const dateParts = dateString.split(' ');
            const date = dateParts[0].split('-');
            const time = dateParts[1];

            const day = date[2];
            const month = date[1];
            const year = date[0];

            return `${day}-${month}-${year}${time}`;
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addPenumpangButton = document.getElementById('add-penumpang');
            if (addPenumpangButton) {
                addPenumpangButton.addEventListener('click', function() {
                    const dropdown = document.getElementById('penumpang');
                    const selectedOptions = Array.from(dropdown.selectedOptions);
                    const penumpangList = document.getElementById('penumpang-list');
                    const penumpangIds = document.getElementById('penumpang-ids');

                    // Menampilkan dropdown jika belum ditampilkan
                    document.getElementById('penumpang-container').style.display = 'block';

                    // Mengosongkan daftar penumpang jika tidak ada pilihan
                    if (selectedOptions.length === 0) return;

                    // Menambahkan setiap penumpang yang dipilih ke dalam daftar
                    selectedOptions.forEach(option => {
                        // Mengecek jika penumpang sudah ada di daftar
                        const existingItem = Array.from(penumpangList.children).find(item => item.textContent.includes(option.textContent));
                        if (!existingItem) {
                            const li = document.createElement('li');
                            li.textContent = option.textContent;

                            // Menambahkan tombol hapus
                            const removeBtn = document.createElement('button');
                            removeBtn.textContent = 'Hapus';
                            removeBtn.className = 'btn btn-danger btn-sm ms-2';
                            removeBtn.onclick = function() {
                                penumpangList.removeChild(li);
                                // Mengambil kembali pilihan di dropdown
                                const optionToSelect = Array.from(dropdown.options).find(opt => opt.text === option.text);
                                if (optionToSelect) {
                                    optionToSelect.selected = true; // Memilih kembali di dropdown
                                }
                                $('.selectpicker').selectpicker('refresh'); // Menyegarkan selectpicker

                                // Menghapus ID dari input tersembunyi
                                updatePenumpangIds();
                            };

                            li.appendChild(removeBtn); // Menambahkan tombol hapus ke elemen daftar
                            penumpangList.appendChild(li); // Menambahkan elemen ke daftar
                            updatePenumpangIds(); // Memperbarui input tersembunyi dengan ID penumpang
                        }
                    });

                    // Menghapus pilihan yang sudah ditambahkan dari dropdown
                    selectedOptions.forEach(option => {
                        option.selected = false; // Menghapus pilihan di dropdown
                    });

                    // Menyegarkan selectpicker
                    $('.selectpicker').selectpicker('refresh');
                });
            }
        });

        function updatePenumpangIds() {
            const penumpangList = document.getElementById('penumpang-list');
            const penumpangIds = document.getElementById('penumpang-ids');
            const ids = Array.from(penumpangList.children).map(item => {
                const text = item.textContent;
                // Mendapatkan npk dari text
                return text.split(' - ')[0]; // Mengambil NPK
            });
            penumpangIds.value = ids.join(','); // Menyimpan NPK ke input tersembunyi
        }
    </script>

    <script>
        $(document).ready(function() {
            $('#tabeldashboard').DataTable({
                autoWidth: false,
                language: {
                    emptyTable: "Tidak ada data yang dapat ditampilkan." // Teks jika tabel kosong
                },
                lengthMenu: [10, 25, 50, 100], // Pilihan jumlah baris yang ditampilkan
            });
        });
    </script>

    <script>
        // Fungsi untuk memuat remark berdasarkan `pemohon_id`
        function loadUserPemohonID(id) {
            $.ajax({
                url: 'get_catatan.php', // Endpoint PHP
                type: 'GET',
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    if (!response.error) {
                        $('#Catatan').val(
                            "Kadept: " + (response.remark_kadept !== null ? response.remark_kadept : ' - ') + "\n" +
                            "Direksi: " + (response.remark_direksi !== null ? response.remark_direksi : ' - ') + "\n" +
                            "GA Pool: " + (response.remark_ga !== null ? response.remark_ga : ' - ')
                        );

                    } else {
                        // Jika ada kesalahan, tampilkan pesan kesalahan
                        $('#Catatan').val(response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error: " + status + " - " + error);
                    console.log(xhr.responseText);
                    $('#Catatan').val("Terjadi kesalahan dalam pengambilan data.");
                }
            });
        }

        function messageExpiredToPemohon() {
            $.ajax({
                url: 'get_expiredpemohon.php', // Endpoint PHP
                type: 'GET',
                success: function() {
                    console.log("Terkirim");
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error: " + status + " - " + error);
                    console.log(xhr.responseText);
                    $('#Catatan').val("Terjadi kesalahan dalam pengambilan data.");
                }
            });
        }
    </script>



    <script>
        $(document).ready(function() {
            messageExpiredToPemohon(); // Panggilan langsung saat halaman dimuat

            setInterval(function() {
                messageExpiredToPemohon()
            }, 3600000);

            $('#created-btn').click(function() {
                const id = $('#edit-id').val();
                // Lakukan konfirmasi sebelum mengubah status
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Anda akan mengubah status menjadi Requested!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, ubah status!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Lakukan AJAX request ke update_status.php
                        $.ajax({
                            url: 'approve.php',
                            type: 'POST',
                            data: {
                                id: id,
                                status: 1,
                                type: "requested",
                            }, // Kirim ID dan status 1
                            dataType: 'json',
                            success: function(response) {
                                console.log(response);
                                if (response.status == "success") {
                                    // SweetAlert untuk alert sukses
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.message,
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        location.reload(); // Reload halaman setelah swal sukses
                                    });
                                } else {
                                    // SweetAlert untuk alert gagal
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: 'Gagal menghapus data : ' + response.message
                                    })
                                }
                            },
                            error: function(xhr, status, error) {
                                console.log(xhr);
                                Swal.fire(
                                    'Gagal!',
                                    'Terjadi kesalahan saat mengubah status.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>

    <script>
        document.getElementById('logoutBtn').addEventListener('click', function(event) {
            event.preventDefault(); // Mencegah link default

            // Tampilkan SweetAlert konfirmasi
            Swal.fire({
                title: 'Anda yakin ingin keluar?',
                text: "Anda akan dialihkan ke halaman login.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, keluar',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Arahkan ke halaman logout atau index.php jika dikonfirmasi
                    window.location.href = 'logout.php';
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#approve-btn').click(function(e) {
                e.preventDefault();
                const id = $('#edit-id').val();
                const typeKendaraan = $("#edit-tipe").val();
                const tanggal = $("#edit-tanggal").val();
                const jampergi = $("#edit-jampergi").val();
                const jampulang = $("#edit-jampulang").val();
                const tanggal_pulang = $("#edit-tanggal_pulang").val();

                console.log(tanggal_pulang);

                <?php
                if ($_SESSION['role'] == "ga pool") {
                ?>
                    if (!id || !typeKendaraan || !tanggal || !jampergi || !jampulang || !tanggal_pulang) {
                        Swal.fire(
                            'Gagal!',
                            'Harap isi kolom Type Mobil!',
                            'warning'
                        );
                        return; // Stop the function if validation fails
                    }
                <?php } ?>

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Anda akan menyetujui data ini!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, setujui!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Kirim AJAX request ke approve.php
                        $.ajax({
                            url: 'approve.php',
                            type: 'POST',
                            data: {
                                id: id,
                                type: "approveKadept",
                                typeKendaraan: typeKendaraan,
                                tanggal: tanggal,
                                jampergi: jampergi,
                                jampulang: jampulang,
                                tanggal_pulang: tanggal_pulang,
                            },
                            dataType: 'json',
                            success: function(response) {

                                console.log(response);
                                if (response.status == "success") {
                                    // SweetAlert untuk alert sukses
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.message,
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        location.reload(); // Reload halaman setelah swal sukses
                                    });
                                } else {
                                    // SweetAlert untuk alert gagal
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: 'Gagal meng-update data: ' + response.message
                                    })
                                }
                            },

                            error: function(xhr) {
                                console.log(xhr);
                                Swal.fire(
                                    'Gagal!',
                                    'Terjadi kesalahan saat menyetujui data.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#tolak-btn').click(function(e) {
                e.preventDefault();
                const id = $('#edit-id').val(); // Ambil ID dari elemen tertentu
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Berikan alasan untuk menolak data ini.",
                    input: 'textarea',
                    inputPlaceholder: 'Masukkan alasan penolakan...',
                    inputAttributes: {
                        'aria-label': 'Masukkan alasan penolakan di sini'
                    },
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, tolak!',
                    cancelButtonText: 'Batal',
                    preConfirm: (reason) => {
                        if (!reason) {
                            Swal.showValidationMessage('Alasan penolakan tidak boleh kosong');
                        }
                        return reason;
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        const alasanPenolakan = result.value;
                        // Lakukan AJAX request ke tolak.php
                        $.ajax({
                            url: 'tolak.php',
                            type: 'POST',
                            data: {
                                id: id,
                                alasan: alasanPenolakan,
                                type: "tolakKadept",
                            },
                            dataType: 'json',
                            success: function(response) {
                                console.log(response);
                                if (response.status == "success") {
                                    // SweetAlert untuk alert sukses
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.message,
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        location.reload(); // Reload halaman setelah swal sukses
                                    });
                                } else {
                                    // SweetAlert untuk alert gagal
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: 'Gagal menghapus data: ' + response.message
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.fire(
                                    'Gagal!',
                                    'Terjadi kesalahan saat menolak data.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Inisialisasi selectpicker
            $("select").selectpicker();

            // Event handler untuk tombol delete
            $(document).on('click', '.deleteBtn', function() {
                const dataId = $(this).data('id');
                console.log(dataId);

                // SweetAlert untuk konfirmasi penghapusan
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Anda tidak dapat mengembalikan data yang telah dihapus!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Lakukan proses penghapusan jika user menekan tombol "Ya, hapus!"
                        $.ajax({
                            url: 'hapus-list.php',
                            type: 'POST',
                            data: {
                                pemohon_id: dataId,
                            },
                            dataType: 'json',
                            success: function(response) {
                                console.log(response); // Cek respon dari server
                                if (response.status == "success") {
                                    // SweetAlert untuk alert sukses
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.message,
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        location.reload(); // Reload halaman setelah swal sukses
                                    });
                                } else {
                                    // SweetAlert untuk alert gagal
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: 'Data tidak dapat dikembalikan : ' + response.message
                                    }).then(() => {
                                        location.reload(); // Reload halaman setelah swal gagal
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.log(xhr.responseText); // Debug error dari server
                                // SweetAlert untuk alert error
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Terjadi Kesalahan',
                                    text: 'Terjadi kesalahan saat menghapus data.'
                                });
                            }
                        });
                    }
                });
            });


            $(document).on('click', '.editBtn', function() {
                var id = $(this).data('id');
                getDetailPemohon(id);
            });

            function getDetailPemohon(id) {
                $('#edit-id').val('');
                $('#edit-tanggal').val('');
                $('#edit-tanggal_pulang').val('');
                $('#edit-jampergi').val('');
                $('#edit-jampulang').val('');
                $('#edit-tujuan').val('');
                $('#edit-keperluan').val('');
                $('#edit-kendaraan').val('');
                $('#edit-urgent').val('');
                $('#edit-pengemudi').val('').selectpicker('refresh'); // Menambahkan pengemudi

                $.ajax({
                    url: 'get_data.php',
                    type: 'GET',
                    data: {
                        id: id
                    },
                    success: function(response) {
                        const data = JSON.parse(response);

                        console.log(data);

                        if (data.pemohon.status == 3 && data.pemohon.urgent == 1 || data.pemohon.status == 2 && data.pemohon.urgent == 0) {
                            $('#edit-tipe').prop('disabled', false).val('').selectpicker('refresh'); // Reset tipee
                        } else {
                            $('#edit-tipe').prop('disabled', true).val('').selectpicker('refresh'); // Reset tipee
                        }

                        function formatTanggal(tanggal) {
                            const [year, month, day] = tanggal.split("-");
                            return `${day}-${month}-${year}`;
                        }

                        // Mengambil data karyawan untuk dropdown pengemudi
                        $.ajax({
                            url: 'get_karyawan.php',
                            type: 'GET',
                            success: function(response) {
                                const pengemudiData = response;
                                const pengemudiDropdown = $('#edit-pengemudi');
                                pengemudiDropdown.empty(); // Kosongkan dropdown

                                // Mengisi dropdown dengan data pengemudi
                                pengemudiData.forEach(function(pengemudi) {
                                    let option = `<option value="${pengemudi.npk}">${pengemudi.npk} - ${pengemudi.full_name}</option>`;
                                    pengemudiDropdown.append(option);
                                });

                                const tanggal = data.pemohon.tanggal;
                                const tanggal_pulang = data.pemohon.tanggal_pulang;
                                const status = data.pemohon.status;

                                $.ajax({
                                    url: 'get_tanggalmobil.php',
                                    type: 'GET',
                                    data: {
                                        tanggal_pulang: tanggal_pulang,
                                        tanggal: tanggal,
                                        status: status
                                    },
                                    success: function(response) {
                                        console.log(response); // Tampilkan hasil untuk debugging

                                        const kendaraanData = response;
                                        const tipeKendaraanDropdown = $('#edit-tipe');
                                        tipeKendaraanDropdown.empty(); // Kosongkan dropdown

                                        // Mengisi dropdown dengan data kendaraan

                                        kendaraanData.forEach(function(data) {
                                            console.log(data.id);
                                            let option = `<option value="${data.id}">${data.type} - ${data.platmobil} - ${data.warna}</option>`;
                                            tipeKendaraanDropdown.append(option);
                                        });

                                        // Mengisi form edit dengan data pemohon
                                        $('#edit-id').val(data.pemohon.id);
                                        $('#edit-tanggal').val(formatTanggal(data.pemohon.tanggal));
                                        $('#edit-tanggal_pulang').val(formatTanggal(data.pemohon.tanggal_pulang));
                                        $('#edit-jampergi').val(data.pemohon.jampergi);
                                        $('#edit-jampulang').val(data.pemohon.jampulang);
                                        $('#edit-tujuan').val(data.pemohon.tujuan);
                                        $('#edit-keperluan').val(data.pemohon.keperluan);
                                        $('#edit-kendaraan').val(data.pemohon.kendaraan).selectpicker('refresh');
                                        $('#edit-urgent').val(data.pemohon.urgent).selectpicker('refresh');
                                        $('#edit-tipe').val(data.pemohon.type).selectpicker('refresh'); // Menetapkan nilai berdasarkan `type`

                                        $('#editModal').modal('show');
                                        pengemudiDropdown.val(data.pemohon.pengemudi).selectpicker('refresh');
                                        // tipeKendaraanDropdown.val(data.pemohon.type).selectpicker('refresh');
                                        console.log("Tanggal Pulang: ", $('#edit-tanggal_pulang').val());

                                        if (data.pemohon.status == 4) {
                                            console.log(data.pemohon.type)
                                            $('#approve-btn').hide();
                                            $('#btn_selesai').show();
                                            $('#tolak-btn').hide();
                                            $('#created-btn').hide();
                                            $('#simpan-btn').hide();
                                            $('.hapusPenumpang').hide();
                                            $('#add-penumpang').hide();

                                            tipeKendaraanDropdown.prop("disabled", true).val(data.pemohon.type).selectpicker('refresh');
                                        } else {
                                            $('#btn_selesai').hide();
                                            $('#approve-btn').show();
                                            $('#tolak-btn').show();
                                            $('#created-btn').show();
                                            $('#simpan-btn').show();
                                            $('.hapusPenumpang').show();
                                            $('#add-penumpang').show();
                                        }


                                        $("#edit-tanggal").datepicker({
                                            dateFormat: "dd-mm-yy",
                                            minDate: 0
                                        });

                                        $("#edit-tanggal_pulang").datepicker({
                                            dateFormat: "dd-mm-yy",
                                            minDate: 0
                                        });

                                        $("#edit-jampulang").flatpickr({
                                            enableTime: true,
                                            noCalendar: true,
                                            dateFormat: "H:i",
                                            time_24hr: true
                                        });

                                        $("#edit-jampergi").flatpickr({
                                            enableTime: true,
                                            noCalendar: true,
                                            dateFormat: "H:i",
                                            time_24hr: true
                                        });
                                    },
                                    error: function(xhr, status, error) {
                                        console.error('Error:', error);
                                        console.error(xhr);
                                    }
                                });
                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX Error:', error);
                            }
                        });


                        loadUserPemohonID(id);

                        // Mengisi daftar penumpang

                        let penumpangData = data.penumpang;
                        console.log(penumpangData);
                        let penumpangList = $('#penumpang-list');
                        penumpangList.empty();

                        penumpangData.forEach(function(penumpang, index) {
                            let listItem = `
                            <li>
                                ${penumpang.npk_penumpang} - ${penumpang.nama_penumpang}
                                 <?php if ($_SESSION['role'] === "karyawan") { ?>
                                <a class="btn btn-sm mb-1 hapusPenumpang" data-npk-penumpang="${penumpang.npk_penumpang}" style="font-size: 0.60rem; padding: 0.50rem 0.4rem;">
                                    <i class="bi bi-trash-fill text-danger"></i>
                                </a>    <?php } ?>
                            </li>`;
                            penumpangList.append(listItem);
                        });
                    }
                });
            }

            // Event handler untuk hapus penumpang
            $(document).on('click', '.hapusPenumpang', function() {
                console.log("ditekan");
                var npkPenumpang = $(this).data('npk-penumpang');
                const pemohon_id = $('#edit-id').val();

                // SweetAlert untuk konfirmasi penghapusan penumpang
                Swal.fire({
                    title: 'Apakah Anda yakin ingin menghapus penumpang ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'hapus_penumpang.php',
                            type: 'POST',
                            data: {
                                npk_penumpang: npkPenumpang,
                                pemohon_id: pemohon_id
                            },
                            success: function(response) {
                                console.log(response); // Cek respon dari server
                                if (response.status == "success") {
                                    // SweetAlert untuk alert sukses
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Penumpang berhasil dihapus!',
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        location.reload(); // Reload halaman setelah swal sukses
                                    });
                                } else {
                                    // SweetAlert untuk alert gagal
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: 'Gagal menghapus penumpang: ' + response.message
                                    }).then(() => {
                                        location.reload(); // Reload halaman setelah swal gagal
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.log(xhr.responseText); // Debug error dari server
                                // SweetAlert untuk alert error
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Terjadi Kesalahan',
                                    text: 'Terjadi kesalahan saat menghapus penumpang.'
                                });
                            }
                        });
                    }
                });
            });

            // Event handler untuk form update
            $('#updateForm').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                console.log(formData); // Cek data yang dikirim

                // Mengirim data form untuk diupdate
                $.ajax({
                    url: 'update_data.php',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        console.log(response); // Cek respon dari server
                        if (response.status == "success") {
                            // SweetAlert untuk alert sukses
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload(); // Reload halaman setelah swal sukses
                            });
                        } else {
                            console.log(response);
                            // SweetAlert untuk alert gagal
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: 'Gagal memperbarui data: ' + response.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText); // Debug error dari server
                        // SweetAlert untuk alert error
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: 'Terjadi kesalahan saat memperbarui data.'
                        });
                    }
                });
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#btn_selesai').on('click', function(e) {
                e.preventDefault(); // Prevent default button behavior

                // Get the pemohon ID from the hidden input field
                const pemohonId = $('#edit-id').val(); // This grabs the value from the hidden input

                // Check if pemohonId is valid (not empty or undefined)
                if (pemohonId) {
                    // Send AJAX request to update status to "selesai"
                    $.ajax({
                        url: 'konfirmasi_selesai.php', // PHP endpoint to handle status update
                        type: 'POST',
                        data: {
                            id: pemohonId // Send the pemohon ID
                        },
                        success: function(response) {
                            const data = JSON.parse(response); // Parse the JSON response
                            if (data.success) {
                                // Use SweetAlert to show success message
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message, // The message from the server
                                    showConfirmButton: false,
                                    timer: 1500 // Close the alert automatically after 1.5 seconds
                                }).then(() => {
                                    location.reload(); // Reload the page after the success alert
                                });
                            } else {
                                // Use SweetAlert to show error message
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: data.message, // The error message from the server
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error); // Log error if request fails
                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi Kesalahan',
                                text: 'Gagal mengubah status, silakan coba lagi.',
                            });
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'ID Pemohon Tidak Ditemukan',
                        text: 'Harap periksa kembali ID pemohon.',
                    });
                }
            });
        });
    </script>


</body>

</html>
<?php
// Proses penyimpanan penumpang
if (isset($_POST['penumpang']) && !empty($_POST['penumpang'])) {
    $permohonan_id = $_POST['id']; // Ambil ID permohonan dari formulir
    foreach ($_POST['penumpang'] as $npkPenumpang) {
        $sqlInsertPenumpang = "INSERT INTO penumpang (pemohon_id, npk_penumpang) VALUES (?, ?)";
        $stmt = $connKendaraan->prepare($sqlInsertPenumpang);
        $stmt->bind_param("is", $permohonan_id, $npkPenumpang);

        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error;
        }
    }
    echo "<script>Swal.fire('Berhasil!', 'Permohonan kendaraan dan penumpang berhasil disimpan!', 'success');</script>";
}

// if (isset($_POST['formpemohon'])) {
//     $tanggal = $_POST['tanggal'];
//     $jampergi = $_POST['jampergi'];
//     $jampulang = $_POST['jampulang'];
//     $tujuan = $_POST['tujuan'];
//     $keperluan = $_POST['keperluan'];
//     $kendaraan = $_POST['kendaraan'];
//     $penumpang = $_POST['penumpang'];
//     $pengemudi = $_POST['pengemudi'];
//     $pemohon = $_SESSION['npk'];

//     // Validasi input
//     if (empty($tanggal) || empty($jampergi) || empty($jampulang) || empty($tujuan) || empty($keperluan) || empty($kendaraan) || empty($pengemudi)) {
//         echo "<script>Swal.fire('Oops!', 'Semua kolom harus diisi!', 'error');</script>";
//     } else {
//         // Debugging query error
//         $stmt = $connKendaraan->prepare("INSERT INTO pemohon (tanggal, jampergi, jampulang, tujuan, keperluan, kendaraan, pengemudi, pemohon) 
//                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
//         if ($stmt === false) {
//             die("Kesalahan pada query SQL: " . $connKendaraan->error);
//         }

//         $stmt->bind_param("ssssssss", date('Y-m-d', strtotime($tanggal)), $jampergi, $jampulang, $tujuan, $keperluan, $kendaraan, $pengemudi, $pemohon);

//         if ($stmt->execute()) {
//             $permohonan_id = $stmt->insert_id; // Ambil ID permohonan yang baru saja dimasukkan;

//             // Menyimpan penumpang
//             if (isset($_POST['penumpang']) && !empty($_POST['penumpang'])) {
//                 foreach ($_POST['penumpang'] as $npkPenumpang) {
//                     $sqlPenumpang = "INSERT INTO penumpang (pemohon_id, npk_penumpang) 
//                                  VALUES ('$permohonan_id', '$npkPenumpang')";
//                     if (!mysqli_query($connKendaraan, $sqlPenumpang)) {
//                         echo "Error: " . mysqli_error($connKendaraan); // Tangkap error jika query gagal
//                     }
//                 }
//                 echo "<script>Swal.fire('Berhasil!', 'Permohonan kendaraan dan penumpang berhasil disimpan!', 'success');</script>";
//             } else {
//                 echo "<script>Swal.fire('Info!', 'Tidak ada penumpang yang dipilih!', 'info');</script>";
//             }
//         } else {
//             echo "<script>Swal.fire('Gagal!', 'Gagal mengajukan permohonan: " . $stmt->error . "', 'error');</script>";
//         }

//         $stmt->close();
//     }
// }

?>
</body>

</html>
<style>
    .fixed-footer {
        position: sticky;
        bottom: 0;
        background-color: white;
        /* Sesuaikan dengan warna latar belakang modal */
        border-top: 1px solid #dee2e6;
        /* Garis batas atas jika diperlukan */
        z-index: 1030;
        /* Sesuaikan dengan z-index modal agar berada di atas */
        padding: 15px;
        /* Penambahan padding jika diperlukan */
    }
</style>