<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'ppdb_mtsn1';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 1. Tambah kolom ke tabel pendaftar
    $pdo->exec("ALTER TABLE pendaftar ADD COLUMN test_hari VARCHAR(50) NULL AFTER status");
    $pdo->exec("ALTER TABLE pendaftar ADD COLUMN test_sesi VARCHAR(20) NULL AFTER test_hari");
    
    echo "Database Berhasil Diperbarui: Kolom 'test_hari' dan 'test_sesi' telah ditambahkan.";
} catch (PDOException $e) {
    echo "Info/Error: " . $e->getMessage();
}
?>
