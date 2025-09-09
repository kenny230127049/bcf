<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/database.php';
require_once 'auth.php';

$auth = new Auth();
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php#workshop');
    exit;
}

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = $auth->getCurrentUser();
$workshop_id = intval($_POST['workshop_id'] ?? 0);

// Validate workshop
$ws = $db->fetch('SELECT * FROM b_workshop WHERE id = ? AND status = "aktif"', [$workshop_id]);
if (!$ws) {
    $_SESSION['flash_error'] = 'Workshop tidak ditemukan atau nonaktif';
    header('Location: index.php#workshop');
    exit;
}

// Prevent duplicate registration
$existing = $db->fetch('SELECT id FROM b_workshop_pendaftar WHERE user_id = ? AND workshop_id = ?', [$user['id'], $workshop_id]);
if ($existing) {
    $_SESSION['flash_error'] = 'Anda sudah mendaftar workshop ini';
    header('Location: index.php#workshop');
    exit;
}

// Generate simple id
$reg_id = 'WS' . date('ymdHis') . rand(10,99);

$data = [
    'id' => $reg_id,
    'user_id' => $user['id'],
    'workshop_id' => $workshop_id,
    'nama' => $user['nama_lengkap'],
    'email' => $user['email'],
    'telepon' => $user['telepon'] ?? '',
    'sekolah' => $user['sekolah'] ?? '',
    'status' => 'pending',
    'metode_pembayaran' => null,
    'bukti_transfer' => null,
];

$ok = $db->insert('b_workshop_pendaftar', $data);
if ($ok) {
    // Langsung arahkan ke halaman pembayaran QRIS untuk workshop
    header('Location: workshop_payment.php?id=' . urlencode($reg_id));
    exit;
} else {
    $_SESSION['flash_error'] = 'Gagal mendaftar workshop.';
    header('Location: index.php#workshop');
    exit;
}
?>



