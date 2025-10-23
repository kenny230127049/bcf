<?php
require_once 'check_auth.php';

$db = getDB();
$id = $_GET['id'] ?? '';
$p = $db->fetch('SELECT wp.*, w.judul, w.tanggal, w.waktu, w.lokasi, w.biaya FROM b_webinar_pendaftar wp JOIN b_webinar w ON wp.webinar_id = w.id WHERE wp.id = ?', [$id]);
if (!$p) {
    header('Location: webinar_pendaftar.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pendaftar Webinar</title>
    <link rel="icon" type="image/png" href="../favicon/bcf.png" sizes="32x32">
    <link href="../bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #1630df 100%);
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .admin-content {
            background-color: #f8f9fa;
            min-height: 100vh;
            margin-left: 250px;
            width: calc(100% - 250px);
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
        }

        .badge {
            font-size: 0.8rem;
            padding: 0.5rem 0.75rem;
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
        }

        .img-fluid {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }

            .admin-sidebar {
                transform: translateX(-100%);
                width: 250px;
            }

            .admin-sidebar.show {
                transform: translateX(0);
            }

            .admin-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="admin-sidebar p-4">
        <div class="text-center mb-4">
            <h4 class="text-white mb-0">
                <i class="fas fa-trophy me-2"></i>Admin Panel
            </h4>
            <small class="text-white-50">Bluvocation Creative Festival</small>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link mb-2" href="index.php">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
            <a class="nav-link mb-2" href="manage_lomba.php">
                <i class="fas fa-trophy me-2"></i>Kelola Lomba
            </a>
            <a class="nav-link mb-2" href="pendaftar.php">
                <i class="fas fa-users me-2"></i>Pendaftar Lomba
            </a>
            <a class="nav-link mb-2" href="kategori.php">
                <i class="fas fa-tags me-2"></i>Kategori Lomba
            </a>
            <a class="nav-link mb-2" href="webinar_manage.php">
                <i class="fas fa-chalkboard-teacher me-2"></i>Kelola Webinar
            </a>
            <a class="nav-link active mb-2" href="webinar_pendaftar.php">
                <i class="fas fa-users me-2"></i>Pendaftar Webinar
            </a>
            <a class="nav-link mb-2" href="pengaturan.php">
                <i class="fas fa-cog me-2"></i>Settings
            </a>
            <hr class="text-white-50">
            <a class="nav-link mb-2" href="logout.php">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </nav>
    </div>
    <div class="admin-content p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-user me-2 text-primary"></i>Detail Pendaftar Webinar</h2>
            <a href="webinar_pendaftar.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
        </div>

        <div class="row g-4">
            <!-- Informasi Pendaftar & Webinar -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Informasi Pendaftar</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>ID Pendaftaran:</strong><br><?= htmlspecialchars($p['id']) ?></p>
                                <p><strong>Nama:</strong><br><?= htmlspecialchars($p['nama']) ?></p>
                                <p><strong>Email:</strong><br><?= htmlspecialchars($p['email']) ?></p>
                                <p><strong>Telepon:</strong><br><?= htmlspecialchars($p['telepon'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Institusi:</strong><br><?= htmlspecialchars($p['institusi'] ?? '-') ?></p>
                                <p><strong>Status:</strong><br>
                                    <span class="badge bg-<?= $p['status'] === 'approved' ? 'success' : ($p['status'] === 'rejected' ? 'danger' : 'secondary') ?>">
                                        <?= ucfirst($p['status']) ?>
                                    </span>
                                </p>
                            </div>
                        </div>

                        <hr>
                        <h6 class="mb-3"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Informasi Webinar</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Judul Webinar:</strong><br><?= htmlspecialchars($p['judul']) ?></p>
                                <p><strong>Tanggal/Waktu:</strong><br><?= htmlspecialchars($p['tanggal'] ?? '-') ?> <?= htmlspecialchars($p['waktu'] ?? '') ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Biaya:</strong><br>Rp <?= number_format((float)$p['biaya'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="fas fa-file-image me-2"></i>Bukti Pembayaran</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php if (!empty($p['bukti_transfer'])): ?>
                            <img src="../<?= htmlspecialchars($p['bukti_transfer']) ?>" alt="Bukti" class="img-fluid rounded mb-3" style="max-height: 400px;">
                            <div>
                                <a href="../<?= htmlspecialchars($p['bukti_transfer']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-up-right-from-square me-1"></i>Buka Gambar</a>
                                <a href="../<?= htmlspecialchars($p['bukti_transfer']) ?>" download class="btn btn-sm btn-outline-secondary"><i class="fas fa-download me-1"></i>Unduh</a>
                            </div>
                        <?php else: ?>
                            <div class="text-muted">Belum ada bukti pembayaran.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Aksi Admin & Pembayaran -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="fas fa-receipt me-2 text-success"></i>Informasi Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span class="text-muted">Metode</span>
                                <span class="fw-semibold"><?= htmlspecialchars($p['metode_pembayaran'] ?? '-') ?></span>
                            </li>
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span class="text-muted">Status</span>
                                <span class="badge bg-<?= ($p['status'] === 'approved') ? 'success' : (($p['status'] === 'rejected') ? 'danger' : 'secondary') ?>"><?= ucfirst($p['status']) ?></span>
                            </li>
                            <?php if (!empty($p['bukti_transfer'])): ?>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Bukti</span>
                                    <a href="../<?= htmlspecialchars($p['bukti_transfer']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-up-right-from-square me-1"></i>Lihat</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="fas fa-cogs me-2 text-primary"></i>Aksi Admin</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($p['status'] === 'pending'): ?>
                            <form method="post" action="webinar_pendaftar.php" class="d-grid gap-2 mb-3">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                                <button name="action" value="approve" class="btn btn-success" onclick="return confirm('Setujui pendaftar ini?')">
                                    <i class="fas fa-check me-2"></i>Setujui
                                </button>
                                <button name="action" value="reject" class="btn btn-danger" onclick="return confirm('Tolak pendaftar ini?')">
                                    <i class="fas fa-times me-2"></i>Tolak
                                </button>
                            </form>
                        <?php endif; ?>

                        <!-- Tombol Hapus -->
                        <form method="post" action="webinar_pendaftar.php" class="d-grid">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                            <button name="action" value="delete" class="btn btn-outline-danger" onclick="return confirm('Yakin ingin menghapus pendaftar ini? Tindakan ini tidak dapat dibatalkan!')">
                                <i class="fas fa-trash me-2"></i>Hapus Pendaftar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.admin-sidebar');
            sidebar.classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.admin-sidebar');
            const toggle = document.querySelector('.mobile-menu-toggle');

            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    </script>
</body>

</html>