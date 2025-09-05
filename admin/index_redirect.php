<?php
// File ini akan redirect ke login jika belum login, atau ke dashboard jika sudah login
session_start();

if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
    // Admin sudah login, redirect ke dashboard
    header('Location: index.php');
} else {
    // Admin belum login, redirect ke login
    header('Location: login.php');
}
exit;
?>




