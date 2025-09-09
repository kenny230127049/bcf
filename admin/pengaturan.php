<?php
require_once 'check_auth.php';

$db = getDB();
$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_event = $_POST['nama_event'] ?? '';
    $tema_event = $_POST['tema_event'] ?? '';
    $tanggal_mulai_pendaftaran = $_POST['tanggal_mulai_pendaftaran'] ?? '';
    $tanggal_akhir_pendaftaran = $_POST['tanggal_akhir_pendaftaran'] ?? '';
    $tanggal_mulai_pengumpulan = $_POST['tanggal_mulai_pengumpulan'] ?? '';
    $tanggal_akhir_pengumpulan = $_POST['tanggal_akhir_pengumpulan'] ?? '';
    $tanggal_pengumuman = $_POST['tanggal_pengumuman'] ?? '';
    $biaya_admin = $_POST['biaya_admin'] ?? '';
    $email_contact = $_POST['email_contact'] ?? '';
    $phone_contact = $_POST['phone_contact'] ?? '';
    
    // Update settings
    $settings = [
        'nama_event' => $nama_event,
        'tema_event' => $tema_event,
        'tanggal_mulai_pendaftaran' => $tanggal_mulai_pendaftaran,
        'tanggal_akhir_pendaftaran' => $tanggal_akhir_pendaftaran,
        'tanggal_mulai_pengumpulan' => $tanggal_mulai_pengumpulan,
        'tanggal_akhir_pengumpulan' => $tanggal_akhir_pengumpulan,
        'tanggal_pengumuman' => $tanggal_pengumuman,
        'biaya_admin' => $biaya_admin,
        'email_contact' => $email_contact,
        'phone_contact' => $phone_contact
    ];
    
    $success = true;
    foreach ($settings as $nama => $nilai) {
        $result = $db->update('b_pengaturan', ['nilai' => $nilai], 'nama = ?', [$nama]);
        if ($result === false) {
            $success = false;
            break;
        }
    }
    
    if ($success) {
        $message = 'Pengaturan berhasil diperbarui!';
        $message_type = 'success';
    } else {
        $message = 'Gagal memperbarui pengaturan!';
        $message_type = 'danger';
    }
}

// Get current settings
$pengaturan = [];
$result = $db->fetchAll("SELECT nama, nilai FROM b_pengaturan");
foreach ($result as $row) {
    $pengaturan[$row['nama']] = $row['nilai'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Admin Panel</title>
<link rel="icon" type="image/png" href="../favicon/bcf.png" sizes="32x32">
    <link href="../bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #1630df 100%);
        }
        .admin-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .nav-link {
            color: rgba(255,255,255,0.8);
            transition: all 0.3s ease;
        }
        .nav-link:hover, .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="admin-sidebar p-4">
                    <div class="text-center mb-4">
                        <h4 class="text-white mb-0">
                            <i class="fas fa-trophy me-2"></i>Admin Panel
                        </h4>
                        <small class="text-white-50">BluvocationCreativeFestival</small>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link mb-2" href="index.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link mb-2" href="manage_lomba.php">
                            <i class="fas fa-trophy me-2"></i>Kelola Lomba
                        </a>
                        <a class="nav-link mb-2" href="pendaftar.php">
                            <i class="fas fa-users me-2"></i>Pendaftar
                        </a>
                        <a class="nav-link mb-2" href="kategori.php">
                            <i class="fas fa-tags me-2"></i>Kategori Lomba
                        </a>
                        
                        <a class="nav-link active mb-2" href="pengaturan.php">
                            <i class="fas fa-cog me-2"></i>Pengaturan
                        </a>
                        <hr class="text-white-50">
                        <a class="nav-link mb-2" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="admin-content p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">
                            <i class="fas fa-cog me-2 text-primary"></i>Pengaturan Sistem
                        </h2>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                            <i class="fas fa-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0">
                                <i class="fas fa-edit me-2 text-primary"></i>Edit Pengaturan
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nama_event" class="form-label">Nama Event</label>
                                            <input type="text" class="form-control" id="nama_event" name="nama_event" 
                                                   value="<?= htmlspecialchars($pengaturan['nama_event'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tema_event" class="form-label">Tema Event</label>
                                            <input type="text" class="form-control" id="tema_event" name="tema_event" 
                                                   value="<?= htmlspecialchars($pengaturan['tema_event'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tanggal_mulai_pendaftaran" class="form-label">Tanggal Mulai Pendaftaran</label>
                                            <input type="date" class="form-control" id="tanggal_mulai_pendaftaran" name="tanggal_mulai_pendaftaran" 
                                                   value="<?= htmlspecialchars($pengaturan['tanggal_mulai_pendaftaran'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tanggal_akhir_pendaftaran" class="form-label">Tanggal Akhir Pendaftaran</label>
                                            <input type="date" class="form-control" id="tanggal_akhir_pendaftaran" name="tanggal_akhir_pendaftaran" 
                                                   value="<?= htmlspecialchars($pengaturan['tanggal_akhir_pendaftaran'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tanggal_mulai_pengumpulan" class="form-label">Tanggal Mulai Pengumpulan</label>
                                            <input type="date" class="form-control" id="tanggal_mulai_pengumpulan" name="tanggal_mulai_pengumpulan" 
                                                   value="<?= htmlspecialchars($pengaturan['tanggal_mulai_pengumpulan'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tanggal_akhir_pengumpulan" class="form-label">Tanggal Akhir Pengumpulan</label>
                                            <input type="date" class="form-control" id="tanggal_akhir_pengumpulan" name="tanggal_akhir_pengumpulan" 
                                                   value="<?= htmlspecialchars($pengaturan['tanggal_akhir_pengumpulan'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tanggal_pengumuman" class="form-label">Tanggal Pengumuman</label>
                                            <input type="date" class="form-control" id="tanggal_pengumuman" name="tanggal_pengumuman" 
                                                   value="<?= htmlspecialchars($pengaturan['tanggal_pengumuman'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="biaya_admin" class="form-label">Biaya Administrasi</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" class="form-control" id="biaya_admin" name="biaya_admin" 
                                                       value="<?= htmlspecialchars($pengaturan['biaya_admin'] ?? '') ?>" min="0" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email_contact" class="form-label">Email Kontak</label>
                                            <input type="email" class="form-control" id="email_contact" name="email_contact" 
                                                   value="<?= htmlspecialchars($pengaturan['email_contact'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone_contact" class="form-label">Nomor Telepon Kontak</label>
                                            <input type="text" class="form-control" id="phone_contact" name="phone_contact" 
                                                   value="<?= htmlspecialchars($pengaturan['phone_contact'] ?? '') ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Simpan Pengaturan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
