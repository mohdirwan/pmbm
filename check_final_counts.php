<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require_once 'includes/config.php';

$query = "SELECT COUNT(*) FROM pendaftar p 
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

$count = $pdo->query($query)->fetchColumn();
echo "Total Lulus Final: " . $count . "\n";

$diterima = $pdo->query("SELECT COUNT(*) FROM pendaftar WHERE status='Diterima'")->fetchColumn();
echo "Status Diterima: " . $diterima . "\n";

$terverifikasi_cat1 = $pdo->query("SELECT COUNT(*) FROM pendaftar p 
                                  WHERE (p.jalur_id IN (8, 10) OR (p.jalur_id = 11 AND p.status_tahfidz = 'Lulus')) 
                                  AND p.status = 'Terverifikasi'")->fetchColumn();
echo "Terverifikasi Kategori 1 (Lulus Langsung): " . $terverifikasi_cat1 . "\n";
