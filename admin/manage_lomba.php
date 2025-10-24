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
        $periode_pendaftaran = $_POST['periode_pendaftaran'];
        $peserta = $_POST['peserta'];
        $tempat = $_POST['tempat'];
        $status = $_POST['status'];

        // Class selection fields
        $allow_sd = isset($_POST['allow_sd']) ? 1 : 0;
        $allow_smp = isset($_POST['allow_smp']) ? 1 : 0;
        $allow_sma = isset($_POST['allow_sma']) ? 1 : 0;

        // Instagram follow proof toggle
        $require_ig_proof = isset($_POST['require_ig_proof']) ? 1 : 0;

        // Optional fields for team configuration
        $jenis_lomba = $_POST['jenis_lomba'] ?? null; // 'individu' | 'kelompok'
        $max_peserta = isset($_POST['max_peserta']) ? intval($_POST['max_peserta']) : null;
        $allow_variable_team_size = isset($_POST['allow_variable_team_size']) ? 1 : 0;

        // Update data lomba (kolom yang pasti ada)
        $db->query(
            "UPDATE {prefix}kategori_lomba SET nama = ?, deskripsi = ?, periode_pendaftaran = ?, peserta = ?, tempat = ?, status = ?, allow_sd = ?, allow_smp = ?, allow_sma = ?, require_ig_proof = ? WHERE id = ?",
            [$nama, $deskripsi, $periode_pendaftaran, $peserta, $tempat, $status, $allow_sd, $allow_smp, $allow_sma, $require_ig_proof, $lomba_id]
        );

        // Pastikan kolom-kolom opsional tersedia; buat jika belum ada
        $colJenis = $db->fetch("SHOW COLUMNS FROM {prefix}kategori_lomba LIKE 'jenis_lomba'");
        if (!$colJenis) {
            try {
                $db->query("ALTER TABLE {prefix}kategori_lomba ADD COLUMN jenis_lomba ENUM('individu','kelompok') NOT NULL DEFAULT 'individu'");
            } catch (Exception $e) {
            }
        }
        $colMax = $db->fetch("SHOW COLUMNS FROM {prefix}kategori_lomba LIKE 'max_peserta'");
        if (!$colMax) {
            try {
                $db->query("ALTER TABLE {prefix}kategori_lomba ADD COLUMN max_peserta INT NOT NULL DEFAULT 1");
            } catch (Exception $e) {
            }
        }
        $colVar = $db->fetch("SHOW COLUMNS FROM {prefix}kategori_lomba LIKE 'allow_variable_team_size'");
        if (!$colVar) {
            try {
                $db->query("ALTER TABLE {prefix}kategori_lomba ADD COLUMN allow_variable_team_size TINYINT(1) NOT NULL DEFAULT 0");
            } catch (Exception $e) {
            }
        }

        // Update nilai kolom-kolom opsional
        if (!empty($jenis_lomba)) {
            $db->query("UPDATE {prefix}kategori_lomba SET jenis_lomba = ? WHERE id = ?", [$jenis_lomba, $lomba_id]);
        }
        if ($max_peserta !== null && $max_peserta > 0) {
            $db->query("UPDATE {prefix}kategori_lomba SET max_peserta = ? WHERE id = ?", [$max_peserta, $lomba_id]);
        }
        $db->query("UPDATE {prefix}kategori_lomba SET allow_variable_team_size = ? WHERE id = ?", [$allow_variable_team_size, $lomba_id]);

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
        } else {
            $success_message = "Lomba berhasil diperbarui!";
        }
    }

    // Handle timeline operations
    if ($action === 'add_timeline') {
        $lomba_id = $_POST['lomba_id'];

        // Helper to normalize text date (including Indonesian months) to Y-m-d or null
        $normalizeDate = function ($value) {
            $value = trim($value ?? '');
            if ($value === '') return null;
            // Replace Indonesian months with English equivalents for strtotime compatibility
            $indoToEn = [
                'januari' => 'january',
                'jan' => 'jan',
                'februari' => 'february',
                'feb' => 'feb',
                'maret' => 'march',
                'mar' => 'mar',
                'april' => 'april',
                'apr' => 'apr',
                'mei' => 'may',
                'juni' => 'june',
                'jun' => 'jun',
                'juli' => 'july',
                'jul' => 'jul',
                'agustus' => 'august',
                'agt' => 'aug',
                'agus' => 'aug',
                'september' => 'september',
                'sep' => 'sep',
                'oktober' => 'october',
                'okt' => 'oct',
                'november' => 'november',
                'nov' => 'nov',
                'desember' => 'december',
                'des' => 'dec',
            ];
            $lower = strtolower($value);
            $lower = strtr($lower, $indoToEn);
            // Try common formats explicitly
            $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'd M Y', 'd F Y'];
            foreach ($formats as $fmt) {
                $dt = DateTime::createFromFormat($fmt, $lower);
                if ($dt && $dt->format($fmt) === $lower) {
                    return $dt->format('Y-m-d');
                }
            }
            // Fallback to strtotime
            $ts = strtotime($lower);
            if ($ts !== false) {
                return date('Y-m-d', $ts);
            }
            return null;
        };

        // Normalize incoming dates
        $pendaftaran_mulai = $normalizeDate($_POST['pendaftaran_mulai'] ?? null);
        $pendaftaran_akhir = $normalizeDate($_POST['pendaftaran_akhir'] ?? null);
        $seleksi_mulai = $normalizeDate($_POST['seleksi_mulai'] ?? null);
        $seleksi_akhir = $normalizeDate($_POST['seleksi_akhir'] ?? null);
        $technical_meeting_mulai = $normalizeDate($_POST['technical_meeting_mulai'] ?? null);
        $technical_meeting_akhir = $normalizeDate($_POST['technical_meeting_akhir'] ?? null);
        $pengumuman_mulai = $normalizeDate($_POST['pengumuman_mulai'] ?? null);
        $pengumuman_akhir = $normalizeDate($_POST['pengumuman_akhir'] ?? null);

        // Hapus timeline lama untuk lomba ini
        $db->query("DELETE FROM {prefix}timeline_lomba WHERE kategori_lomba_id = ?", [$lomba_id]);

        // Tambah timeline baru
        $timelines = [
            [
                'judul' => 'Pendaftaran Dibuka',
                'deskripsi' => 'Pendaftaran lomba telah dibuka. Silakan daftar melalui website resmi.',
                'tanggal_mulai' => $pendaftaran_mulai,
                'tanggal_selesai' => $pendaftaran_akhir,
                'urutan' => 1
            ],
            [
                'judul' => 'Periode Seleksi',
                'deskripsi' => 'Tim juri akan melakukan seleksi terhadap karya yang telah dikumpulkan.',
                'tanggal_mulai' => $seleksi_mulai,
                'tanggal_selesai' => $seleksi_akhir,
                'urutan' => 2
            ],
            [
                'judul' => 'Technical Meeting',
                'deskripsi' => 'Technical meeting untuk peserta yang lolos seleksi. Pembahasan teknis dan persiapan final.',
                'tanggal_mulai' => $technical_meeting_mulai,
                'tanggal_selesai' => $technical_meeting_akhir,
                'urutan' => 3
            ],
            [
                'judul' => 'Pengumuman Pemenang',
                'deskripsi' => 'Pengumuman pemenang lomba akan dilakukan pada tanggal yang telah ditentukan.',
                'tanggal_mulai' => $pengumuman_mulai,
                'tanggal_selesai' => $pengumuman_akhir,
                'urutan' => 4
            ]
        ];

        $added_count = 0;
        foreach ($timelines as $timeline) {
            if (!empty($timeline['tanggal_mulai'])) {
                $db->query(
                    "INSERT INTO {prefix}timeline_lomba (kategori_lomba_id, judul, deskripsi, tanggal_mulai, tanggal_selesai, urutan) VALUES (?, ?, ?, ?, ?, ?)",
                    [$lomba_id, $timeline['judul'], $timeline['deskripsi'], $timeline['tanggal_mulai'], $timeline['tanggal_selesai'], $timeline['urutan']]
                );
                $added_count++;
            }
        }

        if ($added_count > 0) {
            $success_message = "Timeline berhasil disimpan! ($added_count timeline ditambahkan)";
        } else {
            $error_message = "Tidak ada timeline yang ditambahkan. Pastikan minimal ada 1 tanggal yang diisi.";
        }
    }

    if ($action === 'delete_timeline') {
        $timeline_id = $_POST['timeline_id'];
        $db->query("DELETE FROM {prefix}timeline_lomba WHERE id = ?", [$timeline_id]);
        $success_message = "Timeline berhasil dihapus!";
    }
}

// Ambil semua lomba
$lomba_list = $db->fetchAll("SELECT * FROM {prefix}kategori_lomba ORDER BY id DESC");
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
            border-radius: 8px;
            text-align: center;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .file-upload-label:hover {
            border-color: var(--primary-color);
            background: #f0f2ff;
        }

        .file-upload input[type=file]:focus+.file-upload-label {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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
                        <a class="nav-link active mb-2" href="manage_lomba.php">
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
                                            <th>IG Proof</th>
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
                                                    <span class="badge bg-<?php echo $lomba['require_ig_proof'] == 1 ? 'warning' : 'secondary'; ?>">
                                                        <i class="fas fa-<?php echo $lomba['require_ig_proof'] == 1 ? 'check' : 'times'; ?>"></i>
                                                        <?php echo $lomba['require_ig_proof'] == 1 ? 'Wajib' : 'Opsional'; ?>
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
                                                    <button class="btn btn-primary btn-sm btn-admin me-1" onclick="editLomba(<?php echo $lomba['id']; ?>)">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button class="btn btn-info btn-sm btn-admin" onclick="manageTimeline(<?php echo $lomba['id']; ?>)">
                                                        <i class="fas fa-calendar-alt"></i> Timeline
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
                            <small id="editLombaInfo" class="opacity-75">Sedang mengedit: <span id="editLombaNama">-</span></small>
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
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="periode_pendaftaran" class="form-label">Periode Pendaftaran</label>
                                            <input type="text" class="form-control" id="edit_periode_pendaftaran" name="periode_pendaftaran" placeholder="Contoh: 1 Januari - 31 Januari 2024" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="peserta" class="form-label">Peserta</label>
                                            <input type="text" class="form-control" id="edit_peserta" name="peserta" placeholder="Contoh: Siswa SMP Kelas VII, VIII, IX" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Team Configuration -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Jenis Lomba</label>
                                            <select class="form-select" id="edit_jenis_lomba" name="jenis_lomba">
                                                <option value="individu">Individu</option>
                                                <option value="kelompok">Kelompok</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Maksimum Peserta per Tim</label>
                                            <input type="number" min="1" class="form-control" id="edit_max_peserta" name="max_peserta" placeholder="Contoh: 5">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Mode Jumlah Anggota</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="edit_allow_variable_team_size" name="allow_variable_team_size" value="1">
                                                <label class="form-check-label" for="edit_allow_variable_team_size">
                                                    1 sampai Maksimum
                                                </label>
                                            </div>
                                            <small class="text-muted">Nonaktifkan untuk wajib beranggotakan tepat sebanyak maksimum.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="tempat" class="form-label">Tempat</label>
                                    <input type="text" class="form-control" id="edit_tempat" name="tempat" placeholder="Contoh: Gedung BCF" required>
                                </div>

                                <!-- Class Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Kelas Peserta yang Diizinkan</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="edit_allow_sd" name="allow_sd" value="1">
                                                <label class="form-check-label" for="edit_allow_sd">
                                                    <strong>SD Kelas 1-6</strong><br>
                                                    <small class="text-muted">Sekolah Dasar</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="edit_allow_smp" name="allow_smp" value="1">
                                                <label class="form-check-label" for="edit_allow_smp">
                                                    <strong>SMP Kelas 1-3 (VII-IX)</strong><br>
                                                    <small class="text-muted">Sekolah Menengah Pertama</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="edit_allow_sma" name="allow_sma" value="1">
                                                <label class="form-check-label" for="edit_allow_sma">
                                                    <strong>SMA/Sederajat Kelas 1-3 (X-XII)</strong><br>
                                                    <small class="text-muted">Sekolah Menengah Atas</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Instagram Follow Proof Toggle -->
                                <div class="mb-3">
                                    <label class="form-label">Bukti Follow Instagram</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="edit_require_ig_proof" name="require_ig_proof" value="1">
                                        <label class="form-check-label" for="edit_require_ig_proof">
                                            <strong>Wajib Upload Bukti Follow Instagram</strong><br>
                                            <small class="text-muted">Centang jika peserta harus upload bukti follow @bluvocationfest dan @smkblverse</small>
                                        </label>
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

                    <!-- Timeline Management -->
                    <div class="card" id="timelineForm" style="display: none;">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Kelola Timeline Lomba</h5>
                            <small id="timelineLombaInfo" class="opacity-75">Timeline untuk: <span id="timelineLombaNama">-</span></small>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Timeline Saat Ini</h6>
                                    <div id="timelineList">
                                        <!-- Timeline items will be loaded here -->
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Tambah Timeline Lomba</h6>

                                    <!-- Direct Timeline Forms -->
                                    <form method="POST" id="timelineForm">
                                        <input type="hidden" name="action" value="add_timeline">
                                        <input type="hidden" name="lomba_id" id="timeline_lomba_id">

                                        <!-- Pendaftaran -->
                                        <div class="card mb-3">
                                            <div class="card-header bg-primary text-white py-2">
                                                <h6 class="mb-0"><i class="fas fa-user-plus me-2"></i>Pendaftaran</h6>
                                            </div>
                                            <div class="card-body p-3">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Tanggal Mulai</label>
                                                        <input type="text" class="form-control" name="pendaftaran_mulai" id="pendaftaran_mulai" placeholder="Contoh: 1 Okt 2025">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Tanggal Akhir (kosongkan jika 1 hari)</label>
                                                        <input type="text" class="form-control" name="pendaftaran_akhir" id="pendaftaran_akhir" placeholder="Contoh: 5 Okt 2025 atau kosong">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Seleksi -->
                                        <div class="card mb-3">
                                            <div class="card-header bg-warning text-white py-2">
                                                <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Seleksi</h6>
                                            </div>
                                            <div class="card-body p-3">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Tanggal Mulai</label>
                                                        <input type="text" class="form-control" name="seleksi_mulai" id="seleksi_mulai" placeholder="Contoh: 10 Okt 2025">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Tanggal Akhir (kosongkan jika 1 hari)</label>
                                                        <input type="text" class="form-control" name="seleksi_akhir" id="seleksi_akhir" placeholder="Contoh: 12 Okt 2025 atau kosong">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Technical Meeting -->
                                        <div class="card mb-3">
                                            <div class="card-header bg-info text-white py-2">
                                                <h6 class="mb-0"><i class="fas fa-users me-2"></i>Technical Meeting</h6>
                                            </div>
                                            <div class="card-body p-3">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Tanggal Mulai</label>
                                                        <input type="text" class="form-control" name="technical_meeting_mulai" id="technical_meeting_mulai" placeholder="Contoh: 15 Okt 2025">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Tanggal Akhir (kosongkan jika 1 hari)</label>
                                                        <input type="text" class="form-control" name="technical_meeting_akhir" id="technical_meeting_akhir" placeholder="Contoh: 15 Okt 2025 atau kosong">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pengumuman -->
                                        <div class="card mb-3">
                                            <div class="card-header bg-success text-white py-2">
                                                <h6 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Pengumuman</h6>
                                            </div>
                                            <div class="card-body p-3">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Tanggal Mulai</label>
                                                        <input type="text" class="form-control" name="pengumuman_mulai" id="pengumuman_mulai" placeholder="Contoh: 20 Okt 2025">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Tanggal Akhir (kosongkan jika 1 hari)</label>
                                                        <input type="text" class="form-control" name="pengumuman_akhir" id="pengumuman_akhir" placeholder="Contoh: 20 Okt 2025 atau kosong">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-save me-2"></i>Simpan Timeline
                                        </button>
                                        <button type="button" class="btn btn-secondary w-100 mt-2" onclick="cancelTimeline()">
                                            <i class="fas fa-times me-2"></i>Batal
                                        </button>
                                    </form>
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
                        document.getElementById('edit_periode_pendaftaran').value = data.lomba.periode_pendaftaran || '';
                        document.getElementById('edit_peserta').value = data.lomba.peserta || '';
                        document.getElementById('edit_tempat').value = data.lomba.tempat || '';
                        document.getElementById('edit_status').value = data.lomba.status || 'aktif';

                        // Set class checkboxes
                        document.getElementById('edit_allow_sd').checked = data.lomba.allow_sd == 1;
                        document.getElementById('edit_allow_smp').checked = data.lomba.allow_smp == 1;
                        document.getElementById('edit_allow_sma').checked = data.lomba.allow_sma == 1;

                        // Set Instagram follow proof toggle
                        document.getElementById('edit_require_ig_proof').checked = data.lomba.require_ig_proof == 1;

                        // Set team configuration (if available)
                        if (document.getElementById('edit_jenis_lomba')) {
                            document.getElementById('edit_jenis_lomba').value = (data.lomba.jenis_lomba || 'individu');
                        }
                        if (document.getElementById('edit_max_peserta')) {
                            document.getElementById('edit_max_peserta').value = (data.lomba.max_peserta || '1');
                        }
                        if (document.getElementById('edit_allow_variable_team_size')) {
                            document.getElementById('edit_allow_variable_team_size').checked = (data.lomba.allow_variable_team_size == 1);
                        }

                        // Tampilkan nama lomba yang sedang diedit
                        document.getElementById('editLombaNama').textContent = data.lomba.nama;

                        // Tampilkan form
                        document.getElementById('editForm').style.display = 'block';

                        // Scroll ke form
                        document.getElementById('editForm').scrollIntoView({
                            behavior: 'smooth'
                        });
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
            document.getElementById('editLombaNama').textContent = '-';
        }

        function cancelTimeline() {
            document.getElementById('timelineForm').style.display = 'none';
            document.getElementById('timelineLombaNama').textContent = '-';
        }

        function manageTimeline(lombaId) {
            document.getElementById('timeline_lomba_id').value = lombaId;
            document.getElementById('timelineForm').style.display = 'block';

            // Ambil nama lomba untuk ditampilkan
            fetch('get_lomba_data.php?id=' + lombaId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('timelineLombaNama').textContent = data.lomba.nama;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });

            loadTimeline(lombaId);

            // Scroll ke timeline form
            document.getElementById('timelineForm').scrollIntoView({
                behavior: 'smooth'
            });
        }

        function loadTimeline(lombaId) {
            fetch('get_timeline_data.php?id=' + lombaId)
                .then(response => response.json())
                .then(data => {
                    const timelineList = document.getElementById('timelineList');

                    // Reset form
                    document.getElementById('pendaftaran_mulai').value = '';
                    document.getElementById('pendaftaran_akhir').value = '';
                    document.getElementById('seleksi_mulai').value = '';
                    document.getElementById('seleksi_akhir').value = '';
                    document.getElementById('technical_meeting_mulai').value = '';
                    document.getElementById('technical_meeting_akhir').value = '';
                    document.getElementById('pengumuman_mulai').value = '';
                    document.getElementById('pengumuman_akhir').value = '';

                    if (data.success && data.timeline.length > 0) {
                        // Fill form with existing data
                        data.timeline.forEach(item => {
                            if (item.judul.includes('Pendaftaran')) {
                                document.getElementById('pendaftaran_mulai').value = item.tanggal_mulai;
                                document.getElementById('pendaftaran_akhir').value = item.tanggal_selesai || '';
                            } else if (item.judul.includes('Seleksi')) {
                                document.getElementById('seleksi_mulai').value = item.tanggal_mulai;
                                document.getElementById('seleksi_akhir').value = item.tanggal_selesai || '';
                            } else if (item.judul.includes('Technical Meeting')) {
                                document.getElementById('technical_meeting_mulai').value = item.tanggal_mulai;
                                document.getElementById('technical_meeting_akhir').value = item.tanggal_selesai || '';
                            } else if (item.judul.includes('Pengumuman')) {
                                document.getElementById('pengumuman_mulai').value = item.tanggal_mulai;
                                document.getElementById('pengumuman_akhir').value = item.tanggal_selesai || '';
                            }
                        });

                        // Show timeline list
                        timelineList.innerHTML = data.timeline.map(item => `
                            <div class="card mb-2">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">${item.judul}</h6>
                                            <small class="text-muted">${item.tanggal_mulai} ${item.tanggal_selesai ? '- ' + item.tanggal_selesai : ''}</small>
                                            ${item.deskripsi ? `<br><small>${item.deskripsi}</small>` : ''}
                                        </div>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Hapus timeline ini?')">
                                            <input type="hidden" name="action" value="delete_timeline">
                                            <input type="hidden" name="timeline_id" value="${item.id}">
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        timelineList.innerHTML = '<p class="text-muted">Belum ada timeline untuk lomba ini.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('timelineList').innerHTML = '<p class="text-danger">Gagal memuat timeline.</p>';
                });
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