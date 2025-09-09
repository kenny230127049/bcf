# Admin Panel - BluvocationCreativeFestival

Panel administrasi untuk mengelola sistem pendaftaran lomba BluvocationCreativeFestival.

## Fitur Utama

### 1. Dashboard
- Statistik pendaftar (total, menunggu verifikasi, terverifikasi, ditolak)
- Daftar pendaftar terbaru
- Ringkasan kategori lomba

### 2. Kelola Pendaftar
- Lihat semua pendaftar dengan filter dan pencarian
- Verifikasi kartu pelajar
- Update status pendaftaran (verifikasi/tolak)
- Detail lengkap pendaftar
- Dukungan untuk lomba kelompok

### 3. Kelola Kategori Lomba
- Tambah kategori lomba baru
- Edit kategori yang ada
- Hapus kategori (jika tidak ada pendaftar)
- Dukungan untuk lomba individu dan kelompok
- Set maksimal peserta untuk lomba kelompok

### 4. Pengaturan Sistem
- Edit nama dan tema event
- Set tanggal penting (pendaftaran, pengumpulan, pengumuman)
- Konfigurasi biaya administrasi
- Update informasi kontak

## Cara Menggunakan

### 1. Login Admin
- Akses: `admin/login.php`
- Username: `admin`
- Password: `password`

### 2. Mengelola Pendaftar
1. Buka menu "Pendaftar"
2. Gunakan filter untuk mencari pendaftar tertentu
3. Klik tombol "Detail" untuk melihat informasi lengkap
4. Verifikasi kartu pelajar dengan mengklik "Lihat"
5. Klik "Verifikasi" atau "Tolak" untuk update status

### 3. Mengelola Kategori Lomba
1. Buka menu "Kategori Lomba"
2. Klik "Tambah Kategori" untuk membuat kategori baru
3. Isi form dengan informasi lengkap:
   - Nama kategori
   - Deskripsi
   - Biaya pendaftaran
   - Icon (Font Awesome)
   - Jenis lomba (individu/kelompok)
   - Maksimal peserta (untuk lomba kelompok)
   - Status (aktif/nonaktif)

### 4. Pengaturan Sistem
1. Buka menu "Pengaturan"
2. Edit informasi sesuai kebutuhan
3. Klik "Simpan Pengaturan"

## Struktur Database

### Tabel Utama
- `pendaftar` - Data pendaftar
- `kategori_lomba` - Kategori lomba
- `anggota_kelompok` - Anggota untuk lomba kelompok
- `pembayaran` - Data pembayaran
- `karya` - Karya yang diupload
- `admin` - Data admin
- `pengaturan` - Pengaturan sistem

### Fitur Baru
- **Kartu Pelajar**: Upload dan verifikasi kartu pelajar
- **Lomba Kelompok**: Dukungan untuk lomba tim
- **Status Verifikasi**: Pending, confirmed, rejected
- **Catatan Admin**: Tambahan catatan untuk setiap pendaftar

## Keamanan

- Session-based authentication
- SQL injection protection dengan PDO
- File upload validation
- XSS protection dengan htmlspecialchars
- CSRF protection (implementasi dasar)

## File Upload

- Kartu pelajar disimpan di folder `uploads/`
- Format yang diizinkan: JPG, JPEG, PNG
- Maksimal ukuran: 5MB
- Nama file: `kartu_pelajar_[timestamp]_[original_name]`

## Troubleshooting

### Login Gagal
- Pastikan database sudah dibuat dengan benar
- Cek username dan password default
- Pastikan tabel `admin` sudah terisi

### Upload File Gagal
- Pastikan folder `uploads/` sudah dibuat
- Cek permission folder (777 untuk development)
- Pastikan ukuran file tidak melebihi limit

### Database Error
- Import file `database_updated.sql`
- Pastikan koneksi database benar
- Cek struktur tabel sesuai dengan yang baru

## Catatan Pengembangan

- Admin panel menggunakan Bootstrap 5
- Responsive design untuk mobile dan desktop
- Animasi dengan Animate.css
- Icon menggunakan Font Awesome 6
- JavaScript vanilla untuk interaktivitas

## Kontak

Untuk bantuan teknis, hubungi:
- Email: admin@bluecreativefestival.com
- Telepon: +62 812-3456-7890
