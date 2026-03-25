<?php
require_once 'includes/config.php';

try {
    // Cek kolom status_daftar_ulang
    $check = $pdo->query("SHOW COLUMNS FROM pendaftar LIKE 'status_daftar_ulang'");
    if ($check->rowCount() == 0) {
        $pdo->exec("ALTER TABLE pendaftar ADD COLUMN status_daftar_ulang ENUM('Belum', 'Sudah') DEFAULT 'Belum'");
        echo "Kolom status_daftar_ulang berhasil ditambahkan.<br>";
    } else {
        echo "Kolom status_daftar_ulang sudah ada.<br>";
    }

    // Cek kolom tanggal_daftar_ulang
    $check2 = $pdo->query("SHOW COLUMNS FROM pendaftar LIKE 'tanggal_daftar_ulang'");
    if ($check2->rowCount() == 0) {
        $pdo->exec("ALTER TABLE pendaftar ADD COLUMN tanggal_daftar_ulang DATETIME NULL");
        echo "Kolom tanggal_daftar_ulang berhasil ditambahkan.<br>";
    }

    echo "Database structure checked for Daftar Ulang.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>