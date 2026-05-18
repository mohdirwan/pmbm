<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'u914642035_pmbm2026');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->query("USE `u914642035_pmbm2026` ");
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}

echo "Distinct status in pendaftar table:\n";
$stmt = $pdo->query("SELECT DISTINCT status FROM pendaftar");
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
?>
