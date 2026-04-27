<?php
// Pengaturan Lokal (XAMPP) manual untuk terminal
$host = 'localhost';
$db   = 'ppdb_mtsn1';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     $pdo->exec("ALTER TABLE pendaftar ADD COLUMN test_jam_mulai VARCHAR(10) DEFAULT NULL AFTER test_sesi, ADD COLUMN test_jam_selesai VARCHAR(10) DEFAULT NULL AFTER test_jam_mulai");
     echo "Kolom jam ujian berhasil ditambahkan!";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') {
        echo "Kolom sudah ada.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
