-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 08, 2025 at 05:38 AM
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
-- Table structure for table `b-anggota_kelompok`
--

CREATE TABLE `b-anggota_kelompok` (
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

-- --------------------------------------------------------

--
-- Table structure for table `b_admin`
--

CREATE TABLE `b_admin` (
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
-- Dumping data for table `b_admin`
--

INSERT INTO `b_admin` (`id`, `username`, `password`, `nama`, `email`, `role`, `is_active`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@lombabcf.com', 'super_admin', 1, '2025-09-03 02:17:43');

-- --------------------------------------------------------

--
-- Table structure for table `b_karya`
--

CREATE TABLE `b_karya` (
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
-- Table structure for table `b_kategori_lomba`
--

CREATE TABLE `b_kategori_lomba` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text,
  `timeline_pendaftaran` varchar(255) DEFAULT NULL,
  `timeline_seleksi` varchar(255) DEFAULT NULL,
  `timeline_pengumuman` varchar(255) DEFAULT NULL,
  `periode_pendaftaran` varchar(100) DEFAULT NULL,
  `peserta` varchar(100) DEFAULT NULL,
  `durasi` varchar(50) DEFAULT NULL,
  `tempat` varchar(100) DEFAULT NULL,
  `biaya` decimal(10,2) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `card_pic` varchar(255) DEFAULT NULL,
  `jenis_lomba` enum('individu','kelompok') DEFAULT 'individu',
  `max_peserta` int DEFAULT '1',
  `butuh_kartu_pelajar` tinyint(1) DEFAULT '0',
  `link_grup_wa` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `b_kategori_lomba`
--

INSERT INTO `b_kategori_lomba` (`id`, `nama`, `deskripsi`, `timeline_pendaftaran`, `timeline_seleksi`, `timeline_pengumuman`, `periode_pendaftaran`, `peserta`, `durasi`, `tempat`, `biaya`, `icon`, `card_pic`, `jenis_lomba`, `max_peserta`, `butuh_kartu_pelajar`, `link_grup_wa`, `status`, `created_at`) VALUES
(3, 'Robotik', 'Tuangkan ide kreatifmu dalam bentuk cerita pendek. Lomba ini memberikan kesempatan bagi siswa untuk mengekspresikan pikiran dan perasaan mereka.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 25000.00, 'fas fa-pen-fancy', NULL, 'kelompok', 3, 1, 'wa.meme', 'aktif', '2025-09-03 02:17:43'),
(5, 'Lomba Desain Grafis', 'Buat desain grafis yang menarik untuk poster kampanye lingkungan. Lomba ini mendorong siswa untuk bekerja sama dalam tim untuk menghasilkan desain yang kreatif dan bermakna.', '7 september - 16 januari', '20- jannuari - 30-desember', '29j anuari', '7 september - 3 januari', 'Siswa SMP - SMA SMK Sederajat', '4 jam', 'Smk budi luhur', 80000.00, 'fas fa-paint-brush', 'uploads/card_img/lomba_5_1757250611.jpg', 'individu', 2, 1, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'aktif', '2025-09-03 02:17:43');

-- --------------------------------------------------------

--
-- Table structure for table `b_pembayaran`
--

CREATE TABLE `b_pembayaran` (
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
-- Dumping data for table `b_pembayaran`
--

INSERT INTO `b_pembayaran` (`id`, `pendaftar_id`, `metode_pembayaran`, `jumlah`, `status`, `bukti_transfer`, `tanggal_pembayaran`, `created_at`) VALUES
('PAY20250903023140409', 'P202509034003', 'QRIS', 80000.00, 'pending', 'bukti_transfer1756866700_travis-scott-fish-meme-.jpg', NULL, '2025-09-03 02:31:40'),
('PAY20250905131818357', 'P202509059988', 'QRIS', 80000.00, 'pending', 'bukti_transfer1757078298_golshi-in-a-different-file-format.png', NULL, '2025-09-05 13:18:18');

-- --------------------------------------------------------

--
-- Table structure for table `b_pendaftar`
--

CREATE TABLE `b_pendaftar` (
  `id` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telepon` varchar(20) NOT NULL,
  `sekolah` varchar(100) NOT NULL,
  `kelas` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `kategori_lomba_id` int NOT NULL,
  `foto_kartu_pelajar` varchar(255) DEFAULT NULL,
  `status` enum('pending','confirmed','rejected') DEFAULT 'pending',
  `catatan_admin` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `b_pendaftar`
--

INSERT INTO `b_pendaftar` (`id`, `nama`, `email`, `telepon`, `sekolah`, `kelas`, `alamat`, `kategori_lomba_id`, `foto_kartu_pelajar`, `status`, `catatan_admin`, `created_at`) VALUES
('P202509034003', 'Dzulfikar Dava fachreza', 'davafachreza07@gmail.com', '087780195230', 'SMK BUDI LUHUR', 'VIII', 'Jl. Raden Saleh No.999, RT.001/RW.003, Karang Tengah, Kec. Karang Tengah, Kota Tangerang, Banten 15157', 5, 'P202509034003_1756866667_WhatsApp Image 2025-08-27 at 11.52.13_3896e19a.jpg', 'confirmed', NULL, '2025-09-03 02:31:07'),
('P202509059988', 'asdf', 'asdf@asdf.asdf', '123', 'asdf', 'asdf', 'AAAAAAaA', 5, 'P202509059988_1757078291_JavaScript-Emblem-2991555472.png', 'pending', NULL, '2025-09-05 13:18:11');

-- --------------------------------------------------------

--
-- Table structure for table `b_pengaturan`
--

CREATE TABLE `b_pengaturan` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nilai` text,
  `deskripsi` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `b_pengaturan`
--

INSERT INTO `b_pengaturan` (`id`, `nama`, `nilai`, `deskripsi`, `updated_at`) VALUES
(1, 'nama_event', 'BlueCreativeFestival 2025', 'Nama event lomba', '2025-09-03 07:11:21'),
(2, 'tema_event', 'Kreativitas Tanpa Batas', 'Tema event lomba', '2025-09-03 02:17:43'),
(3, 'tanggal_mulai_pendaftaran', '2024-01-01', 'Tanggal mulai pendaftaran', '2025-09-03 02:17:43'),
(4, 'tanggal_akhir_pendaftaran', '2024-02-15', 'Tanggal akhir pendaftaran', '2025-09-03 02:17:43'),
(5, 'tanggal_mulai_pengumpulan', '2024-02-16', 'Tanggal mulai pengumpulan karya', '2025-09-03 02:17:43'),
(6, 'tanggal_akhir_pengumpulan', '2024-03-01', 'Tanggal akhir pengumpulan karya', '2025-09-03 02:17:43'),
(7, 'tanggal_pengumuman', '2024-03-15', 'Tanggal pengumuman pemenang', '2025-09-03 02:17:43'),
(8, 'biaya_admin', '5000', 'Biaya administrasi', '2025-09-03 02:17:43'),
(9, 'email_contact', 'info@bluecreativefestival.com', 'Email kontak', '2025-09-03 02:17:43'),
(10, 'phone_contact', '+62 812-3456-7890', 'Nomor telepon kontak', '2025-09-03 02:17:43'),
(11, 'max_file_size', '10485760', 'Maksimal ukuran file upload (10MB)', '2025-09-03 02:17:43'),
(12, 'allowed_file_types', 'jpg,jpeg,png,pdf,doc,docx,mp4,mov,avi', 'Tipe file yang diizinkan', '2025-09-03 02:17:43'),
(13, 'require_student_card', '1', 'Wajib upload kartu pelajar (1=ya, 0=tidak)', '2025-09-03 02:17:43');

-- --------------------------------------------------------

--
-- Table structure for table `b_users`
--

CREATE TABLE `b_users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `sekolah` varchar(100) DEFAULT NULL,
  `kelas` varchar(255) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `alamat` text,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `b_users`
--

INSERT INTO `b_users` (`id`, `username`, `email`, `password`, `nama_lengkap`, `sekolah`, `kelas`, `telepon`, `alamat`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'angga', 'angga@gmail.com', '$2y$10$8gBadGCGtA4RM2/R3/Qp0eRdAw9kZ1pUFZR9ysALnTdXdiZi6iL9y', 'angga faot', 'SMK BUDI LUHUR', 'VII', '087780195230', 'Jl. Raden Saleh No.999, RT.001/RW.003, Karang Tengah, Kec. Karang Tengah, Kota Tangerang, Banten 15157', 1, '2025-09-03 05:04:22', '2025-09-03 05:04:22'),
(2, 'workshoptest1', 'workshoptest1@workshop.test', '$2y$12$GZE61CYVec/EPvkHV5YQQOloLiZyGizE5fEdMQEPillIlCm7PFKfS', 'workshop test1', 'smk workshop test tangerang', 'VII', '1234', '', 1, '2025-09-04 09:52:59', '2025-09-05 09:12:33'),
(6, 'mas', 'mas@gmail.com', '$2y$12$1gT/Eaud9iaMTFFzzc.oL.Kr10lTC10go2ZoYBO0HwB/gAfyQIJAq', 'mas', NULL, NULL, '123', 'mas123', 1, '2025-09-05 10:35:44', '2025-09-05 10:35:44'),
(7, 'asdf', 'asdf@asdf.asdf', '$2y$12$p2xeQBkfTCtE/RjKCV7wbO/4oQHDkqMe1mMN7ND9JbWHG73uXrvei', 'asdf', NULL, NULL, '1234', 'asdf', 1, '2025-09-05 10:36:28', '2025-09-05 10:36:28'),
(8, 'yangbeneraja', 'yangbeneraja@gmail.com', '$2y$12$lVngk5FzN2JNfMoeREraDelxM5/6ZwoHLQ3ksS1f1kkgxRLILVeuC', 'yang bener aja', NULL, NULL, '1234', 'asdf', 1, '2025-09-05 12:53:47', '2025-09-05 12:53:47');

-- --------------------------------------------------------

--
-- Table structure for table `b_user_pendaftaran`
--

CREATE TABLE `b_user_pendaftaran` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `pendaftar_id` varchar(20) NOT NULL,
  `kategori_lomba_id` int NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `tanggal_daftar` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tanggal_approval` timestamp NULL DEFAULT NULL,
  `catatan_admin` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `b_user_pendaftaran`
--

INSERT INTO `b_user_pendaftaran` (`id`, `user_id`, `pendaftar_id`, `kategori_lomba_id`, `status`, `tanggal_daftar`, `tanggal_approval`, `catatan_admin`) VALUES
(1, 2, 'P202509052160', 5, 'pending', '2025-09-05 09:39:48', NULL, NULL),
(2, 2, 'P202509051585', 3, 'pending', '2025-09-05 09:41:50', NULL, NULL),
(6, 6, 'P202509051428', 5, 'pending', '2025-09-05 10:51:06', NULL, NULL),
(7, 6, 'P202509053315', 3, 'pending', '2025-09-05 11:07:00', NULL, NULL),
(8, 8, 'P202509059988', 5, 'pending', '2025-09-05 13:18:11', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `b_webinar`
--

CREATE TABLE `b_webinar` (
  `id` int NOT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text,
  `pemateri` varchar(100) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `waktu` varchar(50) DEFAULT NULL,
  `lokasi` varchar(150) DEFAULT NULL,
  `kapasitas` int DEFAULT '0',
  `biaya` decimal(10,2) DEFAULT '0.00',
  `banner_path` varchar(255) DEFAULT NULL,
  `materi_pdf_path` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `b_webinar`
--

INSERT INTO `b_webinar` (`id`, `judul`, `deskripsi`, `pemateri`, `tanggal`, `waktu`, `lokasi`, `kapasitas`, `biaya`, `banner_path`, `materi_pdf_path`, `status`, `created_at`) VALUES
(1, 'Camera Movement', '123', 'Akbar', '2025-09-03', '09:00 - 12:00', 'SMK Budi Luhur', 30, 20000.00, NULL, NULL, 'aktif', '2025-09-03 07:33:15');

-- --------------------------------------------------------

--
-- Table structure for table `b_webinar_pendaftar`
--

CREATE TABLE `b_webinar_pendaftar` (
  `id` varchar(20) NOT NULL,
  `user_id` int NOT NULL,
  `webinar_id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telepon` varchar(20) NOT NULL,
  `institusi` varchar(100) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `b_webinar_pendaftar`
--

INSERT INTO `b_webinar_pendaftar` (`id`, `user_id`, `webinar_id`, `nama`, `email`, `telepon`, `institusi`, `status`, `metode_pembayaran`, `bukti_transfer`, `created_at`) VALUES
('WS25090308052852', 1, 1, 'angga faot', 'angga@gmail.com', '087780195230', 'SMK BUDI LUHUR', 'approved', 'QRIS', 'uploads/bukti_transfer/bukti_transfer_1756907154_travis-scott-fish-meme-.jpg', '2025-09-03 08:05:28'),
('WS25090409533726', 2, 1, 'workshop test', 'workshoptest@workshop.test', '', 'smk workshop test tangerang', 'pending', 'QRIS', 'uploads/bukti_transfer/bukti_transfer_1757064409_4c5-2295580792.jpg', '2025-09-04 09:53:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `b-anggota_kelompok`
--
ALTER TABLE `b-anggota_kelompok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_anggota_kelompok_pendaftar` (`pendaftar_id`);

--
-- Indexes for table `b_admin`
--
ALTER TABLE `b_admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `b_karya`
--
ALTER TABLE `b_karya`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pendaftar_id` (`pendaftar_id`),
  ADD KEY `idx_karya_status` (`status`);

--
-- Indexes for table `b_kategori_lomba`
--
ALTER TABLE `b_kategori_lomba`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kategori_status` (`status`),
  ADD KEY `idx_kategori_jenis` (`jenis_lomba`);

--
-- Indexes for table `b_pembayaran`
--
ALTER TABLE `b_pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pendaftar_id` (`pendaftar_id`),
  ADD KEY `idx_pembayaran_status` (`status`);

--
-- Indexes for table `b_pendaftar`
--
ALTER TABLE `b_pendaftar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pendaftar_email` (`email`),
  ADD KEY `idx_pendaftar_status` (`status`),
  ADD KEY `idx_pendaftar_kategori` (`kategori_lomba_id`);

--
-- Indexes for table `b_pengaturan`
--
ALTER TABLE `b_pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `b_users`
--
ALTER TABLE `b_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `b_user_pendaftaran`
--
ALTER TABLE `b_user_pendaftaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `kategori_lomba_id` (`kategori_lomba_id`);

--
-- Indexes for table `b_webinar`
--
ALTER TABLE `b_webinar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `b_webinar_pendaftar`
--
ALTER TABLE `b_webinar_pendaftar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `workshop_id` (`webinar_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `b-anggota_kelompok`
--
ALTER TABLE `b-anggota_kelompok`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `b_admin`
--
ALTER TABLE `b_admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `b_karya`
--
ALTER TABLE `b_karya`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `b_kategori_lomba`
--
ALTER TABLE `b_kategori_lomba`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `b_pengaturan`
--
ALTER TABLE `b_pengaturan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `b_users`
--
ALTER TABLE `b_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `b_user_pendaftaran`
--
ALTER TABLE `b_user_pendaftaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `b_webinar`
--
ALTER TABLE `b_webinar`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `b-anggota_kelompok`
--
ALTER TABLE `b-anggota_kelompok`
  ADD CONSTRAINT `b-anggota_kelompok_ibfk_1` FOREIGN KEY (`pendaftar_id`) REFERENCES `b_pendaftar` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `b_karya`
--
ALTER TABLE `b_karya`
  ADD CONSTRAINT `b_karya_ibfk_1` FOREIGN KEY (`pendaftar_id`) REFERENCES `b_pendaftar` (`id`);

--
-- Constraints for table `b_pembayaran`
--
ALTER TABLE `b_pembayaran`
  ADD CONSTRAINT `b_pembayaran_ibfk_1` FOREIGN KEY (`pendaftar_id`) REFERENCES `b_pendaftar` (`id`);

--
-- Constraints for table `b_pendaftar`
--
ALTER TABLE `b_pendaftar`
  ADD CONSTRAINT `b_pendaftar_ibfk_1` FOREIGN KEY (`kategori_lomba_id`) REFERENCES `b_kategori_lomba` (`id`);

--
-- Constraints for table `b_user_pendaftaran`
--
ALTER TABLE `b_user_pendaftaran`
  ADD CONSTRAINT `b_user_pendaftaran_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `b_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `b_user_pendaftaran_ibfk_2` FOREIGN KEY (`kategori_lomba_id`) REFERENCES `b_kategori_lomba` (`id`);

--
-- Constraints for table `b_webinar_pendaftar`
--
ALTER TABLE `b_webinar_pendaftar`
  ADD CONSTRAINT `b_webinar_pendaftar_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `b_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `b_webinar_pendaftar_ibfk_2` FOREIGN KEY (`webinar_id`) REFERENCES `b_webinar` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
