-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 17, 2024 at 08:44 AM
-- Server version: 10.1.38-MariaDB
-- PHP Version: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kendaraan`
--

-- --------------------------------------------------------

--
-- Table structure for table `approval_pemohon`
--

CREATE TABLE `approval_pemohon` (
  `id` int(11) NOT NULL,
  `pemohon_id` int(11) NOT NULL,
  `npk_kadept` varchar(7) DEFAULT NULL,
  `remark_kadept` varchar(255) DEFAULT NULL,
  `approval_date` datetime DEFAULT NULL,
  `npk_direksi` varchar(7) DEFAULT NULL,
  `remark_direksi` varchar(255) DEFAULT NULL,
  `date_direksi` datetime DEFAULT NULL,
  `npk_ga` varchar(7) DEFAULT NULL,
  `remark_ga` varchar(255) DEFAULT NULL,
  `date_ga` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `jadwalmobil`
--

CREATE TABLE `jadwalmobil` (
  `id` int(11) NOT NULL,
  `pemohon_id` int(11) NOT NULL,
  `mobil_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jampergi` time DEFAULT NULL,
  `jampulang` time DEFAULT NULL,
  `tanggal_pulang` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `jadwalmobil`
--

INSERT INTO `jadwalmobil` (`id`, `pemohon_id`, `mobil_id`, `tanggal`, `jampergi`, `jampulang`, `tanggal_pulang`) VALUES
(29, 174, 2, '2024-12-17', '12:00:00', '13:40:00', '2024-12-17');

-- --------------------------------------------------------

--
-- Table structure for table `notification_push`
--

CREATE TABLE `notification_push` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(14) NOT NULL,
  `message` varchar(255) NOT NULL,
  `flags` varchar(10) NOT NULL,
  `insert_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `notification_push`
--

INSERT INTO `notification_push` (`id`, `phone_number`, `message`, `flags`, `insert_date`) VALUES
(218, '628951212122', 'Terdapat permohonan peminjaman kendaraan dari 11111 - MIS Zafarrr. Membutuhkan approval Anda!', 'queue', '2024-12-17 09:32:21'),
(219, '628951212122', 'Terdapat permohonan peminjaman kendaraan dari 11111 - MIS Zafarrr. Membutuhkan approval Anda!', 'queue', '2024-12-17 09:32:32'),
(220, '6289528541244', 'Terdapat permohonan peminjaman kendaraan dari 11111 MIS Zafarrr. Membutuhkan approval Anda!', 'queue', '2024-12-17 09:33:18'),
(221, '628951212122', 'Permohonan peminjaman mobil Anda sedang diproses dan membutuhkan approval dari GA Pool.', 'queue', '2024-12-17 09:33:18'),
(222, '628910020012', 'Terdapat permohonan peminjaman kendaraan dari 11111 MIS Zafarrr. Membutuhkan approval Anda!', 'queue', '2024-12-17 09:33:27'),
(223, '628951212122', 'Permohonan peminjaman mobil Anda sedang diproses dan membutuhkan approval dari Direksi.', 'queue', '2024-12-17 09:33:27'),
(224, '628492991999', 'Permohonan peminjaman mobil Anda telah disetujui oleh Direksi dan sedang diproses oleh GA Pool.', 'queue', '2024-12-17 09:37:41'),
(225, '6289528541244', 'Permohonan peminjaman kendaraan dari 11111 - MIS Zafarrr telah disetujui. Harap lakukan persetujuan lebih lanjut.', 'queue', '2024-12-17 09:37:41'),
(226, '628492991999', 'Persetujuan anda telah di Approve oleh 44444 - zafarrrrrrrrrrrrrrrrrrrrrrrrrrGA. Mobil yang dipinjamkan adalah VELOZ byon - B 02 IN - HITAM. Berdoa dahulu dan Selamat sampai tujuan.', 'queue', '2024-12-17 09:39:01'),
(227, '628492991999', 'Persetujuan anda telah di Approve oleh 44444 - zafarrrrrrrrrrrrrrrrrrrrrrrrrrGA. Mobil yang dipinjamkan adalah AVANZA REBORN - B 00 WIK - MERAH. Berdoa dahulu dan Selamat sampai tujuan.', 'queue', '2024-12-17 10:41:17'),
(228, '628492991999', 'Persetujuan anda telah di Approve oleh 44444 - zafarrrrrrrrrrrrrrrrrrrrrrrrrrGA. Mobil yang dipinjamkan adalah AVANZA REBORN - B 00 WIK - MERAH. Berdoa dahulu dan Selamat sampai tujuan.', 'queue', '2024-12-17 11:37:55'),
(229, '628951212122', 'Terdapat permohonan peminjaman kendaraan dari 11111 - MIS Zafarrr. Membutuhkan approval Anda!', 'queue', '2024-12-17 13:34:21'),
(230, '6289528541244', 'Terdapat permohonan peminjaman kendaraan dari 11111 MIS Zafarrr. Membutuhkan approval Anda!', 'queue', '2024-12-17 13:35:06'),
(231, '628951212122', 'Permohonan peminjaman mobil Anda sedang diproses dan membutuhkan approval dari GA Pool.', 'queue', '2024-12-17 13:35:06'),
(232, '628492991999', 'Persetujuan anda telah di Approve oleh 44444 - zafarrrrrrrrrrrrrrrrrrrrrrrrrrGA. Mobil yang dipinjamkan adalah INOVA ZENIX - B 26 RAW - PUTIH. Berdoa dahulu dan Selamat sampai tujuan.', 'queue', '2024-12-17 13:35:50');

-- --------------------------------------------------------

--
-- Table structure for table `otp_authentication`
--

CREATE TABLE `otp_authentication` (
  `id` int(11) NOT NULL,
  `npk` varchar(6) NOT NULL,
  `phone_number` varchar(14) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `expiry_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `send` int(11) NOT NULL,
  `send_date` datetime DEFAULT NULL,
  `use` int(11) NOT NULL,
  `use_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `otp_authentication`
--

INSERT INTO `otp_authentication` (`id`, `npk`, `phone_number`, `otp`, `expiry_date`, `send`, `send_date`, `use`, `use_date`) VALUES
(177, '22222', '628951212122', '619461', '2024-12-17 13:39:49', 2, NULL, 1, '2024-12-17 13:35:01'),
(179, '11111', '628492991999', '313601', '2024-12-17 13:38:07', 2, NULL, 1, '2024-12-17 13:33:26'),
(180, '44444', '6289528541244', '837567', '2024-12-17 13:40:22', 2, NULL, 1, '2024-12-17 13:35:37'),
(181, '33333', '628910020012', '811474', '2024-12-17 09:38:45', 2, NULL, 1, '2024-12-17 09:33:58');

-- --------------------------------------------------------

--
-- Table structure for table `pemohon`
--

CREATE TABLE `pemohon` (
  `id` int(255) NOT NULL,
  `tanggal` date NOT NULL,
  `jampergi` time NOT NULL,
  `jampulang` time NOT NULL,
  `tujuan` varchar(255) NOT NULL,
  `keperluan` varchar(255) NOT NULL,
  `kendaraan` varchar(50) NOT NULL,
  `type` int(11) DEFAULT NULL,
  `pengemudi` varchar(255) NOT NULL,
  `pemohon` varchar(10) NOT NULL,
  `dept_pemohon` varchar(50) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `tanggal_dibuat` datetime NOT NULL,
  `urgent` int(11) NOT NULL,
  `tanggal_pulang` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pemohon`
--

INSERT INTO `pemohon` (`id`, `tanggal`, `jampergi`, `jampulang`, `tujuan`, `keperluan`, `kendaraan`, `type`, `pengemudi`, `pemohon`, `dept_pemohon`, `status`, `tanggal_dibuat`, `urgent`, `tanggal_pulang`) VALUES
(174, '2024-12-17', '12:00:00', '13:40:00', 'PT ', 'PT', 'Mobil', 2, '01878', '11111', 'PROD2', '4', '2024-12-17 13:34:14', 0, '2024-12-17');

-- --------------------------------------------------------

--
-- Table structure for table `penumpang`
--

CREATE TABLE `penumpang` (
  `id` int(11) NOT NULL,
  `pemohon_id` int(11) DEFAULT NULL,
  `npk_penumpang` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `penumpang`
--

INSERT INTO `penumpang` (`id`, `pemohon_id`, `npk_penumpang`) VALUES
(185, 174, '01466'),
(186, 174, '01537');

-- --------------------------------------------------------

--
-- Table structure for table `typemobil`
--

CREATE TABLE `typemobil` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `platmobil` varchar(10) NOT NULL,
  `warna` varchar(50) NOT NULL,
  `status` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `typemobil`
--

INSERT INTO `typemobil` (`id`, `type`, `platmobil`, `warna`, `status`) VALUES
(1, 'AVANZA REBORN', 'B 00 WIK', 'MERAH', 0),
(2, 'INOVA ZENIX', 'B 26 RAW', 'PUTIH', 0),
(3, 'SUZUKI SWIFT', 'B 01 KIK', 'BIRU', 0),
(4, 'YARIS CROS', 'B 69 FKT', 'HIJAU', 0),
(5, 'VELOZ byon', 'B 02 IN', 'HITAM', 0),
(6, 'JEEP 4X4', 'B 101 SS', 'MERAH', 0),
(7, 'AGYA GR SS', 'B 99 OH', 'MERAH ', 0),
(8, 'MINI BUSS', 'F 76 KYB', 'HITAM', 0),
(9, 'MAZDA RX7', 'B 97 N', 'BIRU', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `approval_pemohon`
--
ALTER TABLE `approval_pemohon`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pemohon_id_2` (`pemohon_id`),
  ADD KEY `pemohon_id` (`pemohon_id`);

--
-- Indexes for table `jadwalmobil`
--
ALTER TABLE `jadwalmobil`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification_push`
--
ALTER TABLE `notification_push`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `otp_authentication`
--
ALTER TABLE `otp_authentication`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pemohon`
--
ALTER TABLE `pemohon`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `penumpang`
--
ALTER TABLE `penumpang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pemohon_id` (`pemohon_id`);

--
-- Indexes for table `typemobil`
--
ALTER TABLE `typemobil`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `approval_pemohon`
--
ALTER TABLE `approval_pemohon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jadwalmobil`
--
ALTER TABLE `jadwalmobil`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `notification_push`
--
ALTER TABLE `notification_push`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=233;

--
-- AUTO_INCREMENT for table `otp_authentication`
--
ALTER TABLE `otp_authentication`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=182;

--
-- AUTO_INCREMENT for table `pemohon`
--
ALTER TABLE `pemohon`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- AUTO_INCREMENT for table `penumpang`
--
ALTER TABLE `penumpang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=187;

--
-- AUTO_INCREMENT for table `typemobil`
--
ALTER TABLE `typemobil`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pemohon`
--
ALTER TABLE `pemohon`
  ADD CONSTRAINT `pemohon_ibfk_1` FOREIGN KEY (`type`) REFERENCES `typemobil` (`id`);

--
-- Constraints for table `penumpang`
--
ALTER TABLE `penumpang`
  ADD CONSTRAINT `penumpang_ibfk_1` FOREIGN KEY (`pemohon_id`) REFERENCES `pemohon` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
