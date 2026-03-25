<?php
require_once 'includes/config.php';
try {
    $pdo->exec("ALTER TABLE pendaftar ADD COLUMN alamat_sekolah TEXT AFTER npsn_sekolah");
    echo "Migration successful: alamat_sekolah added.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
