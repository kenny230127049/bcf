<?php
require_once 'config/database.php';

$db = getDB();

// Get pendaftar_id from URL
$pendaftar_id = $_GET['pendaftar_id'] ?? null;

if (!$pendaftar_id) {
    header('Location: daftar_lomba.php');
    exit;
}

// Get pendaftar details
$pendaftar = $db->fetch("
    SELECT p.*, kl.nama as kategori_nama, kl.biaya, kl.jenis_lomba, kl.max_peserta
    FROM b_pendaftar p 
    JOIN b_kategori_lomba kl ON p.kategori_lomba_id = kl.id 
    WHERE p.id = ?
", [$pendaftar_id]);

if (!$pendaftar) {
    header('Location: daftar_lomba.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $metode_pembayaran = $_POST['metode_pembayaran'] ?? '';
    
    if (empty($metode_pembayaran)) {
        $message = 'Silakan pilih metode pembayaran!';
    } else {
        $payment_id = 'PAY' . date('YmdHis') . rand(100, 999);
        
        // Insert payment record
        $payment_data = [
            'id' => $payment_id,
            'pendaftar_id' => $pendaftar_id,
            'metode_pembayaran' => $metode_pembayaran,
            'jumlah' => $pendaftar['biaya'],
            'status' => 'pending'
        ];

        if (isset($_FILES['bukti_transfer']) && !empty($_FILES['bukti_transfer']) && $_FILES['foto_kartu_pelajar']['error'] == 0) {
            $file = $_FILES['bukti_transfer'];
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
            
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
                $filename = 'bukti_transfer' . time() . '_' . $file['name'];
                $upload_path = 'uploads/bukti_transfer/' . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $payment_data["bukti_transfer"] = $filename;
                }
            }
        }
        
        $result = $db->insert('b_pembayaran', $payment_data);
        
        if ($result) {
            header("Location: sukses.php?payment_id=$payment_id&pendaftar_id=$pendaftar_id");
            exit;
        } else {
            $message = 'Gagal memproses pembayaran. Silakan coba lagi!';
        }
    }
}

// $metode_pembayaran = [
//     'dana' => ['nama' => 'DANA', 'nomor' => '0812-3456-7890', 'icon' => 'fas fa-wallet', 'color' => '#0070E0'],
//     'ovo' => ['nama' => 'OVO', 'nomor' => '0812-3456-7890', 'icon' => 'fas fa-mobile-alt', 'color' => '#4C3494'],
//     'gopay' => ['nama' => 'GoPay', 'nomor' => '0812-3456-7890', 'icon' => 'fas fa-credit-card', 'color' => '#00AAE4'],
//     'bca' => ['nama' => 'Bank BCA', 'nomor' => '123-456-7890', 'icon' => 'fas fa-university', 'color' => '#00529C'],
//     'mandiri' => ['nama' => 'Bank Mandiri', 'nomor' => '123-456-7890', 'icon' => 'fas fa-university', 'color' => '#003D79']
// ];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - LombaBCF</title>
<link rel="icon" type="image/png" href="favicon/bcf.png" sizes="32x32">
    <link href="bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #1630df 100%);
            min-height: 100vh;
        }
        .payment-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 50px auto;
            max-width: 900px;
        }
        .summary-card {
            background: linear-gradient(135deg, #667eea, #1630df);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            margin: 15px 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-method:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .payment-method.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }
        .payment-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-right: 15px;
        }
        .btn-pay {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            width: 100%;
        }
        .btn-pay:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            color: white;
        }
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: #667eea !important;
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-trophy me-2"></i>LombaBCF
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Beranda</a>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <div class="payment-container animate__animated animate__fadeInUp">
            <h2 class="text-center mb-4">
                <i class="fas fa-credit-card me-2"></i>Pembayaran
            </h2>
            
            <?php if ($message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i><?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="summary-card">
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-user me-2"></i>Data Pendaftar</h5>
                        <p class="mb-1"><strong>Nama:</strong> <?= htmlspecialchars($pendaftar['nama']) ?></p>
                        <p class="mb-1"><strong>Sekolah:</strong> <?= htmlspecialchars($pendaftar['sekolah']) ?></p>
                        <p class="mb-1"><strong>Kategori:</strong> <?= htmlspecialchars($pendaftar['kategori_nama']) ?></p>
                        <p class="mb-1"><strong>Jenis:</strong> 
                            <span class="badge bg-light text-dark">
                                <?= ucfirst($pendaftar['jenis_lomba'] ?? 'individu') ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h5><i class="fas fa-receipt me-2"></i>Detail Biaya</h5>
                        <p class="mb-1"><strong>ID:</strong> <?= htmlspecialchars($pendaftar['id']) ?></p>
                        <p class="mb-1"><strong>Biaya Lomba:</strong> Rp <?= number_format($pendaftar['biaya'], 0, ',', '.') ?></p>
                        <p class="mb-1"><strong>Biaya Admin:</strong> Rp 5.000</p>
                        <h4 class="mb-0"><strong>Total: Rp <?= number_format($pendaftar['biaya'] + 5000, 0, ',', '.') ?></strong></h4>
                    </div>
                </div>
            </div>
            
            <form action="" method="post" id="paymentForm" enctype="multipart/form-data">
                <div class="d-flex flex-column align-items-center">
                    <h4>Bayar dengan QRIS</h4>

                    <img src="images/pembayaran_qris.jpeg" alt="">
                </div>

                <div class="mt-3">
                    <label for="bukti_transfer">Bukti Transfer</label>
                    <input type="file" name="bukti_transfer" id="buktiTransfer" class="form-control">
                </div>

                <input type="hidden" name="metode_pembayaran" value="QRIS">

                <button type="submit" class="btn btn-pay mt-4">
                    <i class="fas fa-lock me-2"></i>Bayar
                </button>
            </form>
            
            <div class="text-center mt-4">
                <a href="daftar.php" class="text-muted">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Pendaftaran
                </a>
            </div>
        </div>
    </div>

    <script src="bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('input[name="metode_pembayaran"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.payment-method').forEach(method => {
                    method.classList.remove('selected');
                });
                if (this.checked) {
                    this.closest('.payment-method').classList.add('selected');
                }
            });
        });
        
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const buktiTransfer = document.getElementById("buktiTransfer");
            if (buktiTransfer.files.length == 0) {
                e.preventDefault();
                alert('Silakan pilih metode pembayaran!');
                return false;
            }
            const btn = document.querySelector('.btn-pay');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
            btn.disabled = true;
        });
    </script>
</body>
</html>
