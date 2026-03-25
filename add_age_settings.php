<?php
require_once 'includes/config.php';

$settings_to_add = [
    ['age_limit_status', 'aktif'], // aktif / nonaktif
    ['age_cutoff_date', '2026-07-01'], // Tanggal patokan seleksi
    ['max_age_limit', '15'] // Batas umur maksimal tahun
];

foreach ($settings_to_add as $s) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    $stmt->execute($s);
}

echo "Age limit settings added successfully.\n";
?>