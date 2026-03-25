<?php
require_once 'includes/config.php';

try {
    $sql = "ALTER TABLE pendaftar 
            ADD COLUMN IF NOT EXISTS anak_ke VARCHAR(10) AFTER agama,
            ADD COLUMN IF NOT EXISTS status_keluarga VARCHAR(50) AFTER anak_ke,
            ADD COLUMN IF NOT EXISTS status_tinggal VARCHAR(50) AFTER alamat,
            ADD COLUMN IF NOT EXISTS jarak_sekolah VARCHAR(50) AFTER status_tinggal,
            ADD COLUMN IF NOT EXISTS transportasi_rumah VARCHAR(50) AFTER jarak_sekolah,
            ADD COLUMN IF NOT EXISTS no_hp VARCHAR(20) AFTER transportasi_rumah,
            ADD COLUMN IF NOT EXISTS hobi VARCHAR(100) AFTER no_hp";

    $pdo->exec($sql);
    echo "Migration successful: Columns added to pendaftar table.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Migration already applied or partial columns exist.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
