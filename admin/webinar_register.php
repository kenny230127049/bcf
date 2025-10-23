<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/database.php';
require_once 'auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) { header('Location: login.php'); exit; }

$db = getDB();
$user = $auth->getCurrentUser();
$webinar_id = intval($_GET['webinar_id'] ?? $_POST['webinar_id'] ?? 0);
$ws = $db->fetch('SELECT * FROM b_webinar WHERE id = ? AND status = "aktif"', [$webinar_id]);
if (!$ws) { header('Location: index.php#webinar'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $institusi = trim($_POST['institusi'] ?? '');

    if ($nama === '' || $email === '') {
        $error = 'Nama dan email wajib diisi';
    } else {
        // Prevent duplicate
        $existing = $db->fetch('SELECT id FROM b_webinar_pendaftar WHERE user_id = ? AND webinar_id = ?', [$user['id'], $webinar_id]);
        if ($existing) {
            // Sudah pernah mendaftar: arahkan langsung ke pembayaran webinar
            header('Location: webinar_payment.php?id=' . urlencode($existing['id']));
            exit;
        }

        $reg_id = 'WS' . date('ymdHis') . rand(10,99);
        $data = [
            'id' => $reg_id,
            'user_id' => $user['id'],
            'webinar_id' => $webinar_id,
            'nama' => $nama,
            'email' => $email,
            'telepon' => $telepon,
            'institusi' => $institusi,
            'status' => 'pending',
        ];
        $ok = $db->insert('b_webinar_pendaftar', $data);
        if ($ok) {
            header('Location: webinar_payment.php?id=' . urlencode($reg_id));
            exit;
        } else {
            $error = 'Gagal menyimpan pendaftaran';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Webinar - <?= htmlspecialchars($ws['judul']) ?></title>
<link rel="icon" type="image/png" href="favicon/bcf.png" sizes="32x32">
    <link href="bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #1630df 100%); min-height: 100vh; }
        .navbar { background: rgba(255, 255, 255, 0.95) !important; backdrop-filter: blur(10px); }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; color: #667eea !important; }
        .register-container { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); margin: 50px auto; max-width: 900px; }
        .summary-card { background: linear-gradient(135deg, #667eea, #1630df); color: white; border-radius: 15px; padding: 25px; margin-bottom: 30px; }
        .form-control, .form-select { border-radius: 10px; border: 2px solid #e9ecef; padding: 12px 15px; transition: all 0.3s ease; }
        .form-control:focus, .form-select:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25); }
        .btn-primary { background: linear-gradient(45deg, #667eea, #1630df); border: none; color: white; padding: 12px 26px; border-radius: 50px; font-weight: 600; }
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
        <div class="register-container animate__animated animate__fadeInUp">
            <h2 class="text-center mb-4"><i class="fas fa-chalkboard-teacher me-2"></i>Form Pendaftaran Webinar</h2>
            <div class="summary-card">
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="mb-1"><?= htmlspecialchars($ws['judul']) ?></h5>
                        <small><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($ws['lokasi'] ?? '-') ?> · <i class="fas fa-calendar me-1 ms-2"></i><?= htmlspecialchars($ws['tanggal'] ?? '-') ?> <?= htmlspecialchars($ws['waktu'] ?? '') ?></small>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div><strong>Biaya:</strong> Rp <?= number_format((float)($ws['biaya'] ?? 0), 0, ',', '.') ?></div>
                        <div><strong>Kapasitas:</strong> <?= (int)($ws['kapasitas'] ?? 0) ?></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                    <form method="post">
                        <input type="hidden" name="webinar_id" value="<?= $webinar_id ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($_POST['nama'] ?? $user['nama_lengkap']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Telepon</label>
                                    <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($_POST['telepon'] ?? ($user['telepon'] ?? '')) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Institusi</label>
                                    <input type="text" name="institusi" class="form-control" value="<?= htmlspecialchars($_POST['institusi'] ?? ($user['institusi'] ?? '')) ?>">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <a href="index.php#webinar" class="btn btn-outline-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Lanjut ke Pembayaran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


