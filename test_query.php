<?php
require 'includes/config.php';
$stmt = $pdo->query("SELECT id, nama_lengkap, jalur_id, status, status_tahfidz FROM pendaftar WHERE status = 'Diterima'");
$diterima = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT id, nama_jalur FROM jalur_pendaftaran");
$jalurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "JALUR:\n";
print_r($jalurs);
echo "\n\nDITERIMA:\n";
print_r($diterima);

$query_final = "SELECT p.id, p.nama_lengkap, j.nama_jalur 
          FROM pendaftar p 
          LEFT JOIN jalur_pendaftaran j ON p.jalur_id = j.id 
          WHERE 
          (
            (p.jalur_id IN (8, 10) OR (p.jalur_id = 11 AND p.status_tahfidz = 'Lulus'))
            AND p.status IN ('Terverifikasi', 'Diterima')
          )
          OR 
          (
            (p.jalur_id IN (7, 9) OR (p.jalur_id = 11 AND p.status_tahfidz = 'Tidak Lulus'))
            AND p.status = 'Diterima'
          )";
$stmt = $pdo->query($query_final);
$final = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n\nDI FINALISASI:\n";
print_r($final);
?>
