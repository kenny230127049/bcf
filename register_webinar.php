<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/database.php';
require_once 'auth.php';

$auth = new Auth();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php#webinar');
    exit;
}

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = $auth->getCurrentUser();
$webinar_id = intval($_POST['webinar_id'] ?? 0);

// Validate webinar
$ws = $db->fetch('SELECT * FROM b_webinar WHERE id = ? AND status = "aktif"', [$webinar_id]);
if (!$ws) {
    $_SESSION['flash_error'] = 'Webinar tidak ditemukan atau nonaktif';
    header('Location: index.php#webinar');
    exit;
}

// Prevent duplicate registration
$existing = $db->fetch('SELECT id FROM b_webinar_pendaftar WHERE user_id = ? AND webinar_id = ?', [$user['id'], $webinar_id]);
if ($existing) {
    $_SESSION['flash_error'] = 'Anda sudah mendaftar webinar ini';
    header('Location: index.php#webinar');
    exit;
}

// Generate simple id
$reg_id = 'WS' . date('ymdHis') . rand(10,99);

$data = [
    'id' => $reg_id,
    'user_id' => $user['id'],
    'webinar_id' => $webinar_id,
    'nama' => $user['nama_lengkap'],
    'email' => $user['email'],
    'telepon' => $user['telepon'] ?? '',
    'institusi' => $user['institusi'] ?? '',
    'status' => 'pending',
    'metode_pembayaran' => null,
    'bukti_transfer' => null,
];

$ok = $db->insert('b_webinar_pendaftar', $data);
if ($ok) {
    // Langsung arahkan ke halaman pembayaran QRIS untuk webinar
    header('Location: webinar_payment.php?id=' . urlencode($reg_id));
    exit;
} else {
    $_SESSION['flash_error'] = 'Gagal mendaftar webinar.';
    header('Location: index.php#webinar');
    exit;
}
?>



