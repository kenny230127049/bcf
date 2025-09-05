<?php
require_once 'check_auth.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID lomba tidak valid']);
    exit;
}

$lomba_id = $_GET['id'];
$db = getDB();

try {
    // Ambil data lomba dari database
    $lomba = $db->fetch("SELECT * FROM kategori_lomba WHERE id = ?", [$lomba_id]);
    
    if ($lomba) {
        echo json_encode([
            'success' => true,
            'lomba' => $lomba
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Lomba tidak ditemukan'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>




