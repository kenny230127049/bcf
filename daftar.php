<?php
session_start();
require_once 'config/database.php';
require_once 'auth.php';

$auth = new Auth();

// Cek apakah user sudah login
if (!$auth->isLoggedIn()) {
    header('Location: login.php?redirect=daftar.php');
    exit;
}

$db = getDB();
$currentUser = $auth->getCurrentUser();

// Get kategori info from URL parameters
$kategori_id = $_GET['kategori'] ?? $_GET['kategori_id'] ?? null;

if (!$kategori_id) {
    header('Location: index.php');
    exit;
}

// Get kategori details
$kategori = $db->fetch("SELECT * FROM b_kategori_lomba WHERE id = ? AND status = 'aktif'", [$kategori_id]);
if (!$kategori) {
    header('Location: index.php');
    exit;
}

// Use jenis_lomba and max_peserta from DB to ensure consistency
$jenis_lomba = $kategori['jenis_lomba'] ?? 'individu';
$max_peserta = $kategori['max_peserta'] ?? 1;

// Cek apakah user sudah mendaftar di lomba ini
$existingPendaftaran = $db->fetch(
    "SELECT id FROM b_user_pendaftaran WHERE user_id = ? AND kategori_lomba_id = ?",
    [$currentUser['id'], $kategori_id]
);

if ($existingPendaftaran) {
    header('Location: index.php?error=already_registered');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $sekolah = trim($_POST['sekolah'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    $nama_kelompok = trim($_POST['nama_kelompok'] ?? '');
    // Get allowed classes based on competition settings
    $allowedKelas = [];
    if ($kategori['allow_sd']) {
        $allowedKelas = array_merge($allowedKelas, ['1', '2', '3', '4', '5', '6']);
    }
    if ($kategori['allow_smp']) {
        // SMP akan disimpan sebagai 7,8,9 (Kelas 1..3)
        $allowedKelas = array_merge($allowedKelas, ['7', '8', '9']);
    }
    if ($kategori['allow_sma']) {
        // SMA akan disimpan sebagai 10,11,12 (Kelas 1..3)
        $allowedKelas = array_merge($allowedKelas, ['10', '11', '12']);
    }

    if (!in_array($kelas, $allowedKelas, true)) {
        $kelas = $allowedKelas[0] ?? '1';
    }

    // Normalisasi nilai kelas menjadi angka konsisten (7-12 untuk SMP/SMA, 1-6 untuk SD)
    $kelasMap = [
        // Peta untuk kompatibilitas lama (input romawi)
        'VII' => '7',
        'VIII' => '8',
        'IX' => '9',
        'X' => '10',
        'XI' => '11',
        'XII' => '12'
    ];
    $kelasNormalized = $kelasMap[$kelas] ?? (string)$kelas;
    // Pakai numeric secara konsisten untuk menghindari truncation di STRICT mode
    $kelasForInsert = $kelasNormalized;

    // Validate required fields
    $requiredFields = [$nama, $email, $telepon, $sekolah, $kelas];
    if ($jenis_lomba == 'kelompok' && $nama_kelompok === '') {
        $error = 'Nama kelompok wajib diisi!';
    }

    if (in_array('', $requiredFields)) {
        $error = 'Semua field harus diisi!';
    } else {
        // Generate unique ID
        $pendaftar_id = 'P' . date('Ymd') . rand(1000, 9999);

        // Handle file upload for student ID card
        $foto_kartu_pelajar = '';
        if (isset($_FILES['foto_kartu_pelajar']) && $_FILES['foto_kartu_pelajar']['error'] == 0) {
            $file = $_FILES['foto_kartu_pelajar'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];

            if (in_array($file['type'], $allowed_types)) {
                $filename = $pendaftar_id . '_' . time() . '_' . $file['name'];
                $upload_path = 'uploads/' . $filename;

                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $foto_kartu_pelajar = $filename;
                }
            }
        }

        // Handle Instagram follow proof uploads
        $ig_bluvocationfest_proof = '';
        $ig_smkblverse_proof = '';

        // Upload @bluvocationfest proof
        if (isset($_FILES['ig_bluvocationfest_proof']) && $_FILES['ig_bluvocationfest_proof']['error'] == 0) {
            $file = $_FILES['ig_bluvocationfest_proof'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];

            if (in_array($file['type'], $allowed_types)) {
                $filename = $pendaftar_id . '_bluvocationfest_' . time() . '_' . $file['name'];
                $upload_path = 'uploads/' . $filename;

                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $ig_bluvocationfest_proof = $filename;
                }
            }
        }

        // Upload @smkbl.verse proof
        if (isset($_FILES['ig_smkblverse_proof']) && $_FILES['ig_smkblverse_proof']['error'] == 0) {
            $file = $_FILES['ig_smkblverse_proof'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];

            if (in_array($file['type'], $allowed_types)) {
                $filename = $pendaftar_id . '_smkblverse_' . time() . '_' . $file['name'];
                $upload_path = 'uploads/' . $filename;

                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $ig_smkblverse_proof = $filename;
                }
            }
        }

        // Insert main registration
        $data = [
            'id' => $pendaftar_id,
            'nama' => $nama,
            'email' => $email,
            'telepon' => $telepon,
            'sekolah' => $sekolah,
            'kelas' => $kelasForInsert,
            'kategori_lomba_id' => $kategori_id,
            'foto_kartu_pelajar' => $foto_kartu_pelajar,
            'ig_bluvocationfest_proof' => $ig_bluvocationfest_proof,
            'ig_smkblverse_proof' => $ig_smkblverse_proof
        ];

        // Jika skema b_pendaftar memiliki kolom nama_kelompok (NOT NULL), isi dengan nilai yang sesuai
        if ($jenis_lomba == 'kelompok') {
            $data['nama_kelompok'] = $nama_kelompok;
        } else {
            // Beberapa skema menyertakan kolom ini untuk individu juga
            $data['nama_kelompok'] = 'INDIVIDU';
        }

        // Cek unik nama_kelompok untuk lomba kelompok (GLOBAL, semua kategori)
        // Gunakan nilai yang akan disimpan ke DB sebagai sumber kebenaran
        $inserted = false;
        if ($jenis_lomba == 'kelompok') {
            $namaKelompokToCheck = $data['nama_kelompok'] ?? '';
            if ($namaKelompokToCheck !== '') {
                $existingTeam = $db->fetch(
                    "SELECT id FROM b_user_pendaftaran WHERE LOWER(nama_kelompok) = LOWER(?) LIMIT 1",
                    [$namaKelompokToCheck]
                );
                if ($existingTeam) {
                    $error = 'Nama kelompok sudah digunakan di lomba lain. Silakan gunakan nama lain.';
                }
            }
        }

        // Jika tidak ada error, simpan data ke session dan alihkan ke halaman pembayaran (belum insert DB)
        if (empty($error)) {
            // Siapkan user_pendaftaran data (tanpa insert)
            $defaultNamaKelompok = ($jenis_lomba == 'kelompok')
                ? $nama_kelompok
                : 'INDIVIDU';

            $userPendaftaranData = [
                'user_id' => $currentUser['id'],
                'pendaftar_id' => $pendaftar_id,
                'kategori_lomba_id' => $kategori_id,
                'status' => 'pending',
                'nama_kelompok' => $defaultNamaKelompok
            ];

            $upKelasColumnInfo = $db->fetch("SHOW COLUMNS FROM b_user_pendaftaran LIKE 'kelas'");
            if ($upKelasColumnInfo) {
                $userPendaftaranData['kelas'] = $kelasForInsert;
            }

            // Kumpulkan data anggota (tanpa insert)
            $anggota_list = [];
            if ($jenis_lomba == 'kelompok') {
                $allowVariable = (int)($kategori['allow_variable_team_size'] ?? 0) === 1;
                $maxPeserta = max(1, intval($kategori['max_peserta'] ?? 1));
                $requested = intval($_POST['jumlah_anggota'] ?? 0);
                $jumlah_anggota = $allowVariable ? min(max($requested, 1), $maxPeserta) : $maxPeserta;

                for ($i = 1; $i < $jumlah_anggota; $i++) {
                    $anggota_nama = trim($_POST["anggota_nama_$i"] ?? '');
                    $anggota_email = trim($_POST["anggota_email_$i"] ?? '');
                    $anggota_telepon = trim($_POST["anggota_telepon_$i"] ?? '');
                    $anggota_sekolah = trim($_POST["anggota_sekolah_$i"] ?? '');
                    $anggota_kelas = $_POST["anggota_kelas_$i"] ?? '';
                    if (!in_array($anggota_kelas, $allowedKelas, true)) {
                        $anggota_kelas = $allowedKelas[0] ?? '1';
                    }
                    $anggotaKelasNormalized = $kelasMap[$anggota_kelas] ?? (string)$anggota_kelas;
                    $anggotaKelasForInsert = $anggotaKelasNormalized;

                    if (!empty($anggota_nama) && !empty($anggota_sekolah) && !empty($anggota_kelas)) {
                        $anggota_foto = '';
                        if (isset($_FILES["anggota_foto_$i"]) && $_FILES["anggota_foto_$i"]['error'] == 0) {
                            $file = $_FILES["anggota_foto_$i"];
                            if (in_array($file['type'], $allowed_types)) {
                                $filename = $pendaftar_id . '_anggota_' . $i . '_' . time() . '_' . $file['name'];
                                $upload_path = 'uploads/' . $filename;
                                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                                    $anggota_foto = $filename;
                                }
                            }
                        }

                        $anggota_ig_bluvocationfest_proof = '';
                        $anggota_ig_smkblverse_proof = '';
                        if (isset($_FILES["anggota_ig_bluvocationfest_$i"]) && $_FILES["anggota_ig_bluvocationfest_$i"]['error'] == 0) {
                            $file = $_FILES["anggota_ig_bluvocationfest_$i"];
                            if (in_array($file['type'], $allowed_types)) {
                                $filename = $pendaftar_id . '_anggota_' . $i . '_bluvocationfest_' . time() . '_' . $file['name'];
                                $upload_path = 'uploads/' . $filename;
                                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                                    $anggota_ig_bluvocationfest_proof = $filename;
                                }
                            }
                        }
                        if (isset($_FILES["anggota_ig_smkblverse_$i"]) && $_FILES["anggota_ig_smkblverse_$i"]['error'] == 0) {
                            $file = $_FILES["anggota_ig_smkblverse_$i"];
                            if (in_array($file['type'], $allowed_types)) {
                                $filename = $pendaftar_id . '_anggota_' . $i . '_smkblverse_' . time() . '_' . $file['name'];
                                $upload_path = 'uploads/' . $filename;
                                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                                    $anggota_ig_smkblverse_proof = $filename;
                                }
                            }
                        }

                        $anggota_list[] = [
                            'pendaftar_id' => $pendaftar_id,
                            'nama' => $anggota_nama,
                            'email' => $anggota_email,
                            'telepon' => $anggota_telepon,
                            'sekolah' => $anggota_sekolah,
                            'kelas' => $anggotaKelasForInsert,
                            'foto_kartu_pelajar' => $anggota_foto,
                            'ig_bluvocationfest_proof' => $anggota_ig_bluvocationfest_proof,
                            'ig_smkblverse_proof' => $anggota_ig_smkblverse_proof,
                            'nama_kelompok' => $defaultNamaKelompok
                        ];
                    }
                }
            }

            // Langsung simpan ke database
            try {
                $db->getConnection()->beginTransaction();

                // 1) Insert pendaftar utama
                $db->insert('b_pendaftar', $data);

                // 2) Insert user_pendaftaran data
                $db->insert('b_user_pendaftaran', $userPendaftaranData);

                // 3) Insert anggota jika ada
                if (!empty($anggota_list)) {
                    foreach ($anggota_list as $anggota) {
                        $db->insert('b_anggota_kelompok', $anggota);
                    }
                }

                $db->getConnection()->commit();

                // Alihkan ke halaman pembayaran
                header("Location: payment.php?pendaftar_id=" . $pendaftar_id);
                exit;
            } catch (Exception $e) {
                $db->getConnection()->rollBack();
                $error = 'Gagal menyimpan data pendaftaran. Silakan coba lagi.';
            }
        } else {
            if (empty($error)) {
                $error = 'Gagal mendaftar. Silakan coba lagi.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran - <?= htmlspecialchars($kategori['nama']) ?></title>
    <link rel="icon" type="image/png" href="favicon/bcf.png" sizes="32x32">
    <link href="bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #1630df 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .registration-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #1630df 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #1630df 100%);
            border: none;
            color: white;
            padding: 15px 40px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .member-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border: 2px dashed #dee2e6;
        }

        .member-section.active {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .kategori-info {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <!-- Back to Home Navigation -->
        <div class="row mb-3">
            <div class="col-12">
                <a href="index.php" class="text-decoration-none bg-white px-3 py-2 rounded shadow-sm d-inline-block">
                    <i class="fas fa-arrow-left me-2 text-primary"></i><span class="text-dark">Kembali ke Beranda</span>
                </a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="registration-card">
                    <!-- Header -->
                    <div class="card-header-custom">
                        <h2 class="mb-2">
                            <i class="<?= htmlspecialchars($kategori['icon'] ?? 'fas fa-trophy') ?> me-3"></i>
                            <?= htmlspecialchars($kategori['nama']) ?>
                        </h2>
                        <p class="mb-0">
                            <span class="badge bg-light text-dark me-2">
                                <i class="fas fa-<?= ($kategori['jenis_lomba'] ?? 'individu') == 'kelompok' ? 'users' : 'user' ?> me-1"></i>
                                <?= ucfirst($kategori['jenis_lomba'] ?? 'individu') ?>
                            </span>
                            <?php if (($kategori['jenis_lomba'] ?? 'individu') == 'kelompok'): ?>
                                <span class="badge bg-light text-dark">
                                    Max: <?= $kategori['max_peserta'] ?? 1 ?> orang
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <!-- Form -->
                    <div class="p-4">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
                            </div>
                        <?php endif; ?>

                        <!-- Kategori Info -->
                        <div class="kategori-info">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="fw-bold mb-2">Deskripsi Lomba:</h6>
                                    <p class="mb-0"><?= htmlspecialchars($kategori['deskripsi']) ?></p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <h5 class="text-primary fw-bold mb-0">
                                        Rp <?= number_format($kategori['biaya'], 0, ',', '.') ?>
                                    </h5>
                                    <small class="text-muted">Biaya Pendaftaran</small>
                                </div>
                            </div>
                        </div>

                        <form method="POST" enctype="multipart/form-data">
                            <!-- Nama Kelompok (hanya untuk lomba kelompok) -->
                            <?php if (($kategori['jenis_lomba'] ?? 'individu') == 'kelompok'): ?>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="nama_kelompok" class="form-label">Nama Kelompok</label>
                                            <input type="text" class="form-control" id="nama_kelompok" name="nama_kelompok" placeholder="Masukkan nama kelompok" required>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Ketua Tim / Peserta Utama -->
                            <h5 class="mb-3">
                                <i class="fas fa-user me-2 text-primary"></i>
                                <?= ($kategori['jenis_lomba'] ?? 'individu') == 'kelompok' ? 'Ketua Tim' : 'Data Peserta' ?>
                            </h5>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama Lengkap *</label>
                                        <input type="text" class="form-control" id="nama" name="nama" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telepon" class="form-label">Nomor Telepon *</label>
                                        <input type="tel" class="form-control" id="telepon" name="telepon" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sekolah" class="form-label">Nama Sekolah *</label>
                                        <input type="text" class="form-control" id="sekolah" name="sekolah" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="kelas" class="form-label">Kelas *</label>
                                        <select class="form-select" id="kelas" name="kelas" required>
                                            <option value="">Pilih Kelas</option>
                                            <?php if ($kategori['allow_sd']): ?>
                                                <option value="1">SD Kelas 1</option>
                                                <option value="2">SD Kelas 2</option>
                                                <option value="3">SD Kelas 3</option>
                                                <option value="4">SD Kelas 4</option>
                                                <option value="5">SD Kelas 5</option>
                                                <option value="6">SD Kelas 6</option>
                                            <?php endif; ?>
                                            <?php if ($kategori['allow_smp']): ?>
                                                <option value="7">SMP Kelas 1</option>
                                                <option value="8">SMP Kelas 2</option>
                                                <option value="9">SMP Kelas 3</option>
                                            <?php endif; ?>
                                            <?php if ($kategori['allow_sma']): ?>
                                                <option value="10">SMA/Sederajat Kelas 1</option>
                                                <option value="11">SMA/Sederajat Kelas 2</option>
                                                <option value="12">SMA/Sederajat Kelas 3</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <?php
                                if ($kategori["butuh_kartu_pelajar"] != 0) {
                                ?>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="foto_kartu_pelajar" class="form-label">Foto Kartu Pelajar *</label>
                                            <input type="file" class="form-control" id="foto_kartu_pelajar" name="foto_kartu_pelajar" accept="image/*" required>
                                            <small class="text-muted">Format: JPG, PNG. Maksimal 2MB</small>
                                        </div>
                                    </div>
                                <?php
                                }
                                ?>
                            </div>

                            <!-- Instagram Follow Proof (only if required) -->
                            <?php if ($kategori['require_ig_proof'] == 1): ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ig_bluvocationfest_proof" class="form-label">Bukti Follow @bluvocationfest *</label>
                                            <input type="file" class="form-control" id="ig_bluvocationfest_proof" name="ig_bluvocationfest_proof" accept="image/*" required>
                                            <small class="text-muted">Screenshot bukti follow Instagram @bluvocationfest</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ig_smkblverse_proof" class="form-label">Bukti Follow @smk.budiluhur *</label>
                                            <input type="file" class="form-control" id="ig_smkblverse_proof" name="ig_smkblverse_proof" accept="image/*" required>
                                            <small class="text-muted">Screenshot bukti follow Instagram @smk.budiluhur</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Anggota Kelompok (jika lomba kelompok) -->
                            <?php if (($kategori['jenis_lomba'] ?? 'individu') == 'kelompok'): ?>
                                <hr class="my-4">
                                <h5 class="mb-3">
                                    <i class="fas fa-users me-2 text-primary"></i>Anggota Kelompok
                                </h5>

                                <div class="mb-3">
                                    <label for="jumlah_anggota" class="form-label">Jumlah Anggota (termasuk ketua tim)</label>
                                    <?php $allowVariable = (int)($kategori['allow_variable_team_size'] ?? 0) === 1;
                                    $maxPeserta = (int)($kategori['max_peserta'] ?? 1); ?>
                                    <select class="form-select" id="jumlah_anggota" name="jumlah_anggota" onchange="updateAnggotaForm()" <?= $allowVariable ? '' : 'disabled' ?>>
                                        <?php if ($allowVariable): ?>
                                            <?php for ($i = 1; $i <= max(1, $maxPeserta); $i++): ?>
                                                <option value="<?= $i ?>" <?= $i === max(1, $maxPeserta) ? 'selected' : '' ?>><?= $i ?> orang</option>
                                            <?php endfor; ?>
                                        <?php else: ?>
                                            <option value="<?= max(1, $maxPeserta) ?>" selected><?= max(1, $maxPeserta) ?> orang (tetap)</option>
                                        <?php endif; ?>
                                    </select>
                                    <?php if (!$allowVariable): ?>
                                        <small class="text-muted">Jumlah anggota wajib tepat <?= max(1, $maxPeserta) ?> orang.</small>
                                    <?php endif; ?>
                                </div>


                                <div id="anggotaContainer">
                                    <!-- Anggota forms will be generated here -->
                                </div>
                            <?php endif; ?>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="agree" required>
                                <label class="form-check-label" for="agree">
                                    Saya setuju dengan syarat dan ketentuan yang berlaku
                                </label>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-submit btn-lg">
                                    <i class="fas fa-arrow-right me-2"></i>Lanjut ke Pembayaran
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap-5.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if (($kategori['jenis_lomba'] ?? 'individu') == 'kelompok'): ?>

            function getClassOptions() {
                let options = '';
                <?php if ($kategori['allow_sd']): ?>
                    options += '<option value="1">SD Kelas 1</option>';
                    options += '<option value="2">SD Kelas 2</option>';
                    options += '<option value="3">SD Kelas 3</option>';
                    options += '<option value="4">SD Kelas 4</option>';
                    options += '<option value="5">SD Kelas 5</option>';
                    options += '<option value="6">SD Kelas 6</option>';
                <?php endif; ?>
                <?php if ($kategori['allow_smp']): ?>
                    options += '<option value="7">SMP Kelas 1</option>';
                    options += '<option value="8">SMP Kelas 2</option>';
                    options += '<option value="9">SMP Kelas 3</option>';
                <?php endif; ?>
                <?php if ($kategori['allow_sma']): ?>
                    options += '<option value="10">SMA/Sederajat Kelas 1</option>';
                    options += '<option value="11">SMA/Sederajat Kelas 2</option>';
                    options += '<option value="12">SMA/Sederajat Kelas 3</option>';
                <?php endif; ?>
                return options;
            }

            const requireStudentCard = <?php echo (int)($kategori['butuh_kartu_pelajar'] ?? 0); ?> === 1;

            function updateAnggotaForm() {
                const select = document.getElementById('jumlah_anggota');
                const jumlahAnggota = parseInt(select.value);
                const container = document.getElementById('anggotaContainer');
                container.innerHTML = '';

                // Generate forms for additional members (excluding ketua tim)
                for (let i = 1; i < jumlahAnggota; i++) {
                    const memberHtml = `
                    <div class="member-section">
                        <h6 class="mb-3">
                            <i class="fas fa-user me-2"></i>Anggota ${i + 1}
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" name="anggota_nama_${i}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="anggota_email_${i}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nomor Telepon</label>
                                    <input type="tel" class="form-control" name="anggota_telepon_${i}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Sekolah *</label>
                                    <input type="text" class="form-control" name="anggota_sekolah_${i}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Kelas *</label>
                                    <select class="form-select" name="anggota_kelas_${i}" required>
                                        <option value="">Pilih Kelas</option>
                                        ${getClassOptions()}
                                    </select>
                                </div>
                            </div>
                            ${requireStudentCard ? `
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Foto Kartu Pelajar *</label>
                                    <input type="file" class="form-control" name="anggota_foto_${i}" accept="image/*" required>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                        ${<?php echo $kategori['require_ig_proof'] == 1 ? 'true' : 'false'; ?> ? `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Bukti Follow @bluvocationfest *</label>
                                    <input type="file" class="form-control" name="anggota_ig_bluvocationfest_${i}" accept="image/*" required>
                                    <small class="text-muted">Screenshot bukti follow Instagram @bluvocationfest</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Bukti Follow @smk.budiluhur *</label>
                                    <input type="file" class="form-control" name="anggota_ig_smkblverse_${i}" accept="image/*" required>
                                    <small class="text-muted">Screenshot bukti follow Instagram @smk.budiluhur</small>
                                </div>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                `;
                    container.innerHTML += memberHtml;
                }
            }

            // Initialize anggota form
            document.addEventListener('DOMContentLoaded', function() {
                updateAnggotaForm();
            });
        <?php endif; ?>
    </script>
</body>

</html>