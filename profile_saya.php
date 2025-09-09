<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';
require_once 'auth.php';

$auth = new Auth();
$db = getDB();

// Jika user sudah login, ambil data user
if ($auth->isLoggedIn()) {
    $currentUser = $auth->getCurrentUser();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Saya - LombaBCF</title>
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
            width: 120px;
            height: 120px;
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

		.container-info {
			margin-top: 6rem;
		}

        .btn-register {
            background: linear-gradient(45deg, #667eea, #1630df);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: bold;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold py-0" href="index.php">
                <img src="images/bcf.png" alt="" width="50"> Bluvocation Creative Fest
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

    <div class="container container-info">
        <!-- Informasi Lomba -->
        <div class="content-section">
            <h2 class="section-title">
                <i class="fas fa-info-circle"></i> Profile Saya
            </h2>
			<div id="alert-container"></div>
			<form class="d-flex flex-column gap-3" id="updateForm">
				<input type="hidden" name="id" value="<?= $currentUser['id'] ?>">
				<div class="row">
					<div class="col-md-6">
						<div class="form-floating">
							<input type="text" class="form-control" id="username" name="username" placeholder="Username" required value="<?= $currentUser['username'] ?>">
							<label for="username">Username</label>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-floating">
							<input type="email" class="form-control" id="email" name="email" placeholder="Email" required value="<?= $currentUser['email'] ?>">
							<label for="email">Email</label>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-6">
						<div class="form-floating mb-0">
							<input type="password" class="form-control" id="password" name="password" placeholder="Password Baru">
							<label for="password">Password Baru</label>
						</div>
						<div class="password-strength" id="passwordStrength"></div>
						<input type="checkbox" id="showPassword">
						<label for="showPassword" class="mb-3">Tunjukkan Password</label>
					</div>
					<div class="col-md-6">
						<div class="form-floating">
							<input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Konfirmasi Password">
							<label for="confirm_password">Konfirmasi Password</label>
						</div>
					</div>
				</div>
				
				<div class="form-floating">
					<input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" placeholder="Nama Lengkap" required value="<?= $currentUser['nama_lengkap'] ?>">
					<label for="nama_lengkap">Nama Lengkap</label>
				</div>
				
				<div class="form-floating">
					<input type="tel" class="form-control" id="telepon" name="telepon" placeholder="Nomor Telepon" required value="<?= $currentUser['telepon'] ?>">
					<label for="telepon">Nomor Telepon</label>
				</div>
				

				
				<button type="submit" class="btn btn-register">
					Update
				</button>
			</form>
        </div>
	</div>

	<script>
        document.getElementById('updateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                showAlert('danger', 'Password dan konfirmasi password tidak cocok');
                return;
            }
            
            if (password.length < 6 && password != "" && confirmPassword != "") {
                showAlert('danger', 'Password minimal 6 karakter');
                return;
            }
            
            const formData = new FormData(this);
            formData.append('action', 'update');
            
            fetch('auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    // setTimeout(() => {
                    //     window.location.href = 'login.php';
                    // }, 2000);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                showAlert('danger', 'Terjadi kesalahan: ' + error.message);
            });
        });

        function showAlert(type, message) {
            const alertContainer = document.getElementById('alert-container');
            alertContainer.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }
	</script>

    <script src="bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
