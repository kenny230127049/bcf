/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-12.0.2-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: lombabcf
-- ------------------------------------------------------
-- Server version	12.0.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('super_admin','admin','jury') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `admin` VALUES
(1,'admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Administrator','admin@lombabcf.com','super_admin',1,'2025-09-03 02:17:43');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `anggota_kelompok`
--

DROP TABLE IF EXISTS `anggota_kelompok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `anggota_kelompok` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pendaftar_id` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `sekolah` varchar(100) NOT NULL,
  `kelas` enum('VII','VIII','IX') NOT NULL,
  `foto_kartu_pelajar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_anggota_kelompok_pendaftar` (`pendaftar_id`),
  CONSTRAINT `anggota_kelompok_ibfk_1` FOREIGN KEY (`pendaftar_id`) REFERENCES `pendaftar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anggota_kelompok`
--

LOCK TABLES `anggota_kelompok` WRITE;
/*!40000 ALTER TABLE `anggota_kelompok` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `anggota_kelompok` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `karya`
--

DROP TABLE IF EXISTS `karya`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `karya` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pendaftar_id` varchar(20) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `status` enum('submitted','reviewed','accepted','rejected') DEFAULT 'submitted',
  `nilai` decimal(5,2) DEFAULT NULL,
  `komentar` text DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pendaftar_id` (`pendaftar_id`),
  KEY `idx_karya_status` (`status`),
  CONSTRAINT `karya_ibfk_1` FOREIGN KEY (`pendaftar_id`) REFERENCES `pendaftar` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `karya`
--

LOCK TABLES `karya` WRITE;
/*!40000 ALTER TABLE `karya` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `karya` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `kategori_lomba`
--

DROP TABLE IF EXISTS `kategori_lomba`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kategori_lomba` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `periode_pendaftaran` varchar(100) DEFAULT NULL,
  `peserta` varchar(100) DEFAULT NULL,
  `durasi` varchar(50) DEFAULT NULL,
  `tempat` varchar(100) DEFAULT NULL,
  `biaya` decimal(10,2) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `jenis_lomba` enum('individu','kelompok') DEFAULT 'individu',
  `max_peserta` int(11) DEFAULT 1,
  `butuh_kartu_pelajar` tinyint(1) DEFAULT 0,
  `link_grup_wa` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_kategori_status` (`status`),
  KEY `idx_kategori_jenis` (`jenis_lomba`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kategori_lomba`
--

LOCK TABLES `kategori_lomba` WRITE;
/*!40000 ALTER TABLE `kategori_lomba` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `kategori_lomba` VALUES
(3,'Robotik','Tuangkan ide kreatifmu dalam bentuk cerita pendek. Lomba ini memberikan kesempatan bagi siswa untuk mengekspresikan pikiran dan perasaan mereka.',NULL,NULL,NULL,NULL,25000.00,'fas fa-pen-fancy','kelompok',3,1,'wa.meme','aktif','2025-09-03 02:17:43'),
(5,'Lomba Desain Grafis','Buat desain grafis yang menarik untuk poster kampanye lingkungan. Lomba ini mendorong siswa untuk bekerja sama dalam tim untuk menghasilkan desain yang kreatif dan bermakna.','7 september - 3 januari','Siswa SMP - SMA SMK Sederajat','4 jam','Smk budi luhur',80000.00,'fas fa-paint-brush','individu',2,1,'https://www.youtube.com/watch?v=dQw4w9WgXcQ','aktif','2025-09-03 02:17:43');
/*!40000 ALTER TABLE `kategori_lomba` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `pembayaran`
--

DROP TABLE IF EXISTS `pembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembayaran` (
  `id` varchar(20) NOT NULL,
  `pendaftar_id` varchar(20) NOT NULL,
  `metode_pembayaran` varchar(50) NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `tanggal_pembayaran` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pendaftar_id` (`pendaftar_id`),
  KEY `idx_pembayaran_status` (`status`),
  CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`pendaftar_id`) REFERENCES `pendaftar` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembayaran`
--

LOCK TABLES `pembayaran` WRITE;
/*!40000 ALTER TABLE `pembayaran` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `pembayaran` VALUES
('PAY20250903023140409','P202509034003','QRIS',80000.00,'pending','bukti_transfer1756866700_travis-scott-fish-meme-.jpg',NULL,'2025-09-03 02:31:40'),
('PAY20250905131818357','P202509059988','QRIS',80000.00,'pending','bukti_transfer1757078298_golshi-in-a-different-file-format.png',NULL,'2025-09-05 13:18:18');
/*!40000 ALTER TABLE `pembayaran` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `pendaftar`
--

DROP TABLE IF EXISTS `pendaftar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pendaftar` (
  `id` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telepon` varchar(20) NOT NULL,
  `sekolah` varchar(100) NOT NULL,
  `kelas` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `kategori_lomba_id` int(11) NOT NULL,
  `foto_kartu_pelajar` varchar(255) DEFAULT NULL,
  `status` enum('pending','confirmed','rejected') DEFAULT 'pending',
  `catatan_admin` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pendaftar_email` (`email`),
  KEY `idx_pendaftar_status` (`status`),
  KEY `idx_pendaftar_kategori` (`kategori_lomba_id`),
  CONSTRAINT `pendaftar_ibfk_1` FOREIGN KEY (`kategori_lomba_id`) REFERENCES `kategori_lomba` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pendaftar`
--

LOCK TABLES `pendaftar` WRITE;
/*!40000 ALTER TABLE `pendaftar` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `pendaftar` VALUES
('P202509034003','Dzulfikar Dava fachreza','davafachreza07@gmail.com','087780195230','SMK BUDI LUHUR','VIII','Jl. Raden Saleh No.999, RT.001/RW.003, Karang Tengah, Kec. Karang Tengah, Kota Tangerang, Banten 15157',5,'P202509034003_1756866667_WhatsApp Image 2025-08-27 at 11.52.13_3896e19a.jpg','confirmed',NULL,'2025-09-03 02:31:07'),
('P202509059988','asdf','asdf@asdf.asdf','123','asdf','asdf','AAAAAAaA',5,'P202509059988_1757078291_JavaScript-Emblem-2991555472.png','pending',NULL,'2025-09-05 13:18:11');
/*!40000 ALTER TABLE `pendaftar` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `pengaturan`
--

DROP TABLE IF EXISTS `pengaturan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `nilai` text DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengaturan`
--

LOCK TABLES `pengaturan` WRITE;
/*!40000 ALTER TABLE `pengaturan` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `pengaturan` VALUES
(1,'nama_event','BlueCreativeFestival 2025','Nama event lomba','2025-09-03 07:11:21'),
(2,'tema_event','Kreativitas Tanpa Batas','Tema event lomba','2025-09-03 02:17:43'),
(3,'tanggal_mulai_pendaftaran','2024-01-01','Tanggal mulai pendaftaran','2025-09-03 02:17:43'),
(4,'tanggal_akhir_pendaftaran','2024-02-15','Tanggal akhir pendaftaran','2025-09-03 02:17:43'),
(5,'tanggal_mulai_pengumpulan','2024-02-16','Tanggal mulai pengumpulan karya','2025-09-03 02:17:43'),
(6,'tanggal_akhir_pengumpulan','2024-03-01','Tanggal akhir pengumpulan karya','2025-09-03 02:17:43'),
(7,'tanggal_pengumuman','2024-03-15','Tanggal pengumuman pemenang','2025-09-03 02:17:43'),
(8,'biaya_admin','5000','Biaya administrasi','2025-09-03 02:17:43'),
(9,'email_contact','info@bluecreativefestival.com','Email kontak','2025-09-03 02:17:43'),
(10,'phone_contact','+62 812-3456-7890','Nomor telepon kontak','2025-09-03 02:17:43'),
(11,'max_file_size','10485760','Maksimal ukuran file upload (10MB)','2025-09-03 02:17:43'),
(12,'allowed_file_types','jpg,jpeg,png,pdf,doc,docx,mp4,mov,avi','Tipe file yang diizinkan','2025-09-03 02:17:43'),
(13,'require_student_card','1','Wajib upload kartu pelajar (1=ya, 0=tidak)','2025-09-03 02:17:43');
/*!40000 ALTER TABLE `pengaturan` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `user_pendaftaran`
--

DROP TABLE IF EXISTS `user_pendaftaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_pendaftaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `pendaftar_id` varchar(20) NOT NULL,
  `kategori_lomba_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `tanggal_daftar` timestamp NULL DEFAULT current_timestamp(),
  `tanggal_approval` timestamp NULL DEFAULT NULL,
  `catatan_admin` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `kategori_lomba_id` (`kategori_lomba_id`),
  CONSTRAINT `user_pendaftaran_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_pendaftaran_ibfk_2` FOREIGN KEY (`kategori_lomba_id`) REFERENCES `kategori_lomba` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_pendaftaran`
--

LOCK TABLES `user_pendaftaran` WRITE;
/*!40000 ALTER TABLE `user_pendaftaran` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `user_pendaftaran` VALUES
(1,2,'P202509052160',5,'pending','2025-09-05 09:39:48',NULL,NULL),
(2,2,'P202509051585',3,'pending','2025-09-05 09:41:50',NULL,NULL),
(6,6,'P202509051428',5,'pending','2025-09-05 10:51:06',NULL,NULL),
(7,6,'P202509053315',3,'pending','2025-09-05 11:07:00',NULL,NULL),
(8,8,'P202509059988',5,'pending','2025-09-05 13:18:11',NULL,NULL);
/*!40000 ALTER TABLE `user_pendaftaran` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `sekolah` varchar(100) DEFAULT NULL,
  `kelas` varchar(255) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `users` VALUES
(1,'angga','angga@gmail.com','$2y$10$8gBadGCGtA4RM2/R3/Qp0eRdAw9kZ1pUFZR9ysALnTdXdiZi6iL9y','angga faot','SMK BUDI LUHUR','VII','087780195230','Jl. Raden Saleh No.999, RT.001/RW.003, Karang Tengah, Kec. Karang Tengah, Kota Tangerang, Banten 15157',1,'2025-09-03 05:04:22','2025-09-03 05:04:22'),
(2,'workshoptest1','workshoptest1@workshop.test','$2y$12$GZE61CYVec/EPvkHV5YQQOloLiZyGizE5fEdMQEPillIlCm7PFKfS','workshop test1','smk workshop test tangerang','VII','1234','',1,'2025-09-04 09:52:59','2025-09-05 09:12:33'),
(6,'mas','mas@gmail.com','$2y$12$1gT/Eaud9iaMTFFzzc.oL.Kr10lTC10go2ZoYBO0HwB/gAfyQIJAq','mas',NULL,NULL,'123','mas123',1,'2025-09-05 10:35:44','2025-09-05 10:35:44'),
(7,'asdf','asdf@asdf.asdf','$2y$12$p2xeQBkfTCtE/RjKCV7wbO/4oQHDkqMe1mMN7ND9JbWHG73uXrvei','asdf',NULL,NULL,'1234','asdf',1,'2025-09-05 10:36:28','2025-09-05 10:36:28'),
(8,'yangbeneraja','yangbeneraja@gmail.com','$2y$12$lVngk5FzN2JNfMoeREraDelxM5/6ZwoHLQ3ksS1f1kkgxRLILVeuC','yang bener aja',NULL,NULL,'1234','asdf',1,'2025-09-05 12:53:47','2025-09-05 12:53:47');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `webinar`
--

DROP TABLE IF EXISTS `webinar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `webinar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `pemateri` varchar(100) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `waktu` varchar(50) DEFAULT NULL,
  `lokasi` varchar(150) DEFAULT NULL,
  `kapasitas` int(11) DEFAULT 0,
  `biaya` decimal(10,2) DEFAULT 0.00,
  `banner_path` varchar(255) DEFAULT NULL,
  `materi_pdf_path` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webinar`
--

LOCK TABLES `webinar` WRITE;
/*!40000 ALTER TABLE `webinar` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `webinar` VALUES
(1,'Camera Movement','123','Akbar','2025-09-03','09:00 - 12:00','SMK Budi Luhur',30,20000.00,NULL,NULL,'aktif','2025-09-03 07:33:15');
/*!40000 ALTER TABLE `webinar` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `webinar_pendaftar`
--

DROP TABLE IF EXISTS `webinar_pendaftar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `webinar_pendaftar` (
  `id` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `webinar_id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telepon` varchar(20) NOT NULL,
  `institusi` varchar(100) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `workshop_id` (`webinar_id`),
  CONSTRAINT `webinar_pendaftar_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `webinar_pendaftar_ibfk_2` FOREIGN KEY (`webinar_id`) REFERENCES `webinar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webinar_pendaftar`
--

LOCK TABLES `webinar_pendaftar` WRITE;
/*!40000 ALTER TABLE `webinar_pendaftar` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `webinar_pendaftar` VALUES
('WS25090308052852',1,1,'angga faot','angga@gmail.com','087780195230','SMK BUDI LUHUR','approved','QRIS','uploads/bukti_transfer/bukti_transfer_1756907154_travis-scott-fish-meme-.jpg','2025-09-03 08:05:28'),
('WS25090409533726',2,1,'workshop test','workshoptest@workshop.test','','smk workshop test tangerang','pending','QRIS','uploads/bukti_transfer/bukti_transfer_1757064409_4c5-2295580792.jpg','2025-09-04 09:53:37');
/*!40000 ALTER TABLE `webinar_pendaftar` ENABLE KEYS */;
UNLOCK TABLES;
commit;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-09-05 20:19:43
