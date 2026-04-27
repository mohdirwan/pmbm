<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require 'includes/config.php';
$stmt = $pdo->query('SHOW TABLES');
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "Daftar Tabel dan Jumlah Data:\n";
echo str_repeat("-", 40) . "\n";
foreach ($tables as $table) {
    $stmtCount = $pdo->query("SELECT COUNT(*) FROM `$table` ");
    $count = $stmtCount->fetchColumn();
    echo sprintf("%-30s : %d baris\n", $table, $count);
}
