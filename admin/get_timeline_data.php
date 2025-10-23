<?php
require_once 'check_auth.php';

$db = getDB();

$lomba_id = $_GET['id'] ?? 0;

try {
    $timeline = $db->fetchAll(
        'SELECT * FROM b_timeline_lomba WHERE kategori_lomba_id = ? ORDER BY urutan ASC, tanggal_mulai ASC',
        [$lomba_id]
    );
    
    echo json_encode([
        'success' => true,
        'timeline' => $timeline
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>