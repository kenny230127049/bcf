<?php
session_start();
require_once 'auth.php';

$auth = new Auth();
$auth->logout();

// Redirect ke halaman utama
header('Location: index.php');
exit;
?>

