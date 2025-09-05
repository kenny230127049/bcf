<?php
require_once 'check_auth.php';

$db = getDB();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    
    try {
        // Check if category exists
        $category = $db->fetch("SELECT * FROM kategori_lomba WHERE id = ?", [$id]);
        if (!$category) {
            header('Location: kategori.php?error=Kategori tidak ditemukan');
            exit;
        }
        
        // Check if category has registrations
        $result = $db->fetch("SELECT COUNT(*) as count FROM pendaftar WHERE kategori_lomba_id = ?", [$id]);
        $has_registrations = $result['count'] > 0;
        
        if ($has_registrations) {
            header('Location: kategori.php?error=Kategori tidak dapat dihapus karena sudah ada pendaftar');
            exit;
        }
        
        // Delete the category
        $deleted = $db->delete('kategori_lomba', 'id = ?', [$id]);
        
        if ($deleted > 0) {
            header('Location: kategori.php?success=Kategori berhasil dihapus');
        } else {
            header('Location: kategori.php?error=Gagal menghapus kategori');
        }
    } catch (Exception $e) {
        header('Location: kategori.php?error=Error: ' . urlencode($e->getMessage()));
    }
} else {
    header('Location: kategori.php?error=ID kategori tidak valid');
}
exit;
?>
