<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

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
    <title>History Ga Department</title>
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
    <nav class="navbar navbar-expand-lg bg-body-tertiary sticky-top">
        <?php include_once 'public/header.php'; ?>
    </nav>

    <div class="container p-4">
        <h4 class="text-center"> <strong> Jadwal Data Mobil </strong></h4>

        <!-- Form untuk filter berdasarkan tanggal -->
        <div class="row mb-4">
            <div class="col-md-6">
                <!-- <label for="filterTanggal">Filter Tanggal:</label> -->
                <input type="text" class="form-control" name="tanggal" id="filtertanggal" placeholder="Pilih Tanggal.." maxlength="10" autocomplete="off">
            </div>
            <!-- Mobil akan dimuat di sini -->
            <div class="col-md-6 ml-auto">
                <!-- <label for="searchInput">Search:</label> -->
                <input type="text" id="searchInput" class="form-control" maxlength="50" placeholder="Search..">
            </div>
        </div>
        <div class="row" id="mobilContainer"></div>

        <!-- Modal Detail Jadwal -->
        <div class="modal fade" id="detailJadwalModal" tabindex="-1" aria-labelledby="detailJadwalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog  modal-dialog-centered modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailJadwalLabel">
                            <a class="navbar-brand" href="#">
                                <img src="public/img/kayaba-logo.png" alt="Logo Kayaba" style="width: 100px">
                            </a>
                        </h5>
                        <!-- Modal Button -->
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="container p-4 border bg-light rounded shadow-sm">
                            <h5 class="text-center mb-4">History Jadwal Mobil</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover text-center" id="tableJadwal">
                                    <thead>
                                        <tr>
                                            <th>Pemohon</th>
                                            <th>Penumpang</th>
                                            <th>Tujuan</th>
                                            <th>Waktu Berangkat</th>
                                            <th>Waktu Pulang</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
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

        <script src="public/library/DataTables/datatables.min.js"></script>

        <!-- JavaScript -->
        <script>
            document.getElementById('searchInput').addEventListener('input', function() {
                let searchQuery = this.value.toLowerCase();
                let jadwalItems = document.querySelectorAll('.jadwal-item');

                jadwalItems.forEach(function(item) {
                    let type = item.querySelector('.card-title').textContent.toLowerCase();
                    let platMobil = item.querySelector('.card-text').textContent.toLowerCase();

                    if (type.includes(searchQuery) || platMobil.includes(searchQuery)) {
                        item.style.display = ''; // Tampilkan item yang sesuai
                    } else {
                        item.style.display = 'none'; // Sembunyikan item yang tidak sesuai
                    }
                });
            });
        </script>

        <script>
            $(document).ready(function() {
                // Menampilkan data mobil ketika halaman pertama kali dimuat
                fetchMobilData();

                $('#filtertanggal').datepicker({
                    dateFormat: 'dd-mm-yy', // Set format tanggal menjadi d-m-Y
                    onSelect: function(dateText) {
                        // Konversi tanggal ke format Y-m-d (untuk pengiriman ke PHP)
                        let dateObj = new Date(dateText.split('-').reverse().join('-')); // Mengubah D-M-Y menjadi Y-m-d
                        let year = dateObj.getFullYear();
                        let month = String(dateObj.getMonth() + 1).padStart(2, '0'); // Menambahkan leading zero jika bulan < 10
                        let day = String(dateObj.getDate()).padStart(2, '0'); // Menambahkan leading zero jika tanggal < 10

                        // Format menjadi Y-m-d
                        let formattedDate = `${year}-${month}-${day}`;

                        console.log("Tanggal Dipilih (Format Y-m-d):", formattedDate); // Debug tanggal yang telah diformat

                        // Ambil data mobil berdasarkan tanggal yang dipilih
                        fetchMobilData(formattedDate);
                    }
                });

                // Fungsi untuk mengambil data mobil
                function fetchMobilData(tanggal = '') {
                    // Membuat URL untuk mengambil data mobil (jika tanggal kosong, data akan ditampilkan tanpa filter tanggal)
                    let url = tanggal ? `filter_mobil.php?tanggal=${tanggal}` : `filter_mobil.php`;

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            let mobilContainer = $('#mobilContainer');
                            mobilContainer.empty(); // Kosongkan kontainer

                            // Iterasi data mobil dan tampilkan
                            data.forEach(mobil => {
                                // Menentukan class status dan warna untuk border dan background
                                let statusClass = mobil.status === 'Tersedia' ? 'text-warning' : 'text-warning';
                                let backgroundClass = mobil.status === 'Tersedia' ? 'bg-success' : 'bg-danger';
                                let borderClass = mobil.status === 'Tersedia' ? 'border-dark' : 'border-dark';
                                let icon = mobil.status === 'Tersedia' ? 'bi-check-circle' : 'bi-x-circle';

                                let mobilElement = `
                            <div class="col-md-4 mb-4 jadwal-item">
                                <div class="card shadow-sm border-2 ${borderClass} h-100">
                                    <div class="card-header ${backgroundClass}">
                                        <h4 class="card-title d-flex justify-content-between text-white">
                                            <strong>${mobil.type}</strong>
                                            <span class="badge ${statusClass}">${mobil.status}</span>
                                        </h4>
                                    </div>
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <p class="card-text">
                                            <strong>Plat Mobil:</strong> ${mobil.platmobil}<br>
                                            <strong>Warna Mobil:</strong> ${mobil.warna}<br>
                                            ${mobil.status !== 'Tersedia' ? `  
                                                <marquee>
                                                <strong>Pemohon:</strong> <span style="color: blue;">${mobil.pemohon}</span> 
                                                <strong>Tujuan:</strong> <span style="color: blue;">${mobil.tujuan}</span>
                                                <br> 
                                                </marquee>
                                            ` : ``}
                                        </p>
                                        <button class="btn btn-outline-dark btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#detailJadwalModal"
                                                onclick="showDetailJadwal(${mobil.id})">
                                            <i class="bi ${icon}"></i> History Jadwal
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                                mobilContainer.append(mobilElement);
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan saat mengambil data.');
                        });
                }
            });
        </script>


        <script>
            function showDetailJadwal(mobil_id) {
                console.log("Mengambil data untuk mobil_id:", mobil_id); // Log mobil_id yang dikirim

                fetch(`get_jadwal.php?mobil_id=${mobil_id}`)
                    .then(response => {
                        if (!response.ok) {
                            // Tangkap error HTTP seperti status 404 atau 500
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        return response.json(); // Parsing data jika sukses
                    })
                    .then(data => {
                        // Hapus DataTables jika sudah ada
                        if ($.fn.DataTable.isDataTable('#tableJadwal')) {
                            $('#tableJadwal').DataTable().destroy();
                        }

                        console.log("Respons data dari server:", data); // Log respons dari server

                        const jadwalTableBody = document.querySelector('#tableJadwal tbody'); // Mengakses body tabel
                        jadwalTableBody.innerHTML = '';

                        if (Array.isArray(data) && data.length > 0) {
                            // Bersihkan konten tabel dan tambahkan data

                            // Loop untuk menambahkan setiap jadwal ke tabel
                            for (let i = 0; i < data.length; i++) {
                                const row = document.createElement('tr'); // Buat elemen baris
                                const pemohonCell = document.createElement('td');
                                const penumpangCell = document.createElement('td');
                                const tujuanCell = document.createElement('td');
                                const tanggalCell = document.createElement('td');
                                const tanggal_pulangCell = document.createElement('td');
                                const jamPergiCell = document.createElement('td');
                                const jamPulangCell = document.createElement('td');

                                // Format tanggal menjadi dd/mm/yyyy
                                const tanggal = new Date(data[i].tanggal);
                                const tanggal_pulang = new Date(data[i].tanggal_pulang);
                                const formattedTanggal = formatDate(tanggal);
                                const formattedTanggal_pulang = formatDate(tanggal_pulang);

                                // Isi sel dengan data
                                pemohonCell.textContent = data[i].pemohon + " - " + data[i].pemohonName;

                                const penumpangList = document.createElement('ul');
                                if (Array.isArray(data[i].penumpang) && data[i].penumpang.length > 0) {
                                    // Loop untuk setiap penumpang dalam array penumpang
                                    for (let x = 0; x < data[i].penumpang.length; x++) {
                                        const li = document.createElement('li');
                                        li.textContent = data[i].penumpang[x]; // Format: "NPK - Nama"
                                        penumpangList.appendChild(li);
                                    }
                                } else {
                                    const li = document.createElement('li');
                                    li.textContent = "Tidak ada penumpang"; // Jika tidak ada penumpang
                                    penumpangList.appendChild(li);
                                }
                                penumpangCell.appendChild(penumpangList);

                                tujuanCell.textContent = data[i].tujuan;
                                tanggalCell.textContent = formattedTanggal;
                                tanggal_pulangCell.textContent = formattedTanggal_pulang;
                                jamPergiCell.textContent = data[i].jampergi;
                                jamPulangCell.textContent = data[i].jampulang;

                                // Tambahkan sel ke dalam baris
                                row.appendChild(pemohonCell);
                                row.appendChild(penumpangCell);
                                row.appendChild(tujuanCell);
                                let pergiCell = document.createElement('td');
                                pergiCell.textContent = `${tanggalCell.textContent} - ${jamPergiCell.textContent}`;
                                row.appendChild(pergiCell);

                                let pulangCell = document.createElement('td');
                                pulangCell.textContent = `${tanggal_pulangCell.textContent} - ${jamPulangCell.textContent}`;
                                row.appendChild(pulangCell);


                                // Tambahkan baris ke tabel
                                jadwalTableBody.appendChild(row);
                            }
                        }



                        // Inisialisasi ulang DataTables
                        $('#tableJadwal').DataTable({
                            autoWidth: false,
                            "language": {
                                "emptyTable": "Informasi yang Anda cari saat ini tidak tersedia.",
                            }
                        });

                        // Tampilkan modal setelah data diisi
                        $('#detailJadwalModal').modal('show');
                    })
                    .catch(error => {
                        console.error('Terjadi kesalahan:', error); // Log error jika ada
                        alert('Terjadi kesalahan: ' + error);
                    });
            }


            function formatDate(date) {
                const day = String(date.getDate()).padStart(2, '0'); // Pastikan dua digit
                const month = String(date.getMonth() + 1).padStart(2, '0'); // Bulan 1-12
                const year = date.getFullYear();

                return `${day}/${month}/${year}`; // Format dd/mm/yyyy
            }
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