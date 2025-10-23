<?php
require_once 'check_auth.php';

$db = getDB();
$message = '';
$message_type = '';

// Handle success message from URL
if (isset($_GET['success'])) {
    $message = $_GET['success'];
    $message_type = 'success';
}

// Actions approve/reject/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = $_POST['id'] ?? '';
    if ($_POST['action'] === 'approve') {
        $upd = $db->update('b_webinar_pendaftar', ['status' => 'approved'], 'id = ?', [$id]);
        $message = $upd ? 'Pendaftar disetujui' : 'Gagal menyetujui';
        $message_type = $upd ? 'success' : 'danger';
    } elseif ($_POST['action'] === 'reject') {
        $upd = $db->update('b_webinar_pendaftar', ['status' => 'rejected'], 'id = ?', [$id]);
        $message = $upd ? 'Pendaftar ditolak' : 'Gagal menolak';
        $message_type = $upd ? 'success' : 'danger';
    } elseif ($_POST['action'] === 'delete') {
        // Hapus pendaftar webinar
        $del = $db->delete('b_webinar_pendaftar', 'id = ?', [$id]);
        if ($del) {
            header('Location: webinar_pendaftar.php?success=Pendaftar berhasil dihapus');
            exit;
        } else {
            $message = 'Gagal menghapus pendaftar';
            $message_type = 'danger';
        }
    }
}

// Filters
$status = $_GET['status'] ?? '';
$webinar_id = $_GET['webinar_id'] ?? '';
$where = [];
$params = [];
if ($status) {
    $where[] = 'wp.status = ?';
    $params[] = $status;
}
if ($webinar_id) {
    $where[] = 'wp.webinar_id = ?';
    $params[] = $webinar_id;
}
$where_clause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Fetch
$pendaftar = $db->fetchAll("SELECT wp.*, w.judul FROM b_webinar_pendaftar wp JOIN b_webinar w ON wp.webinar_id = w.id $where_clause ORDER BY wp.created_at DESC", $params);
$webinars = $db->fetchAll('SELECT id, judul FROM b_webinar ORDER BY judul ASC');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftar Webinar - Admin</title>
    <link rel="icon" type="image/png" href="../favicon/bcf.png" sizes="32x32">
    <link href="../bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .stat-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            transition: all 0.2s ease;
            border: 1px solid var(--border-color);
        }

        .stat-card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }

        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
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

        .btn-admin {
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-admin:hover {
            transform: translateY(-1px);
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

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }

        .status-badge {
            font-size: 0.8rem;
            padding: 5px 10px;
        }

        .form-label {
            font-weight: bold;
            color: var(--text-color);
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 12px 15px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .badge-status {
            font-size: .85rem;
        }

        .table td,
        .table th {
            vertical-align: middle;
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

            .stat-card {
                margin-bottom: 16px;
            }

            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 18px;
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
        }

        @media (max-width: 576px) {
            .admin-content {
                padding: 16px !important;
            }

            .stat-card {
                padding: 16px !important;
            }

            .stat-card h3 {
                font-size: 1.5rem;
            }

            .stat-card p {
                font-size: 0.9rem;
            }

            .table-responsive {
                font-size: 12px;
            }

            .btn-admin {
                padding: 4px 8px;
                font-size: 12px;
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

            .stat-card {
                padding: 20px;
            }

            .stat-icon {
                width: 45px;
                height: 45px;
                font-size: 18px;
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
        .status-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 500;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
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
            </div>
            <div class="col-12">
                <div class="admin-content p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0"><i class="fas fa-users me-2 text-primary"></i>Pendaftar Webinar</h2>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show"><i class="fas fa-info-circle me-2"></i><?= htmlspecialchars($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php endif; ?>

                    <div class="card mb-3">
                        <div class="card-body">
                            <form class="row g-2">
                                <div class="col-md-3">
                                    <select name="status" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Status</option>
                                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
                                        <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <select name="webinar_id" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Webinar</option>
                                        <?php foreach ($webinars as $w): ?>
                                            <option value="<?= $w['id'] ?>" <?= $webinar_id == $w['id'] ? 'selected' : '' ?>><?= htmlspecialchars($w['judul']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered table-sm align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Webinar</th>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>Telepon</th>
                                            <th>Bukti</th>
                                            <th>Metode</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pendaftar as $p): ?>
                                            <tr>
                                                <td><?= $p['id'] ?></td>
                                                <td><?= htmlspecialchars($p['judul']) ?></td>
                                                <td><?= htmlspecialchars($p['nama']) ?></td>
                                                <td><?= htmlspecialchars($p['email']) ?></td>
                                                <td><?= htmlspecialchars($p['telepon']) ?></td>
                                                <td>
                                                    <?php if (!empty($p['bukti_transfer'])): ?>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <img src="../<?= htmlspecialchars($p['bukti_transfer']) ?>" alt="Bukti" style="height:42px; width:auto; border-radius:4px;">
                                                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#buktiModal" data-img="../<?= htmlspecialchars($p['bukti_transfer']) ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">Belum ada</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($p['metode_pembayaran'] ?? '-') ?></td>
                                                <td><span class="badge badge-status bg-<?= $p['status'] === 'approved' ? 'success' : ($p['status'] === 'rejected' ? 'danger' : 'secondary') ?>"><?= ucfirst($p['status']) ?></span></td>
                                                <td>
                                                    <a href="webinar_pendaftar_detail.php?id=<?= urlencode($p['id']) ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-info-circle"></i></a>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                        <button name="action" value="approve" class="btn btn-sm btn-success" <?= $p['status'] === 'approved' ? 'disabled' : '' ?>><i class="fas fa-check"></i></button>
                                                        <button name="action" value="reject" class="btn btn-sm btn-danger" <?= $p['status'] === 'rejected' ? 'disabled' : '' ?>><i class="fas fa-times"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="buktiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:700px">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-image me-2"></i>Bukti Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="buktiModalImg" src="#" alt="Bukti" style="max-width:100%; height:auto; border-radius:8px;">
                </div>
            </div>
        </div>
    </div>

    <script src="../bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var buktiModal = document.getElementById('buktiModal');
        if (buktiModal) {
            buktiModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var img = button.getAttribute('data-img');
                document.getElementById('buktiModalImg').setAttribute('src', img);
            })
        }

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