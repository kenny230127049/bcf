    <?php
    session_start();
    require_once 'config/database.php';

    // Cek apakah user sudah login
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    $db = getDB();
    $user_id = $_SESSION['user_id'];

    // Ambil data pendaftaran user
    $pendaftaran = $db->fetch("
        SELECT 
            p.*,
            kl.nama as nama_lomba,
            kl.deskripsi,
            kl.biaya as biaya_pendaftaran,
            kl.allow_sd,
            kl.allow_smp,
            kl.allow_sma,
            kl.jenis_lomba as tipe_lomba,
            pb.status as status_pembayaran,
            pb.bukti_transfer,
            pb.tanggal_pembayaran,
            up.tanggal_daftar,
            up.tanggal_approval,
            up.catatan_admin
        FROM b_user_pendaftaran up
        JOIN b_pendaftar p ON up.pendaftar_id = p.id
        JOIN b_kategori_lomba kl ON p.kategori_lomba_id = kl.id
        LEFT JOIN b_pembayaran pb ON p.id = pb.pendaftar_id
        WHERE up.user_id = ?
        ORDER BY up.tanggal_daftar DESC
    ", [$user_id]);

    if (!$pendaftaran) {
        header('Location: index.php');
        exit();
    }

    // Ambil data anggota tim jika ada
    $anggota_tim = [];
    if ($pendaftaran['tipe_lomba'] == 'kelompok') {
        $anggota_tim = $db->fetchAll("
            SELECT * FROM b_anggota_kelompok 
            WHERE pendaftar_id = ? 
            ORDER BY id
        ", [$pendaftaran['id']]);
    }

    // Hitung total peserta
    $total_peserta = 1; // Ketua tim
    if ($pendaftaran['tipe_lomba'] == 'kelompok') {
        $total_peserta += count($anggota_tim);
    }

    // Ambil data timeline lomba
    $timeline = $db->fetchAll("
        SELECT * FROM b_timeline_lomba 
        WHERE kategori_lomba_id = ? 
        ORDER BY tanggal_mulai
    ", [$pendaftaran['kategori_lomba_id']]);

    // Format tanggal
    function formatTanggal($tanggal)
    {
        return date('d F Y', strtotime($tanggal));
    }

    // Format kelas
    function formatKelas($kelas)
    {
        $kelas_map = [
            'SD Kelas 1-6' => 'SD Kelas 1-6',
            'SMP Kelas 1-3 (VII-IX)' => 'SMP Kelas 1-3 (VII-IX)',
            'SMA/Sederajat Kelas 1-3 (X-XII)' => 'SMA/Sederajat Kelas 1-3 (X-XII)'
        ];
        return $kelas_map[$kelas] ?? $kelas;
    }
    ?>

    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Detail Lomba Saya - Bluvocation Creative Fest</title>
        <link rel="icon" type="image/x-icon" href="images/bcf.png">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            :root {
                --primary-color: #667eea;
                /* match site/login primary */
                --secondary-color: #1630df;
                /* match site/login primary-dark */
                --accent-color: #4facfe;
                --success-color: #28a745;
                --warning-color: #ffc107;
                --danger-color: #dc3545;
                --light-bg: #f8fafc;
                --dark-text: #1e293b;
                --border-color: #e2e8f0;
            }

            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #1630df 100%);
                min-height: 100vh;
                color: var(--dark-text);
            }

            .navbar {
                background: rgba(255, 255, 255, 0.95) !important;
                backdrop-filter: blur(10px);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }

            .navbar-brand {
                font-weight: 700;
                color: var(--primary-color) !important;
            }

            .main-container {
                background: rgba(255, 255, 255, 0.95);
                border-radius: 20px;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                margin: 2rem auto;
                overflow: hidden;
            }

            .header-section {
                background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
                color: white;
                padding: 2rem;
                text-align: center;
            }

            .lomba-title {
                font-size: 2.5rem;
                font-weight: 700;
                margin-bottom: 0.5rem;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            }

            .lomba-subtitle {
                font-size: 1.1rem;
                opacity: 0.9;
                margin-bottom: 1.5rem;
            }

            .status-badge {
                display: inline-block;
                padding: 0.5rem 1rem;
                border-radius: 50px;
                font-weight: 600;
                font-size: 0.9rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .status-pending {
                background: linear-gradient(135deg, var(--warning-color), #f59e0b);
                color: #212529;
            }

            .status-confirmed {
                background: linear-gradient(135deg, var(--success-color), #20c997);
                color: white;
            }

            .status-rejected {
                background: linear-gradient(135deg, var(--danger-color), #e74c3c);
                color: white;
            }

            .content-section {
                padding: 2rem;
            }

            .info-card {
                background: white;
                border-radius: 15px;
                padding: 1.5rem;
                margin-bottom: 1.5rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                border: 1px solid var(--border-color);
            }

            .info-card h5 {
                color: var(--primary-color);
                font-weight: 600;
                margin-bottom: 1rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .info-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.75rem 0;
                border-bottom: 1px solid #f1f5f9;
            }

            .info-row:last-child {
                border-bottom: none;
            }

            .info-label {
                font-weight: 600;
                color: var(--dark-text);
                min-width: 150px;
            }

            .info-value {
                color: #64748b;
                text-align: right;
                flex: 1;
            }

            .anggota-card {
                background: #f8fafc;
                border-radius: 10px;
                padding: 1rem;
                margin-bottom: 0.5rem;
                border-left: 4px solid var(--accent-color);
            }

            .anggota-header {
                font-weight: 600;
                color: var(--primary-color);
                margin-bottom: 0.5rem;
            }

            .anggota-info {
                font-size: 0.9rem;
                color: #64748b;
            }

            .timeline-item {
                display: flex;
                align-items: center;
                padding: 1rem 0;
                border-bottom: 1px solid #e2e8f0;
            }

            .timeline-item:last-child {
                border-bottom: none;
            }

            .timeline-icon {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                margin-right: 1rem;
                flex-shrink: 0;
            }

            .timeline-content {
                flex: 1;
            }

            .timeline-title {
                font-weight: 600;
                color: var(--primary-color);
                margin-bottom: 0.25rem;
            }

            .timeline-date {
                color: #64748b;
                font-size: 0.9rem;
            }

            .btn-back {
                border: 1px solid var(--border-color);
                background: var(--border-color);
                color: var(--dark-text);
                padding: 0.75rem 1.5rem;
                border-radius: 50px;
                font-weight: 600;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                transition: all 0.3s ease;
            }

            .btn-back:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
                color: var(--dark-text);
            }

            .btn-continue {
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                border: none;
                color: white;
                padding: 0.75rem 1.5rem;
                border-radius: 50px;
                font-weight: 600;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                transition: all 0.3s ease;
            }

            .btn-continue:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
                color: white;
            }

            .stats-card {
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                color: white;
                border-radius: 15px;
                padding: 1.5rem;
                text-align: center;
                margin-bottom: 1.5rem;
            }

            .stats-number {
                font-size: 2rem;
                font-weight: 700;
                margin-bottom: 0.5rem;
            }

            .stats-label {
                font-size: 0.9rem;
                opacity: 0.9;
            }

            /* Mobile Responsive */
            @media (max-width: 768px) {
                .main-container {
                    margin: 1rem;
                    border-radius: 15px;
                }

                .header-section {
                    padding: 1.5rem 1rem;
                }

                .lomba-title {
                    font-size: 1.8rem;
                }

                .lomba-subtitle {
                    font-size: 1rem;
                }

                .content-section {
                    padding: 1.5rem 1rem;
                }

                .info-card {
                    padding: 1rem;
                }

                .info-row {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 0.25rem;
                }

                .info-label {
                    min-width: auto;
                    font-size: 0.9rem;
                }

                .info-value {
                    text-align: left;
                    font-size: 0.9rem;
                }

                .anggota-card {
                    padding: 0.75rem;
                }

                .timeline-item {
                    padding: 0.75rem 0;
                }

                .timeline-icon {
                    width: 35px;
                    height: 35px;
                    margin-right: 0.75rem;
                }

                .stats-card {
                    padding: 1rem;
                }

                .stats-number {
                    font-size: 1.5rem;
                }
            }

            @media (max-width: 576px) {
                .lomba-title {
                    font-size: 1.5rem;
                }

                .status-badge {
                    padding: 0.4rem 0.8rem;
                    font-size: 0.8rem;
                }

                .info-card h5 {
                    font-size: 1rem;
                }

                .timeline-title {
                    font-size: 0.9rem;
                }

                .timeline-date {
                    font-size: 0.8rem;
                }
            }
        </style>
    </head>

    <body>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <img src="images/bcf.png" alt="BCF" height="30" class="me-2">
                    Bluvocation Creative Fest
                </a>
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="logout.php">Logout</a>
                </div>
            </div>
        </nav>

        <div class="container">
            <div class="main-container">
                <!-- Header Section -->
                <div class="header-section">
                    <h1 class="lomba-title"><?= htmlspecialchars($pendaftaran['nama_lomba']) ?></h1>
                    <p class="lomba-subtitle">Detail Pendaftaran Lomba</p>
                    <span class="status-badge status-<?= $pendaftaran['status'] ?>">
                        <?= ucfirst($pendaftaran['status']) ?>
                    </span>
                </div>

                <!-- Content Section -->
                <div class="content-section">
                    <!-- Statistics -->
                    <div class="stats-card">
                        <div class="stats-number"><?= $total_peserta ?></div>
                        <div class="stats-label">Total Peserta</div>
                    </div>

                    <!-- Informasi Pendaftaran -->
                    <div class="info-card">
                        <h5><i class="fas fa-user"></i> Informasi Pendaftaran</h5>
                        <div class="info-row">
                            <span class="info-label">Nama Lengkap:</span>
                            <span class="info-value"><?= htmlspecialchars($pendaftaran['nama']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?= htmlspecialchars($pendaftaran['email']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">No. Telepon:</span>
                            <span class="info-value"><?= htmlspecialchars($pendaftaran['telepon']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Kelas:</span>
                            <span class="info-value"><?= formatKelas($pendaftaran['kelas']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Asal Sekolah:</span>
                            <span class="info-value"><?= htmlspecialchars($pendaftaran['sekolah']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tanggal Daftar:</span>
                            <span class="info-value"><?= formatTanggal($pendaftaran['tanggal_daftar']) ?></span>
                        </div>
                    </div>

                    <!-- Informasi Tim (jika kelompok) -->
                    <?php if ($pendaftaran['tipe_lomba'] == 'kelompok' && !empty($anggota_tim)): ?>
                        <div class="info-card">
                            <h5><i class="fas fa-users"></i> Anggota Tim</h5>
                            <?php foreach ($anggota_tim as $index => $anggota): ?>
                                <div class="anggota-card">
                                    <div class="anggota-header">Anggota <?= $index + 1 ?></div>
                                    <div class="anggota-info">
                                        <strong>Nama:</strong> <?= htmlspecialchars($anggota['nama']) ?><br>
                                        <strong>Email:</strong> <?= htmlspecialchars($anggota['email']) ?><br>
                                        <strong>Kelas:</strong> <?= formatKelas($anggota['kelas']) ?><br>
                                        <strong>Asal Sekolah:</strong> <?= htmlspecialchars($anggota['sekolah']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Informasi Pembayaran -->
                    <div class="info-card">
                        <h5><i class="fas fa-credit-card"></i> Informasi Pembayaran</h5>
                        <div class="info-row">
                            <span class="info-label">Status Pembayaran:</span>
                            <span class="info-value">
                                <?php if ($pendaftaran['status_pembayaran'] == 'success'): ?>
                                    <span class="status-badge bg-success text-white">Diterima</span>
                                <?php elseif ($pendaftaran['status_pembayaran'] == 'pending'): ?>
                                    <span class="status-badge bg-warning text-dark">Menunggu Verifikasi</span>
                                <?php else: ?>
                                    <span class="status-badge bg-danger text-white">Belum Dibayar</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Biaya Pendaftaran:</span>
                            <span class="info-value">Rp <?= number_format($pendaftaran['biaya_pendaftaran'], 0, ',', '.') ?></span>
                        </div>
                        <?php if ($pendaftaran['tanggal_pembayaran']): ?>
                            <div class="info-row">
                                <span class="info-label">Tanggal Pembayaran:</span>
                                <span class="info-value"><?= formatTanggal($pendaftaran['tanggal_pembayaran']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>



                    <!-- Deskripsi Lomba -->
                    <div class="info-card">
                        <h5><i class="fas fa-info-circle"></i> Deskripsi Lomba</h5>
                        <p><?= nl2br(htmlspecialchars($pendaftaran['deskripsi'])) ?></p>
                    </div>


                    <!-- Tombol Kembali -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="index.php" class="btn-back">
                            <i class="fas fa-arrow-left"></i>
                            Kembali ke Beranda
                        </a>

                        <?php if ($pendaftaran['status_pembayaran'] == null): ?>
                            <a href="payment.php?pendaftar_id=<?= $pendaftaran['id'] ?>" class="btn-continue">
                                <i class="fas fa-arrow-right"></i>
                                Lanjut Proses Pendaftaran
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>

    </html>
