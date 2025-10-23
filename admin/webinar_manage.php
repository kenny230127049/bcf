<?php
require_once 'check_auth.php';

$db = getDB();

$message = '';
$message_type = '';

// Handle create/update/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $pemateri = trim($_POST['pemateri'] ?? '');
        $tanggal = $_POST['tanggal'] ?? null;
        $waktu = trim($_POST['waktu'] ?? '');
        $lokasi = trim($_POST['lokasi'] ?? '');
        $kapasitas = intval($_POST['kapasitas'] ?? 0);
        $biaya = floatval($_POST['biaya'] ?? 0);
        $status = $_POST['status'] ?? 'aktif';
        $link_grup_wa = $_POST['link_grup_wa'] ?? null;

        $data = [
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'pemateri' => $pemateri,
            'tanggal' => $tanggal,
            'waktu' => $waktu,
            'lokasi' => $lokasi,
            'kapasitas' => $kapasitas,
            'biaya' => $biaya,
            'status' => $status,
            'link_grup_wa' => $link_grup_wa,
        ];

        // Handle banner upload
        $upload_base = __DIR__ . '/../uploads/webinar/';
        if (!is_dir($upload_base)) {
            @mkdir($upload_base, 0777, true);
        }

        if (!empty($_FILES['banner']['name']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                $target = $upload_base . 'banner_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['banner']['tmp_name'], $target)) {
                    $rel = 'uploads/webinar/' . basename($target);
                    $data['banner_path'] = $rel;
                }
            }
        }

        if ($action === 'create') {
            $res = $db->insert('b_webinar', $data);
            if ($res) { 
                $message = 'Webinar berhasil ditambahkan'; 
                $message_type = 'success'; 
            } else { 
                $message = 'Gagal menambahkan webinar'; 
                $message_type = 'danger'; 
            }
        } else {
            $res = $db->update('b_webinar', $data, 'id = ?', [$id]);
            if ($res !== false) { 
                $message = 'Webinar berhasil diperbarui'; 
                $message_type = 'success'; 
            } else { 
                $message = 'Gagal memperbarui webinar'; 
                $message_type = 'danger'; 
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        $deleted = $db->delete('b_webinar', 'id = ?', [$id]);
        if ($deleted) { 
            $message = 'Webinar dihapus'; 
            $message_type = 'success'; 
        } else { 
            $message = 'Gagal menghapus webinar'; 
            $message_type = 'danger'; 
        }
    }
}

// Fetch webinar list
$webinars = $db->fetchAll('SELECT * FROM b_webinar ORDER BY created_at DESC');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Webinar - Admin</title>
    <link rel="icon" type="image/png" href="../favicon/bcf.png" sizes="32x32">
    <link href="../bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .admin-sidebar { 
            min-height: 100vh; 
            background: linear-gradient(135deg, #667eea 0%, #1630df 100%); 
            position: sticky; 
            top: 0; 
        }
        .admin-content { 
            background-color: #f8f9fa; 
            min-height: 100vh; 
            padding: 20px;
            margin-left: 0;
            margin-right: 0;
        }
        .content-wrapper {
            max-width: 100%;
            margin: 0 auto;
        }
        .nav-link { 
            color: rgba(255,255,255,0.8); 
            padding: 10px 15px;
            margin: 2px 0;
            border-radius: 8px;
        }
        .nav-link:hover, .nav-link.active { 
            color: #fff; 
            background: rgba(255,255,255,0.1); 
        }
        .card { 
            border: none; 
            border-radius: 15px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); 
        }
        .table-responsive { 
            border-radius: 12px; 
            overflow: hidden; 
            background: white;
            border: 1px solid #dee2e6;
        }
        .table thead th { 
            background: #f1f5ff; 
            border-bottom: 2px solid #dee2e6;
        }
        .table td, .table th { 
            vertical-align: middle; 
            padding: 12px 8px;
        }
        .btn-admin { 
            border-radius: 25px; 
            padding: 8px 20px; 
            font-weight: 600; 
        }
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1050;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        @media (max-width: 768px) {
            .mobile-menu-toggle { display: block; }
            .admin-sidebar { 
                position: fixed; 
                left: -100%; 
                transition: left 0.3s ease;
                z-index: 1040;
            }
            .admin-sidebar.show { left: 0; }
            .admin-content {
                padding: 15px;
            }
            .content-wrapper {
                margin: 0;
            }
        }
        @media (min-width: 1200px) {
            .content-wrapper {
                max-width: 95%;
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
                        <a class="nav-link mb-2" href="pendaftar.php">
                            <i class="fas fa-users me-2"></i>Pendaftar Lomba
                        </a>
                        <a class="nav-link mb-2" href="kategori.php">
                            <i class="fas fa-tags me-2"></i>Kategori Lomba
                        </a>
                        <a class="nav-link active mb-2" href="webinar_manage.php">
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
            <div class="col-md-9 col-lg-10">
                <div class="admin-content">
                    <div class="content-wrapper">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">
                            <i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Kelola Webinar
                        </h2>
                        <button class="btn btn-primary btn-admin" data-bs-toggle="modal" data-bs-target="#webinarModal">
                            <i class="fas fa-plus me-2"></i>Tambah Webinar
                        </button>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
                            <i class="fas fa-info-circle me-2"></i><?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Webinar List -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <strong><i class="fas fa-list me-2"></i>Daftar Webinar</strong>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($webinars)): ?>
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-chalkboard-teacher fa-4x mb-3 text-muted"></i>
                                    <h5>Belum ada webinar</h5>
                                    <p class="lead">Klik tombol "Tambah Webinar" untuk menambahkan webinar pertama.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th width="5%">ID</th>
                                                <th width="25%">Judul</th>
                                                <th width="15%">Tanggal & Waktu</th>
                                                <th width="20%">Lokasi</th>
                                                <th width="10%">Pemateri</th>
                                                <th width="10%">Status</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($webinars as $webinar): ?>
                                            <tr>
                                                <td><?= $webinar['id'] ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($webinar['judul']) ?></strong>
                                                    <?php if ($webinar['banner_path']): ?>
                                                        <br><small class="text-muted">Banner tersedia</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($webinar['tanggal']) ?><br>
                                                    <small class="text-muted"><?= htmlspecialchars($webinar['waktu']) ?></small>
                                                </td>
                                                <td><?= htmlspecialchars($webinar['lokasi']) ?></td>
                                                <td><?= htmlspecialchars($webinar['pemateri']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $webinar['status'] === 'aktif' ? 'success' : 'secondary' ?>">
                                                        <?= ucfirst($webinar['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary me-1" onclick="editWebinar(<?= htmlspecialchars(json_encode($webinar)) ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Hapus webinar ini?')">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?= $webinar['id'] ?>">
                                                        <button class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Add/Edit Webinar -->
    <div class="modal fade" id="webinarModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-chalkboard-teacher me-2"></i>
                            <span id="modalTitle">Tambah Webinar</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="webinarId">

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Judul Webinar <span class="text-danger">*</span></label>
                                    <input type="text" name="judul" id="webinarJudul" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Pemateri</label>
                                    <input type="text" name="pemateri" id="webinarPemateri" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" id="webinarDeskripsi" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal</label>
                                    <input type="date" name="tanggal" id="webinarTanggal" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Waktu</label>
                                    <input type="text" name="waktu" id="webinarWaktu" class="form-control" placeholder="09:00 - 12:00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Lokasi</label>
                                    <input type="text" name="lokasi" id="webinarLokasi" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Kapasitas</label>
                                    <input type="number" name="kapasitas" id="webinarKapasitas" class="form-control" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Biaya (Rp)</label>
                                    <input type="number" step="0.01" name="biaya" id="webinarBiaya" class="form-control" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" id="webinarStatus" class="form-select">
                                        <option value="aktif">Aktif</option>
                                        <option value="nonaktif">Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Link Grup WhatsApp</label>
                            <input type="text" name="link_grup_wa" id="webinarLinkGrupWa" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Banner (JPG/PNG)</label>
                            <input type="file" name="banner" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp">
                            <small class="form-text text-muted">Format yang didukung: JPG, PNG, GIF, WebP</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editWebinar(webinar) {
            document.getElementById('modalTitle').innerText = 'Edit Webinar';
            document.getElementById('formAction').value = 'update';
            document.getElementById('webinarId').value = webinar.id;
            document.getElementById('webinarJudul').value = webinar.judul || '';
            document.getElementById('webinarPemateri').value = webinar.pemateri || '';
            document.getElementById('webinarDeskripsi').value = webinar.deskripsi || '';
            document.getElementById('webinarTanggal').value = webinar.tanggal || '';
            document.getElementById('webinarWaktu').value = webinar.waktu || '';
            document.getElementById('webinarLokasi').value = webinar.lokasi || '';
            document.getElementById('webinarKapasitas').value = webinar.kapasitas || 0;
            document.getElementById('webinarBiaya').value = webinar.biaya || 0;
            document.getElementById('webinarStatus').value = webinar.status || 'aktif';
            document.getElementById('webinarLinkGrupWa').value = webinar.link_grup_wa || '';
            
            var modal = new bootstrap.Modal(document.getElementById('webinarModal'));
            modal.show();
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

        // Reset form when modal is closed
        document.getElementById('webinarModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('modalTitle').innerText = 'Tambah Webinar';
            document.getElementById('formAction').value = 'create';
            document.getElementById('webinarId').value = '';
            document.querySelector('form').reset();
        });
    </script>
</body>
</html>
