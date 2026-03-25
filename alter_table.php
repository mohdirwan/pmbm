<?php
require 'includes/config.php';
try {
    $pdo->exec("ALTER TABLE pendaftar ADD COLUMN ujian_password VARCHAR(50) DEFAULT NULL AFTER nilai_ujian");
    echo "Column 'ujian_password' added successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
