<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';
require_once 'auth.php';

$auth = new Auth();
$db = getDB();

// Ambil ID lomba dari parameter
$lomba_id = $_GET['id'] ?? null;

if (!$lomba_id) {
    header('Location: index.php');
    exit;
}

// Ambil detail lomba
$lomba = $db->fetch("SELECT * FROM b_kategori_lomba WHERE id = ? AND status = 'aktif'", [$lomba_id]);

if (!$lomba) {
    header('Location: index.php');
    exit;
}

// Jika user sudah login, ambil data user
if ($auth->isLoggedIn()) {
    $currentUser = $auth->getCurrentUser();
    $userPendaftaran = $auth->getUserPendaftaran();
    
    // Cek apakah user sudah mendaftar di lomba ini
    $sudahDaftar = $db->fetch(
        "SELECT * FROM b_user_pendaftaran WHERE user_id = ? AND kategori_lomba_id = ?",
        [$currentUser['id'], $lomba_id]
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lomba['nama']); ?> - LombaBCF</title>
<link rel="icon" type="image/png" href="favicon/bcf.png" sizes="32x32">
    <link href="bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: bold;
            color: #667eea !important;
        }
        
        .nav-link {
            color: #495057 !important;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: #667eea !important;
            transform: translateY(-1px);
        }
        
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #1630df 100%);
            padding: 100px 0 60px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .lomba-icon-large {
            width: 170px;
            height: 170px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
            margin: 0 auto 30px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .content-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin: 30px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: #333;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .section-title i {
            color: #667eea;
            margin-right: 10px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .info-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            border-left: 4px solid #667eea;
        }
        
        .info-card i {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .info-card h5 {
            color: #333;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .info-card p {
            color: #666;
            margin: 0;
        }
        
        .rule-book-section {
            background: linear-gradient(135deg, #667eea 0%, #1630df 100%);
            color: white;
            border-radius: 20px;
            padding: 40px;
            margin: 30px 0;
            text-align: center;
        }
        
        .rule-book-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.8;
        }
        
        .btn-primary-custom {
            background: linear-gradient(45deg, #667eea, #1630df);
            border: none;
            border-radius: 25px;
            padding: 15px 30px;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin: 10px;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .btn-success-custom {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 25px;
            padding: 15px 30px;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin: 10px;
        }
        
        .btn-success-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.3);
            color: white;
        }
        
        .btn-warning-custom {
            background: linear-gradient(45deg, #ffc107, #ff9800);
            border: none;
            border-radius: 25px;
            padding: 15px 30px;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin: 10px;
        }
        
        .btn-warning-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 193, 7, 0.3);
            color: white;
        }
        .action-stack-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 12px;
            margin-top: 40px;
            width: 100%;
        }
        
        .status-badge {
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .status-pending { 
            background: linear-gradient(45deg, #fff3cd, #ffeaa7); 
            color: #856404; 
            border-color: #ffc107;
        }
        .status-approved { 
            background: linear-gradient(45deg, #d4edda, #c3f3d3); 
            color: #155724; 
            border-color: #28a745;
        }
        .status-rejected { 
            background: linear-gradient(45deg, #f8d7da, #f5c6cb); 
            color: #721c24; 
            border-color: #dc3545;
        }
        
        .status-badge:hover {
            transform: scale(1.05);
        }

        /* Timeline styles (mirroring index.php) */
        .timeline {
            position: relative;
            padding: 40px 0;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 80px;
            bottom: 0;
            width: 2px;
            background: linear-gradient(45deg, #667eea, #1630df);
            transform: translateX(-50%);
        }
        .timeline-item {
            position: relative;
            margin-bottom: 60px;
            margin-top: 40px;
        }
        .timeline-item:nth-child(odd) {
            padding-right: 50%;
            padding-right: calc(50% + 20px);
        }
        .timeline-item:nth-child(even) {
            padding-left: 50%;
            padding-left: calc(50% + 20px);
        }
        .timeline-content {
            background: #ffffff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            position: relative;
        }
        .timeline-content::before {
            content: '';
            position: absolute;
            top: 20px;
            width: 0;
            height: 0;
            border: 10px solid transparent;
        }
        .timeline-item:nth-child(odd) .timeline-content::before {
            right: -20px;
            border-left-color: #ffffff;
        }
        .timeline-item:nth-child(even) .timeline-content::before {
            left: -20px;
            border-right-color: #ffffff;
        }
        .timeline-dot {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 20px;
            height: 20px;
            background: linear-gradient(45deg, #667eea, #1630df);
            border-radius: 50%;
            transform: translateX(-50%) translateY(-50%);
            z-index: 2;
            border: 3px solid #ffffff;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
        }
        @media (max-width: 768px) {
            .timeline::before {
                left: 20px;
                top: 0;
                transform: none;
            }
            .timeline-item {
                padding-left: 50px !important;
                padding-right: 0 !important;
                margin: 30px 0;
            }
            .timeline-content::before {
                left: -20px !important;
                right: auto !important;
                border-right-color: #ffffff !important;
                border-left-color: transparent !important;
            }
            .timeline-dot {
                left: 20px;
                transform: translateX(-50%) translateY(-50%);
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold py-0" href="index.php">
                <img src="images/bcf.png" alt="" width="70"> Bluvocation Creative Fest
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#kategori">Kategori Lomba</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#timeline">Timeline</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#hadiah">Hadiah</a>
                    </li>
                    <?php if ($auth->isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php#lomba-saya">Lomba Saya</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if ($auth->isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($currentUser['nama_lengkap']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="index.php#lomba-saya">Lomba Saya</a></li>
                                <li><a class="dropdown-item" href="daftar.php">Daftar Lomba</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="index.php?logout=1">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <div class="mb-4">
                        <?php if (!empty($lomba['card_pic'])): ?>
                            <img src="<?= htmlspecialchars($lomba['card_pic']) ?>" alt="<?= htmlspecialchars($lomba['nama']) ?>" style="width:70%;height:auto;display:block;border-radius:15px;margin:0 auto;">
                        <?php else: ?>
                            <div class="lomba-icon-large" style="overflow:hidden;">
                                <i class="fas fa-trophy"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h1 class="display-4 fw-bold mb-4 animate__animated animate__fadeInUp">
                        <?php echo htmlspecialchars($lomba['nama']); ?>
                    </h1>
                    <p class="lead mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                        <?php echo htmlspecialchars($lomba['deskripsi']); ?>
                    </p>
                    
                    <?php if ($auth->isLoggedIn() && $sudahDaftar): ?>
                        <div class="animate__animated animate__fadeInUp animate__delay-2s">
                            <span class="status-badge status-<?php echo $sudahDaftar['status']; ?>">
                                <?php 
                                switch($sudahDaftar['status']) {
                                    case 'pending': 
                                        echo '<i class="fas fa-clock"></i> Menunggu Approval'; 
                                        break;
                                    case 'approved': 
                                        echo '<i class="fas fa-check"></i> Diterima'; 
                                        break;
                                    case 'rejected': 
                                        echo '<i class="fas fa-times"></i> Ditolak'; 
                                        break;
                                }
                                ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <!-- Informasi Lomba -->
        <div class="content-section">
            <h2 class="section-title">
                <i class="fas fa-info-circle"></i> Informasi Lomba
            </h2>
            
            <div class="info-grid">
                
                <div class="info-card">
                    <i class="fas fa-users"></i>
                    <h5>Peserta</h5>
                    <p><?php echo htmlspecialchars($lomba['peserta'] ?? 'Siswa SD/SMP/SMA/Sederajat'); ?></p>
                </div>
                <div class="info-card">
                    <i class="fas fa-clock"></i>
                    <h5>Durasi Lomba</h5>
                    <p><?php echo htmlspecialchars($lomba['durasi'] ?? '3 Jam'); ?></p>
                </div>
                <div class="info-card">
                    <i class="fas fa-map-marker-alt"></i>
                    <h5>Tempat</h5>
                    <p><?php echo htmlspecialchars($lomba['tempat'] ?? 'Gedung BCF'); ?></p>
                </div>
            </div>
        </div>

        <!-- Rule Book Section -->
        <div class="rule-book-section">
            <div class="rule-book-icon">
                <i class="fas fa-book"></i>
            </div>
            <h2 class="mb-4">Rule Book Lomba</h2>
            <p class="lead mb-4">
                Unduh rule book untuk mengetahui ketentuan dan peraturan lomba secara lengkap
            </p>
            
            <div class="d-flex justify-content-center flex-wrap">
                <!-- <a href="uploads/rulebook/<?php //echo $lomba['id']; ?>.pdf" class="btn-primary-custom" target="_blank">
                    <i class="fas fa-download"></i> Download Rule Book
                </a> -->
                <a href="uploads/rulebook/<?php echo $lomba['id']; ?>.pdf" class="btn-success-custom" target="_blank">
                    <i class="fas fa-eye"></i> Lihat Rule Book
                </a>
            </div>
        </div>

        <!-- Timeline Section per Lomba -->
        <div class="content-section">
            <h2 class="section-title">
                <i class="fas fa-calendar"></i> Timeline Lomba
            </h2>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>Pendaftaran</h4>
                        <p>Periode pendaftaran dibuka untuk kategori ini</p>
                        <small class="text-muted"><?php echo htmlspecialchars($lomba['timeline_pendaftaran'] ?? ''); ?></small>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>Seleksi</h4>
                        <p>Tim juri menyeleksi karya yang masuk</p>
                        <small class="text-muted"><?php echo htmlspecialchars($lomba['timeline_seleksi'] ?? ''); ?></small>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>Pengumuman</h4>
                        <p>Pengumuman pemenang</p>
                        <small class="text-muted"><?php echo htmlspecialchars($lomba['timeline_pengumuman'] ?? ''); ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="content-section text-center">
            <h2 class="section-title">
                <i class="fas fa-rocket"></i> Mulai Sekarang
            </h2>
            
            <div class="action-stack-right">
                <?php if ($auth->isLoggedIn()): ?>
                    <?php if (!$sudahDaftar): ?>
                        <a href="daftar.php?kategori=<?php echo $lomba['id']; ?>" class="btn-primary-custom">
                            <i class="fas fa-plus"></i> Daftar Lomba
                        </a>
                    <?php else: ?>
                        <span class="btn-warning-custom">
                            <i class="fas fa-check"></i> Sudah Terdaftar
                        </span>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php" class="btn-primary-custom">
                        <i class="fas fa-sign-in-alt"></i> Login untuk Daftar
                    </a>
                    <a href="register.php" class="btn-success-custom">
                        <i class="fas fa-user-plus"></i> Daftar Akun
                    </a>
                <?php endif; ?>
                
                <a href="index.php" class="btn-warning-custom">
                    <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

    <script src="bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
