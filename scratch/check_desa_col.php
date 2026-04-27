<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'ppdb_mtsn1';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $result = $pdo->query("SHOW COLUMNS FROM pendaftar LIKE 'desa_kelurahan'");
    if ($result->rowCount() > 0) {
        echo "Kolom 'desa_kelurahan' TERSEDIA.";
    } else {
        echo "Kolom 'desa_kelurahan' BELUM ADA. Sedang menambahkan...";
        $pdo->exec("ALTER TABLE pendaftar ADD COLUMN desa_kelurahan VARCHAR(50) AFTER alamat");
        echo " Sukses ditambahkan.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
