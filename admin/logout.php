<?php
session_start();

// Destroy admin session
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_role']);
session_destroy();

// Redirect ke halaman login admin
header('Location: login.php');
exit;
?>
