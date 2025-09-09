<?php
session_start();
require_once 'config/database.php';

// Redirect jika sudah login
if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LombaBCF</title>
<link rel="icon" type="image/png" href="favicon/bcf.png" sizes="32x32">
    <link href="bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #1630df 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        
        .register-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h2 {
            color: #333;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .register-header p {
            color: #666;
            margin: 0;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-size: 18px;
            transition: all 0.3s ease;
        }
        
        .back-home:hover {
            color: #f8f9fa;
            transform: translateX(-5px);
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }
        
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
    </style>
</head>
<body>
    <a href="index.php" class="back-home">
        <i class="fas fa-arrow-left"></i> Kembali ke Beranda
    </a>
    
    <div class="register-container">
        <div class="register-header">
            <h2><i class="fas fa-user-plus"></i> Register</h2>
            <p>Daftar akun baru untuk mengikuti lomba</p>
        </div>
        
        <div id="alert-container"></div>
        
        <form id="registerForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                        <label for="username">Username</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                        <label for="email">Email</label>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-floating mb-0">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                        <label for="password">Password</label>
                    </div>
                    <div class="password-strength" id="passwordStrength"></div>
                    <input type="checkbox" id="showPassword">
                    <label for="showPassword" class="mb-3">Tunjukkan Password</label>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Konfirmasi Password" required>
                        <label for="confirm_password">Konfirmasi Password</label>
                    </div>
                </div>
            </div>
            
            <div class="form-floating">
                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" placeholder="Nama Lengkap" required>
                <label for="nama_lengkap">Nama Lengkap</label>
            </div>
            
            <!-- <div class="row"> -->
                <!-- <div class="col-md-6"> -->
                <!--     <div class="form-floating"> -->
                <!--         <input type="text" class="form-control" id="sekolah" name="sekolah" placeholder="Nama Sekolah" required> -->
                <!--         <label for="sekolah">Nama Sekolah</label> -->
                <!--     </div> -->
                <!-- </div> -->
                <!-- <div class="col-md-6"> -->
                <!--     <div class="form-floating"> -->
                <!--         <input type="text" class="form-control" id="kelas" name="kelas" placeholder="Kelas" required> -->
                <!--         <label for="kelas">Kelas</label> -->
                <!--     </div> -->
                <!-- </div> -->
            <!-- </div> -->
            
            <div class="form-floating">
                <input type="tel" class="form-control" id="telepon" name="telepon" placeholder="Nomor Telepon">
                <label for="telepon">Nomor Telepon (Opsional)</label>
            </div>
            
            <div class="form-floating">
                <textarea class="form-control" id="alamat" name="alamat" placeholder="Alamat" style="height: 100px"></textarea>
                <label for="alamat">Alamat (Opsional)</label>
            </div>
            
            <button type="submit" class="btn btn-register">
                <i class="fas fa-user-plus"></i> Daftar
            </button>
        </form>
        
        <div class="login-link">
            <p>Sudah punya akun? <a href="login.php">Login disini</a></p>
        </div>
    </div>

    <script src="bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);
            const strengthElement = document.getElementById('passwordStrength');
            
            if (password.length > 0) {
                strengthElement.textContent = `Kekuatan password: ${strength.text}`;
                strengthElement.className = `password-strength strength-${strength.class}`;
            } else {
                strengthElement.textContent = '';
            }
        });
        
        function checkPasswordStrength(password) {
            let score = 0;
            
            if (password.length >= 8) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;
            
            if (score < 2) return { text: 'Lemah', class: 'weak' };
            if (score < 4) return { text: 'Sedang', class: 'medium' };
            return { text: 'Kuat', class: 'strong' };
        }
        
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                showAlert('danger', 'Password dan konfirmasi password tidak cocok');
                return;
            }
            
            if (password.length < 6) {
                showAlert('danger', 'Password minimal 6 karakter');
                return;
            }
            
            const formData = new FormData(this);
            formData.append('action', 'register');
            
            fetch('auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
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

        document.getElementById("showPassword").onclick = function() {
            const showPassword = document.getElementById("showPassword").checked;
            const passwordElement = document.getElementById('password');

            passwordElement.type = (showPassword ? "text" : "password");
        }
    </script>
</body>
</html>

