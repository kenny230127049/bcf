<?php
require_once 'check_auth.php';

$db = getDB();
$message = '';
$message_type = '';

// Actions approve/reject
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
    }
}

// Filters
$status = $_GET['status'] ?? '';
$webinar_id = $_GET['webinar_id'] ?? '';
$where = [];
$params = [];
if ($status) { $where[] = 'wp.status = ?'; $params[] = $status; }
if ($webinar_id) { $where[] = 'wp.webinar_id = ?'; $params[] = $webinar_id; }
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
        .admin-sidebar { min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #1630df 100%); position: sticky; top: 0; }
        .admin-content { background-color: #f8f9fa; min-height: 100vh; }
        .nav-link { color: rgba(255,255,255,0.8); }
        .nav-link:hover, .nav-link.active { color: #fff; background: rgba(255,255,255,0.1); }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .table-responsive { border-radius: 12px; overflow: hidden; }
        .btn-admin { border-radius: 25px; padding: 8px 20px; font-weight: 600; }
        .badge-status { font-size: .85rem; }
        .table td, .table th { vertical-align: middle; }
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
                        <a class="nav-link mb-2" href="webinar_manage.php"><i class="fas fa-chalkboard-teacher me-2"></i>Kelola Webinar</a>
                        <a class="nav-link mb-2 active" href="webinar_pendaftar.php"><i class="fas fa-users me-2"></i>Pendaftar Webinar</a>
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
                                        <option value="pending" <?= $status==='pending'?'selected':'' ?>>Pending</option>
                                        <option value="approved" <?= $status==='approved'?'selected':'' ?>>Approved</option>
                                        <option value="rejected" <?= $status==='rejected'?'selected':'' ?>>Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <select name="webinar_id" class="form-select" onchange="this.form.submit()">
                                        <option value="">Semua Webinar</option>
                                        <?php foreach($webinars as $w): ?>
                                            <option value="<?= $w['id'] ?>" <?= $webinar_id==$w['id']?'selected':'' ?>><?= htmlspecialchars($w['judul']) ?></option>
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
                                            <td><span class="badge badge-status bg-<?= $p['status']==='approved'?'success':($p['status']==='rejected'?'danger':'secondary') ?>"><?= ucfirst($p['status']) ?></span></td>
                                            <td>
                                                <a href="webinar_pendaftar_detail.php?id=<?= urlencode($p['id']) ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-info-circle"></i></a>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                                    <button name="action" value="approve" class="btn btn-sm btn-success" <?= $p['status']==='approved'?'disabled':'' ?>><i class="fas fa-check"></i></button>
                                                    <button name="action" value="reject" class="btn btn-sm btn-danger" <?= $p['status']==='rejected'?'disabled':'' ?>><i class="fas fa-times"></i></button>
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
            buktiModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var img = button.getAttribute('data-img');
                document.getElementById('buktiModalImg').setAttribute('src', img);
            })
        }
    </script>
</body>
</html>



