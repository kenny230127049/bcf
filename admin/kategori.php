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
        $periode_pendaftaran = $_POST['periode_pendaftaran'] ?? '1 Januari - 31 Januari 2024';
        
        // Handle image upload
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $file = $_FILES['image'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB
            
            if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
                $filename = 'kategori_' . time() . '_' . $file['name'];
                $upload_path = '../uploads/kategori/' . $filename;
                
                // Create directory if it doesn't exist
                if (!file_exists('../uploads/kategori/')) {
                    mkdir('../uploads/kategori/', 0755, true);
                }
                
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $image_path = 'uploads/kategori/' . $filename;
                } else {
                    $message = 'Gagal mengupload gambar!';
                    $message_type = 'danger';
                }
            } else {
                $message = 'Format gambar tidak didukung atau ukuran terlalu besar!';
                $message_type = 'danger';
            }
        } elseif ($action == 'edit' && !empty($_POST['existing_image'])) {
            // Keep existing image if no new image uploaded
            $image_path = $_POST['existing_image'];
        }
        
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
                    'image_path' => $image_path,
                    'jenis_lomba' => $jenis_lomba,
                    'max_peserta' => $max_peserta,
                    'status' => $status,
                    'butuh_kartu_pelajar' => $butuh_kartu_pelajar,
                    'link_grup_wa' => $link_grup_wa,
                    'periode_pendaftaran' => $periode_pendaftaran,
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
                    'image_path' => $image_path,
                    'jenis_lomba' => $jenis_lomba,
                    'max_peserta' => $max_peserta,
                    'status' => $status,
                    'butuh_kartu_pelajar' => $butuh_kartu_pelajar,
                    'link_grup_wa' => $link_grup_wa,
                    'periode_pendaftaran' => $periode_pendaftaran,
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
        :root {
            --primary-color: #667eea;
            --primary-dark: #1630df;
            --secondary-color: #f8f9fa;
            --text-color: #2c3e50;
            --text-muted: #6c757d;
            --border-color: #e9ecef;
            --shadow: 0 2px 10px rgba(0,0,0,0.08);
            --shadow-hover: 0 4px 20px rgba(0,0,0,0.12);
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
            color: rgba(255,255,255,0.85);
            transition: all 0.2s ease;
            border-radius: 8px;
            margin-bottom: 4px;
            padding: 12px 16px;
        }

        .nav-link:hover, .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.15);
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

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 12px 15px;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .kategori-card {
            transition: transform 0.2s ease;
        }

        .kategori-card:hover {
            transform: translateY(-2px);
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

            .table th, .table td {
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

            .d-flex.justify-content-between > div:last-child {
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
                        <a class="nav-link mb-2" href="pendaftar.php">
                            <i class="fas fa-users me-2"></i>Pendaftar Lomba
                        </a>
                        <a class="nav-link active mb-2" href="kategori.php">
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
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?= $edit_kategori ? 'edit' : 'add' ?>">
                        <?php if ($edit_kategori): ?>
                            <input type="hidden" name="id" value="<?= $edit_kategori['id'] ?>">
                            <input type="hidden" name="existing_image" value="<?= htmlspecialchars($edit_kategori['image_path'] ?? '') ?>">
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
                        
                        <!-- Image Upload Field -->
                        <div class="mb-3">
                            <label for="image" class="form-label">Gambar Kategori</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <?php if (!empty($edit_kategori['image_path'])): ?>
                                <div class="mt-2">
                                    <small class="text-muted">Gambar saat ini:</small><br>
                                    <img src="../<?= htmlspecialchars($edit_kategori['image_path']) ?>" 
                                         alt="Current Image" 
                                         style="max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 8px;"
                                         class="mt-1">
                                </div>
                            <?php endif; ?>
                            <small class="text-muted">Format yang didukung: JPG, PNG, GIF. Maksimal 2MB.</small>
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
                            <label for="periode_pendaftaran" class="form-label">Periode Pendaftaran</label>
                            <input type="text" class="form-control" id="periode_pendaftaran" name="periode_pendaftaran" 
                                   value="<?= $edit_kategori['periode_pendaftaran'] ?? '1 Januari - 31 Januari 2024' ?>" required>
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
