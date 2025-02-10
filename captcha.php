<?php
session_start();
header('Content-Type: image/png');

// Konfigurasi
$charCount = 6; // Jumlah karakter dalam CAPTCHA
$charWidth = 20; // Lebar setiap karakter
$padding = 45; // Padding di sekitar gambar
$imageWidth = $charCount * $charWidth + $padding; // Total lebar gambar
$imageHeight = 70; // Tinggi gambar (ditingkatkan)


// Buat gambar baru
$image = imagecreatetruecolor($imageWidth, $imageHeight);

$backgroundColor =  imagecolorallocate($image, rand(200, 255), rand(200, 255), rand(200, 255));
$textColor = imagecolorallocate($image, 0, 0, 0);
$dotColor = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255)); // Warna acak


// Isi gambar dengan warna latar belakang
imagefilledrectangle($image, 0, 0, $imageWidth, $imageHeight, $backgroundColor);

// Tambahkan titik acak untuk gangguan visual
for ($i = 0; $i < 50; $i++) {
    imagesetpixel($image, rand(0, $imageWidth), rand(0, $imageHeight), $dotColor);
}

// Buat kode random untuk CAPTCHA (6 karakter)
$captchaCode = substr(str_shuffle("123456789"), 0, $charCount);

// Simpan kode CAPTCHA di sesi
$_SESSION['captcha'] = $captchaCode;

// Jalur ke file font TTF Roboto (ganti dengan jalur yang sesuai di sistem Anda)
$fontPath = 'Roboto/Roboto-Regular.ttf'; // Pastikan jalur font sudah benar

// Tulis setiap karakter dengan posisi acak menggunakan imagettftext()
for ($i = 0; $i < strlen($captchaCode); $i++) {
    // Posisi x dan y acak untuk setiap karakter
    $x = $padding / 2 + ($i * $charWidth) + rand(-5, 5); // Atur posisi x acak dengan sedikit variasi
    $y = rand(40, 60); // Atur posisi y acak untuk memastikan teks berada di tengah
    imagettftext($image, 25, 0, $x, $y, $textColor, $fontPath, $captchaCode[$i]); // Gunakan ukuran font 20
}

// Output gambar
imagepng($image);
imagedestroy($image);
