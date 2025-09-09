-- Database untuk sistem pendaftaran lomba LombaBCF
-- Buat database
CREATE DATABASE IF NOT EXISTS lombabcf;
USE lombabcf;

-- Tabel kategori lomba
CREATE TABLE kategori_lomba (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    biaya DECIMAL(10,2) NOT NULL,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel pendaftar
CREATE TABLE pendaftar (
    id VARCHAR(20) PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telepon VARCHAR(20) NOT NULL,
    sekolah VARCHAR(100) NOT NULL,
    kelas ENUM('VII', 'VIII', 'IX') NOT NULL,
    alamat TEXT NOT NULL,
    kategori_lomba_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_lomba_id) REFERENCES kategori_lomba(id)
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

-- Insert data kategori lomba
INSERT INTO kategori_lomba (nama, deskripsi, biaya, icon) VALUES
('Lomba Menggambar', 'Tunjukkan kreativitasmu dalam menggambar dengan tema "Masa Depan Indonesia"', 50000, 'fas fa-palette'),
('Lomba Fotografi', 'Abadikan momen indah dengan tema "Keindahan Alam Sekitar"', 75000, 'fas fa-camera'),
('Lomba Menulis', 'Tuangkan ide kreatifmu dalam bentuk cerita pendek', 25000, 'fas fa-pen-fancy');

-- Insert data admin default
INSERT INTO admin (username, password, nama, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@lombabcf.com', 'super_admin');

-- Insert data pengaturan
INSERT INTO pengaturan (nama, nilai, deskripsi) VALUES
('nama_event', 'LombaBCF 2024', 'Nama event lomba'),
('tema_event', 'Kreativitas Tanpa Batas', 'Tema event lomba'),
('tanggal_mulai_pendaftaran', '2024-01-01', 'Tanggal mulai pendaftaran'),
('tanggal_akhir_pendaftaran', '2024-02-15', 'Tanggal akhir pendaftaran'),
('tanggal_mulai_pengumpulan', '2024-02-16', 'Tanggal mulai pengumpulan karya'),
('tanggal_akhir_pengumpulan', '2024-03-01', 'Tanggal akhir pengumpulan karya'),
('tanggal_pengumuman', '2024-03-15', 'Tanggal pengumuman pemenang'),
('biaya_admin', '5000', 'Biaya administrasi'),
('email_contact', 'info@lombabcf.com', 'Email kontak'),
('phone_contact', '+62 812-3456-7890', 'Nomor telepon kontak');

-- Index untuk optimasi query
CREATE INDEX idx_pendaftar_email ON pendaftar(email);
CREATE INDEX idx_pendaftar_status ON pendaftar(status);
CREATE INDEX idx_pembayaran_status ON pembayaran(status);
CREATE INDEX idx_karya_status ON karya(status);
CREATE INDEX idx_pendaftar_kategori ON pendaftar(kategori_lomba_id);
