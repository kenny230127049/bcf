<?php
// File untuk menangani akses yang tidak sah
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - Admin Panel</title>
<link rel="icon" type="image/png" href="../favicon/bcf.png" sizes="32x32">
    <link href="../bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #1630df 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .deny-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        
        .deny-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 20px;
        }
        
        .deny-title {
            color: #333;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .deny-message {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .btn-login {
            background: linear-gradient(45deg, #667eea, #1630df);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            color: white;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .btn-home {
            background: #6c757d;
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            color: white;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-left: 10px;
        }
        
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(108, 117, 125, 0.3);
            color: white;
        }
    </style>
</head>
<body>
    <div class="deny-container">
        <div class="deny-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        <h2 class="deny-title">Akses Ditolak</h2>
        <p class="deny-message">
            Anda tidak memiliki izin untuk mengakses halaman ini. 
            Silakan login sebagai admin terlebih dahulu.
        </p>
        <div>
            <a href="login.php" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Login Admin
            </a>
            <a href="../index.php" class="btn-home">
                <i class="fas fa-home"></i> Beranda
            </a>
        </div>
    </div>
    
    <script>
        // Auto redirect ke login setelah 5 detik
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 5000);
    </script>
</body>
</html>




