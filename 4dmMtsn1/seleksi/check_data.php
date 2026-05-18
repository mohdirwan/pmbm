<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ppdb_mtsn1');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}

echo "All Pendaftar Data:\n";
$pendaftar = $pdo->query("SELECT id, nama_lengkap, status, jalur_id, status_tahfidz, nilai_ujian FROM pendaftar")->fetchAll(PDO::FETCH_ASSOC);
print_r($pendaftar);
?>
