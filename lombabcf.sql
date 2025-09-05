-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 01, 2025 at 10:07 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lombabcf`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('super_admin','admin','jury') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `nama`, `email`, `role`, `is_active`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@lombabcf.com', 'super_admin', 1, '2025-08-31 14:10:51');

-- --------------------------------------------------------

--
-- Table structure for table `anggota_kelompok`
--

CREATE TABLE `anggota_kelompok` (
  `id` int NOT NULL,
  `pendaftar_id` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `sekolah` varchar(100) NOT NULL,
  `kelas` enum('VII','VIII','IX') NOT NULL,
  `foto_kartu_pelajar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `anggota_kelompok`
--

INSERT INTO `anggota_kelompok` (`id`, `pendaftar_id`, `nama`, `email`, `telepon`, `sekolah`, `kelas`, `foto_kartu_pelajar`, `created_at`) VALUES
(5, 'P202509012848', 'awdawda', 'davafachreza07@gmail.com', '087780195230', 'smk budi luhur', 'VIII', 'P202509012848_anggota_1_1756690004_3.png', '2025-09-01 01:26:44'),
(6, 'P202509012848', 'dava fachreza', 'davafachreza07@gmail.com', '087780195230', 'smk budi luhur', 'VIII', 'P202509012848_anggota_2_1756690004_3.png', '2025-09-01 01:26:44');

-- --------------------------------------------------------

--
-- Table structure for table `karya`
--

CREATE TABLE `karya` (
  `id` int NOT NULL,
  `pendaftar_id` varchar(20) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `deskripsi` text,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `status` enum('submitted','reviewed','accepted','rejected') DEFAULT 'submitted',
  `nilai` decimal(5,2) DEFAULT NULL,
  `komentar` text,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori_lomba`
--

CREATE TABLE `kategori_lomba` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text,
  `biaya` decimal(10,2) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `jenis_lomba` enum('individu','kelompok') DEFAULT 'individu',
  `max_peserta` int DEFAULT '1',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori_lomba`
--

INSERT INTO `kategori_lomba` (`id`, `nama`, `deskripsi`, `biaya`, `icon`, `jenis_lomba`, `max_peserta`, `status`, `created_at`) VALUES
(1, 'Lomba Menggambar', 'Tunjukkan kreativitasmu dalam menggambar dengan tema \"Masa Depan Indonesia\"', 50000.00, 'fas fa-palette', 'individu', 3, 'aktif', '2025-08-31 14:10:51'),
(2, 'Lomba Fotografi', 'Abadikan momen indah dengan tema \"Keindahan Alam Sekitar\"', 75000.00, 'fas fa-camera', 'individu', 1, 'aktif', '2025-08-31 14:10:51'),
(3, 'Lomba Menulis', 'Tuangkan ide kreatifmu dalam bentuk cerita pendek', 25000.00, 'fas fa-pen-fancy', 'individu', 1, 'aktif', '2025-08-31 14:10:51'),
(5, 'Lomba Menggambar Digital', 'Tunjukkan kreativitasmu dalam menggambar digital dengan tema \"Masa Depan Indonesia yang Hijau dan Berkelanjutan\". Peserta dapat menggunakan software menggambar digital seperti Photoshop, Procreate, atau aplikasi menggambar lainnya.', 75000.00, 'fas fa-palette', 'individu', 1, 'aktif', '2025-09-01 00:57:49'),
(6, 'Lomba Menulis Cerita Pendek', 'Tuangkan ide kreatifmu dalam bentuk cerita pendek dengan tema \"Petualangan di Indonesia\". Peserta dapat menulis cerita fiksi atau non-fiksi yang menggambarkan petualangan menarik di berbagai tempat di Indonesia.', 25000.00, 'fas fa-pen-fancy', 'individu', 1, 'aktif', '2025-09-01 00:57:49'),
(7, 'Lomba Video Pendek', 'Buat video pendek kreatif dengan tema \"Kreativitas Tanpa Batas\". Peserta dapat membuat video dokumenter, vlog, atau video kreatif lainnya yang menampilkan bakat dan kreativitas mereka.', 100000.00, 'fas fa-video', 'kelompok', 3, 'aktif', '2025-09-01 00:57:49'),
(8, 'Lomba Desain Poster', 'Buat poster yang menarik dan informatif dengan tema \"Kampanye Lingkungan Hidup\". Peserta dapat menggunakan berbagai teknik desain untuk membuat poster yang efektif dalam menyampaikan pesan lingkungan.', 60000.00, 'fas fa-paint-brush', 'kelompok', 2, 'aktif', '2025-09-01 00:57:49');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id` varchar(20) NOT NULL,
  `pendaftar_id` varchar(20) NOT NULL,
  `metode_pembayaran` varchar(50) NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `tanggal_pembayaran` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id`, `pendaftar_id`, `metode_pembayaran`, `jumlah`, `status`, `bukti_transfer`, `tanggal_pembayaran`, `created_at`) VALUES
('PAY20250901012647594', 'P202509012848', 'gopay', 100000.00, 'pending', NULL, NULL, '2025-09-01 01:26:47');

-- --------------------------------------------------------

--
-- Table structure for table `pendaftar`
--

CREATE TABLE `pendaftar` (
  `id` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telepon` varchar(20) NOT NULL,
  `sekolah` varchar(100) NOT NULL,
  `kelas` enum('VII','VIII','IX') NOT NULL,
  `alamat` text NOT NULL,
  `kategori_lomba_id` int NOT NULL,
  `foto_kartu_pelajar` varchar(255) DEFAULT NULL,
  `status` enum('pending','confirmed','rejected') DEFAULT 'pending',
  `catatan_admin` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pendaftar`
--

INSERT INTO `pendaftar` (`id`, `nama`, `email`, `telepon`, `sekolah`, `kelas`, `alamat`, `kategori_lomba_id`, `foto_kartu_pelajar`, `status`, `catatan_admin`, `created_at`) VALUES
('P202509012848', 'dava', 'davafachreza07@gmail.com', '087780195230', 'smk budi luhur', 'VII', 'wewe43rqw3rwerwe', 7, 'P202509012848_1756690004_4.png', 'confirmed', NULL, '2025-09-01 01:26:44'),
('P202509017553', 'dawdawdawd', 'davafachreza07@gmail.com', '087780195230', 'smk budi luhur', 'VII', 'wdawdawdawd', 1, 'P202509017553_1756689684_3.png', 'pending', NULL, '2025-09-01 01:21:24'),
('P202509018301', 'dawdawdawd', 'davafachreza07@gmail.com', '087780195230', 'smk budi luhur', 'VII', 'wdawdawdawd', 1, 'P202509018301_1756689888_3.png', 'pending', NULL, '2025-09-01 01:24:48'),
('P202509019344', 'dava', 'davafachreza07@gmail.com', '087780195230', 'smk budi luhur', 'VII', 'dawdawdawd', 1, 'P202509019344_1756689666_3.png', 'pending', NULL, '2025-09-01 01:21:06'),
('TEST20250901012156', 'Test User', 'test@example.com', '08123456789', 'Test School', 'VII', 'Test Address', 1, '', 'rejected', NULL, '2025-09-01 01:21:56');

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nilai` text,
  `deskripsi` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `nama`, `nilai`, `deskripsi`, `updated_at`) VALUES
(1, 'nama_event', 'LombaBCF 2024', 'Nama event lomba', '2025-08-31 14:10:51'),
(2, 'tema_event', 'Kreativitas Tanpa Batas', 'Tema event lomba', '2025-08-31 14:10:51'),
(3, 'tanggal_mulai_pendaftaran', '2024-01-01', 'Tanggal mulai pendaftaran', '2025-08-31 14:10:51'),
(4, 'tanggal_akhir_pendaftaran', '2024-02-15', 'Tanggal akhir pendaftaran', '2025-08-31 14:10:51'),
(5, 'tanggal_mulai_pengumpulan', '2024-02-16', 'Tanggal mulai pengumpulan karya', '2025-08-31 14:10:51'),
(6, 'tanggal_akhir_pengumpulan', '2024-03-01', 'Tanggal akhir pengumpulan karya', '2025-08-31 14:10:51'),
(7, 'tanggal_pengumuman', '2024-03-15', 'Tanggal pengumuman pemenang', '2025-08-31 14:10:51'),
(8, 'biaya_admin', '5000', 'Biaya administrasi', '2025-08-31 14:10:51'),
(9, 'email_contact', 'info@lombabcf.com', 'Email kontak', '2025-08-31 14:10:51'),
(10, 'phone_contact', '+62 812-3456-7890', 'Nomor telepon kontak', '2025-08-31 14:10:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `anggota_kelompok`
--
ALTER TABLE `anggota_kelompok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pendaftar_id` (`pendaftar_id`);

--
-- Indexes for table `karya`
--
ALTER TABLE `karya`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pendaftar_id` (`pendaftar_id`),
  ADD KEY `idx_karya_status` (`status`);

--
-- Indexes for table `kategori_lomba`
--
ALTER TABLE `kategori_lomba`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pendaftar_id` (`pendaftar_id`),
  ADD KEY `idx_pembayaran_status` (`status`);

--
-- Indexes for table `pendaftar`
--
ALTER TABLE `pendaftar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pendaftar_email` (`email`),
  ADD KEY `idx_pendaftar_status` (`status`),
  ADD KEY `idx_pendaftar_kategori` (`kategori_lomba_id`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `anggota_kelompok`
--
ALTER TABLE `anggota_kelompok`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `karya`
--
ALTER TABLE `karya`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori_lomba`
--
ALTER TABLE `kategori_lomba`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `anggota_kelompok`
--
ALTER TABLE `anggota_kelompok`
  ADD CONSTRAINT `anggota_kelompok_ibfk_1` FOREIGN KEY (`pendaftar_id`) REFERENCES `pendaftar` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `karya`
--
ALTER TABLE `karya`
  ADD CONSTRAINT `karya_ibfk_1` FOREIGN KEY (`pendaftar_id`) REFERENCES `pendaftar` (`id`);

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`pendaftar_id`) REFERENCES `pendaftar` (`id`);

--
-- Constraints for table `pendaftar`
--
ALTER TABLE `pendaftar`
  ADD CONSTRAINT `pendaftar_ibfk_1` FOREIGN KEY (`kategori_lomba_id`) REFERENCES `kategori_lomba` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
