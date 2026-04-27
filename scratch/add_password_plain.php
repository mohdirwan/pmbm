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
     $pdo->exec("ALTER TABLE pendaftar ADD COLUMN password_plain VARCHAR(255) DEFAULT NULL AFTER password");
     echo "Kolom password_plain berhasil ditambahkan ke database ppdb_mtsn1!";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') {
        echo "Kolom sudah ada.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
