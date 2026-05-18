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

echo "Pendaftar with nilai_ujian > 0 AND jalur_id NOT IN (7, 9, 11):\n";
$pendaftar = $pdo->query("SELECT id, nama_lengkap, status, jalur_id, status_tahfidz, nilai_ujian FROM pendaftar WHERE nilai_ujian > 0 AND jalur_id NOT IN (7, 9, 11)")->fetchAll(PDO::FETCH_ASSOC);
print_r($pendaftar);
?>
