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
    SELECT p.*, kl.nama as kategori_nama, kl.jenis_lomba, kl.max_peserta, kl.biaya
    FROM b_pendaftar p 
    JOIN b_kategori_lomba kl ON p.kategori_lomba_id = kl.id 
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
    $anggota_kelompok = $db->fetchAll("SELECT * FROM b_anggota_kelompok WHERE pendaftar_id = ?", [$pendaftar_id]);
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $catatan = $_POST['catatan_admin'] ?? '';
    
    if ($action == 'verify') {
        $result = $db->update('pendaftar', 
            ['status' => 'confirmed', 'catatan_admin' => $catatan], 
            'id = ?', 
            [$pendaftar_id]
        );
        
        if ($result) {
            header('Location: pendaftar.php?success=Pendaftaran berhasil diverifikasi');
            exit;
        }
    } elseif ($action == 'reject') {
        $result = $db->update('pendaftar', 
            ['status' => 'rejected', 'catatan_admin' => $catatan], 
            'id = ?', 
            [$pendaftar_id]
        );
        
        if ($result) {
            header('Location: pendaftar.php?success=Pendaftaran ditolak');
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
        .status-badge {
            font-size: 0.9rem;
            padding: 8px 15px;
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
                        <a class="nav-link active mb-2" href="pendaftar.php">
                            <i class="fas fa-users me-2"></i>Pendaftar
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
                            <i class="fas fa-user me-2 text-primary"></i>Detail Pendaftar
                        </h2>
                        <a href="pendaftar.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                    </div>

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
                                    
                                    <div class="mt-3">
                                        <p><strong>Alamat:</strong><br><?= nl2br(htmlspecialchars($pendaftar['alamat'])) ?></p>
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

                            <!-- Group Members (if group competition) -->
                            <?php if (($pendaftar['jenis_lomba'] ?? 'individu') == 'kelompok' && !empty($anggota_kelompok)): ?>
                            <div class="card mb-4">
                                <div class="card-header bg-white border-0">
                                    <h5 class="mb-0">
                                        <i class="fas fa-users me-2 text-primary"></i>Anggota Kelompok
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Nama</th>
                                                    <th>Email</th>
                                                    <th>Telepon</th>
                                                    <th>Sekolah</th>
                                                    <th>Kelas</th>
                                                    <th>Kartu Pelajar</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($anggota_kelompok as $anggota): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($anggota['nama']) ?></td>
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
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
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
                                                    <span class="badge bg-<?= $pembayaran['status'] == 'success' ? 'success' : ($pembayaran['status'] == 'pending' ? 'warning' : 'danger') ?>">
                                                        <?= ucfirst($pembayaran['status']) ?>
                                                    </span>
                                                </p>
                                                <p class="mb-2 text-muted"><?= date('d/m/Y H:i', strtotime($pembayaran['created_at'])) ?></p>
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

    <script src="../bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
