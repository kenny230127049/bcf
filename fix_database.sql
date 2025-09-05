-- Fix database untuk menambahkan kolom butuh_kartu_pelajar
-- Jalankan query ini untuk memperbaiki error

-- Tambah kolom butuh_kartu_pelajar ke tabel kategori_lomba
ALTER TABLE kategori_lomba ADD COLUMN butuh_kartu_pelajar TINYINT(1) DEFAULT 0 AFTER max_peserta;

-- Update data kategori yang sudah ada
-- Lomba yang membutuhkan kartu pelajar (1 = butuh, 0 = tidak butuh)
UPDATE kategori_lomba SET butuh_kartu_pelajar = 1 WHERE id IN (1, 2, 3, 5, 6); -- Lomba yang membutuhkan kartu pelajar
UPDATE kategori_lomba SET butuh_kartu_pelajar = 0 WHERE id IN (7, 8); -- Lomba yang tidak membutuhkan kartu pelajar

-- Tambah kolom foto_kartu_pelajar ke tabel pendaftar jika belum ada
ALTER TABLE pendaftar ADD COLUMN foto_kartu_pelajar VARCHAR(255) DEFAULT NULL AFTER kategori_lomba_id;

-- Tambah kolom foto_kartu_pelajar ke tabel anggota_kelompok jika belum ada
ALTER TABLE anggota_kelompok ADD COLUMN foto_kartu_pelajar VARCHAR(255) DEFAULT NULL AFTER kelas;

