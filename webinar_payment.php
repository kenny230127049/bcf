<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/database.php';
require_once 'auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) { header('Location: login.php'); exit; }

$db = getDB();
$id = $_GET['id'] ?? '';
$reg = $db->fetch('SELECT wp.*, w.judul, w.biaya FROM b_webinar_pendaftar wp JOIN b_webinar w ON wp.webinar_id = w.id WHERE wp.id = ?', [$id]);
if (!$reg) { header('Location: index.php#webinar'); exit; }

$message = '';
$type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Selaraskan dengan sistem lomba: QRIS + upload bukti wajib
    $metode = 'QRIS';
    $bukti_path = null;

    if (isset($_FILES['bukti_transfer']) && is_array($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] === 0) {
        $file = $_FILES['bukti_transfer'];
        $allowed = ['image/jpeg','image/jpg','image/png','image/webp'];
        $max = 5 * 1024 * 1024;
        if (in_array($file['type'], $allowed) && $file['size'] <= $max) {
            if (!is_dir('uploads/bukti_transfer')) { @mkdir('uploads/bukti_transfer', 0777, true); }
            $filename = 'bukti_transfer_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/','', $file['name']);
            $dest = 'uploads/bukti_transfer/' . $filename;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $bukti_path = $dest; // simpan full relative path agar tampil di admin
            } else {
                $message = 'Gagal mengunggah bukti pembayaran';
                $type = 'danger';
            }
        } else {
            $message = 'File tidak valid. Gunakan JPG/PNG/WEBP maks 5MB';
            $type = 'danger';
        }
    } else {
        $message = 'Silakan unggah bukti pembayaran QRIS';
        $type = 'danger';
    }

    if (!$message) {
        $updData = ['metode_pembayaran' => $metode];
        if ($bukti_path) { $updData['bukti_transfer'] = $bukti_path; }
        $upd = $db->update('webinar_pendaftar', $updData, 'id = ?', [$id]);
        if ($upd !== false) {
            header('Location: webinar_success.php?id=' . urlencode($id));
            exit;
        } else {
            $message = 'Gagal menyimpan pembayaran'; $type = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Webinar - <?= htmlspecialchars($reg['judul']) ?></title>
<link rel="icon" type="image/png" href="favicon/bcf.png" sizes="32x32">
    <link href="bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #1630df 100%); min-height: 100vh; }
        .navbar { background: rgba(255, 255, 255, 0.95) !important; backdrop-filter: blur(10px); }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; color: #667eea !important; }
        .payment-container { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); margin: 50px auto; max-width: 900px; }
        .summary-card { background: linear-gradient(135deg, #667eea, #1630df); color: white; border-radius: 15px; padding: 25px; margin-bottom: 30px; }
        .pay-option { border: 2px solid #e9ecef; border-radius: 12px; padding: 15px; cursor: pointer; }
        .pay-option input { display:none; }
        .pay-option.active { border-color: #667eea; background: #f0f2ff; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-trophy me-2"></i>Bluvocation Creative Fest</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Beranda</a>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <div class="payment-container animate__animated animate__fadeInUp">
            <h2 class="text-center mb-4"><i class="fas fa-credit-card me-2"></i>Pembayaran Webinar</h2>
            <div class="summary-card">
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="mb-1"><?= htmlspecialchars($reg['judul']) ?></h5>
                        <small><i class="fas fa-user me-1"></i><?= htmlspecialchars($reg['nama']) ?> · <i class="fas fa-envelope ms-2 me-1"></i><?= htmlspecialchars($reg['email']) ?></small>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div><strong>Total Bayar:</strong> Rp <?= number_format((float)$reg['biaya'], 0, ',', '.') ?></div>
                        <div><strong>ID:</strong> <?= htmlspecialchars($reg['id']) ?></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                            <?php if ($message): ?><div class="alert alert-<?= $type ?>"><?= htmlspecialchars($message) ?></div><?php endif; ?>
                            <form method="post" enctype="multipart/form-data" id="wsPayForm">
                                <div class="text-center mb-3">
                                    <h4>Bayar dengan QRIS</h4>
                                    <img src="images/pembayaran_qris.jpeg" alt="QRIS" style="max-width: 280px; width: 100%; border-radius: 12px; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Unggah Bukti Pembayaran</label>
                                    <input type="file" name="bukti_transfer" id="buktiTransfer" class="form-control" accept="image/*" required>
                                    <div class="form-text">Format JPG/PNG/WEBP, maks 5MB</div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <a href="index.php#webinar" class="btn btn-outline-secondary me-2">Kembali</a>
                                    <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.pay-option').forEach(el=>{
            el.addEventListener('click', ()=>{
                document.querySelectorAll('.pay-option').forEach(x=>x.classList.remove('active'));
                el.classList.add('active');
                el.querySelector('input').checked = true;
            });
        });
    </script>
</body>
</html>


