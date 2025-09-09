<?php
session_start();
require_once 'config/database.php';

$message = '';
$message_type = '';

// Get competition categories
$db = getDB();
$kategori_lomba = $db->fetchAll("SELECT * b_FROM kategori_lomba WHERE status = 'aktif' ORDER BY nama");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $telepon = $_POST['telepon'] ?? '';
    $sekolah = $_POST['sekolah'] ?? '';
    $kelas = $_POST['kelas'] ?? '';
    $kategori_lomba_id = $_POST['kategori_lomba_id'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    
    // Handle file upload
    $foto_kartu_pelajar = '';
    if (isset($_FILES['foto_kartu_pelajar']) && $_FILES['foto_kartu_pelajar']['error'] == 0) {
        $file = $_FILES['foto_kartu_pelajar'];
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
            $filename = 'kartu_pelajar_' . time() . '_' . $file['name'];
            $upload_path = 'uploads/' . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $foto_kartu_pelajar = $filename;
            }
        }
    }
    
    // Validation
    if (empty($nama) || empty($email) || empty($telepon) || empty($sekolah) || empty($kategori_lomba_id)) {
        $message = 'Semua field wajib diisi!';
        $message_type = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Format email tidak valid!';
        $message_type = 'danger';
    } elseif (empty($foto_kartu_pelajar)) {
        $message = 'Foto kartu pelajar wajib diupload!';
        $message_type = 'danger';
    } else {
        // Get category details
        $kategori = $db->fetch("SELECT * FROM b_kategori_lomba WHERE id = ?", [$kategori_lomba_id]);
        
        // Generate registration ID
        $pendaftaran_id = 'BCF' . date('Ymd') . rand(1000, 9999);
        
        // Save to database
        $data = [
            'id' => $pendaftaran_id,
            'nama' => $nama,
            'email' => $email,
            'telepon' => $telepon,
            'sekolah' => $sekolah,
            'kelas' => $kelas,
            'alamat' => $alamat,
            'kategori_lomba_id' => $kategori_lomba_id,
            'foto_kartu_pelajar' => $foto_kartu_pelajar
        ];
        $result = $db->insert('b_pendaftar', $data);
        
        if ($result) {
            // Save to session for payment
            $_SESSION['pendaftaran'] = [
                'id' => $pendaftaran_id,
                'nama' => $nama,
                'email' => $email,
                'telepon' => $telepon,
                'sekolah' => $sekolah,
                'kelas' => $kelas,
                'kategori_lomba_id' => $kategori_lomba_id,
                'kategori_nama' => $kategori['nama'],
                'alamat' => $alamat,
                'biaya' => $kategori['biaya'],
                'jenis_lomba' => $kategori['jenis_lomba']
            ];
            
            header('Location: payment.php');
            exit;
        } else {
            $message = 'Gagal menyimpan pendaftaran!';
            $message_type = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Lomba - BluvocationCreativeFestival</title>
<link rel="icon" type="image/png" href="favicon/bcf.png" sizes="32x32">
    <link href="bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #1630df 100%);
            min-height: 100vh;
        }
        .form-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 50px auto;
            max-width: 800px;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-submit {
            background: linear-gradient(45deg, #667eea, #1630df);
            border: none;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: bold;
            width: 100%;
        }
        .kategori-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .kategori-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }
        .kategori-card.selected {
            border-color: #667eea;
            background-color: rgba(102, 126, 234, 0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-trophy me-2"></i>BluvocationCreativeFestival
            </a>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <div class="form-container animate__animated animate__fadeInUp">
            <h2 class="text-center mb-4">
                <i class="fas fa-user-plus me-2"></i>Form Pendaftaran Lomba
            </h2>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i><?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registrationForm" enctype="multipart/form-data">
                <!-- Competition Categories -->
                <div class="mb-4">
                    <h5 class="mb-3"><i class="fas fa-trophy me-2"></i>Pilih Kategori Lomba</h5>
                    <div class="row">
                        <?php foreach ($kategori_lomba as $kategori): ?>
                        <div class="col-md-6 mb-3">
                            <div class="kategori-card" data-id="<?= $kategori['id'] ?>">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="<?= htmlspecialchars($kategori['icon']) ?> fa-2x text-primary me-3"></i>
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($kategori['nama']) ?></h6>
                                        <span class="badge bg-<?= ($kategori['jenis_lomba'] ?? 'individu') == 'kelompok' ? 'success' : 'info' ?>">
                                            <?= ucfirst($kategori['jenis_lomba'] ?? 'individu') ?>
                                        </span>
                                    </div>
                                </div>
                                <p class="text-muted mb-2"><?= htmlspecialchars(substr($kategori['deskripsi'], 0, 80)) ?>...</p>
                                <span class="h6 text-primary">Rp <?= number_format($kategori['biaya'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="kategori_lomba_id" id="kategori_lomba_id" required>
                </div>
                
                <!-- Registration Form -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="telepon" class="form-label">Nomor Telepon</label>
                            <input type="tel" class="form-control" id="telepon" name="telepon" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sekolah" class="form-label">Nama Sekolah</label>
                            <input type="text" class="form-control" id="sekolah" name="sekolah" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="kelas" class="form-label">Kelas</label>
                            <select class="form-select" id="kelas" name="kelas" required>
                                <option value="">Pilih Kelas</option>
                                <option value="VII">Kelas VII</option>
                                <option value="VIII">Kelas VIII</option>
                                <option value="IX">Kelas IX</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="foto_kartu_pelajar" class="form-label">Foto Kartu Pelajar</label>
                            <input type="file" class="form-control" id="foto_kartu_pelajar" name="foto_kartu_pelajar" accept="image/*" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat Lengkap</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                </div>
                
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="agree" required>
                    <label class="form-check-label" for="agree">
                        Saya setuju dengan syarat dan ketentuan yang berlaku
                    </label>
                </div>
                
                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-arrow-right me-2"></i>Lanjut ke Pembayaran
                </button>
            </form>
            
            <div class="text-center mt-4">
                <a href="index.php" class="text-muted">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

    <script src="bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle category selection
        document.querySelectorAll('.kategori-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.kategori-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('kategori_lomba_id').value = this.dataset.id;
            });
        });
        
        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const kategori = document.getElementById('kategori_lomba_id').value;
            const agree = document.getElementById('agree').checked;
            
            if (!kategori) {
                e.preventDefault();
                alert('Silakan pilih kategori lomba!');
                return false;
            }
            
            if (!agree) {
                e.preventDefault();
                alert('Anda harus menyetujui syarat dan ketentuan!');
                return false;
            }
        });
    </script>
</body>
</html>
