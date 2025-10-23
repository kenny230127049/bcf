# Perbaikan Fitur Kartu Pelajar - LombaBCF

## Masalah yang Ditemukan
Error SQL: `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'butuh_kartu_pelajar' in 'field list'`

## Penyebab
Kolom `butuh_kartu_pelajar` belum ada di database, padahal kode sudah menggunakan kolom tersebut.

## Solusi

### Langkah 1: Jalankan Script Perbaikan Database
1. Buka browser dan akses: `http://localhost/LombaBCF/fix_database.php`
2. Script akan otomatis menambahkan kolom yang diperlukan
3. Tunggu sampai muncul pesan "Database berhasil diperbaiki!"

### Langkah 2: Verifikasi Perbaikan
1. Buka halaman daftar lomba: `http://localhost/LombaBCF/daftar_lomba.php`
2. Pastikan setiap kategori lomba menampilkan badge "Butuh Kartu Pelajar" atau "Tidak Butuh Kartu Pelajar"
3. Pilih salah satu lomba dan klik "Daftar Sekarang"
4. Pastikan form pendaftaran menampilkan field kartu pelajar sesuai kebutuhan kategori

### Langkah 3: Test Fitur
1. **Lomba yang Butuh Kartu Pelajar** (ID: 1, 2, 3, 5, 6):
   - Form akan menampilkan field upload foto kartu pelajar
   - Field wajib diisi (required)
   - Validasi format dan ukuran file

2. **Lomba yang Tidak Butuh Kartu Pelajar** (ID: 7, 8):
   - Form akan menampilkan informasi "Tidak diperlukan"
   - Tidak ada field upload

## File yang Telah Diperbaiki

### 1. `fix_database.php`
- Script untuk menambahkan kolom `butuh_kartu_pelajar` ke database
- Menambahkan kolom `foto_kartu_pelajar` ke tabel pendaftar dan anggota_kelompok
- Update data kategori yang sudah ada

### 2. `daftar.php`
- Implementasi logika kartu pelajar berdasarkan kategori
- Validasi upload file hanya jika diperlukan
- Form dinamis untuk anggota kelompok

### 3. `daftar_lomba.php`
- Tampilan badge status kartu pelajar
- Informasi detail saat memilih lomba
- Parameter URL yang lengkap

### 4. `admin/kategori.php`
- Form admin untuk mengatur kebutuhan kartu pelajar
- Checkbox "Butuh Kartu Pelajar?"

## Struktur Database Baru

### Tabel `kategori_lomba`
```sql
ALTER TABLE kategori_lomba ADD COLUMN butuh_kartu_pelajar TINYINT(1) DEFAULT 0 AFTER max_peserta;
```

### Tabel `pendaftar`
```sql
ALTER TABLE pendaftar ADD COLUMN foto_kartu_pelajar VARCHAR(255) DEFAULT NULL AFTER kategori_lomba_id;
```

### Tabel `anggota_kelompok`
```sql
ALTER TABLE anggota_kelompok ADD COLUMN foto_kartu_pelajar VARCHAR(255) DEFAULT NULL AFTER kelas;
```

## Pengaturan Kategori Lomba

### Lomba yang Butuh Kartu Pelajar (butuh_kartu_pelajar = 1)
- Lomba Menggambar (ID: 1)
- Lomba Fotografi (ID: 2)
- Lomba Menulis (ID: 3)
- Lomba Menggambar Digital (ID: 5)
- Lomba Menulis Cerita Pendek (ID: 6)

### Lomba yang Tidak Butuh Kartu Pelajar (butuh_kartu_pelajar = 0)
- Lomba Video Pendek (ID: 7)
- Lomba Desain Poster (ID: 8)

## Fitur yang Tersedia

1. **Form Pendaftaran Dinamis**: Field kartu pelajar muncul sesuai kebutuhan kategori
2. **Validasi File**: Format JPG/PNG, maksimal 2MB
3. **Upload Otomatis**: Nama file otomatis dengan prefix ID pendaftar
4. **Anggota Kelompok**: Setiap anggota tim juga mengikuti aturan kartu pelajar
5. **Admin Panel**: Kemudahan mengatur kebutuhan kartu pelajar per kategori

## Troubleshooting

### Jika masih ada error:
1. Pastikan script `fix_database.php` sudah dijalankan
2. Periksa apakah kolom sudah ditambahkan dengan query:
   ```sql
   DESCRIBE kategori_lomba;
   ```
3. Pastikan semua file yang diperbaiki sudah tersimpan dengan benar

### Jika form tidak muncul:
1. Periksa error di console browser
2. Pastikan JavaScript tidak error
3. Periksa apakah parameter URL lengkap

## Kontak Support
Jika masih ada masalah, silakan hubungi admin sistem.

