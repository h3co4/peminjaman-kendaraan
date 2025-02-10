<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

// Mencegah akses jika pengguna belum login
if (!isset($_SESSION['npk']) || $_SESSION['role'] !== "ga pool") {
    header('Location: dashboard.php');
    exit();
}

include 'config.php'; // Koneksi database
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





    <!-- Konten Utama -->
    <div class="container p-4 border bg-light rounded shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Daftar Mobil</h4>
            <!-- Tombol "Tambah Mobil" di kanan atas -->
            <a href="daftarmobil.php" class="btn btn-success btn-sm" id="tambahMobilBtn">
                Tambah Mobil
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover text-center" id="tabelmobil">
                <thead style="text-align: center;">
                    <tr>
                        <th style="text-align: center;">Jenis Mobil</th>
                        <th style="text-align: center;">Warna Mobil</th>
                        <th style="text-align: center;">Plat Nomor Mobil</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    // Melakukan query untuk mengambil data dari tabel typemobil
                    $resultMobil = $connKendaraan->query("SELECT * FROM typemobil");

                    // Mengecek apakah query berhasil
                    if ($resultMobil === false) {
                        // Menampilkan pesan kesalahan jika query gagal
                        echo "<tr><td colspan='4' class='text-danger'>Terjadi kesalahan: " . htmlspecialchars($connKendaraan->error) . "</td></tr>";
                    } else {
                        // Mengecek apakah ada hasil dalam query
                        if ($resultMobil->num_rows > 0) {
                            // Menampilkan data setiap baris dari hasil query
                            while ($row = $resultMobil->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td class='text-sm text-center p-1'>" . htmlspecialchars($row['type']) . "</td>";
                                echo "<td class='text-sm text-center p-1'>" . htmlspecialchars($row['warna']) . "</td>";
                                echo "<td class='text-sm text-center p-1'>" . htmlspecialchars($row['platmobil']) . "</td>";
                                echo "<td class='text-sm p-1'>";
                                echo '<button type="button" class="btn btn-warning btn-sm editBtn ms-1" data-id="' . htmlspecialchars($row['id']) . '" data-bs-toggle="modal" data-bs-target="#editModal">
                                        <i class="bi bi-pencil-square"></i>
                                </button>';
                                echo '<button type="button" class="btn btn-danger btn-sm deleteBtn ms-1" data-id="' . htmlspecialchars($row['id']) . '">
                                <i class="bi bi-trash-fill"></i>
                            </button>';
                                echo "</td>"; // Tutup kolom tombol
                                echo "</tr>"; // Tutup baris tabel
                            }
                        } else {
                            // Jika tidak ada data yang ditemukan
                            echo "<tr><td colspan='4' class='text-center'>Tidak ada data yang ditemukan.</td></tr>";
                        }
                    }
                    ?>

                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Data Mobil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="editId" name="id">
                        <div class="mb-3">
                            <label for="editType" class="form-label">Jenis Mobil</label>
                            <input type="text" class="form-control" id="editType" name="type" required maxlength="50">
                        </div>
                        <div class="mb-3">
                            <label for="editWarna" class="form-label">Warna Mobil</label>
                            <input type="text" class="form-control" id="editWarna" name="warna" required maxlength="50">
                        </div>
                        <div class="mb-3">
                            <label for="editPlat" class="form-label">Plat Nomor Mobil</label>
                            <input type="text" class="form-control" id="editPlat" name="platmobil" maxlength="10" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="saveEditBtn">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        $(document).ready(function() {
            $('#tabelmobil').DataTable({
                autoWidth: false,
                language: {
                    emptyTable: "Tidak ada data yang dapat ditampilkan." // Teks jika tabel kosong
                },
                lengthMenu: [10, 25, 50, 100], // Pilihan jumlah baris yang ditampilkan
            });
        });
    </script>

    <script>
        // Menggunakan jQuery untuk menangani event klik tombol edit
        $(document).on('click', '.editBtn', function() {
            var id = $(this).data('id'); // Ambil ID dari data-id
            // Memuat data mobil berdasarkan ID (gunakan AJAX untuk mengambil data)
            $.ajax({
                url: 'getCar.php', // Gantilah dengan file PHP untuk mengambil data mobil berdasarkan ID
                type: 'GET',
                data: {
                    id: id
                },
                success: function(response) {
                    // Misalnya, response adalah JSON dengan data mobil
                    var data = JSON.parse(response);
                    // Mengisi form dengan data mobil yang diambil
                    $('#editId').val(data.id);
                    $('#editType').val(data.type);
                    $('#editWarna').val(data.warna);
                    $('#editPlat').val(data.platmobil);
                }
            });
        });

        // Menyimpan perubahan data
        $('#saveEditBtn').on('click', function() {
            var formData = $('#editForm').serialize(); // Ambil data dari form
            $.ajax({
                url: 'getCar.php', // Gantilah dengan file PHP untuk menyimpan perubahan data mobil
                type: 'POST',
                data: formData,
                success: function(response) {
                    // Jika berhasil, tampilkan SweetAlert sukses
                    if (response == 'success') {
                        // Menampilkan SweetAlert2 sukses tanpa tombol
                        Swal.fire({
                            icon: 'success',
                            title: 'Data berhasil disimpan!',
                            showConfirmButton: false, // Menyembunyikan tombol
                            timer: 1500 // Menampilkan alert selama 1.5 detik
                        }).then(function() {
                            // Setelah alert selesai, tutup modal dan perbarui halaman
                            $('#editModal').modal('hide');
                            location.reload(); // Bisa diganti dengan memperbarui baris tabel secara dinamis
                        });
                    } else {
                        // Jika gagal, tampilkan SweetAlert error
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal menyimpan perubahan',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                }
            });
        });

        $(document).on('click', '.deleteBtn', function() {
            var id = $(this).data('id'); // Ambil ID dari data-id

            // Menampilkan konfirmasi menggunakan SweetAlert
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Data ini akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika pengguna mengkonfirmasi penghapusan, kirim AJAX untuk menghapus data
                    $.ajax({
                        url: 'deleteCar.php', // Gantilah dengan file PHP untuk menghapus data
                        type: 'POST',
                        data: {
                            id: id
                        },
                        success: function(response) {
                            // Jika berhasil, tampilkan SweetAlert sukses
                            if (response == 'success') {
                                // Menampilkan SweetAlert sukses
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Data berhasil dihapus',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(function() {
                                    // Menghapus baris yang terkait dengan ID yang dihapus
                                    $('button[data-id="' + id + '"]').closest('tr').remove(); // Menghapus baris tabel yang sesuai
                                });
                            } else {
                                // Jika gagal, tampilkan SweetAlert error
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal menghapus data',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }
                        }
                    });
                }
            });
        });
    </script>



</body>

</html>
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