<?php
require_once 'includes/config.php';

try {
    $sql = "ALTER TABLE pendaftar 
            ADD COLUMN IF NOT EXISTS no_kk VARCHAR(20) AFTER nik,
            ADD COLUMN IF NOT EXISTS tempat_lahir_ayah VARCHAR(50) AFTER nik_ayah,
            ADD COLUMN IF NOT EXISTS tanggal_lahir_ayah DATE AFTER tempat_lahir_ayah,
            ADD COLUMN IF NOT EXISTS alamat_ayah TEXT AFTER no_hp_ayah,
            ADD COLUMN IF NOT EXISTS tempat_lahir_ibu VARCHAR(50) AFTER nik_ibu,
            ADD COLUMN IF NOT EXISTS tanggal_lahir_ibu DATE AFTER tempat_lahir_ibu,
            ADD COLUMN IF NOT EXISTS alamat_ibu TEXT AFTER no_hp_ibu,
            ADD COLUMN IF NOT EXISTS status_orang_tua VARCHAR(50) AFTER no_hp_ibu,
            ADD COLUMN IF NOT EXISTS tempat_lahir_wali VARCHAR(50) AFTER nik_wali,
            ADD COLUMN IF NOT EXISTS tanggal_lahir_wali DATE AFTER tempat_lahir_wali,
            ADD COLUMN IF NOT EXISTS pendidikan_wali VARCHAR(50) AFTER tanggal_lahir_wali,
            ADD COLUMN IF NOT EXISTS penghasilan_wali VARCHAR(50) AFTER pekerjaan_wali,
            ADD COLUMN IF NOT EXISTS alamat_wali TEXT AFTER no_hp_wali";

    $pdo->exec($sql);
    echo "Migration successful: Parental columns added to pendaftar table.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
