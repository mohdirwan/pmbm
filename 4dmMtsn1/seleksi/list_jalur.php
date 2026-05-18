<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'u914642035_pmbm2026');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}

$data = $pdo->query("SELECT no_pendaftaran, nama_lengkap, jenis_kelamin FROM pendaftar")->fetchAll();
foreach ($data as $row) {
    echo "NO: {$row['no_pendaftaran']}, NAMA: {$row['nama_lengkap']}, JK: [{$row['jenis_kelamin']}]\n";
}
?>
