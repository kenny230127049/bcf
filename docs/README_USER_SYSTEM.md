# Sistem Login dan Register LombaBCF

## Overview
Sistem ini telah ditambahkan fitur login dan register untuk user, dimana user harus login terlebih dahulu sebelum dapat mendaftar lomba. User dapat melihat status pendaftaran mereka di dashboard dan admin dapat mengelola approval pendaftaran.

## Fitur yang Ditambahkan

### 1. Sistem Autentikasi
- **Login**: User dapat login menggunakan username atau email
- **Register**: User dapat mendaftar akun baru dengan data lengkap
- **Session Management**: Sistem session untuk menjaga user tetap login
- **Password Hashing**: Password di-hash menggunakan bcrypt untuk keamanan

### 2. Halaman User Dashboard
- **index.php**: Menjadi dashboard user setelah login
- **Statistik Pendaftaran**: Menampilkan jumlah pendaftaran pending, approved, dan rejected
- **Daftar Pendaftaran**: Menampilkan semua pendaftaran user dengan status
- **Lomba Tersedia**: Menampilkan lomba yang bisa didaftar

### 3. Sistem Approval Admin
- **admin_approval.php**: Halaman admin untuk mengelola approval pendaftaran
- **Status Management**: Admin dapat approve/reject pendaftaran
- **Catatan Admin**: Admin dapat memberikan catatan pada setiap approval

## File yang Dibuat/Dimodifikasi

### File Baru:
1. **auth.php** - Class untuk menangani autentikasi
2. **login.php** - Halaman login user
3. **register.php** - Halaman register user
4. **landing_page.php** - Halaman utama untuk user yang belum login
5. **admin_approval.php** - Halaman admin untuk approval
6. **users_table.sql** - SQL untuk membuat tabel users dan user_pendaftaran

### File yang Dimodifikasi:
1. **index.php** - Diubah menjadi dashboard user
2. **daftar.php** - Ditambahkan sistem login check
3. **config/database.php** - Sudah ada, digunakan untuk koneksi database

## Struktur Database

### Tabel `users`
```sql
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `sekolah` varchar(100) NOT NULL,
  `kelas` enum('VII','VIII','IX') NOT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `alamat` text,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
```

### Tabel `user_pendaftaran`
```sql
CREATE TABLE `user_pendaftaran` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `pendaftar_id` varchar(20) NOT NULL,
  `kategori_lomba_id` int NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `tanggal_daftar` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tanggal_approval` timestamp NULL DEFAULT NULL,
  `catatan_admin` text,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`kategori_lomba_id`) REFERENCES `kategori_lomba`(`id`)
);
```

## Cara Penggunaan

### Untuk User:
1. **Register**: Buka `register.php` dan daftar akun baru
2. **Login**: Buka `login.php` dan login dengan akun yang sudah dibuat
3. **Dashboard**: Setelah login, akan diarahkan ke dashboard user
4. **Daftar Lomba**: Klik "Daftar Lomba" untuk mendaftar ke lomba tertentu
5. **Cek Status**: Lihat status pendaftaran di dashboard

### Untuk Admin:
1. **Login Admin**: Login ke sistem admin yang sudah ada
2. **Approval**: Buka `admin_approval.php` untuk mengelola approval
3. **Terima/Tolak**: Klik tombol "Terima" atau "Tolak" untuk mengubah status
4. **Catatan**: Berikan catatan jika diperlukan

## Alur Sistem

### User Flow:
1. User membuka website → Landing page (landing_page.php)
2. User register → register.php → auth.php (proses register)
3. User login → login.php → auth.php (proses login)
4. User diarahkan ke dashboard → index.php (dashboard user)
5. User daftar lomba → daftar.php (dengan login check)
6. User lihat status → index.php (dashboard dengan status)

### Admin Flow:
1. Admin login → Sistem admin yang sudah ada
2. Admin akses approval → admin_approval.php
3. Admin approve/reject → Update status di database
4. User lihat update → Refresh dashboard

## Keamanan

1. **Password Hashing**: Menggunakan `password_hash()` dan `password_verify()`
2. **Session Management**: Session untuk menjaga user tetap login
3. **SQL Injection Prevention**: Menggunakan prepared statements
4. **XSS Prevention**: Menggunakan `htmlspecialchars()` untuk output
5. **CSRF Protection**: Form validation dan session check

## Fitur Tambahan

1. **Redirect System**: User diarahkan ke halaman yang dimaksud setelah login
2. **Duplicate Prevention**: User tidak bisa mendaftar lomba yang sama 2x
3. **Status Tracking**: Tracking lengkap status pendaftaran
4. **Admin Notes**: Admin dapat memberikan catatan pada approval
5. **Statistics**: Dashboard menampilkan statistik pendaftaran

## Troubleshooting

### Jika ada error:
1. **Database Connection**: Pastikan database `lombabcf` sudah ada
2. **Table Creation**: Pastikan tabel `users` dan `user_pendaftaran` sudah dibuat
3. **File Permissions**: Pastikan folder `uploads/` bisa ditulis
4. **Session**: Pastikan session PHP berfungsi dengan baik

### Untuk testing:
1. Register user baru
2. Login dengan user tersebut
3. Daftar ke lomba
4. Login sebagai admin dan approve pendaftaran
5. Cek status di dashboard user

## Catatan Penting

- Sistem ini terintegrasi dengan sistem lomba yang sudah ada
- Tabel `pendaftar` dan `anggota_kelompok` tetap digunakan
- Tabel `user_pendaftaran` adalah bridge antara user dan pendaftaran
- Admin approval hanya mengubah status di `user_pendaftaran`
- Data pendaftaran asli tetap tersimpan di tabel `pendaftar`

