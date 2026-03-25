<?php
/**
 * Script untuk membuat tabel app_popup
 * Jalankan file ini melalui browser (contoh: domainanda.com/update_db_popup.php)
 */

require_once 'includes/config.php';

// Pastikan hanya admin yang bisa menjalankan ini (opsional, tapi aman)
// Jika Anda kesulitan menjalankan karena belum login, Anda bisa komentar sementara baris auth_check ini
// require_once 'includes/auth_check.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS `app_popup` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `image_path` varchar(255) NOT NULL,
      `status` tinyint(1) NOT NULL DEFAULT 1,
      `timer` int(11) NOT NULL DEFAULT 5000 COMMENT 'duration in ms',
      `link` varchar(255) DEFAULT NULL,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);

    echo "<div style='font-family: Arial; padding: 20px; color: green; border: 1px solid green; background: #eaffea; border-radius: 8px;'>";
    echo "<h3>Success!</h3>";
    echo "Tabel <b>app_popup</b> berhasil dibuat atau sudah ada.<br>";
    echo "Sekarang Anda dapat menghapus file ini demi keamanan.";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='font-family: Arial; padding: 20px; color: red; border: 1px solid red; background: #ffeaea; border-radius: 8px;'>";
    echo "<h3>Error!</h3>";
    echo "Gagal membuat tabel: " . $e->getMessage();
    echo "</div>";
}
?>