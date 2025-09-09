# LombaBCF - Website Lomba Kreatif untuk SMP

Website lomba kreatif yang interaktif dan menarik untuk siswa SMP dengan sistem pendaftaran dan pembayaran yang terintegrasi.

## 🎯 Fitur Utama

### ✨ Frontend
- **Desain Responsif**: Menggunakan Bootstrap 5 untuk tampilan yang responsif di semua device
- **Animasi Interaktif**: Menggunakan Animate.css dan CSS3 animations untuk pengalaman yang menarik
- **UI/UX Modern**: Desain yang colorful dan menarik untuk target anak SMP
- **Smooth Scrolling**: Navigasi yang halus antar section

### 📝 Sistem Pendaftaran
- **Form Pendaftaran**: Form yang user-friendly dengan validasi real-time
- **Progress Indicator**: Menunjukkan progress pendaftaran (3 tahap)
- **Validasi Data**: Validasi email, nomor telepon, dan field wajib
- **Session Management**: Menggunakan PHP session untuk data pendaftaran

### 💳 Sistem Pembayaran
- **Multiple Payment Methods**: DANA, OVO, GoPay, Bank BCA, Bank Mandiri
- **Payment Instructions**: Instruksi detail untuk setiap metode pembayaran
- **Payment Status**: Tracking status pembayaran
- **QR Code Support**: Dukungan QR code untuk pembayaran

### 🏆 Kategori Lomba
1. **Lomba Menggambar** - Biaya: Rp 50.000
2. **Lomba Fotografi** - Biaya: Rp 75.000  
3. **Lomba Menulis** - Biaya: Rp 25.000

### 🎁 Hadiah
- **Juara 1**: Rp 2.000.000 + Sertifikat + Trophy
- **Juara 2**: Rp 1.500.000 + Sertifikat + Trophy
- **Juara 3**: Rp 1.000.000 + Sertifikat + Trophy

## 🚀 Instalasi

### Prerequisites
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx)
- Composer (opsional)

### Langkah Instalasi

1. **Clone Repository**
   ```bash
   git clone https://github.com/yourusername/lombabcf.git
   cd lombabcf
   ```

2. **Setup Database**
   ```bash
   # Import database
   mysql -u root -p < database.sql
   ```

3. **Konfigurasi Database**
   - Edit file `config/database.php`
   - Sesuaikan host, username, password, dan nama database

4. **Setup Web Server**
   - Pastikan folder project dapat diakses melalui web server
   - Set document root ke folder project

5. **Test Website**
   - Buka browser dan akses `http://localhost/lombabcf`
   - Pastikan semua fitur berfungsi dengan baik

## 📁 Struktur File

```
lombabcf/
├── bootstrap-5.0.2-dist/     # Bootstrap CSS & JS
├── config/
│   └── database.php          # Konfigurasi database
├── index.php                 # Halaman utama
├── daftar.php               # Halaman pendaftaran
├── payment.php              # Halaman pembayaran
├── sukses.php               # Halaman sukses
├── database.sql             # Struktur database
└── README.md                # Dokumentasi
```

## 🗄️ Database Schema

### Tabel Utama
- **kategori_lomba**: Data kategori lomba
- **pendaftar**: Data pendaftar
- **pembayaran**: Data pembayaran
- **karya**: Data karya yang diupload
- **admin**: Data admin/juri
- **pengaturan**: Pengaturan sistem

## 🎨 Customization

### Mengubah Warna Tema
Edit CSS variables di file CSS:
```css
:root {
  --primary-color: #667eea;
  --secondary-color: #1630df;
  --success-color: #28a745;
  --warning-color: #ffc107;
  --danger-color: #dc3545;
}
```

### Menambah Kategori Lomba
1. Tambah data di tabel `kategori_lomba`
2. Update array `$metode_pembayaran` di `daftar.php`
3. Sesuaikan form pendaftaran

### Mengubah Pengaturan
Edit tabel `pengaturan` untuk mengubah:
- Nama event
- Tanggal-tanggal penting
- Biaya administrasi
- Informasi kontak

## 🔧 Konfigurasi

### Email Settings
Untuk mengirim email konfirmasi, konfigurasi SMTP di PHP:
```php
// Di config/email.php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

### Payment Gateway
Untuk integrasi payment gateway real:
1. Daftar di payment gateway (Midtrans, Xendit, dll)
2. Update konfigurasi di `payment.php`
3. Implementasi webhook untuk callback

## 🛡️ Security Features

- **SQL Injection Protection**: Menggunakan PDO prepared statements
- **XSS Protection**: HTML escaping untuk output
- **CSRF Protection**: Session-based protection
- **Input Validation**: Validasi server-side dan client-side
- **File Upload Security**: Validasi file type dan size

## 📱 Responsive Design

Website sudah dioptimasi untuk:
- **Desktop**: 1200px+
- **Tablet**: 768px - 1199px
- **Mobile**: < 768px

## 🎭 Animasi & Efek

### CSS Animations
- **Fade In**: Elemen muncul dengan fade effect
- **Slide Up**: Elemen slide dari bawah
- **Pulse**: Efek pulse pada tombol dan icon
- **Hover Effects**: Efek hover pada card dan button

### JavaScript Effects
- **Confetti**: Efek confetti saat sukses
- **Smooth Scrolling**: Navigasi halus
- **Loading Animation**: Animasi loading saat submit
- **Form Validation**: Validasi real-time

## 🚀 Performance Optimization

- **Minified CSS/JS**: Bootstrap sudah di-minify
- **Optimized Images**: Gunakan format WebP untuk gambar
- **Caching**: Implementasi browser caching
- **Database Indexing**: Index pada kolom yang sering di-query

## 📊 Monitoring & Analytics

Untuk monitoring website:
1. **Google Analytics**: Tambahkan tracking code
2. **Error Logging**: Implementasi error logging
3. **Performance Monitoring**: Monitor loading time
4. **User Behavior**: Track user interaction

## 🔄 Update & Maintenance

### Regular Updates
- Update Bootstrap ke versi terbaru
- Update PHP ke versi terbaru
- Update dependencies
- Backup database secara berkala

### Backup Strategy
```bash
# Backup database
mysqldump -u root -p lombabcf > backup_$(date +%Y%m%d).sql

# Backup files
tar -czf backup_$(date +%Y%m%d).tar.gz lombabcf/
```

## 📞 Support

Untuk bantuan dan support:
- **Email**: info@lombabcf.com
- **Phone**: +62 812-3456-7890
- **Documentation**: Lihat file README.md

## 📄 License

Project ini menggunakan license MIT. Lihat file LICENSE untuk detail lebih lanjut.

## 🤝 Contributing

1. Fork repository
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📈 Roadmap

### Versi 1.1
- [ ] Admin panel untuk manajemen pendaftar
- [ ] Upload karya online
- [ ] Sistem penilaian juri
- [ ] Email notification

### Versi 1.2
- [ ] Mobile app
- [ ] Real-time chat support
- [ ] Social media integration
- [ ] Advanced analytics

### Versi 2.0
- [ ] Multi-event support
- [ ] Advanced payment gateway
- [ ] AI-powered judging
- [ ] Virtual exhibition

---

**Dibuat dengan ❤️ untuk siswa SMP Indonesia**
