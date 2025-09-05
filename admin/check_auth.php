<?php
session_start();
require_once '../config/database.php';

// Fungsi untuk mengecek apakah admin sudah login
function checkAdminAuth() {
    // Cek session admin_id
    if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
        // Redirect ke halaman deny access
        header('Location: deny_access.php');
        exit;
    }
    
    // Verifikasi admin masih ada di database
    try {
        $db = getDB();
        $admin = $db->fetch("SELECT * FROM admin WHERE id = ?", [$_SESSION['admin_id']]);
        
        if (!$admin) {
            // Admin tidak ditemukan, hapus session dan redirect
            session_destroy();
            header('Location: deny_access.php');
            exit;
        }
        
        return $admin;
    } catch (Exception $e) {
        // Error database, hapus session dan redirect
        session_destroy();
        header('Location: deny_access.php');
        exit;
    }
}

// Fungsi untuk mengecek apakah user adalah admin
function isAdmin() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Fungsi untuk logout admin
function adminLogout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Auto-check jika file ini di-include
$current_file = basename($_SERVER['PHP_SELF']);
if ($current_file !== 'login.php' && 
    $current_file !== 'logout.php' &&
    $current_file !== 'check_auth.php' &&
    $current_file !== 'index_redirect.php') {
    checkAdminAuth();
}
?>
