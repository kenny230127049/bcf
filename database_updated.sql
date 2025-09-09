-- Database untuk sistem pendaftaran lomba BlueCreativeFestival
-- Buat database
CREATE DATABASE IF NOT EXISTS lombabcf;
USE lombabcf;

-- Tabel kategori lomba (updated)
CREATE TABLE kategori_lomba (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    biaya DECIMAL(10,2) NOT NULL,
    icon VARCHAR(50),
    jenis_lomba ENUM('individu', 'kelompok') DEFAULT 'individu',
    max_peserta INT DEFAULT 1,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel pendaftar (updated with student ID card)
CREATE TABLE pendaftar (
    id VARCHAR(20) PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telepon VARCHAR(20) NOT NULL,
    sekolah VARCHAR(100) NOT NULL,
    kelas ENUM('VII', 'VIII', 'IX') NOT NULL,
    alamat TEXT NOT NULL,
    kategori_lomba_id INT NOT NULL,
    foto_kartu_pelajar VARCHAR(255),
    status ENUM('pending', 'confirmed', 'rejected') DEFAULT 'pending',
    catatan_admin TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_lomba_id) REFERENCES kategori_lomba(id)
);

-- Tabel anggota kelompok (untuk lomba kelompok)
CREATE TABLE anggota_kelompok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pendaftar_id VARCHAR(20) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telepon VARCHAR(20),
    sekolah VARCHAR(100) NOT NULL,
    kelas ENUM('VII', 'VIII', 'IX') NOT NULL,
    foto_kartu_pelajar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pendaftar_id) REFERENCES pendaftar(id) ON DELETE CASCADE
);

-- Tabel pembayaran
CREATE TABLE pembayaran (
    id VARCHAR(20) PRIMARY KEY,
    pendaftar_id VARCHAR(20) NOT NULL,
    metode_pembayaran VARCHAR(50) NOT NULL,
    jumlah DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    bukti_transfer VARCHAR(255),
    tanggal_pembayaran TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pendaftar_id) REFERENCES pendaftar(id)
);

-- Tabel karya
CREATE TABLE karya (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pendaftar_id VARCHAR(20) NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    status ENUM('submitted', 'reviewed', 'accepted', 'rejected') DEFAULT 'submitted',
    nilai DECIMAL(5,2),
    komentar TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pendaftar_id) REFERENCES pendaftar(id)
);

-- Tabel admin
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin', 'jury') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel pengaturan
CREATE TABLE pengaturan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    nilai TEXT,
    deskripsi TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert data kategori lomba (updated)
INSERT INTO kategori_lomba (nama, deskripsi, biaya, icon, jenis_lomba, max_peserta) VALUES
('Lomba Menggambar', 'Tunjukkan kreativitasmu dalam menggambar dengan tema "Masa Depan Indonesia". Lomba ini mengajak siswa untuk menuangkan imajinasi dan harapan mereka tentang masa depan Indonesia yang lebih baik.', 50000, 'fas fa-palette', 'individu', 1),
('Lomba Fotografi', 'Abadikan momen indah dengan tema "Keindahan Alam Sekitar". Lomba ini mendorong siswa untuk melihat keindahan alam di sekitar mereka dan mengabadikannya dalam bentuk foto yang artistik.', 75000, 'fas fa-camera', 'individu', 1),
('Lomba Menulis', 'Tuangkan ide kreatifmu dalam bentuk cerita pendek. Lomba ini memberikan kesempatan bagi siswa untuk mengekspresikan pikiran dan perasaan mereka melalui tulisan yang kreatif dan inspiratif.', 25000, 'fas fa-pen-fancy', 'individu', 1),
('Lomba Video Pendek', 'Buat video pendek yang kreatif dan inspiratif dengan tema "Kreativitas Anak Muda". Lomba ini mengajak siswa untuk bekerja sama dalam tim untuk menghasilkan karya video yang menarik.', 100000, 'fas fa-video', 'kelompok', 3),
('Lomba Desain Grafis', 'Buat desain grafis yang menarik untuk poster kampanye lingkungan. Lomba ini mendorong siswa untuk bekerja sama dalam tim untuk menghasilkan desain yang kreatif dan bermakna.', 80000, 'fas fa-paint-brush', 'kelompok', 2);

-- Insert data admin default (password: password)
INSERT INTO admin (username, password, nama, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@lombabcf.com', 'super_admin');

-- Insert data pengaturan
INSERT INTO pengaturan (nama, nilai, deskripsi) VALUES
('nama_event', 'BlueCreativeFestival 2024', 'Nama event lomba'),
('tema_event', 'Kreativitas Tanpa Batas', 'Tema event lomba'),
('tanggal_mulai_pendaftaran', '2024-01-01', 'Tanggal mulai pendaftaran'),
('tanggal_akhir_pendaftaran', '2024-02-15', 'Tanggal akhir pendaftaran'),
('tanggal_mulai_pengumpulan', '2024-02-16', 'Tanggal mulai pengumpulan karya'),
('tanggal_akhir_pengumpulan', '2024-03-01', 'Tanggal akhir pengumpulan karya'),
('tanggal_pengumuman', '2024-03-15', 'Tanggal pengumuman pemenang'),
('biaya_admin', '5000', 'Biaya administrasi'),
('email_contact', 'info@bluecreativefestival.com', 'Email kontak'),
('phone_contact', '+62 812-3456-7890', 'Nomor telepon kontak'),
('max_file_size', '10485760', 'Maksimal ukuran file upload (10MB)'),
('allowed_file_types', 'jpg,jpeg,png,pdf,doc,docx,mp4,mov,avi', 'Tipe file yang diizinkan'),
('require_student_card', '1', 'Wajib upload kartu pelajar (1=ya, 0=tidak)');

-- Index untuk optimasi query
CREATE INDEX idx_pendaftar_email ON pendaftar(email);
CREATE INDEX idx_pendaftar_status ON pendaftar(status);
CREATE INDEX idx_pendaftar_kategori ON pendaftar(kategori_lomba_id);
CREATE INDEX idx_pembayaran_status ON pembayaran(status);
CREATE INDEX idx_karya_status ON karya(status);
CREATE INDEX idx_kategori_status ON kategori_lomba(status);
CREATE INDEX idx_kategori_jenis ON kategori_lomba(jenis_lomba);
CREATE INDEX idx_anggota_kelompok_pendaftar ON anggota_kelompok(pendaftar_id);
