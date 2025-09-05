<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // Register user baru
    public function register($data) {
        try {
            // Cek apakah username atau email sudah ada
            $existingUser = $this->db->fetch(
                "SELECT id FROM users WHERE username = ? OR email = ?", 
                [$data['username'], $data['email']]
            );
            
            if ($existingUser) {
                return ['success' => false, 'message' => 'Username atau email sudah terdaftar'];
            }
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insert user baru
            $userData = [
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'nama_lengkap' => $data['nama_lengkap'],
                'sekolah' => $data['sekolah'],
                'kelas' => $data['kelas'],
                'telepon' => $data['telepon'],
                'alamat' => $data['alamat']
            ];
            
            $userId = $this->db->insert('users', $userData);
            
            if ($userId) {
                return ['success' => true, 'message' => 'Registrasi berhasil', 'user_id' => $userId];
            } else {
                return ['success' => false, 'message' => 'Gagal mendaftar user'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // Login user
    public function login($username, $password) {
        try {
            // Cari user berdasarkan username atau email
            $user = $this->db->fetch(
                "SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1", 
                [$username, $username]
            );
            
            if (!$user) {
                return ['success' => false, 'message' => 'Username/email tidak ditemukan'];
            }
            
            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['sekolah'] = $user['sekolah'];
                $_SESSION['kelas'] = $user['kelas'];
                $_SESSION['is_logged_in'] = true;
                
                return ['success' => true, 'message' => 'Login berhasil', 'user' => $user];
            } else {
                return ['success' => false, 'message' => 'Password salah'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // Logout
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Logout berhasil'];
    }
    
    // Cek apakah user sudah login
    public function isLoggedIn() {
        return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
    }
    
    // Dapatkan data user yang sedang login
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $this->db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    }
    
    // Dapatkan pendaftaran user
    public function getUserPendaftaran($userId = null) {
        if (!$userId) {
            $userId = $_SESSION['user_id'] ?? null;
        }
        
        if (!$userId) {
            return [];
        }
        
        return $this->db->fetchAll(
            "SELECT up.*, kl.nama as nama_lomba, kl.deskripsi 
             FROM user_pendaftaran up 
             JOIN kategori_lomba kl ON up.kategori_lomba_id = kl.id 
             WHERE up.user_id = ? 
             ORDER BY up.tanggal_daftar DESC",
            [$userId]
        );
    }
    
    // Tambah pendaftaran baru
    public function addPendaftaran($kategoriLombaId, $pendaftarId) {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                return ['success' => false, 'message' => 'User tidak login'];
            }
            
            // Cek apakah sudah mendaftar di kategori ini
            $existing = $this->db->fetch(
                "SELECT id FROM user_pendaftaran WHERE user_id = ? AND kategori_lomba_id = ?",
                [$userId, $kategoriLombaId]
            );
            
            if ($existing) {
                return ['success' => false, 'message' => 'Anda sudah mendaftar di lomba ini'];
            }
            
            $data = [
                'user_id' => $userId,
                'pendaftar_id' => $pendaftarId,
                'kategori_lomba_id' => $kategoriLombaId,
                'status' => 'pending'
            ];
            
            $result = $this->db->insert('user_pendaftaran', $data);
            
            if ($result) {
                return ['success' => true, 'message' => 'Pendaftaran berhasil ditambahkan'];
            } else {
                return ['success' => false, 'message' => 'Gagal menambahkan pendaftaran'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'register':
                $result = $auth->register($_POST);
                echo json_encode($result);
                exit;
                
            case 'login':
                $result = $auth->login($_POST['username'], $_POST['password']);
                echo json_encode($result);
                exit;
                
            case 'logout':
                $result = $auth->logout();
                echo json_encode($result);
                exit;
        }
    }
}
?>
