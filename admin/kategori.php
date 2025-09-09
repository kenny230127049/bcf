<?php
require_once 'check_auth.php';

$db = getDB();
$message = '';
$message_type = '';

// Handle messages from URL parameters
if (isset($_GET['success'])) {
    $message = $_GET['success'];
    $message_type = 'success';
} elseif (isset($_GET['error'])) {
    $message = $_GET['error'];
    $message_type = 'danger';
}

// Handle form submission for adding/editing categories
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add' || $action == 'edit') {
        $nama = trim($_POST['nama'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $biaya = floatval($_POST['biaya'] ?? 0);
        $icon = trim($_POST['icon'] ?? '');
        $jenis_lomba = $_POST['jenis_lomba'] ?? 'individu';
        $max_peserta = intval($_POST['max_peserta'] ?? 1);
        $status = $_POST['status'] ?? 'aktif';
        $butuh_kartu_pelajar = $_POST['butuh_kartu_pelajar'] ?? 0;
        $link_grup_wa = $_POST['link_grup_wa'] ?? '';
        
        if (empty($nama) || empty($deskripsi) || $biaya <= 0) {
            $message = 'Semua field harus diisi dengan benar!';
            $message_type = 'danger';
        } else {
            if ($action == 'add') {
                $data = [
                    'nama' => $nama,
                    'deskripsi' => $deskripsi,
                    'biaya' => $biaya,
                    'icon' => $icon,
                    'jenis_lomba' => $jenis_lomba,
                    'max_peserta' => $max_peserta,
                    'status' => $status,
                    'butuh_kartu_pelajar' => $butuh_kartu_pelajar,
                    'link_grup_wa' => $link_grup_wa,
                ];
                $result = $db->insert('b_kategori_lomba', $data);
                
                if ($result) {
                    $message = 'Kategori lomba berhasil ditambahkan!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal menambahkan kategori lomba!';
                    $message_type = 'danger';
                }
            } else {
                $id = intval($_POST['id'] ?? 0);
                $data = [
                    'nama' => $nama,
                    'deskripsi' => $deskripsi,
                    'biaya' => $biaya,
                    'icon' => $icon,
                    'jenis_lomba' => $jenis_lomba,
                    'max_peserta' => $max_peserta,
                    'status' => $status,
                    'butuh_kartu_pelajar' => $butuh_kartu_pelajar,
                    'link_grup_wa' => $link_grup_wa,
                ];
                $result = $db->update('b_kategori_lomba', $data, 'id = ?', [$id]);
                
                if ($result) {
                    $message = 'Kategori lomba berhasil diperbarui!';
                    $message_type = 'success';
                } else {
                    $message = 'Gagal memperbarui kategori lomba!';
                    $message_type = 'danger';
                }
            }
        }
    }
}

// Get all categories
$kategori_lomba = $db->fetchAll("SELECT * FROM b_kategori_lomba ORDER BY created_at DESC");

// Get category for editing
$edit_kategori = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_kategori = $db->fetch("SELECT * FROM b_kategori_lomba WHERE id = ?", [$edit_id]);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori Lomba - Admin Panel</title>
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
        .btn-admin {
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 600;
        }
        .kategori-card {
            transition: transform 0.3s ease;
        }
        .kategori-card:hover {
            transform: translateY(-5px);
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
                        <a class="nav-link mb-2" href="webinar_manage.php">
                            <i class="fas fa-chalkboard-teacher me-2"></i>Kelola Webinar
                        </a>
                        <a class="nav-link mb-2" href="webinar_pendaftar.php">
                            <i class="fas fa-users me-2"></i>Pendaftar Webinar
                        </a>
                        <a class="nav-link mb-2" href="pendaftar.php">
                            <i class="fas fa-users me-2"></i>Pendaftar
                        </a>
                        <a class="nav-link active mb-2" href="kategori.php">
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
                            <i class="fas fa-tags me-2 text-primary"></i>Kelola Kategori Lomba
                        </h2>
                        <button class="btn btn-primary btn-admin" data-bs-toggle="modal" data-bs-target="#addKategoriModal">
                            <i class="fas fa-plus me-2"></i>Tambah Kategori
                        </button>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                            <i class="fas fa-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Categories Grid -->
                    <div class="row">
                        <?php foreach ($kategori_lomba as $kategori): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card kategori-card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="me-3">
                                            <i class="<?= htmlspecialchars($kategori['icon']) ?> fa-2x text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="card-title mb-1"><?= htmlspecialchars($kategori['nama']) ?></h5>
                                            <span class="badge bg-<?= ($kategori['jenis_lomba'] ?? 'individu') == 'kelompok' ? 'success' : 'info' ?>">
                                                <?= ucfirst($kategori['jenis_lomba'] ?? 'individu') ?>
                                            </span>
                                            <?php if (($kategori['jenis_lomba'] ?? 'individu') == 'kelompok'): ?>
                                                <span class="badge bg-secondary ms-1">Max: <?= $kategori['max_peserta'] ?? 1 ?> orang</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <p class="card-text text-muted mb-3">
                                        <?= htmlspecialchars(substr($kategori['deskripsi'], 0, 100)) ?>...
                                    </p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="h5 text-primary mb-0">
                                            Rp <?= number_format($kategori['biaya'], 0, ',', '.') ?>
                                        </span>
                                        <span class="badge bg-<?= ($kategori['status'] ?? 'aktif') == 'aktif' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($kategori['status'] ?? 'aktif') ?>
                                        </span>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <a href="?edit=<?= $kategori['id'] ?>" class="btn btn-outline-primary btn-sm flex-fill">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                        <a href="delete_kategori.php?id=<?= $kategori['id'] ?>" 
                                           class="btn btn-outline-danger btn-sm"
                                           onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                            <i class="fas fa-trash me-1"></i>Hapus
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Category Modal -->
    <div class="modal fade" id="addKategoriModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-<?= $edit_kategori ? 'edit' : 'plus' ?> me-2"></i>
                        <?= $edit_kategori ? 'Edit' : 'Tambah' ?> Kategori Lomba
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?= $edit_kategori ? 'edit' : 'add' ?>">
                        <?php if ($edit_kategori): ?>
                            <input type="hidden" name="id" value="<?= $edit_kategori['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Kategori</label>
                                    <input type="text" class="form-control" id="nama" name="nama" 
                                           value="<?= htmlspecialchars($edit_kategori['nama'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="icon" class="form-label">Icon</label>
                                    <input type="text" class="form-control" id="icon" name="icon" 
                                           value="<?= htmlspecialchars($edit_kategori['icon'] ?? 'fas fa-trophy') ?>" 
                                           placeholder="fas fa-trophy">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required><?= htmlspecialchars($edit_kategori['deskripsi'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="biaya" class="form-label">Biaya Pendaftaran</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" id="biaya" name="biaya" 
                                               value="<?= $edit_kategori['biaya'] ?? '' ?>" min="0" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="jenis_lomba" class="form-label">Jenis Lomba</label>
                                    <select class="form-select" id="jenis_lomba" name="jenis_lomba" required>
                                        <option value="individu" <?= ($edit_kategori['jenis_lomba'] ?? '') == 'individu' ? 'selected' : '' ?>>Individu</option>
                                        <option value="kelompok" <?= ($edit_kategori['jenis_lomba'] ?? '') == 'kelompok' ? 'selected' : '' ?>>Kelompok</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="max_peserta" class="form-label">Maksimal Peserta</label>
                                    <input type="number" class="form-control" id="max_peserta" name="max_peserta" 
                                           value="<?= $edit_kategori['max_peserta'] ?? 1 ?>" min="1" max="10" required>
                                </div>
                            </div>
                        </div>

                        
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="aktif" <?= ($edit_kategori['status'] ?? '') == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="nonaktif" <?= ($edit_kategori['status'] ?? '') == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="link_grup_wa">Link Grup WhatsApp</label>
                            <input type="text" class="form-control" id="link_grup_wa" name="link_grup_wa" value="<?= $edit_kategori['link_grup_wa'] ?? '' ?>">
                        </div>

                        <div class="mb-3">
                            <input type="checkbox" name="butuh_kartu_pelajar" value="1" <?= ($edit_kategori['butuh_kartu_pelajar'] ?? '') == '1' ? 'checked' : '' ?>>
                            <label for="butuh_kartu_pelajar">Butuh Kartu Pelajar?</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-<?= $edit_kategori ? 'save' : 'plus' ?> me-2"></i>
                            <?= $edit_kategori ? 'Simpan' : 'Tambah' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-show modal if editing
        <?php if ($edit_kategori): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('addKategoriModal'));
            modal.show();
        });
        <?php endif; ?>
        
        // Update max_peserta based on jenis_lomba
        document.getElementById('jenis_lomba').addEventListener('change', function() {
            const maxPesertaField = document.getElementById('max_peserta');
            if (this.value === 'individu') {
                maxPesertaField.value = 1;
                maxPesertaField.max = 1;
            } else {
                maxPesertaField.max = 10;
                if (maxPesertaField.value < 2) {
                    maxPesertaField.value = 2;
                }
            }
        });
    </script>
</body>
</html>
