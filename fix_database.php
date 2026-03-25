<?php
require_once 'includes/config.php';

try {
    // 1. Tambah kolom pendukung CBT & Jalur
    try {
        $pdo->exec("ALTER TABLE pendaftar 
                    ADD COLUMN IF NOT EXISTS catatan_admin TEXT AFTER status,
                    ADD COLUMN IF NOT EXISTS nilai_ujian FLOAT DEFAULT 0 AFTER nilai_rapor_rata2,
                    ADD COLUMN IF NOT EXISTS ujian_password VARCHAR(50) AFTER nisn,
                    ADD COLUMN IF NOT EXISTS jalur_id INT(11) AFTER id");
        echo "<p>✅ Kolom pendukung (CBT & Jalur) berhasil dipastikan ada.</p>";
    } catch (PDOException $e) {
        echo "<p>ℹ️ Info (Kolom): " . $e->getMessage() . "</p>";
    }

    // 2. Buat tabel jalur_pendaftaran
    $sql_jalur = "CREATE TABLE IF NOT EXISTS `jalur_pendaftaran` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `nama_jalur` varchar(50) NOT NULL,
        `kuota` int(11) NOT NULL DEFAULT 0,
        `syarat` text,
        `syarat_pilihan` text NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql_jalur);

    // Pastikan kolom syarat_pilihan ada jika tabel sudah ada sebelumnya
    try {
        $pdo->exec("ALTER TABLE jalur_pendaftaran ADD COLUMN IF NOT EXISTS syarat_pilihan TEXT NULL AFTER syarat");
    } catch (PDOException $e) {
        // Abaikan jika sudah ada atau IF NOT EXISTS tidak didukung
    }

    // Seed default jalur jika kosong
    $check = $pdo->query("SELECT COUNT(*) FROM jalur_pendaftaran")->fetchColumn();
    if ($check == 0) {
        $pdo->exec("INSERT INTO jalur_pendaftaran (nama_jalur, kuota) VALUES 
                   ('Zonasi', 150), ('Prestasi', 50), ('Afirmasi', 30), ('Perpindahan Orang Tua', 10)");
    }
    echo "<p>✅ Tabel 'jalur_pendaftaran' berhasil dipastikan ada.</p>";

    // 3. Buat tabel jadwal_ujian
    $sql_jadwal = "CREATE TABLE IF NOT EXISTS `jadwal_ujian` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `mata_uji` varchar(100) NOT NULL,
        `tanggal` date NOT NULL,
        `waktu` varchar(50) NOT NULL,
        `lokasi` varchar(100) NOT NULL,
        `status` varchar(20) DEFAULT 'Mendatang',
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql_jadwal);
    echo "<p>✅ Tabel 'jadwal_ujian' berhasil dipastikan ada.</p>";

    echo "<h1>Sukses Besar Ges!</h1>";
    echo "<p>Database sudah sinkron. Sekarang silakan coba ekspor lagi di menu Integrasi CBT.</p>";
    echo "<a href='admin/dashboard.php'>Kembali ke Dashboard</a>";
} catch (PDOException $e) {
    echo "<h1>Gagal ges!</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<a href='admin/dashboard.php'>Kembali ke Dashboard</a>";
}
?>