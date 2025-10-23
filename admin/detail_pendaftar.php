<?php
require_once 'check_auth.php';

$db = getDB();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: pendaftar.php');
    exit;
}

$pendaftar_id = $_GET['id'];

// Get registration details
$pendaftar = $db->fetch("
    SELECT p.*, kl.nama as kategori_nama, kl.jenis_lomba, kl.max_peserta, kl.biaya,
           up.nama_kelompok
    FROM b_pendaftar p 
    JOIN b_kategori_lomba kl ON p.kategori_lomba_id = kl.id 
    LEFT JOIN b_user_pendaftaran up ON up.pendaftar_id = p.id
    WHERE p.id = ?
", [$pendaftar_id]);

// Get payment details
$pembayaran = $db->fetch("
    SELECT * FROM b_pembayaran 
    WHERE pendaftar_id = ?
", [$pendaftar_id]);

if (!$pendaftar) {
    header('Location: pendaftar.php');
    exit;
}

// Get group members if it's a group competition
$anggota_kelompok = [];
if ($pendaftar['jenis_lomba'] == 'kelompok') {
    // Resolve anggota_kelompok table name across environments
    $tableCheck = $db->fetch("SHOW TABLES LIKE 'b_anggota_kelompok'");
    $anggotaTable = $tableCheck ? 'b_anggota_kelompok' : 'anggota_kelompok';
    $anggota_kelompok = $db->fetchAll("SELECT * FROM `$anggotaTable` WHERE pendaftar_id = ?", [$pendaftar_id]);
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $catatan = $_POST['catatan_admin'] ?? '';

    if ($action == 'verify') {
        $result = $db->update(
            'b_pendaftar',
            ['status' => 'confirmed', 'catatan_admin' => $catatan],
            'id = ?',
            [$pendaftar_id]
        );
        // Sinkronkan status ke tabel user_pendaftaran
        $db->update(
            'b_user_pendaftaran',
            ['status' => 'approved', 'tanggal_approval' => date('Y-m-d H:i:s')],
            'pendaftar_id = ?',
            [$pendaftar_id]
        );

        // Otomatis setujui pembayaran jika ada
        $db->update(
            'b_pembayaran',
            ['status' => 'success', 'tanggal_pembayaran' => date('Y-m-d H:i:s')],
            'pendaftar_id = ? AND status = ?',
            [$pendaftar_id, 'pending']
        );

        if ($result) {
            header('Location: pendaftar.php?success=Pendaftaran berhasil diverifikasi dan pembayaran disetujui');
            exit;
        }
    } elseif ($action == 'reject') {
        $result = $db->update(
            'b_pendaftar',
            ['status' => 'rejected', 'catatan_admin' => $catatan],
            'id = ?',
            [$pendaftar_id]
        );
        // Sinkronkan status ke tabel user_pendaftaran
        $db->update(
            'b_user_pendaftaran',
            ['status' => 'rejected', 'tanggal_approval' => date('Y-m-d H:i:s')],
            'pendaftar_id = ?',
            [$pendaftar_id]
        );

        if ($result) {
            header('Location: pendaftar.php?success=Pendaftaran ditolak');
            exit;
        }
    } elseif ($action == 'approve_payment') {
        // Update status pembayaran menjadi success
        $result = $db->update(
            'b_pembayaran',
            ['status' => 'success', 'tanggal_pembayaran' => date('Y-m-d H:i:s')],
            'pendaftar_id = ?',
            [$pendaftar_id]
        );

        if ($result) {
            header('Location: detail_pendaftar.php?id=' . $pendaftar_id . '&success=Pembayaran berhasil disetujui');
            exit;
        }
    } elseif ($action == 'reject_payment') {
        // Update status pembayaran menjadi failed
        $result = $db->update(
            'b_pembayaran',
            ['status' => 'failed'],
            'pendaftar_id = ?',
            [$pendaftar_id]
        );

        if ($result) {
            header('Location: detail_pendaftar.php?id=' . $pendaftar_id . '&success=Pembayaran ditolak');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pendaftar - Admin Panel</title>
    <link rel="icon" type="image/png" href="../favicon/bcf.png" sizes="32x32">
    <link href="../bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #667eea;
            --primary-dark: #1630df;
            --secondary-color: #f8f9fa;
            --text-color: #2c3e50;
            --text-muted: #6c757d;
            --border-color: #e9ecef;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 4px 20px rgba(0, 0, 0, 0.12);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--secondary-color);
            color: var(--text-color);
        }

        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .admin-content {
            background-color: var(--secondary-color);
            min-height: 100vh;
            margin-left: 250px;
            transition: margin-left 0.3s ease;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.85);
            transition: all 0.2s ease;
            border-radius: 8px;
            margin-bottom: 4px;
            padding: 12px 16px;
        }

        .nav-link:hover,
        .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateX(4px);
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }

        .status-badge {
            font-size: 12px;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-admin {
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-admin:hover {
            transform: translateY(-1px);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                width: 280px;
            }

            .admin-sidebar.show {
                transform: translateX(0);
            }

            .admin-content {
                margin-left: 0;
            }

            .d-flex.justify-content-between {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .d-flex.justify-content-between>div:last-child {
                margin-top: 8px;
            }

            .card-body {
                padding: 16px;
            }

            .row>div {
                margin-bottom: 16px;
            }
        }

        @media (max-width: 576px) {
            .admin-content {
                padding: 16px !important;
            }

            .card-body {
                padding: 12px;
            }

            .status-badge {
                font-size: 10px;
                padding: 4px 8px;
            }

            .btn-admin {
                padding: 6px 12px;
                font-size: 14px;
            }
        }

        /* Tablet Responsive */
        @media (min-width: 769px) and (max-width: 1024px) {
            .admin-sidebar {
                width: 220px;
            }

            .admin-content {
                margin-left: 220px;
            }
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px;
            box-shadow: var(--shadow);
        }

        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }
        }

        /* Clean Status Badges */
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Clean Buttons */
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 6px;
        }

        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Image Modal Responsive */
        @media (max-width: 768px) {
            .modal-dialog {
                margin: 10px;
            }

            .modal-body {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
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
                        <a class="nav-link active mb-2" href="pendaftar.php">
                            <i class="fas fa-users me-2"></i>Pendaftar Lomba
                        </a>
                        <a class="nav-link mb-2" href="kategori.php">
                            <i class="fas fa-tags me-2"></i>Kategori Lomba
                        </a>
                        <a class="nav-link mb-2" href="webinar_manage.php">
                            <i class="fas fa-chalkboard-teacher me-2"></i>Kelola Webinar
                        </a>
                        <a class="nav-link mb-2" href="webinar_pendaftar.php">
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
            </div>

            <!-- Main Content -->
            <div class="col-12">
                <div class="admin-content p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">
                            <i class="fas fa-user me-2 text-primary"></i>Detail Pendaftar
                        </h2>
                        <a href="pendaftar.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['success']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Registration Details -->
                        <div class="col-lg-8">
                            <div class="card mb-4">
                                <div class="card-header bg-white border-0">
                                    <h5 class="mb-0">
                                        <i class="fas fa-info-circle me-2 text-primary"></i>Informasi Pendaftar
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>ID Pendaftaran:</strong><br><?= htmlspecialchars($pendaftar['id']) ?></p>
                                            <p><strong>Nama:</strong><br><?= htmlspecialchars($pendaftar['nama']) ?></p>
                                            <p><strong>Email:</strong><br><?= htmlspecialchars($pendaftar['email']) ?></p>
                                            <p><strong>Telepon:</strong><br><?= htmlspecialchars($pendaftar['telepon']) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Sekolah:</strong><br><?= htmlspecialchars($pendaftar['sekolah']) ?></p>
                                            <p><strong>Kelas:</strong><br><?= htmlspecialchars($pendaftar['kelas']) ?></p>
                                            <p><strong>Kategori Lomba:</strong><br>
                                                <span class="badge bg-info"><?= htmlspecialchars($pendaftar['kategori_nama']) ?></span>
                                                <span class="badge bg-<?= ($pendaftar['jenis_lomba'] ?? 'individu') == 'kelompok' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst($pendaftar['jenis_lomba'] ?? 'individu') ?>
                                                </span>
                                            </p>
                                            <?php if (!empty($pendaftar['nama_kelompok'])): ?>
                                                <p><strong>Nama Kelompok:</strong><br>
                                                    <span class="badge bg-success"><?= htmlspecialchars($pendaftar['nama_kelompok']) ?></span>
                                                </p>
                                            <?php endif; ?>
                                            <p><strong>Status:</strong><br>
                                                <?php if ($pendaftar['status'] == 'pending'): ?>
                                                    <span class="badge bg-warning status-badge">Menunggu Verifikasi</span>
                                                <?php elseif ($pendaftar['status'] == 'confirmed'): ?>
                                                    <span class="badge bg-success status-badge">Terverifikasi</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger status-badge">Ditolak</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>



                                    <?php if (!empty($pendaftar['catatan_admin'])): ?>
                                        <div class="mt-3">
                                            <p><strong>Catatan Admin:</strong><br><?= nl2br(htmlspecialchars($pendaftar['catatan_admin'])) ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-3">
                                        <p><strong>Tanggal Pendaftaran:</strong><br><?= date('d/m/Y H:i', strtotime($pendaftar['created_at'])) ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Instagram Follow Proof - Ketua Tim -->
                            <div class="card mb-4">
                                <div class="card-header bg-white border-0">
                                    <h5 class="mb-0">
                                        <i class="fab fa-instagram me-2 text-danger"></i>Bukti Follow Instagram - Peserta
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Peserta:</strong> <?= htmlspecialchars($pendaftar['nama']) ?>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <h6 class="text-primary">@bluvocationfest</h6>
                                            <?php if (!empty($pendaftar['ig_bluvocationfest_proof'])): ?>
                                                <img src="../uploads/<?= htmlspecialchars($pendaftar['ig_bluvocationfest_proof']) ?>"
                                                    alt="Bukti Follow @bluvocationfest" class="img-fluid rounded mb-2" style="max-height: 200px;">
                                                <button class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#igProofModal"
                                                    data-image="<?= htmlspecialchars($pendaftar['ig_bluvocationfest_proof']) ?>"
                                                    data-account="@bluvocationfest - Ketua Tim">
                                                    <i class="fas fa-expand me-1"></i>Lihat Full Size
                                                </button>
                                            <?php else: ?>
                                                <div class="text-muted py-3">
                                                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                                    <p>Belum upload bukti follow</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <h6 class="text-primary">@smk.budiluhur</h6>
                                            <?php if (!empty($pendaftar['ig_smkblverse_proof'])): ?>
                                                <img src="../uploads/<?= htmlspecialchars($pendaftar['ig_smkblverse_proof']) ?>"
                                                    alt="Bukti Follow @smk.budiluhur" class="img-fluid rounded mb-2" style="max-height: 200px;">
                                                <button class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#igProofModal"
                                                    data-image="<?= htmlspecialchars($pendaftar['ig_smkblverse_proof']) ?>"
                                                    data-account="@smk.budiluhur - Ketua Tim">
                                                    <i class="fas fa-expand me-1"></i>Lihat Full Size
                                                </button>
                                            <?php else: ?>
                                                <div class="text-muted py-3">
                                                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                                    <p>Belum upload bukti follow</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Group Members (if group competition) -->
                            <?php if (($pendaftar['jenis_lomba'] ?? 'individu') == 'kelompok'): ?>
                                <div class="card mb-4">
                                    <div class="card-header bg-white border-0">
                                        <h5 class="mb-0">
                                            <i class="fas fa-users me-2 text-primary"></i>Anggota Kelompok
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($anggota_kelompok)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Nama</th>
                                                            <th>Nama Kelompok</th>
                                                            <th>Email</th>
                                                            <th>Telepon</th>
                                                            <th>Sekolah</th>
                                                            <th>Kelas</th>
                                                            <th>Kartu Pelajar</th>
                                                            <th>IG @bluvocationfest</th>
                                                            <th>IG @smk.budiluhur</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($anggota_kelompok as $anggota): ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($anggota['nama']) ?></td>
                                                                <td><?= htmlspecialchars($pendaftar['nama_kelompok'] ?? ($anggota['nama_kelompok'] ?? '-')) ?></td>
                                                                <td><?= htmlspecialchars($anggota['email']) ?></td>
                                                                <td><?= htmlspecialchars($anggota['telepon']) ?></td>
                                                                <td><?= htmlspecialchars($anggota['sekolah']) ?></td>
                                                                <td><?= htmlspecialchars($anggota['kelas']) ?></td>
                                                                <td>
                                                                    <?php if (!empty($anggota['foto_kartu_pelajar'])): ?>
                                                                        <button class="btn btn-sm btn-outline-primary"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#kartuPelajarModal"
                                                                            data-image="<?= htmlspecialchars($anggota['foto_kartu_pelajar']) ?>"
                                                                            data-nama="<?= htmlspecialchars($anggota['nama']) ?>">
                                                                            <i class="fas fa-eye me-1"></i>Lihat
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">Tidak ada</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if (!empty($anggota['ig_bluvocationfest_proof'])): ?>
                                                                        <button class="btn btn-sm btn-outline-danger"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#igProofModal"
                                                                            data-image="<?= htmlspecialchars($anggota['ig_bluvocationfest_proof']) ?>"
                                                                            data-account="@bluvocationfest">
                                                                            <i class="fab fa-instagram me-1"></i>Lihat
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">Tidak ada</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if (!empty($anggota['ig_smkblverse_proof'])): ?>
                                                                        <button class="btn btn-sm btn-outline-danger"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#igProofModal"
                                                                            data-image="<?= htmlspecialchars($anggota['ig_smkblverse_proof']) ?>"
                                                                            data-account="@smkbl.verse">
                                                                            <i class="fab fa-instagram me-1"></i>Lihat
                                                                        </button>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">Tidak ada</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center text-muted py-3">
                                                Belum ada anggota kelompok yang terdaftar.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Group Members Instagram Follow Proof -->
                            <?php if (($pendaftar['jenis_lomba'] ?? 'individu') == 'kelompok' && !empty($anggota_kelompok)): ?>
                                <div class="card mb-4">
                                    <div class="card-header bg-white border-0">
                                        <h5 class="mb-0">
                                            <i class="fab fa-instagram me-2 text-danger"></i>Bukti Follow Instagram - Anggota Kelompok
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach ($anggota_kelompok as $index => $anggota): ?>
                                            <div class="mb-4">
                                                <h6 class="text-primary mb-3">
                                                    <i class="fas fa-user me-2"></i><?= htmlspecialchars($anggota['nama']) ?>
                                                </h6>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <h6 class="text-info">@bluvocationfest</h6>
                                                        <?php if (!empty($anggota['ig_bluvocationfest_proof'])): ?>
                                                            <img src="../uploads/<?= htmlspecialchars($anggota['ig_bluvocationfest_proof']) ?>"
                                                                alt="Bukti Follow @bluvocationfest - <?= htmlspecialchars($anggota['nama']) ?>"
                                                                class="img-fluid rounded mb-2" style="max-height: 150px;">
                                                            <button class="btn btn-sm btn-outline-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#igProofModal"
                                                                data-image="<?= htmlspecialchars($anggota['ig_bluvocationfest_proof']) ?>"
                                                                data-account="@bluvocationfest - <?= htmlspecialchars($anggota['nama']) ?>">
                                                                <i class="fas fa-expand me-1"></i>Lihat Full Size
                                                            </button>
                                                        <?php else: ?>
                                                            <div class="text-muted py-2">
                                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                                Belum upload bukti follow
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <h6 class="text-info">@smk.budiluhur</h6>
                                                        <?php if (!empty($anggota['ig_smkblverse_proof'])): ?>
                                                            <img src="../uploads/<?= htmlspecialchars($anggota['ig_smkblverse_proof']) ?>"
                                                                alt="Bukti Follow @smk.budiluhur - <?= htmlspecialchars($anggota['nama']) ?>"
                                                                class="img-fluid rounded mb-2" style="max-height: 150px;">
                                                            <button class="btn btn-sm btn-outline-danger"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#igProofModal"
                                                                data-image="<?= htmlspecialchars($anggota['ig_smkblverse_proof']) ?>"
                                                                data-account="@smkbl.verse - <?= htmlspecialchars($anggota['nama']) ?>">
                                                                <i class="fas fa-expand me-1"></i>Lihat Full Size
                                                            </button>
                                                        <?php else: ?>
                                                            <div class="text-muted py-2">
                                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                                Belum upload bukti follow
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php if ($index < count($anggota_kelompok) - 1): ?>
                                                    <hr class="my-3">
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Student ID Card & Actions -->
                        <div class="col-lg-4">
                            <!-- Student ID Card -->
                            <div class="card mb-4">
                                <div class="card-header bg-white border-0">
                                    <h5 class="mb-0">
                                        <i class="fas fa-id-card me-2 text-primary"></i>Kartu Pelajar
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    <?php if (!empty($pendaftar['foto_kartu_pelajar'])): ?>
                                        <img src="../uploads/<?= htmlspecialchars($pendaftar['foto_kartu_pelajar']) ?>"
                                            alt="Kartu Pelajar" class="img-fluid rounded mb-3" style="max-height: 300px;">
                                        <p class="text-muted">Kartu Pelajar: <?= htmlspecialchars($pendaftar['nama']) ?></p>
                                    <?php else: ?>
                                        <div class="text-muted py-4">
                                            <i class="fas fa-image fa-3x mb-3"></i>
                                            <p>Tidak ada foto kartu pelajar</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Payment Information -->
                            <div class="card mb-4">
                                <div class="card-header bg-white border-0">
                                    <h5 class="mb-0">
                                        <i class="fas fa-receipt me-2 text-success"></i>Informasi Pembayaran
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($pembayaran): ?>
                                        <div class="text-center mb-3">
                                            <?php if (!empty($pembayaran['bukti_transfer'])): ?>
                                                <img src="../uploads/bukti_transfer/<?= htmlspecialchars($pembayaran['bukti_transfer']) ?>"
                                                    alt="Bukti Pembayaran" class="img-fluid rounded mb-3" style="max-height: 200px;">
                                            <?php endif; ?>
                                        </div>

                                        <div class="row">
                                            <div class="col-6">
                                                <p class="mb-2"><strong>ID Pembayaran:</strong></p>
                                                <p class="mb-2"><strong>Metode:</strong></p>
                                                <p class="mb-2"><strong>Jumlah:</strong></p>
                                                <p class="mb-2"><strong>Status:</strong></p>
                                                <p class="mb-2"><strong>Tanggal:</strong></p>
                                            </div>
                                            <div class="col-6">
                                                <p class="mb-2 text-muted"><?= htmlspecialchars($pembayaran['id']) ?></p>
                                                <p class="mb-2 text-muted"><?= ucfirst(htmlspecialchars($pembayaran['metode_pembayaran'])) ?></p>
                                                <p class="mb-2 text-muted">Rp <?= number_format($pembayaran['jumlah'], 0, ',', '.') ?></p>
                                                <p class="mb-2">
                                                    <?php if ($pembayaran['status'] == 'success'): ?>
                                                        <span class="badge bg-success text-white">
                                                            <i class="fas fa-check me-1"></i>Diterima
                                                        </span>
                                                    <?php elseif ($pembayaran['status'] == 'pending'): ?>
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-clock me-1"></i>Menunggu Verifikasi
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times me-1"></i>Ditolak
                                                        </span>
                                                    <?php endif; ?>
                                                </p>
                                                <p class="mb-2 text-muted">
                                                    <?php if ($pembayaran['status'] == 'success' && $pembayaran['tanggal_pembayaran']): ?>
                                                        <?= date('d/m/Y H:i', strtotime($pembayaran['tanggal_pembayaran'])) ?>
                                                        <small class="text-success d-block">(Disetujui)</small>
                                                    <?php else: ?>
                                                        <?= date('d/m/Y H:i', strtotime($pembayaran['created_at'])) ?>
                                                        <small class="text-muted d-block">(Dibuat)</small>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>

                                        <?php if (!empty($pembayaran['bukti_transfer'])): ?>
                                            <div class="text-center mt-3">
                                                <button class="btn btn-sm btn-outline-success"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#buktiPembayaranModal"
                                                    data-image="<?= htmlspecialchars($pembayaran['bukti_transfer']) ?>"
                                                    data-nama="<?= htmlspecialchars($pendaftar['nama']) ?>"
                                                    data-metode="<?= htmlspecialchars($pembayaran['metode_pembayaran']) ?>"
                                                    data-status="<?= htmlspecialchars($pembayaran['status']) ?>">
                                                    <i class="fas fa-expand me-1"></i>Lihat Detail
                                                </button>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Payment Actions -->
                                        <?php if ($pembayaran): ?>
                                            <div class="mt-3">
                                                <hr>
                                                <?php if ($pembayaran['status'] == 'pending'): ?>
                                                    <h6 class="text-primary mb-3">
                                                        <i class="fas fa-cogs me-2"></i>Aksi Pembayaran
                                                    </h6>
                                                    <form method="POST" action="" class="d-grid gap-2">
                                                        <button type="submit" name="action" value="approve_payment"
                                                            class="btn btn-success btn-sm"
                                                            onclick="return confirm('Setujui pembayaran ini?')">
                                                            <i class="fas fa-check me-1"></i>Setujui Pembayaran
                                                        </button>
                                                        <button type="submit" name="action" value="reject_payment"
                                                            class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Tolak pembayaran ini?')">
                                                            <i class="fas fa-times me-1"></i>Tolak Pembayaran
                                                        </button>
                                                    </form>
                                                <?php elseif ($pembayaran['status'] == 'success'): ?>
                                                    <div class="alert alert-success mb-0">
                                                        <i class="fas fa-check-circle me-2"></i>
                                                        <strong>Pembayaran telah disetujui!</strong><br>
                                                        <small>Pembayaran ini telah diverifikasi dan diterima pada
                                                            <?= date('d/m/Y H:i', strtotime($pembayaran['tanggal_pembayaran'])) ?>
                                                        </small>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="alert alert-danger mb-0">
                                                        <i class="fas fa-times-circle me-2"></i>
                                                        <strong>Pembayaran ditolak!</strong><br>
                                                        <small>Pembayaran ini telah ditolak oleh admin.</small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-receipt fa-3x mb-3"></i>
                                            <p>Belum ada pembayaran</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Admin Actions -->
                            <?php if ($pendaftar['status'] == 'pending'): ?>
                                <div class="card">
                                    <div class="card-header bg-white border-0">
                                        <h5 class="mb-0">
                                            <i class="fas fa-cogs me-2 text-primary"></i>Aksi Admin
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="">
                                            <div class="mb-3">
                                                <label for="catatan_admin" class="form-label">Catatan (Opsional)</label>
                                                <textarea class="form-control" id="catatan_admin" name="catatan_admin" rows="3" placeholder="Tambahkan catatan jika diperlukan..."><?= htmlspecialchars($pendaftar['catatan_admin'] ?? '') ?></textarea>
                                            </div>

                                            <div class="d-grid gap-2">
                                                <button type="submit" name="action" value="verify" class="btn btn-success"
                                                    onclick="return confirm('Verifikasi pendaftaran ini?')">
                                                    <i class="fas fa-check me-2"></i>Verifikasi
                                                </button>
                                                <button type="submit" name="action" value="reject" class="btn btn-danger"
                                                    onclick="return confirm('Tolak pendaftaran ini?')">
                                                    <i class="fas fa-times me-2"></i>Tolak
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student ID Card Modal -->
    <div class="modal fade" id="kartuPelajarModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-id-card me-2"></i>Kartu Pelajar
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="text-muted mb-3">Kartu Pelajar: <span id="studentName"></span></p>
                    <img id="studentCardImage" src="" alt="Kartu Pelajar" class="img-fluid rounded" style="max-height: 500px;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bukti Pembayaran Modal -->
    <div class="modal fade" id="buktiPembayaranModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-receipt me-2"></i>Bukti Pembayaran
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Nama:</strong> <span id="paymentName"></span><br>
                            <strong>Metode:</strong> <span id="paymentMethod"></span><br>
                            <strong>Status:</strong> <span id="paymentStatus"></span>
                        </div>
                        <div class="col-md-6 text-end">
                            <span id="paymentStatusBadge" class="badge"></span>
                        </div>
                    </div>
                    <div class="text-center">
                        <img id="paymentImage" src="" alt="Bukti Pembayaran" class="img-fluid rounded" style="max-height: 500px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Instagram Follow Proof Modal -->
    <div class="modal fade" id="igProofModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fab fa-instagram me-2"></i>Bukti Follow Instagram
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="text-muted mb-3">Akun: <span id="igAccount"></span></p>
                    <img id="igProofImage" src="" alt="Bukti Follow Instagram" class="img-fluid rounded" style="max-height: 500px;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile menu toggle
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

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.querySelector('.admin-sidebar');
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
            }
        });

        // Handle student ID card modal
        document.addEventListener('DOMContentLoaded', function() {
            const kartuPelajarModal = document.getElementById('kartuPelajarModal');
            if (kartuPelajarModal) {
                kartuPelajarModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const image = button.getAttribute('data-image');
                    const nama = button.getAttribute('data-nama');

                    document.getElementById('studentCardImage').src = '../uploads/' + image;
                    document.getElementById('studentName').textContent = nama;
                });
            }

            // Handle Instagram follow proof modal
            const igProofModal = document.getElementById('igProofModal');
            if (igProofModal) {
                igProofModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const image = button.getAttribute('data-image');
                    const account = button.getAttribute('data-account');

                    document.getElementById('igProofImage').src = '../uploads/' + image;
                    document.getElementById('igAccount').textContent = account;
                });
            }

            // Handle bukti pembayaran modal
            const buktiPembayaranModal = document.getElementById('buktiPembayaranModal');
            if (buktiPembayaranModal) {
                buktiPembayaranModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const image = button.getAttribute('data-image');
                    const nama = button.getAttribute('data-nama');
                    const metode = button.getAttribute('data-metode');
                    const status = button.getAttribute('data-status');

                    document.getElementById('paymentImage').src = '../uploads/bukti_transfer/' + image;
                    document.getElementById('paymentName').textContent = nama;
                    document.getElementById('paymentMethod').textContent = metode;
                    document.getElementById('paymentStatus').textContent = status;

                    // Set status badge
                    const statusBadge = document.getElementById('paymentStatusBadge');
                    statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    statusBadge.className = `badge bg-${status === 'success' ? 'success' : (status === 'pending' ? 'warning' : 'danger')}`;
                });
            }
        });
    </script>
</body>

</html>