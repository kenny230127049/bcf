<?php
session_start();
require_once 'config/database.php';
require_once 'auth.php';

$auth = new Auth();

// Cek apakah user sudah login
if (!$auth->isLoggedIn()) {
    header('Location: login.php?redirect=daftar.php');
    exit;
}

$db = getDB();
$currentUser = $auth->getCurrentUser();

// Get kategori info from URL parameters
$kategori_id = $_GET['kategori'] ?? $_GET['kategori_id'] ?? null;
$jenis_lomba = $_GET['jenis'] ?? 'individu';
$max_peserta = $_GET['max_peserta'] ?? 1;

if (!$kategori_id) {
    header('Location: index.php');
    exit;
}

// Get kategori details
$kategori = $db->fetch("SELECT * FROM b_kategori_lomba WHERE id = ? AND status = 'aktif'", [$kategori_id]);
if (!$kategori) {
    header('Location: index.php');
    exit;
}

// Cek apakah user sudah mendaftar di lomba ini
$existingPendaftaran = $db->fetch(
    "SELECT id FROM b_user_pendaftaran WHERE user_id = ? AND kategori_lomba_id = ?",
    [$currentUser['id'], $kategori_id]
);

if ($existingPendaftaran) {
    header('Location: index.php?error=already_registered');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $sekolah = trim($_POST['sekolah'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    
    // Validate required fields
    if (empty($nama) || empty($email) || empty($telepon) || empty($sekolah) || empty($kelas) || empty($alamat)) {
        $error = 'Semua field harus diisi!';
    } else {
        // Generate unique ID
        $pendaftar_id = 'P' . date('Ymd') . rand(1000, 9999);
        
        // Handle file upload for student ID card
        $foto_kartu_pelajar = '';
        if (isset($_FILES['foto_kartu_pelajar']) && $_FILES['foto_kartu_pelajar']['error'] == 0) {
            $file = $_FILES['foto_kartu_pelajar'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            
            if (in_array($file['type'], $allowed_types)) {
                $filename = $pendaftar_id . '_' . time() . '_' . $file['name'];
                $upload_path = 'uploads/' . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $foto_kartu_pelajar = $filename;
                }
            }
        }
        
        // Insert main registration
        $data = [
            'id' => $pendaftar_id,
            'nama' => $nama,
            'email' => $email,
            'telepon' => $telepon,
            'sekolah' => $sekolah,
            'kelas' => $kelas,
            'alamat' => $alamat,
            'kategori_lomba_id' => $kategori_id,
            'foto_kartu_pelajar' => $foto_kartu_pelajar
        ];
        
        $result = $db->insert('b_pendaftar', $data);
        
        if ($result) {
            // Tambahkan ke user_pendaftaran
            $userPendaftaranData = [
                'user_id' => $currentUser['id'],
                'pendaftar_id' => $pendaftar_id,
                'kategori_lomba_id' => $kategori_id,
                'status' => 'pending'
            ];
            
            $db->insert('b_user_pendaftaran', $userPendaftaranData);
            
            // Handle group members if it's a group competition
            if ($jenis_lomba == 'kelompok') {
                $jumlah_anggota = intval($_POST['jumlah_anggota'] ?? 1);
                
                for ($i = 1; $i <= $jumlah_anggota; $i++) {
                    $anggota_nama = trim($_POST["anggota_nama_$i"] ?? '');
                    $anggota_email = trim($_POST["anggota_email_$i"] ?? '');
                    $anggota_telepon = trim($_POST["anggota_telepon_$i"] ?? '');
                    $anggota_sekolah = trim($_POST["anggota_sekolah_$i"] ?? '');
                    $anggota_kelas = $_POST["anggota_kelas_$i"] ?? '';
                    
                    if (!empty($anggota_nama) && !empty($anggota_sekolah) && !empty($anggota_kelas)) {
                        // Handle file upload for group member student ID card
                        $anggota_foto = '';
                        if (isset($_FILES["anggota_foto_$i"]) && $_FILES["anggota_foto_$i"]['error'] == 0) {
                            $file = $_FILES["anggota_foto_$i"];
                            if (in_array($file['type'], $allowed_types)) {
                                $filename = $pendaftar_id . '_anggota_' . $i . '_' . time() . '_' . $file['name'];
                                $upload_path = 'uploads/' . $filename;
                                
                                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                                    $anggota_foto = $filename;
                                }
                            }
                        }
                        
                        $anggota_data = [
                            'pendaftar_id' => $pendaftar_id,
                            'nama' => $anggota_nama,
                            'email' => $anggota_email,
                            'telepon' => $anggota_telepon,
                            'sekolah' => $anggota_sekolah,
                            'kelas' => $anggota_kelas,
                            'foto_kartu_pelajar' => $anggota_foto
                        ];
                        
                        $db->insert('b_anggota_kelompok', $anggota_data);
                    }
                }
            }
            
            // Redirect to success page
            header("Location: payment.php?pendaftar_id=" . $pendaftar_id);
            exit;
        } else {
            $error = 'Gagal mendaftar. Silakan coba lagi.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran - <?= htmlspecialchars($kategori['nama']) ?></title>
<link rel="icon" type="image/png" href="favicon/bcf.png" sizes="32x32">
    <link href="bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #1630df 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .registration-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #1630df 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #1630df 100%);
            border: none;
            color: white;
            padding: 15px 40px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        .member-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border: 2px dashed #dee2e6;
        }
        .member-section.active {
            border-color: #667eea;
            background: #f0f4ff;
        }
        .kategori-info {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="registration-card">
                    <!-- Header -->
                    <div class="card-header-custom">
                        <h2 class="mb-2">
                            <i class="<?= htmlspecialchars($kategori['icon'] ?? 'fas fa-trophy') ?> me-3"></i>
                            <?= htmlspecialchars($kategori['nama']) ?>
                        </h2>
                        <p class="mb-0">
                            <span class="badge bg-light text-dark me-2">
                                <i class="fas fa-<?= ($kategori['jenis_lomba'] ?? 'individu') == 'kelompok' ? 'users' : 'user' ?> me-1"></i>
                                <?= ucfirst($kategori['jenis_lomba'] ?? 'individu') ?>
                            </span>
                            <?php if (($kategori['jenis_lomba'] ?? 'individu') == 'kelompok'): ?>
                            <span class="badge bg-light text-dark">
                                Max: <?= $kategori['max_peserta'] ?? 1 ?> orang
                            </span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <!-- Form -->
                    <div class="p-4">
                        <?php if (isset($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
                        </div>
                        <?php endif; ?>

                        <!-- Kategori Info -->
                        <div class="kategori-info">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="fw-bold mb-2">Deskripsi Lomba:</h6>
                                    <p class="mb-0"><?= htmlspecialchars($kategori['deskripsi']) ?></p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <h5 class="text-primary fw-bold mb-0">
                                        Rp <?= number_format($kategori['biaya'], 0, ',', '.') ?>
                                    </h5>
                                    <small class="text-muted">Biaya Pendaftaran</small>
                                </div>
                            </div>
                        </div>

                        <form method="POST" enctype="multipart/form-data">
                            <!-- Ketua Tim / Peserta Utama -->
                            <h5 class="mb-3">
                                <i class="fas fa-user me-2 text-primary"></i>
                                <?= ($kategori['jenis_lomba'] ?? 'individu') == 'kelompok' ? 'Ketua Tim' : 'Data Peserta' ?>
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama Lengkap *</label>
                                        <input type="text" class="form-control" id="nama" name="nama" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telepon" class="form-label">Nomor Telepon *</label>
                                        <input type="tel" class="form-control" id="telepon" name="telepon" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sekolah" class="form-label">Nama Sekolah *</label>
                                        <input type="text" class="form-control" id="sekolah" name="sekolah" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="kelas" class="form-label">Kelas *</label>
                                        <input class="form-control" id="kelas" name="kelas" required>
                                        <!--     <option value="">Pilih Kelas</option> -->
                                        <!--     <option value="VII">Kelas VII</option> -->
                                        <!--     <option value="VIII">Kelas VIII</option> -->
                                        <!--     <option value="IX">Kelas IX</option> -->
                                        <!-- </select> -->
                                    </div>
                                </div>
                                <?php
                                    if ($kategori["butuh_kartu_pelajar"] != 0) {
                                        ?>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="foto_kartu_pelajar" class="form-label">Foto Kartu Pelajar *</label>
                                                    <input type="file" class="form-control" id="foto_kartu_pelajar" name="foto_kartu_pelajar" accept="image/*" required>
                                                    <small class="text-muted">Format: JPG, PNG. Maksimal 2MB</small>
                                                </div>
                                            </div>
                                        <?php
                                    }
                                ?>
                            </div>

                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat Lengkap *</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                            </div>

                            <!-- Anggota Kelompok (jika lomba kelompok) -->
                            <?php if (($kategori['jenis_lomba'] ?? 'individu') == 'kelompok'): ?>
                            <hr class="my-4">
                            <h5 class="mb-3">
                                <i class="fas fa-users me-2 text-primary"></i>Anggota Kelompok
                            </h5>
                            
                            <div class="mb-3">
                                <label for="jumlah_anggota" class="form-label">Jumlah Anggota (termasuk ketua tim)</label>
                                <select class="form-select" id="jumlah_anggota" name="jumlah_anggota" onchange="updateAnggotaForm()">
                                    <?php for ($i = 2; $i <= $kategori['max_peserta']; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?> orang</option>
                                    <?php endfor; ?>
                                </select>
                            </div>


                            <div id="anggotaContainer">
                                <!-- Anggota forms will be generated here -->
                            </div>
                            <?php endif; ?>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="agree" required>
                                <label class="form-check-label" for="agree">
                                    Saya setuju dengan syarat dan ketentuan yang berlaku
                                </label>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-submit btn-lg">
                                    <i class="fas fa-arrow-right me-2"></i>Lanjut ke Pembayaran
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <a href="daftar_lomba.php" class="text-muted text-decoration-none">
                                <i class="fas fa-arrow-left me-2"></i>Kembali ke Pilihan Lomba
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap-5.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if (($kategori['jenis_lomba'] ?? 'individu') == 'kelompok'): ?>
        function updateAnggotaForm() {
            const jumlahAnggota = parseInt(document.getElementById('jumlah_anggota').value);
            const container = document.getElementById('anggotaContainer');
            container.innerHTML = '';
            
            // Generate forms for additional members (excluding ketua tim)
            for (let i = 1; i < jumlahAnggota; i++) {
                const memberHtml = `
                    <div class="member-section">
                        <h6 class="mb-3">
                            <i class="fas fa-user me-2"></i>Anggota ${i + 1}
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" name="anggota_nama_${i}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="anggota_email_${i}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nomor Telepon</label>
                                    <input type="tel" class="form-control" name="anggota_telepon_${i}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Sekolah *</label>
                                    <input type="text" class="form-control" name="anggota_sekolah_${i}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Kelas *</label>
                                    <input type="text" class="form-control" name="anggota_kelas_${i}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Foto Kartu Pelajar *</label>
                                    <input type="file" class="form-control" name="anggota_foto_${i}" accept="image/*" required>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += memberHtml;
            }
        }
        
        // Initialize anggota form
        document.addEventListener('DOMContentLoaded', function() {
            updateAnggotaForm();
        });
        <?php endif; ?>
    </script>
</body>
</html>
