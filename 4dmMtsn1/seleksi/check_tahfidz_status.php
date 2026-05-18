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

echo "Status Tahfidz for Jalur 11 students with status 'Terverifikasi':\n";
$sql = "SELECT status_tahfidz, COUNT(*) as count 
        FROM pendaftar 
        WHERE jalur_id = 11 AND status = 'Terverifikasi'
        GROUP BY status_tahfidz";
$results = $pdo->query($sql)->fetchAll();
print_r($results);
?>
