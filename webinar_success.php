<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/database.php';
require_once 'auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) { header('Location: login.php'); exit; }

$db = getDB();
$id = $_GET['id'] ?? '';
$reg = $db->fetch('SELECT wp.*, w.judul, w.biaya, w.tanggal, w.waktu, w.lokasi FROM b_webinar_pendaftar wp JOIN b_webinar w ON wp.webinar_id = w.id WHERE wp.id = ?', [$id]);
if (!$reg) { header('Location: index.php#webinar'); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil - Webinar</title>
<link rel="icon" type="image/png" href="favicon/bcf.png" sizes="32x32">
    <link href="bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #1630df 100%); min-height: 100vh; }
        .container-box { background: #fff; border-radius: 20px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); margin: 60px auto; max-width: 800px; }
        .success-icon { width: 90px; height: 90px; border-radius: 50%; background: #eafaf1; color: #28a745; display:flex; align-items:center; justify-content:center; font-size: 42px; margin: 0 auto 20px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm" style="background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-trophy me-2"></i>Bluvocation Creative Fest</a>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <div class="container-box text-center">
            <div class="success-icon"><i class="fas fa-check"></i></div>
            <h2 class="mb-2">Pembayaran Diterima</h2>
            <p class="text-muted">Bukti pembayaran Anda telah diunggah. Menunggu verifikasi admin.</p>
            <div class="card text-start mt-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-7">
                            <h5 class="mb-1"><?= htmlspecialchars($reg['judul']) ?></h5>
                            <small><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($reg['lokasi'] ?? '-') ?> · <i class="fas fa-calendar me-1 ms-2"></i><?= htmlspecialchars($reg['tanggal'] ?? '-') ?> <?= htmlspecialchars($reg['waktu'] ?? '') ?></small>
                            <div class="mt-3">
                                <div><strong>Nama:</strong> <?= htmlspecialchars($reg['nama']) ?></div>
                                <div><strong>Email:</strong> <?= htmlspecialchars($reg['email']) ?></div>
                                <div><strong>Telepon:</strong> <?= htmlspecialchars($reg['telepon'] ?? '-') ?></div>
                                <div><strong>Institusi:</strong> <?= htmlspecialchars($reg['institusi'] ?? '-') ?></div>
                                <div><strong>ID Pendaftaran:</strong> <?= htmlspecialchars($reg['id']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-5 text-md-end">
                            <div><strong>Total Bayar:</strong> Rp <?= number_format((float)$reg['biaya'], 0, ',', '.') ?></div>
                            <?php if (!empty($reg['bukti_transfer'])): ?>
                                <div class="mt-2">
                                    <a href="<?= htmlspecialchars($reg['bukti_transfer']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-image me-1"></i>Lihat Bukti</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-2">
                Silahkan masuk grup WhatsApp untuk mendapatkan informasi lebih lanjut:<br><a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">https://wa.me/1234567890</a>
            </div>

            <div class="mt-4">
                <a href="index.php#webinar" class="btn btn-primary"><i class="fas fa-home me-2"></i>Kembali ke Beranda</a>
            </div>
        </div>
    </div>

    <script src="bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
