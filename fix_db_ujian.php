<?php
require_once 'includes/config.php';

try {
    // Cek kolom nilai_ujian
    $check = $pdo->query("SHOW COLUMNS FROM pendaftar LIKE 'nilai_ujian'");
    if ($check->rowCount() == 0) {
        $pdo->exec("ALTER TABLE pendaftar ADD COLUMN nilai_ujian DECIMAL(5,2) DEFAULT 0");
        echo "Kolom nilai_ujian berhasil ditambahkan.<br>";
    } else {
        echo "Kolom nilai_ujian sudah ada.<br>";
    }

    echo "Database fixed. Silakan refresh halaman nilai.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>