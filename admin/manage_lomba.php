<?php
require_once 'check_auth.php';

$db = getDB();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_lomba') {
        $lomba_id = $_POST['lomba_id'];
        $nama = $_POST['nama'];
        $deskripsi = $_POST['deskripsi'];
        $timeline_pendaftaran = $_POST['timeline_pendaftaran'] ?? '';
        $timeline_seleksi = $_POST['timeline_seleksi'] ?? '';
        $timeline_pengumuman = $_POST['timeline_pengumuman'] ?? '';
        $peserta = $_POST['peserta'];
        $durasi = $_POST['durasi'];
        $tempat = $_POST['tempat'];
        $status = $_POST['status'];
        
        // Update data lomba
        $db->query(
            "UPDATE b_kategori_lomba SET nama = ?, deskripsi = ?, timeline_pendaftaran = ?, timeline_seleksi = ?, timeline_pengumuman = ?, peserta = ?, durasi = ?, tempat = ?, status = ? WHERE id = ?",
            [$nama, $deskripsi, $timeline_pendaftaran, $timeline_seleksi, $timeline_pengumuman, $peserta, $durasi, $tempat, $status, $lomba_id]
        );
        
        // Handle PDF upload
        if (isset($_FILES['rulebook_pdf']) && $_FILES['rulebook_pdf']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/rulebook/';
            $file_extension = pathinfo($_FILES['rulebook_pdf']['name'], PATHINFO_EXTENSION);
            
            if ($file_extension === 'pdf') {
                $filename = $lomba_id . '.pdf';
                $filepath = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['rulebook_pdf']['tmp_name'], $filepath)) {
                    $success_message = "Lomba berhasil diperbarui dan rule book berhasil diupload!";
                } else {
                    $error_message = "Gagal mengupload file PDF.";
                }
            } else {
                $error_message = "File harus berformat PDF.";
            }
        }

        // Handle Image upload for lomba banner
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/card_img/';
            $ext = strtolower(pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $filename = 'lomba_' . $lomba_id . '_' . time() . '.' . $ext;
                $filepath = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $filepath)) {
                    $db->query("UPDATE b_kategori_lomba SET card_pic = ? WHERE id = ?", ['uploads/card_img/' . $filename, $lomba_id]);
                    $success_message = ($success_message ?? 'Lomba berhasil diperbarui!') . ' Gambar berhasil diupload.';
                } else {
                    $error_message = ($error_message ?? '') . ' Gagal mengupload gambar.';
                }
            } else {
                $error_message = ($error_message ?? '') . ' Format gambar harus JPG/PNG/WebP.';
            }
        }

        if (!isset($success_message) && !isset($error_message)) {
            $success_message = "Lomba berhasil diperbarui!";
        }
    }
}

// Ambil semua lomba
$lomba_list = $db->fetchAll("SELECT * FROM b_kategori_lomba ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Lomba - Admin Panel</title>
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
        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
        }
        .btn-admin {
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 600;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 5px 10px;
        }
        .form-label {
            font-weight: bold;
            color: #333;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .file-upload {
            position: relative;
            display: inline-block;
            cursor: pointer;
            width: 100%;
        }
        .file-upload input[type=file] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        .file-upload-label {
            display: block;
            padding: 12px 20px;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .file-upload-label:hover {
            border-color: #667eea;
            background: #f0f2ff;
        }
        .file-upload input[type=file]:focus + .file-upload-label {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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
                        <a class="nav-link mb-2 active" href="manage_lomba.php">
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
                            <i class="fas fa-trophy me-2 text-primary"></i>Kelola Lomba
                        </h2>
                        <div>
                            <span class="text-muted">Selamat datang, <?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin') ?></span>
                        </div>
                    </div>
                    <!-- Alert Messages -->
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Daftar Lomba -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Lomba</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nama Lomba</th>
                                            <th>Status</th>
                                            <th>Rule Book</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lomba_list as $lomba): ?>
                                            <tr>
                                                <td><?php echo $lomba['id']; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($lomba['nama']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars(substr($lomba['deskripsi'], 0, 50)) . '...'; ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $lomba['status'] === 'aktif' ? 'success' : 'danger'; ?>">
                                                        <?php echo ucfirst($lomba['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $rulebook_path = "../uploads/rulebook/" . $lomba['id'] . ".pdf";
                                                    if (file_exists($rulebook_path)): ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-file-pdf"></i> Tersedia
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-exclamation-triangle"></i> Belum Upload
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm btn-admin" onclick="editLomba(<?php echo $lomba['id']; ?>)">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Form Edit Lomba -->
                    <div class="card" id="editForm" style="display: none;">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Detail Lomba</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_lomba">
                                <input type="hidden" name="lomba_id" id="edit_lomba_id">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nama" class="form-label">Nama Lomba</label>
                                            <input type="text" class="form-control" id="edit_nama" name="nama" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="edit_status" name="status" required>
                                                <option value="aktif">Aktif</option>
                                                <option value="nonaktif">Nonaktif</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="deskripsi" class="form-label">Deskripsi</label>
                                    <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3" required></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="edit_timeline_pendaftaran" class="form-label">Timeline Pendaftaran</label>
                                            <input type="text" class="form-control" id="edit_timeline_pendaftaran" name="timeline_pendaftaran" placeholder="mis. 1-31 Jan 2025">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="edit_timeline_seleksi" class="form-label">Timeline Seleksi</label>
                                            <input type="text" class="form-control" id="edit_timeline_seleksi" name="timeline_seleksi" placeholder="mis. 1-15 Feb 2025">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="edit_timeline_pengumuman" class="form-label">Timeline Pengumuman</label>
                                            <input type="text" class="form-control" id="edit_timeline_pengumuman" name="timeline_pengumuman" placeholder="mis. 20 Feb 2025">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="peserta" class="form-label">Peserta</label>
                                            <input type="text" class="form-control" id="edit_peserta" name="peserta" placeholder="Contoh: Siswa SMP Kelas VII, VIII, IX" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="durasi" class="form-label">Durasi Lomba</label>
                                            <input type="text" class="form-control" id="edit_durasi" name="durasi" placeholder="Contoh: 3 Jam" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tempat" class="form-label">Tempat</label>
                                            <input type="text" class="form-control" id="edit_tempat" name="tempat" placeholder="Contoh: Gedung BCF" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="rulebook_pdf" class="form-label">Rule Book PDF</label>
                                    <div class="file-upload">
                                        <input type="file" id="rulebook_pdf" name="rulebook_pdf" accept=".pdf">
                                        <label for="rulebook_pdf" class="file-upload-label">
                                            <i class="fas fa-cloud-upload-alt"></i> Pilih file PDF Rule Book
                                        </label>
                                    </div>
                                    <small class="text-muted">Upload file PDF rule book untuk lomba ini. File akan disimpan dengan nama {id_lomba}.pdf</small>
                                </div>

                                <div class="mb-3">
                                    <label for="banner_image" class="form-label">Gambar Lomba (opsional)</label>
                                    <div class="file-upload">
                                        <input type="file" id="banner_image" name="banner_image" accept="image/*">
                                        <label for="banner_image" class="file-upload-label">
                                            <i class="fas fa-cloud-upload-alt"></i> Pilih gambar (JPG/PNG/WebP)
                                        </label>
                                    </div>
                                    <small class="text-muted">Jika tidak diisi, ikon akan digunakan seperti biasa.</small>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success btn-admin">
                                        <i class="fas fa-save"></i> Simpan Perubahan
                                    </button>
                                    <button type="button" class="btn btn-warning btn-admin" onclick="cancelEdit()">
                                        <i class="fas fa-times"></i> Batal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editLomba(lombaId) {
            // Ambil data lomba dari database via AJAX
            fetch('get_lomba_data.php?id=' + lombaId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Isi form dengan data lomba
                        document.getElementById('edit_lomba_id').value = lombaId;
                        document.getElementById('edit_nama').value = data.lomba.nama || '';
                        document.getElementById('edit_deskripsi').value = data.lomba.deskripsi || '';
                        document.getElementById('edit_peserta').value = data.lomba.peserta || '';
                        document.getElementById('edit_durasi').value = data.lomba.durasi || '';
                        document.getElementById('edit_tempat').value = data.lomba.tempat || '';
                        document.getElementById('edit_status').value = data.lomba.status || 'aktif';
                        document.getElementById('edit_timeline_pendaftaran').value = data.lomba.timeline_pendaftaran || '';
                        document.getElementById('edit_timeline_seleksi').value = data.lomba.timeline_seleksi || '';
                        document.getElementById('edit_timeline_pengumuman').value = data.lomba.timeline_pengumuman || '';
                        
                        // Tampilkan form
                        document.getElementById('editForm').style.display = 'block';
                        
                        // Scroll ke form
                        document.getElementById('editForm').scrollIntoView({ behavior: 'smooth' });
                    } else {
                        alert('Gagal mengambil data lomba: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data lomba');
                });
        }
        
        function cancelEdit() {
            document.getElementById('editForm').style.display = 'none';
        }
        
        // File upload preview
        document.getElementById('rulebook_pdf').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const label = e.target.nextElementSibling;
            
            if (file) {
                label.innerHTML = `<i class="fas fa-file-pdf"></i> ${file.name}`;
                label.style.borderColor = '#28a745';
                label.style.background = '#f8fff9';
            } else {
                label.innerHTML = `<i class="fas fa-cloud-upload-alt"></i> Pilih file PDF Rule Book`;
                label.style.borderColor = '#dee2e6';
                label.style.background = '#f8f9fa';
            }
        });
    </script>
</body>
</html>
