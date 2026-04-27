<?php
// Hardcoded config untuk lingkungan lokal XAMPP
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'ppdb_mtsn1';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "ALTER TABLE pendaftar ADD COLUMN password VARCHAR(255) AFTER nisn";
    $pdo->exec($sql);
    echo "Sukses: Kolom 'password' berhasil ditambahkan ke tabel 'pendaftar'.";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') {
        echo "Info: Kolom 'password' sudah ada.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
