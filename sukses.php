<?php
require_once 'config/database.php';

$db = getDB();

// Get parameters from URL
$payment_id = $_GET['payment_id'] ?? null;
$pendaftar_id = $_GET['pendaftar_id'] ?? null;
$id = $_GET['id'] ?? null;

// Determine if this is a webinar or competition registration
$is_webinar = false;
$webinar_reg = null;
$pendaftar = null;
$payment = null;
$info_kategori = null;

if ($id) {
    // Check if this is a webinar registration
    $webinar_reg = $db->fetch("SELECT * FROM b_webinar_pendaftar WHERE id = ?", [$id]);
    if ($webinar_reg) {
        $is_webinar = true;
        $pendaftar = $db->fetch("
            SELECT wp.*, w.judul as webinar_nama, w.biaya
            FROM b_webinar_pendaftar wp 
            JOIN b_webinar w ON wp.webinar_id = w.id 
            WHERE wp.id = ?
        ", [$id]);
    } else {
        // This is a competition registration
        $pendaftar = $db->fetch("
            SELECT p.*, kl.nama as kategori_nama, kl.biaya
            FROM b_pendaftar p 
            JOIN b_kategori_lomba kl ON p.kategori_lomba_id = kl.id 
            WHERE p.id = ?
        ", [$id]);

        if ($pendaftar) {
            $info_kategori = $db->fetch("SELECT * FROM b_kategori_lomba WHERE id = ?", [$pendaftar['kategori_lomba_id']]);
        }
    }
} elseif ($payment_id && $pendaftar_id) {
    // Legacy competition registration with payment_id
    $payment = $db->fetch("SELECT * FROM b_pembayaran WHERE id = ?", [$payment_id]);
    $pendaftar = $db->fetch("
        SELECT p.*, kl.nama as kategori_nama, kl.biaya
        FROM b_pendaftar p 
        JOIN b_kategori_lomba kl ON p.kategori_lomba_id = kl.id 
        WHERE p.id = ?
    ", [$pendaftar_id]);

    if ($pendaftar) {
        $info_kategori = $db->fetch("SELECT * FROM b_kategori_lomba WHERE id = ?", [$pendaftar['kategori_lomba_id']]);
    }
}

if (!$pendaftar) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Berhasil - Bluvocation Creative Fest</title>
    <link rel="icon" type="image/png" href="favicon/bcf.png" sizes="32x32">
    <link href="bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #1630df 100%);
            min-height: 100vh;
        }

        .success-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin: 50px auto;
            max-width: 800px;
            text-align: center;
        }

        .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(45deg, #28a745, #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: white;
            font-size: 3rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background: #ff6b6b;
            animation: confetti-fall 3s linear infinite;
        }

        @keyframes confetti-fall {
            0% {
                transform: translateY(-100vh) rotate(0deg);
                opacity: 1;
            }

            100% {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }

        .info-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
            border-left: 5px solid #28a745;
        }

        .btn-home {
            background: linear-gradient(45deg, #667eea, #1630df);
            border: none;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            display: inline-block;
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
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

        .progress-bar {
            background: linear-gradient(45deg, #667eea, #1630df);
            border-radius: 10px;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-weight: bold;
            position: relative;
        }

        .step.completed {
            background: #28a745;
            color: white;
        }

        .step::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 100%;
            width: 100px;
            height: 2px;
            background: #e9ecef;
            transform: translateY(-50%);
        }

        .step:last-child::after {
            display: none;
        }

        .step.completed::after {
            background: #28a745;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold py-0" href="index.php">
                <img src="images/bcf.png" alt="" width="50"> Bluvocation Creative Fest
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Beranda</a>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <div class="success-container animate__animated animate__fadeInUp">
            <!-- Progress Bar -->
            <div class="progress mb-4" style="height: 8px;">
                <div class="progress-bar" role="progressbar" style="width: 100%"></div>
            </div>

            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step completed">
                    <i class="fas fa-user"></i>
                </div>
                <div class="step completed">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="step completed">
                    <i class="fas fa-check"></i>
                </div>
            </div>

            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>

            <h1 class="text-success mb-3">
                <i class="fas fa-trophy me-2"></i>Pendaftaran <?= $is_webinar ? 'Webinar' : 'Lomba' ?> Berhasil!
            </h1>

            <p class="lead text-muted mb-4">
                Selamat! Pendaftaran Anda telah berhasil dan pembayaran sedang diproses.
                Tim kami akan menghubungi Anda segera untuk konfirmasi lebih lanjut.
            </p>

            <div class="row">
                <div class="col-md-6">
                    <div class="info-card">
                        <h5><i class="fas fa-user me-2"></i>Data Pendaftar</h5>
                        <p class="mb-1"><strong>Nama:</strong> <?= htmlspecialchars($pendaftar['nama']) ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($pendaftar['email']) ?></p>
                        <?php if ($is_webinar): ?>
                            <?php if (!empty($pendaftar['telepon'])): ?>
                                <p class="mb-1"><strong>Telepon:</strong> <?= htmlspecialchars($pendaftar['telepon']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($pendaftar['institusi'])): ?>
                                <p class="mb-1"><strong>Institusi:</strong> <?= htmlspecialchars($pendaftar['institusi']) ?></p>
                            <?php endif; ?>
                            <p class="mb-0"><strong>Webinar:</strong> <?= htmlspecialchars($pendaftar['judul'] ?? 'Webinar') ?></p>
                        <?php else: ?>
                            <p class="mb-1"><strong>Sekolah:</strong> <?= htmlspecialchars($pendaftar['sekolah']) ?></p>
                            <p class="mb-1"><strong>Kelas:</strong> <?= htmlspecialchars($pendaftar['kelas']) ?></p>
                            <p class="mb-0"><strong>Kategori Lomba:</strong> <?= htmlspecialchars($pendaftar['kategori_nama']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-card">
                        <h5><i class="fas fa-receipt me-2"></i>Informasi Pembayaran</h5>
                        <p class="mb-1"><strong>ID Pendaftaran:</strong> <?= htmlspecialchars($pendaftar['id']) ?></p>
                        <?php if ($payment): ?>
                            <p class="mb-1"><strong>ID Pembayaran:</strong> <?= htmlspecialchars($payment['id']) ?></p>
                            <p class="mb-1"><strong>Metode Pembayaran:</strong> <?= ucfirst(htmlspecialchars($payment['metode_pembayaran'])) ?></p>
                        <?php else: ?>
                            <p class="mb-1"><strong>Metode Pembayaran:</strong> <?= ucfirst(htmlspecialchars($pendaftar['metode_pembayaran'] ?? 'QRIS')) ?></p>
                        <?php endif; ?>
                        <p class="mb-1"><strong>Total Bayar:</strong> Rp <?= number_format($pendaftar['biaya'] + ($is_webinar ? 0 : 5000), 0, ',', '.') ?></p>
                        <p class="mb-0"><strong>Status:</strong> <span class="badge bg-warning">Menunggu Konfirmasi</span></p>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-4" role="alert">
                <h6><i class="fas fa-info-circle me-2"></i>Langkah Selanjutnya</h6>
                <ul class="mb-0 text-start">
                    <li>Tim kami akan memverifikasi pembayaran Anda dalam 1x24 jam</li>
                    <li>Setelah verifikasi, Anda akan menerima email konfirmasi</li>
                    <?php if ($is_webinar): ?>
                        <li>Link webinar akan dikirim melalui email sebelum acara dimulai</li>
                    <?php else: ?>
                        <li>Informasi pengumpulan karya akan dikirim melalui email</li>
                    <?php endif; ?>
                    <li>Jangan lupa untuk memeriksa email Anda secara berkala</li>
                    <li><strong>Link grup WhatsApp akan diberikan setelah pendaftaran disetujui</strong></li>
                </ul>
            </div>

            <div class="mt-4">
                <a href="index.php" class="btn btn-home me-3">
                    <i class="fas fa-home me-2"></i>Kembali ke Beranda
                </a>
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Cetak Bukti
                </button>
            </div>

            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-phone me-1"></i>Butuh bantuan? Hubungi kami di +62 812-3456-7890
                </small>
            </div>
        </div>
    </div>

    <script src="bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Create confetti effect
        function createConfetti() {
            const colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#feca57', '#ff9ff3'];

            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDelay = Math.random() * 3 + 's';
                confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
                document.body.appendChild(confetti);

                setTimeout(() => {
                    confetti.remove();
                }, 5000);
            }
        }

        // Trigger confetti on page load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(createConfetti, 500);

            // Auto redirect after 10 seconds
            setTimeout(() => {
                if (confirm('Apakah Anda ingin kembali ke halaman utama?')) {
                    window.location.href = 'index.php';
                }
            }, 10000);
        });

        // Add success sound effect (optional)
        function playSuccessSound() {
            // You can add audio element here if needed
            console.log('Success sound played!');
        }

        // Play sound on page load
        setTimeout(playSuccessSound, 1000);
    </script>
</body>

</html>
