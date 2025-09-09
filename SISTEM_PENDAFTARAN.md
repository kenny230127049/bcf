# Sistem Pendaftaran Lomba BluvocationCreativeFestival

## Fitur Utama

### 1. **Halaman Pilihan Lomba** (`daftar_lomba.php`)
- Menampilkan semua kategori lomba yang tersedia
- Informasi lengkap setiap lomba (nama, deskripsi, biaya, jenis)
- Pembedaan lomba individu dan kelompok
- Interface yang menarik dengan animasi dan hover effects
- Pemilihan lomba dengan konfirmasi sebelum ke halaman pendaftaran

### 2. **Halaman Pendaftaran Dinamis** (`daftar.php`)
- Form pendaftaran yang menyesuaikan jenis lomba
- **Lomba Individu**: Form untuk 1 peserta
- **Lomba Kelompok**: Form untuk ketua tim + anggota kelompok (dinamis)
- Upload foto kartu pelajar untuk setiap peserta
- Validasi form yang lengkap
- Redirect ke halaman pembayaran setelah pendaftaran berhasil

### 3. **Halaman Pembayaran** (`payment.php`)
- Menampilkan detail pendaftar dan biaya
- Pilihan metode pembayaran (DANA, OVO, GoPay, Bank BCA, Bank Mandiri)
- Informasi lengkap tentang pembayaran
- Redirect ke halaman sukses setelah memilih metode pembayaran

### 4. **Halaman Sukses** (`sukses.php`)
- Konfirmasi pendaftaran berhasil
- Informasi lengkap pendaftar dan pembayaran
- Instruksi langkah selanjutnya
- Animasi dan efek visual yang menarik



### 6. **Panel Admin**
- **Kelola Kategori Lomba**: Tambah, edit, hapus kategori lomba
- **Kelola Pendaftar**: Lihat semua pendaftar, verifikasi, tolak
- **Detail Pendaftar**: Informasi lengkap + foto kartu pelajar
- **Kelola Pembayaran**: Lihat status pembayaran
- **Pengaturan**: Konfigurasi sistem

## Database Structure

### Tabel Utama:
1. **kategori_lomba**: Data kategori lomba (nama, deskripsi, biaya, jenis, max_peserta, status)
2. **pendaftar**: Data pendaftar utama (ketua tim untuk lomba kelompok)
3. **anggota_kelompok**: Data anggota kelompok (untuk lomba kelompok)
4. **pembayaran**: Data pembayaran
5. **admin**: Data admin
6. **pengaturan**: Konfigurasi sistem

## Fitur Khusus

### 1. **Sistem Kelompok Dinamis**
- Admin dapat mengatur maksimal peserta per kategori
- Form anggota kelompok muncul secara dinamis
- Upload foto kartu pelajar untuk setiap anggota
- Validasi jumlah anggota sesuai maksimal yang ditentukan

### 2. **Upload File**
- Upload foto kartu pelajar dengan validasi format
- File disimpan dengan nama unik untuk menghindari konflik
- Validasi ukuran dan tipe file

### 3. **Sistem ID Unik**
- ID pendaftar: `P` + tanggal + random number
- ID pembayaran: `PAY` + timestamp + random number
- Memudahkan tracking dan pencarian

### 4. **Status Tracking**
- Status pendaftar: pending, confirmed, rejected
- Status pembayaran: pending, success, failed
- Catatan admin untuk komunikasi

## Alur Pendaftaran

1. **User membuka website** → `index.php`
2. **Pilih lomba** → `daftar_lomba.php`
3. **Isi form pendaftaran** → `daftar.php`
4. **Pilih metode pembayaran** → `payment.php`
5. **Konfirmasi sukses** → `sukses.php`


## Alur Admin

1. **Login admin** → `admin/login.php`
2. **Dashboard** → `admin/index.php`
3. **Kelola kategori** → `admin/kategori.php`
4. **Kelola pendaftar** → `admin/pendaftar.php`
5. **Detail pendaftar** → `admin/detail_pendaftar.php`
6. **Verifikasi/tolak pendaftaran**

## Keamanan

- Validasi input di semua form
- Sanitasi data sebelum disimpan ke database
- Prepared statements untuk mencegah SQL injection
- Validasi file upload
- Session management untuk admin

## Responsive Design

- Menggunakan Bootstrap 5
- Responsive untuk desktop, tablet, dan mobile
- Animasi CSS untuk UX yang lebih baik
- Icon Font Awesome untuk visual yang menarik

## File Structure

```
LombaBCF/
├── index.php                 # Halaman utama
├── daftar_lomba.php         # Pilihan lomba
├── daftar.php               # Form pendaftaran
├── payment.php              # Halaman pembayaran
├── sukses.php               # Halaman sukses

├── config/
│   └── database.php         # Konfigurasi database
├── admin/                   # Panel admin
│   ├── index.php
│   ├── login.php
│   ├── kategori.php
│   ├── pendaftar.php
│   ├── detail_pendaftar.php
│   └── pengaturan.php
├── uploads/                 # Folder upload file
└── bootstrap-5.0.2-dist/    # Bootstrap CSS/JS
```

## Cara Penggunaan

### Untuk User:
1. Buka website
2. Klik "Daftar Sekarang"
3. Pilih lomba yang diinginkan
4. Isi form pendaftaran
5. Upload foto kartu pelajar
6. Pilih metode pembayaran


### Untuk Admin:
1. Login ke panel admin
2. Kelola kategori lomba
3. Verifikasi pendaftar
4. Lihat detail pendaftar dan foto kartu pelajar
5. Kelola pembayaran

## Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap 5
- **Icons**: Font Awesome 6
- **Animations**: Animate.css
