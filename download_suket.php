<?php
require_once 'includes/config.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    die("ID surat keterangan tidak valid");
}

$stmt = $pdo->prepare("SELECT * FROM surat_keterangan WHERE id = ? AND is_active = 1");
$stmt->execute([$id]);
$surat = $stmt->fetch();

if (!$surat) {
    die("Surat keterangan tidak ditemukan");
}

// Set headers for download
$filename = 'Template_' . str_replace(' ', '_', $surat['nama_surat']) . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Redirect to preview (for now, we'll use preview as download)
// In production, you would generate actual PDF here
header('Location: preview_suket.php?id=' . $id);
exit();
