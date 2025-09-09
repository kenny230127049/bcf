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
        ];

        // Handle uploads
        $upload_base = __DIR__ . '/../uploads/webinar/';
        if (!is_dir($upload_base)) {
            @mkdir($upload_base, 0777, true);
        }

        // Banner upload (image)
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

        // Materi PDF dihilangkan sesuai permintaan (tidak ada upload PDF)

        if ($action === 'create') {
            $res = $db->insert('webinar', $data);
            if ($res) { $message = 'Webinar berhasil ditambahkan'; $message_type = 'success'; }
            else { $message = 'Gagal menambahkan webinar'; $message_type = 'danger'; }
        } else {
            $res = $db->update('webinar', $data, 'id = ?', [$id]);
            if ($res !== false) { $message = 'Webinar berhasil diperbarui'; $message_type = 'success'; }
            else { $message = 'Gagal memperbarui webinar'; $message_type = 'danger'; }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        $deleted = $db->delete('webinar', 'id = ?', [$id]);
        if ($deleted) { $message = 'Webinar dihapus'; $message_type = 'success'; }
        else { $message = 'Gagal menghapus webinar'; $message_type = 'danger'; }
    }
}

// Fetch list
$list = $db->fetchAll('SELECT * FROM b_webinar ORDER BY created_at DESC');
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
        .admin-sidebar { min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #1630df 100%); position: sticky; top: 0; }
        .admin-content { background-color: #f8f9fa; min-height: 100vh; }
        .nav-link { color: rgba(255,255,255,0.8); }
        .nav-link:hover, .nav-link.active { color: #fff; background: rgba(255,255,255,0.1); }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .table-responsive { border-radius: 12px; overflow: hidden; }
        .table thead th { background: #f1f5ff; }
        .table td, .table th { vertical-align: middle; }
        .btn-admin { border-radius: 25px; padding: 8px 20px; font-weight: 600; }
    </style>
    </head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 px-0">
                <div class="admin-sidebar p-4">
                    <div class="text-center mb-4">
                        <h4 class="text-white mb-0"><i class="fas fa-trophy me-2"></i>Admin Panel</h4>
                        <small class="text-white-50">Bluvocation Creative Fest</small>
                    </div>
                    <nav class="nav flex-column">
                        <a class="nav-link mb-2" href="index.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                        <a class="nav-link mb-2" href="manage_lomba.php"><i class="fas fa-trophy me-2"></i>Kelola Lomba</a>
                        <a class="nav-link mb-2 active" href="webinar_manage.php"><i class="fas fa-chalkboard-teacher me-2"></i>Kelola Webinar</a>
                        <a class="nav-link mb-2" href="webinar_pendaftar.php"><i class="fas fa-users me-2"></i>Pendaftar Webinar</a>
                        <a class="nav-link mb-2" href="pendaftar.php"><i class="fas fa-users me-2"></i>Pendaftar Lomba</a>
                        <a class="nav-link mb-2" href="kategori.php"><i class="fas fa-tags me-2"></i>Kategori Lomba</a>
                        <a class="nav-link mb-2" href="pengaturan.php"><i class="fas fa-cog me-2"></i>Pengaturan</a>
                        <hr class="text-white-50">
                        <a class="nav-link mb-2" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                    </nav>
                </div>
            </div>
            <div class="col-md-9 col-lg-10">
                <div class="admin-content p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Kelola Webinar</h2>
                        <button class="btn btn-primary btn-admin" data-bs-toggle="modal" data-bs-target="#wsModal"><i class="fas fa-plus me-2"></i>Tambah Webinar</button>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show"><i class="fas fa-info-circle me-2"></i><?= htmlspecialchars($message) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header bg-primary text-white"><strong>Daftar Webinar</strong></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-bordered table-sm align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Judul</th>
                                            <th>Tanggal</th>
                                            <th>Lokasi</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($list as $w): ?>
                                        <tr>
                                            <td><?= $w['id'] ?></td>
                                            <td><?= htmlspecialchars($w['judul']) ?></td>
                                            <td><?= htmlspecialchars($w['tanggal']) ?> <?= htmlspecialchars($w['waktu']) ?></td>
                                            <td><?= htmlspecialchars($w['lokasi']) ?></td>
                                            <td><span class="badge bg-<?= $w['status']==='aktif'?'success':'secondary' ?>"><?= ucfirst($w['status']) ?></span></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" onclick='editWS(<?= json_encode($w, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>)'><i class="fas fa-edit"></i></button>
                                                <form method="post" class="d-inline" onsubmit="return confirm('Hapus webinar ini?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $w['id'] ?>">
                                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
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

    <!-- Modal Add/Edit -->
    <div class="modal fade" id="wsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-chalkboard-teacher me-2"></i><span id="wsModalTitle">Tambah Webinar</span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="wsAction" value="create">
                        <input type="hidden" name="id" id="wsId">

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Judul</label>
                                    <input type="text" name="judul" id="wsJudul" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Pemateri</label>
                                    <input type="text" name="pemateri" id="wsPemateri" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" id="wsDeskripsi" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal</label>
                                    <input type="date" name="tanggal" id="wsTanggal" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Waktu</label>
                                    <input type="text" name="waktu" id="wsWaktu" class="form-control" placeholder="09:00 - 12:00">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Lokasi</label>
                                    <input type="text" name="lokasi" id="wsLokasi" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Kapasitas</label>
                                    <input type="number" name="kapasitas" id="wsKapasitas" class="form-control" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Biaya (Rp)</label>
                                    <input type="number" step="0.01" name="biaya" id="wsBiaya" class="form-control" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" id="wsStatus" class="form-select">
                                        <option value="aktif">Aktif</option>
                                        <option value="nonaktif">Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Banner (JPG/PNG)</label>
                                    <input type="file" name="banner" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp">
                                </div>
                            </div>
                            
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
        function editWS(w){
            document.getElementById('wsModalTitle').innerText = 'Edit Webinar';
            document.getElementById('wsAction').value = 'update';
            document.getElementById('wsId').value = w.id;
            document.getElementById('wsJudul').value = w.judul || '';
            document.getElementById('wsPemateri').value = w.pemateri || '';
            document.getElementById('wsDeskripsi').value = w.deskripsi || '';
            document.getElementById('wsTanggal').value = w.tanggal || '';
            document.getElementById('wsWaktu').value = w.waktu || '';
            document.getElementById('wsLokasi').value = w.lokasi || '';
            document.getElementById('wsKapasitas').value = w.kapasitas || 0;
            document.getElementById('wsBiaya').value = w.biaya || 0;
            document.getElementById('wsStatus').value = w.status || 'aktif';
            var modal = new bootstrap.Modal(document.getElementById('wsModal'));
            modal.show();
        }
    </script>
</body>
</html>


