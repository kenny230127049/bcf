<?php
require_once 'config/database.php';

$db = getDB();

echo "<h2>Memperbaiki Database...</h2>";

try {
    // Tambah kolom butuh_kartu_pelajar ke tabel kategori_lomba
    echo "<p>Menambahkan kolom butuh_kartu_pelajar...</p>";
    $db->query("ALTER TABLE kategori_lomba ADD COLUMN butuh_kartu_pelajar TINYINT(1) DEFAULT 0 AFTER max_peserta");
    echo "<p style='color: green;'>✓ Kolom butuh_kartu_pelajar berhasil ditambahkan</p>";
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠ Kolom butuh_kartu_pelajar sudah ada atau error: " . $e->getMessage() . "</p>";
}

try {
    // Update data kategori yang sudah ada
    echo "<p>Mengupdate data kategori...</p>";
    $db->query("UPDATE kategori_lomba SET butuh_kartu_pelajar = 1 WHERE id IN (1, 2, 3, 5, 6)");
    $db->query("UPDATE kategori_lomba SET butuh_kartu_pelajar = 0 WHERE id IN (7, 8)");
    echo "<p style='color: green;'>✓ Data kategori berhasil diupdate</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error mengupdate data kategori: " . $e->getMessage() . "</p>";
}

try {
    // Tambah kolom foto_kartu_pelajar ke tabel pendaftar
    echo "<p>Menambahkan kolom foto_kartu_pelajar ke tabel pendaftar...</p>";
    $db->query("ALTER TABLE pendaftar ADD COLUMN foto_kartu_pelajar VARCHAR(255) DEFAULT NULL AFTER kategori_lomba_id");
    echo "<p style='color: green;'>✓ Kolom foto_kartu_pelajar berhasil ditambahkan ke tabel pendaftar</p>";
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠ Kolom foto_kartu_pelajar sudah ada atau error: " . $e->getMessage() . "</p>";
}

try {
    // Tambah kolom foto_kartu_pelajar ke tabel anggota_kelompok
    echo "<p>Menambahkan kolom foto_kartu_pelajar ke tabel anggota_kelompok...</p>";
    $db->query("ALTER TABLE anggota_kelompok ADD COLUMN foto_kartu_pelajar VARCHAR(255) DEFAULT NULL AFTER kelas");
    echo "<p style='color: green;'>✓ Kolom foto_kartu_pelajar berhasil ditambahkan ke tabel anggota_kelompok</p>";
} catch (Exception $e) {
    echo "<p style='color: orange;'>⚠ Kolom foto_kartu_pelajar sudah ada atau error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Database berhasil diperbaiki!</h3>";
echo "<p><a href='daftar_lomba.php'>Kembali ke Daftar Lomba</a></p>";
echo "<p><a href='admin/kategori.php'>Kelola Kategori Lomba</a></p>";
?>

