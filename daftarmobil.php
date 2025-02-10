<?php
session_start();

// Mencegah akses jika pengguna belum login
if (!isset($_SESSION['npk']) || $_SESSION['role'] !== "ga pool") {
    header('Location: dashboard.php');
    exit();
}

include 'config.php'; // Pastikan file ini berisi koneksi ke database

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form GA Department</title>
    <link href="public/img/kyb-icon.ico" rel="icon">
    <link rel="stylesheet" href="public/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="public/library/sweetalert2/dist/sweetalert2.min.css">
    <script src="public/library/sweetalert2/dist/sweetalert2.all.min.js"></script>
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary mb-4 sticky-top">
        <?php include_once 'public/header.php'; ?>
    </nav>

    <div class="container d-flex justify-content-center align-items-center">
        <div class="card shadow-lg w-100" style="max-width: 500px; margin-top:70px;">
            <div class="card-body">
                <h3 class="mb-4 text-center">Form Daftar Kendaraan</h3>
                <!-- Form Input Kendaraan -->
                <form method="post" action="" autocomplete="off">
                    <div class="mb-3">
                        <label for="type" class="form-label">Tipe Kendaraan</label>
                        <input type="text" class="form-control" id="type" name="type" maxlength="50" required>
                    </div>
                    <div class="mb-3">
                        <label for="platmobil" class="form-label">Plat Kendaraan</label>
                        <input type="text" class="form-control" id="platmobil" name="platmobil" maxlength="10" required>
                    </div>
                    <div class="mb-3">
                        <label for="warna" class="form-label">Warna Kendaraan</label>
                        <input type="text" class="form-control" id="warna" name="warna" maxlength="20" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="simpanMobil">Simpan</button>
                </form>
            </div>
        </div>
    </div>



    <script src="public/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById('simpanMobil').addEventListener('click', function(event) {
            event.preventDefault(); // Mencegah form melakukan reload halaman

            // Ambil nilai input form
            var type = document.getElementById('type').value;
            var platmobil = document.getElementById('platmobil').value;
            var warna = document.getElementById('warna').value;

            if (type === '' || platmobil === '' || warna === '') {
                // Jika ada field yang kosong, tampilkan SweetAlert dengan pesan error
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Semua field harus diisi.',
                });
                return; // Menghentikan proses jika ada field yang kosong
            }


            // Nonaktifkan tombol Simpan sementara
            document.getElementById('simpanMobil').disabled = true;

            // Menggunakan Ajax untuk mengirim data ke server
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'get_mobil.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) { // Jika sudah menerima respons dari server
                    if (xhr.status === 200) { // Jika status OK
                        var response = xhr.responseText;

                        // Menampilkan SweetAlert berdasarkan respons dari server
                        if (response === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sukses!',
                                text: 'Data kendaraan berhasil disimpan.',
                            }).then(() => {
                                // Redirect ke halaman daftar mobil setelah berhasil
                                window.location.href = 'kelolamobil.php';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: 'Terjadi kesalahan saat menyimpan data.',
                            });
                        }
                    } else {
                        // Menampilkan pesan error jika gagal menghubungi server
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Tidak dapat menghubungi server.',
                        });
                    }

                    // Aktifkan tombol Simpan kembali
                    document.getElementById('simpanMobil').disabled = false;
                }
            };

            // Mengirim data menggunakan URL encoded
            xhr.send('type=' + encodeURIComponent(type) + '&platmobil=' + encodeURIComponent(platmobil) + '&warna=' + encodeURIComponent(warna));
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
</body>

</html>