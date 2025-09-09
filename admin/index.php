
<?php
// Proteksi ganda untuk memastikan admin sudah login
require_once 'check_auth.php';

// Double check - pastikan admin_id ada
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    header('Location: deny_access.php');
    exit;
}

$db = getDB();

// Get statistics
$total_pendaftar = $db->fetch("SELECT COUNT(*) as total FROM b_pendaftar")['total'];
$pending_pendaftar = $db->fetch("SELECT COUNT(*) as total FROM b_pendaftar WHERE status = 'pending'")['total'];
$confirmed_pendaftar = $db->fetch("SELECT COUNT(*) as total FROM b_pendaftar WHERE status = 'confirmed'")['total'];
$total_kategori = $db->fetch("SELECT COUNT(*) as total FROM b_kategori_lomba")['total'];

// Get recent registrations
$recent_pendaftar = $db->fetchAll("
    SELECT p.*, kl.nama as kategori_nama 
    FROM b_pendaftar p 
    JOIN b_kategori_lomba kl ON p.kategori_lomba_id = kl.id 
    ORDER BY p.created_at DESC 
    LIMIT 10
");

// Get competition categories
$kategori_lomba = $db->fetchAll("SELECT * FROM b_kategori_lomba ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bluvocation Creative Fest</title>
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
        .stat-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .nav-link {
            color: rgba(255,255,255,0.8);
            transition: all 0.3s ease;
        }
        .nav-link:hover, .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        .btn-admin {
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 600;
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
                        <a class="nav-link active mb-2" href="index.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link mb-2" href="manage_lomba.php">
                            <i class="fas fa-trophy me-2"></i>Kelola Lomba
                        </a>
                        <a class="nav-link mb-2" href="webinar_manage.php">
                            <i class="fas fa-chalkboard-teacher me-2"></i>Kelola Webinar
                        </a>
                        <a class="nav-link mb-2" href="webinar_pendaftar.php">
                            <i class="fas fa-users me-2"></i>Pendaftar Webinar
                        </a>
                        <a class="nav-link mb-2" href="pendaftar.php">
                            <i class="fas fa-users me-2"></i>Pendaftar Lomba
                        </a>
                        <a class="nav-link mb-2" href="kategori.php">
                            <i class="fas fa-tags me-2"></i>Kategori Lomba
                        </a>
                        
                        <a class="nav-link mb-2" href="pengaturan.php">
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
                            <i class="fas fa-tachometer-alt me-2 text-primary"></i>Dashboard
                        </h2>
                        <div>
                            <span class="text-muted">Selamat datang, <?= htmlspecialchars($_SESSION['admin_nama']) ?></span>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="stat-card p-4 animate__animated animate__fadeInUp">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-primary me-3">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-1"><?= $total_pendaftar ?></h3>
                                        <p class="text-muted mb-0">Total Pendaftar</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card p-4 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-warning me-3">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-1"><?= $pending_pendaftar ?></h3>
                                        <p class="text-muted mb-0">Menunggu Verifikasi</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card p-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-success me-3">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-1"><?= $confirmed_pendaftar ?></h3>
                                        <p class="text-muted mb-0">Terverifikasi</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card p-4 animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-info me-3">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-1"><?= $total_kategori ?></h3>
                                        <p class="text-muted mb-0">Kategori Lomba</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Recent Registrations -->
                        <div class="col-lg-8 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-0">
                                    <h5 class="mb-0">
                                        <i class="fas fa-users me-2 text-primary"></i>Pendaftar Terbaru
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Nama</th>
                                                    <th>Kategori</th>
                                                    <th>Sekolah</th>
                                                    <th>Status</th>
                                                    <th>Tanggal</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_pendaftar as $pendaftar): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                                <?= strtoupper(substr($pendaftar['nama'], 0, 1)) ?>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold"><?= htmlspecialchars($pendaftar['nama']) ?></div>
                                                                <small class="text-muted"><?= htmlspecialchars($pendaftar['email']) ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info"><?= htmlspecialchars($pendaftar['kategori_nama']) ?></span>
                                                    </td>
                                                    <td><?= htmlspecialchars($pendaftar['sekolah']) ?></td>
                                                    <td>
                                                        <?php if ($pendaftar['status'] == 'pending'): ?>
                                                            <span class="badge bg-warning">Menunggu</span>
                                                        <?php elseif ($pendaftar['status'] == 'confirmed'): ?>
                                                            <span class="badge bg-success">Terverifikasi</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Ditolak</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            <?= date('d/m/Y H:i', strtotime($pendaftar['created_at'])) ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <a href="detail_pendaftar.php?id=<?= $pendaftar['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Competition Categories -->
                        <div class="col-lg-4 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-tags me-2 text-primary"></i>Kategori Lomba
                                    </h5>
                                    <a href="kategori.php" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($kategori_lomba as $kategori): ?>
                                    <div class="d-flex align-items-center mb-3 p-2 rounded" style="background-color: #f8f9fa;">
                                        <div class="me-3">
                                            <i class="<?= htmlspecialchars($kategori['icon']) ?> fa-2x text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?= htmlspecialchars($kategori['nama']) ?></h6>
                                            <small class="text-muted">Rp <?= number_format($kategori['biaya'], 0, ',', '.') ?></small>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="edit_kategori.php?id=<?= $kategori['id'] ?>">
                                                    <i class="fas fa-edit me-2"></i>Edit
                                                </a></li>
                                                <li><a class="dropdown-item text-danger" href="delete_kategori.php?id=<?= $kategori['id'] ?>" onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                                    <i class="fas fa-trash me-2"></i>Hapus
                                                </a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add some interactive animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate statistics cards on scroll
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            });

            document.querySelectorAll('.stat-card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.6s ease';
                observer.observe(card);
            });
        });
    </script>
</body>
</html>
