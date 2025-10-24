<?php
require_once 'check_auth.php';

$db = getDB();
$message = '';
$message_type = '';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $pendaftar_id = $_POST['pendaftar_id'] ?? '';

    if ($action == 'verify' && !empty($pendaftar_id)) {
        $result = $db->update('{prefix}pendaftar', ['status' => 'confirmed'], 'id = ?', [$pendaftar_id]);

        // Sinkronkan status ke tabel user_pendaftaran
        $db->update(
            '{prefix}user_pendaftaran',
            ['status' => 'approved', 'tanggal_approval' => date('Y-m-d H:i:s')],
            'pendaftar_id = ?',
            [$pendaftar_id]
        );

        // Otomatis setujui pembayaran jika ada
        $db->update(
            '{prefix}pembayaran',
            ['status' => 'success', 'tanggal_pembayaran' => date('Y-m-d H:i:s')],
            'pendaftar_id = ? AND status = ?',
            [$pendaftar_id, 'pending']
        );

        if ($result) {
            $message = 'Pendaftaran berhasil diverifikasi dan pembayaran disetujui!';
            $message_type = 'success';
        } else {
            $message = 'Gagal memverifikasi pendaftaran!';
            $message_type = 'danger';
        }
    } elseif ($action == 'reject' && !empty($pendaftar_id)) {
        $result = $db->update('{prefix}pendaftar', ['status' => 'rejected'], 'id = ?', [$pendaftar_id]);

        if ($result) {
            $message = 'Pendaftaran ditolak!';
            $message_type = 'success';
        } else {
            $message = 'Gagal menolak pendaftaran!';
            $message_type = 'danger';
        }
    }
}

// Get filters
$status_filter = $_GET['status'] ?? '';
$kategori_filter = $_GET['kategori'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($status_filter)) {
    $where_conditions[] = "p.status = ?";
    $params[] = $status_filter;
}

if (!empty($kategori_filter)) {
    $where_conditions[] = "p.kategori_lomba_id = ?";
    $params[] = $kategori_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(p.nama LIKE ? OR p.email LIKE ? OR p.sekolah LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get registrations
$query = "
    SELECT p.*, kl.nama as kategori_nama, kl.jenis_lomba, kl.max_peserta,
           pay.bukti_transfer, pay.status as status_pembayaran, pay.metode_pembayaran,
           up.nama_kelompok
    FROM {prefix}pendaftar p 
    JOIN {prefix}kategori_lomba kl ON p.kategori_lomba_id = kl.id 
    LEFT JOIN {prefix}pembayaran pay ON p.id = pay.pendaftar_id
    LEFT JOIN {prefix}user_pendaftaran up ON up.pendaftar_id = p.id
    $where_clause
    ORDER BY p.created_at DESC
";

$pendaftar_list = $db->fetchAll($query, $params);

// Get categories for filter
$kategori_list = $db->fetchAll("SELECT id, nama FROM {prefix}kategori_lomba ORDER BY nama");

// Get statistics
$total_pendaftar = $db->fetch("SELECT COUNT(*) as total FROM {prefix}pendaftar")['total'];
$pending_pendaftar = $db->fetch("SELECT COUNT(*) as total FROM {prefix}pendaftar WHERE status = 'pending'")['total'];
$confirmed_pendaftar = $db->fetch("SELECT COUNT(*) as total FROM {prefix}pendaftar WHERE status = 'confirmed'")['total'];
$rejected_pendaftar = $db->fetch("SELECT COUNT(*) as total FROM {prefix}pendaftar WHERE status = 'rejected'")['total'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pendaftar - Admin Panel</title>
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

        .table-responsive {
            border-radius: 12px;
            overflow-x: auto;
            overflow-y: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            -webkit-overflow-scrolling: touch;
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

        .status-badge {
            font-size: 12px;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filters-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }

        .table th {
            background-color: #f8f9fa;
            border: none;
            font-weight: 600;
            color: var(--text-color);
            padding: 16px 12px;
        }

        .table td {
            border: none;
            padding: 12px;
            vertical-align: middle;
        }

        .table tbody tr {
            border-bottom: 1px solid var(--border-color);
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
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

            .table-responsive {
                font-size: 14px;
            }

            .table th,
            .table td {
                padding: 8px 6px;
            }

            .btn-admin {
                padding: 6px 12px;
                font-size: 14px;
            }

            .d-flex.justify-content-between {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .d-flex.justify-content-between>div:last-child {
                margin-top: 8px;
            }

            .filters-card .row>div {
                margin-bottom: 16px;
            }
        }

        @media (max-width: 576px) {
            .admin-content {
                padding: 16px !important;
            }

            .table-responsive {
                font-size: 12px;
            }

            .btn-admin {
                padding: 4px 8px;
                font-size: 12px;
            }

            .status-badge {
                font-size: 10px;
                padding: 4px 8px;
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
                            <i class="fas fa-users me-2 text-primary"></i>Kelola Pendaftar
                        </h2>
                        <div>
                            <span class="text-muted">Total: <?= $total_pendaftar ?> pendaftar</span>
                        </div>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                            <i class="fas fa-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3 class="text-primary mb-1"><?= $total_pendaftar ?></h3>
                                    <p class="text-muted mb-0">Total Pendaftar</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3 class="text-warning mb-1"><?= $pending_pendaftar ?></h3>
                                    <p class="text-muted mb-0">Menunggu Verifikasi</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3 class="text-success mb-1"><?= $confirmed_pendaftar ?></h3>
                                    <p class="text-muted mb-0">Terverifikasi</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h3 class="text-danger mb-1"><?= $rejected_pendaftar ?></h3>
                                    <p class="text-muted mb-0">Ditolak</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="filters-card p-4 mb-4">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Cari</label>
                                <input type="text" class="form-control" id="search" name="search"
                                    value="<?= htmlspecialchars($search) ?>" placeholder="Nama, email, atau sekolah">
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Semua Status</option>
                                    <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Menunggu</option>
                                    <option value="confirmed" <?= $status_filter == 'confirmed' ? 'selected' : '' ?>>Terverifikasi</option>
                                    <option value="rejected" <?= $status_filter == 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="kategori" class="form-label">Kategori</label>
                                <select class="form-select" id="kategori" name="kategori">
                                    <option value="">Semua Kategori</option>
                                    <?php foreach ($kategori_list as $kategori): ?>
                                        <option value="<?= $kategori['id'] ?>" <?= $kategori_filter == $kategori['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kategori['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-2"></i>Filter
                                </button>
                                <a href="pendaftar.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Registrations Table -->
                    <div class="card">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2 text-primary"></i>Daftar Pendaftar
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Pendaftar</th>
                                            <th>Kategori</th>
                                            <th>Nama Kelompok</th>
                                            <th>Sekolah</th>
                                            <th>Kartu Pelajar</th>
                                            <th>Bukti Pembayaran</th>
                                            <th>Status</th>
                                            <th>Tanggal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($pendaftar_list)): ?>
                                            <tr>
                                                <td colspan="9" class="text-center py-4">
                                                    <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                                                    <p class="text-muted">Tidak ada data pendaftar</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($pendaftar_list as $pendaftar): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                                                <?= strtoupper(substr($pendaftar['nama'], 0, 1)) ?>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold"><?= htmlspecialchars($pendaftar['nama']) ?></div>
                                                                <small class="text-muted"><?= htmlspecialchars($pendaftar['email']) ?></small>
                                                                <br>
                                                                <small class="text-muted"><?= htmlspecialchars($pendaftar['telepon']) ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <span class="badge bg-info"><?= htmlspecialchars($pendaftar['kategori_nama']) ?></span>
                                                            <br>
                                                            <small class="text-muted">
                                                                <?= ucfirst($pendaftar['jenis_lomba'] ?? 'individu') ?>
                                                                <?php if (($pendaftar['jenis_lomba'] ?? 'individu') == 'kelompok'): ?>
                                                                    (Max: <?= $pendaftar['max_peserta'] ?? 1 ?>)
                                                                <?php endif; ?>
                                                            </small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if (($pendaftar['jenis_lomba'] ?? 'individu') == 'kelompok' && !empty($pendaftar['nama_kelompok'])): ?>
                                                            <div>
                                                                <span class="badge bg-success"><?= htmlspecialchars($pendaftar['nama_kelompok']) ?></span>
                                                            </div>
                                                        <?php else: ?>
                                                            <div>
                                                                <span class="text-muted">-</span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <div class="fw-bold"><?= htmlspecialchars($pendaftar['sekolah']) ?></div>
                                                            <small class="text-muted">Kelas <?= $pendaftar['kelas'] ?></small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($pendaftar['foto_kartu_pelajar'])): ?>
                                                            <button class="btn btn-sm btn-outline-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#kartuPelajarModal"
                                                                data-image="<?= htmlspecialchars($pendaftar['foto_kartu_pelajar']) ?>"
                                                                data-nama="<?= htmlspecialchars($pendaftar['nama']) ?>">
                                                                <i class="fas fa-eye me-1"></i>Lihat
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="text-muted">Tidak ada</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($pendaftar['bukti_transfer'])): ?>
                                                            <div class="d-flex flex-column gap-1">
                                                                <button class="btn btn-sm btn-outline-success"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#buktiPembayaranModal"
                                                                    data-image="<?= htmlspecialchars($pendaftar['bukti_transfer']) ?>"
                                                                    data-nama="<?= htmlspecialchars($pendaftar['nama']) ?>"
                                                                    data-metode="<?= htmlspecialchars($pendaftar['metode_pembayaran'] ?? '') ?>"
                                                                    data-status="<?= htmlspecialchars($pendaftar['status_pembayaran'] ?? '') ?>">
                                                                    <i class="fas fa-receipt me-1"></i>Lihat Bukti
                                                                </button>
                                                                <small class="text-muted">
                                                                    <?= ucfirst($pendaftar['metode_pembayaran'] ?? '') ?>
                                                                </small>
                                                                <span class="badge bg-<?= ($pendaftar['status_pembayaran'] ?? '') == 'success' ? 'success' : (($pendaftar['status_pembayaran'] ?? '') == 'pending' ? 'warning' : 'danger') ?>">
                                                                    <?= ucfirst($pendaftar['status_pembayaran'] ?? 'pending') ?>
                                                                </span>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">Belum upload</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($pendaftar['status'] == 'pending'): ?>
                                                            <span class="badge bg-warning status-badge">Menunggu</span>
                                                        <?php elseif ($pendaftar['status'] == 'confirmed'): ?>
                                                            <span class="badge bg-success status-badge">Terverifikasi</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger status-badge">Ditolak</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?= date('d/m/Y H:i', strtotime($pendaftar['created_at'])) ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="detail_pendaftar.php?id=<?= $pendaftar['id'] ?>"
                                                                class="btn btn-sm btn-outline-primary" title="Detail">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <?php if ($pendaftar['status'] == 'pending'): ?>
                                                                <form method="POST" action="" class="d-inline"
                                                                    onsubmit="return confirm('Verifikasi pendaftaran ini?')">
                                                                    <input type="hidden" name="action" value="verify">
                                                                    <input type="hidden" name="pendaftar_id" value="<?= $pendaftar['id'] ?>">
                                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Verifikasi">
                                                                        <i class="fas fa-check"></i>
                                                                    </button>
                                                                </form>
                                                                <form method="POST" action="" class="d-inline"
                                                                    onsubmit="return confirm('Tolak pendaftaran ini?')">
                                                                    <input type="hidden" name="action" value="reject">
                                                                    <input type="hidden" name="pendaftar_id" value="<?= $pendaftar['id'] ?>">
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Tolak">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
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