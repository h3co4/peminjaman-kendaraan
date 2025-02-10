<?php


// Mengimpor library FPDF
require('fpdf/fpdf.php'); // Sesuaikan dengan lokasi file FPDF jika perlu
include 'config.php'; // Koneksi database

include 'phpqrcode/qrlib.php';

class PDF extends FPDF
{
    // Constructor untuk mengatur orientasi dan ukuran kertas
    function __construct()
    {
        parent::__construct('P', 'mm', 'A4'); // Mengubah orientasi menjadi landscape
    }

    // Header untuk PDF
    function Header()
    {


        $this->SetXY(10, 8);
        $this->SetFont('Arial', 'BI', 10);
        $this->Cell(0, 6, 'PT. KAYABA INDONESIA', 0, 1, 'L');


        // Mengatur posisi untuk teks "GA DEPARTEMENT"
        $this->SetXY(10, 14); // Mengubah Y untuk memisahkan jarak dengan teks sebelumnya
        $this->Cell(0, 4, 'GA DEPARTEMENT', 0, 1, 'L'); // Teks rata kiri

        // Menampilkan teks "Seksi Pool & Scrap" di bagian kanan atas
        $this->SetXY(-70, 8); // Pindahkan teks ke bagian atas kanan
        $this->Cell(0, 6, 'Seksi Pool & Scrap', 0, 1, 'R'); // Teks rata kanan

        $this->SetXY(0, 15);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 10, 'FORMULIR PERMOHONAN PEMAKAIAN', 0, 1, 'C');

        $this->SetXY(0, 15);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 20, 'KENDARAAN UNTUK DINAS', 0, 1, 'C');

        // Menambahkan garis bawah untuk teks "KENDARAAN UNTUK DINAS"
        $this->SetLineWidth(0.5); // Set ketebalan garis
        $this->Line(74, 27, 126, 27); // Menggambar garis (atur posisi dan panjang sesuai keinginan)

        $this->Ln(5);

        // Membuat border
        $this->SetLineWidth(0.5);
        $this->Rect(5, 8, 200, 280); // Menggambar border luar

        // Mengatur ketebalan garis untuk border dalam
        $this->SetLineWidth(0.5); // Ketebalan untuk border dalam
        $this->Rect(4, 7, 202, 282); // Menggambar border dalam

    }

    // Footer untuk PDF
    function Footer()
    {
        $this->SetY(-10); // Mengatur posisi footer
        $this->SetFont('Arial', 'I', 8); // Mengatur font untuk footer
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo(), 0, 0, 'C'); // Nomor halaman
    }
}

// Membuat instance dari PDF
$pdf = new PDF();
$pdf->AddPage(); // Menambahkan halaman baru



// Cek koneksi
if ($connKendaraan->connect_error) {
    die("Koneksi gagal: " . $connKendaraan->connect_error);
}

// Mengambil ID dari URL dan memvalidasi
$id = isset($_GET['id']) ? intval($_GET['id']) : 0; // Mengambil ID dari URL


$stmtpenumpang = $connKendaraan->prepare("SELECT * FROM penumpang WHERE pemohon_id=?");
$stmtpenumpang->bind_param("i", $id);
$stmtpenumpang->execute();
$resultpenumpang = $stmtpenumpang->get_result(); // Mengambil hasil query

$namaPenumpang = array();

// Mengeksekusi query untuk mendapatkan penumpang
if ($stmtpenumpang->execute()) {
    $resultpenumpang = $stmtpenumpang->get_result(); // Mengambil hasil query

    if ($resultpenumpang->num_rows > 0) {
        // Menampilkan data penumpang dan mengumpulkannya dalam array
        while ($penumpang = $resultpenumpang->fetch_assoc()) {
            // Ganti 'nama' dengan nama kolom yang sesuai dari tabel penumpang
            $namaPenumpang[] = getNameFromUsers($conn, $penumpang['npk_penumpang']); // Menambahkan nama ke dalam array
        }

        // Menggabungkan array menjadi string dengan nomor urut
        $namaPenumpangWithNumbers = '';
        foreach ($namaPenumpang as $index => $nama) {
            $nomorUrut = $index + 1; // Menghitung nomor urut
            $namaPenumpangWithNumbers .=  '  ' . $nomorUrut . '. ' . $nama . "\n"; // Menambahkan nomor urut dan nama
        }

        // Jika tidak ada penumpang, tampilkan pesan
        if (empty($namaPenumpangWithNumbers)) {
            $namaPenumpangWithNumbers = 'Tidak ada penumpang.';
        }
    } else {
        $namaPenumpangWithNumbers = 'Tidak ada penumpang.'; // Jika tidak ada penumpang
    }
} else {
    echo "Error: " . $stmtpenumpang->error; // Tampilkan kesalahan jika ada
}

// Mengambil data untuk tabel
$stmt = $connKendaraan->prepare("SELECT p.*, ap.*, tm.warna, tm.id, tm.`type`, tm.platmobil
FROM pemohon p
LEFT JOIN approval_pemohon ap ON p.id = ap.pemohon_id
LEFT JOIN typemobil tm ON p.type = tm.id
WHERE p.id = ?;");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result(); // Mengambil hasil query

if ($result->num_rows > 0) {
    // Mengambil data dari hasil query
    $row = $result->fetch_assoc();
    $row['nama_pemohon'] = getNameFromUsers($conn, $row['pemohon']);
    $row['nama_pengemudi'] = getNameFromUsers($conn, $row['pengemudi']);
    $row['nama_kadept'] = getNameFromUsers($conn, $row['npk_kadept']);
    $row['nama_direksi'] = getNameFromUsers($conn, $row['npk_direksi']);
    $row['nama_ga'] = getNameFromUsers($conn, $row['npk_ga']);
    $status = $row['status'];
    $urgent = $row['urgent'];
    $type = $row['type'];
    $warna = $row['warna'];
    $platmobil = $row['platmobil'];

    // Menampilkan data dari database ke dalam format formulir
    $pdf->SetFont('Arial', 'B', 10);

    session_start();

    // Mendapatkan data untuk QR code
    $dataPemohon = "Nama Pemohon: {$row['nama_pemohon']}\nNPK: {$row['pemohon']}";
    $dataKadept = "Nama Kadept: {$row['nama_kadept']}\nNPK: {$row['npk_kadept']}";
    $dataDireksi = "Nama Direksi: {$row['nama_direksi']}\nNPK: {$row['npk_direksi']}";
    $dataGapool = "Nama Gapool: {$row['nama_ga']}\nNPK: {$row['npk_ga']}";

    // Path untuk menyimpan QR code image di folder fotoqr
    $pemohon = 'fotoqr/qrpemohon.png';
    $kadept = 'fotoqr/qrkadept.png';
    $direksi = 'fotoqr/qrdireksi.png';
    $gapool = 'fotoqr/qrgapool.png';

    // Pastikan folder fotoqr sudah ada, jika belum, buat folder terlebih dahulu
    if (!is_dir('fotoqr')) {
        mkdir('fotoqr', 0777, true); // Membuat folder fotoqr jika belum ada
    }

    // Membuat QR Code
    QRcode::png($dataPemohon, $pemohon, QR_ECLEVEL_L, 4, 4);
    QRcode::png($dataKadept, $kadept, QR_ECLEVEL_L, 4, 4);
    QRcode::png($dataDireksi, $direksi, QR_ECLEVEL_L, 4, 4);
    QRcode::png($dataGapool, $gapool, QR_ECLEVEL_L, 4, 4);

    // Menambahkan gambar QR ke PDF jika status lebih besar dari angka tertentu
    if ($status > 0) {
        $pdf->Image($pemohon, 169, 245, 17, 17); // Menambahkan gambar ke PDF
    }
    if ($status > 1) {
        $pdf->Image($kadept, 123, 245, 17, 17); // Menambahkan gambar ke PDF
    }

    if ($status > 2 && $urgent == 1) {
        $pdf->Image($direksi, 67, 245, 17, 17); // Menambahkan gambar ke PDF
    }
    if ($status > 3) {
        $pdf->Image($gapool, 17, 245, 17, 17); // Menambahkan gambar ke PDF
    }

    // if (file_exists($Kadept1)) {
    //     unlink($Kadept1); 
    // }


    // Mengatur posisi Y agar lebih ke atas
    $pdf->SetY(40); // Set posisi Y ke 35mm atau sesuaikan sesuai kebutuhan

    // Tampilkan data formulir
    $pdf->Cell(40, 6, 'NAMA PEMOHON', 0, 0);
    $pdf->Cell(80, 6, ': ' . $row['nama_pemohon'], 0, 0);
    $pdf->Cell(-20); // Menambahkan jarak horizontal antara dua bagian
    $pdf->Cell(40, 6, 'NPK/DEPARTEMEN', 0, 0);
    $pdf->Cell(80, 6, ': ' . $row['pemohon'], 0, 1);
    $y = $pdf->GetY(); // Mendapatkan posisi Y 
    $y -= 0.6;
    $pdf->Ln(0.1); // Jarak tambahan setelah data
    $pdf->SetLineWidth(0.5); // Set ketebalan garis
    $pdf->Line(52, $y, 110, $y); // Garis 
    $pdf->Line(152, $y, 200, $y); // Garis 
    $pdf->Ln(1); // Jarak 

    $pdf->Cell(40, 6, 'TANGGAL', 0, 0);
    $pdf->Cell(80, 6, ': ' . date("d-m-Y", strtotime($row['tanggal'])), 0, 0);
    $pdf->Cell(-20); // Menambahkan jarak horizontal antara dua bagian
    $pdf->Cell(40, 6, 'JAM PERGI/PULANG', 0, 0);
    $pdf->Cell(80, 6, ': ' . $row['jampergi'] . ' / ' . $row['jampulang'], 0, 1);
    $y = $pdf->GetY(); // Mendapatkan posisi Y 
    $y -= 0.6;
    $pdf->Ln(0.1); // Jarak tambahan setelah data
    $pdf->SetLineWidth(0.5); // Set ketebalan garis
    $pdf->Line(52, $y, 110, $y); // Garis 
    $pdf->Line(152, $y, 200, $y); // Garis 
    $pdf->Ln(1); // Jarak 

    $pdf->Cell(40, 6, 'TUJUAN', 0, 0);
    $pdf->Cell(120, 6, ': ' . $row['tujuan'], 0, 1);
    $y = $pdf->GetY(); // Mendapatkan posisi Y 
    $y -= 0.6;
    $pdf->Ln(0.1); // Jarak tambahan setelah data
    $pdf->SetLineWidth(0.5); // Set ketebalan garis
    $pdf->Line(52, $y, 200, $y); // Garis untuk
    $pdf->Ln(1);

    $pdf->Cell(40, 6, 'KEPERLUAN', 0, 0);
    $pdf->Cell(120, 6, ': ' . $row['keperluan'], 0, 1);
    $y = $pdf->GetY(); // Mendapatkan posisi Y 
    $y -= 0.6;
    $pdf->Ln(0.1); // Jarak tambahan setelah data
    $pdf->SetLineWidth(0.5); // Set ketebalan garis
    $pdf->Line(52, $y, 200, $y); // Garis 
    $pdf->Ln(1);


    $pdf->Cell(40, 6, 'KENDARAAN', 0, 0);
    $pdf->Cell(50, 6, ': ' . $row['kendaraan'], 0, 1);
    $y = $pdf->GetY();
    $y -= 0.6;
    $pdf->Ln(0.1);
    $pdf->SetLineWidth(0.5);
    $pdf->Line(52, $y, 110, $y);
    $pdf->Ln(1);

    $pdf->Cell(40, 6, 'TIPE KENDARAAN', 0, 0);
    $pdf->Cell(50, 6, ': ' . $row['type'] . ' ' . $row['warna'] . ' - (' . $row['platmobil'] . ')', 0, 1);
    $y = $pdf->GetY();
    $y -= 0.6;
    $pdf->Ln(0.1);
    $pdf->SetLineWidth(0.5);
    $pdf->Line(52, $y, 110, $y);
    $pdf->Ln(1);

    $pdf->Cell(40, 6, 'PENGEMUDI', 0, 0);
    $pdf->Cell(120, 6, ': ' . $row['pengemudi'] . " - " . $row['nama_pengemudi'], 0, 1);
    $y = $pdf->GetY(); // Mendapatkan posisi Y
    $y -= 0.6;
    $pdf->Ln(0.1); // Jarak
    $pdf->SetLineWidth(0.5); // Set ketebalan
    $pdf->Line(52, $y, 110, $y); // Garis 
    $pdf->Ln(1);

    $pdf->Cell(40, 6, 'PENUMPANG                 :', 0, 0);
    $pdf->MultiCell(120, 6, $namaPenumpangWithNumbers, 0, 'L');
    $y = $pdf->GetY(); // Mendapatkan posisi Y 
    $y -= 0.6;
    $pdf->Ln(0.1); // Jarak tambahan setelah data
    // $pdf->SetLineWidth(0.5); // Set ketebalan garis
    // $pdf->Line(52, $y, 200, $y); // Garis untuk
    // $pdf->Ln(130);

    //BEKASI
    $pdf->SetXY(130, 200);
    // Tampilkan data formulir
    $pdf->Cell(13, 8, 'BEKASI', 0, 0); // Label "BEKASI"
    $pdf->Cell(80, 8, ' , ' . date("d-m-Y", strtotime($row['tanggal'])), 0, 1); // Nilai tanggal
    $y = $pdf->GetY(); // Mendapatkan posisi Y 
    $y -= 1;
    $pdf->Ln(0.1); // Jarak
    $pdf->SetLineWidth(0.5); // Set ketebalan
    $pdf->Line(145, $y, 195, $y);
    $pdf->Ln(1);

    $pdf->SetY(230);
    // Tanda tangan
    $pdf->Cell(30, 6, 'DISETUJUI', 0, 0, 'L');
    $pdf->Cell(107, 6, 'MENGETAHUI', 0, 0, 'C');
    $pdf->Cell(34, 6, 'PEMOHON', 0, 1, 'R');
    $pdf->Cell(130, 6, '(Jika Urgent)', 0, 1, 'C');
    $pdf->Ln(25);

    $pdf->Cell(34, 4, '(GA - POLL KEND.)', 0, 0, 'L');
    $pdf->Cell(62, 4, 'DIREKSI', 0, 0, 'C');
    $pdf->Cell(50, 4, 'KADEPT', 0, 0, 'C');
    $pdf->Cell(34, 4, 'KARYAWAN', 0, 1, 'R');
    $y = $pdf->GetY();
    $y -= 5; // Mendapatkan posisi Y 
    $pdf->Ln(0.1); // Jarak
    $pdf->SetLineWidth(0.5); // Set ketebalan
    $pdf->Line(10, $y, 45, $y); // Garis 
    $pdf->Line(60, $y, 93, $y);
    $pdf->Line(110, $y, 150, $y);
    $pdf->Line(160, $y, 195, $y);
    $pdf->Ln(1);

    // Menyimpan PDF ke buffer
    $pdfOutput = $pdf->Output('S'); // Mengambil output sebagai string

    // Menampilkan PDF di browser
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="formulir_permohonan_kendaraan.pdf"');
    echo $pdfOutput; // Menampilkan PDF
} else {
    // Jika tidak ada data, tampilkan pesan
    echo "Data tidak ditemukan.";
}

$connKendaraan->close(); // Menutup koneksi database
