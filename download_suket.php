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

$upload_dir = 'uploads/suket_templates/';
$file_path = '';
$file_name = '';
$mime_type = '';

// Check if DOCX template exists
if (!empty($surat['file_template_docx'])) {
    $temp_path = $upload_dir . $surat['file_template_docx'];
    if (file_exists($temp_path)) {
        $file_path = $temp_path;
        $file_name = $surat['file_template_docx'];
        $mime_type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    }
}

// Fallback to PDF preview if DOCX not found but PDF exists
if (empty($file_path) && !empty($surat['file_preview_pdf'])) {
    $temp_path = $upload_dir . $surat['file_preview_pdf'];
    if (file_exists($temp_path)) {
        $file_path = $temp_path;
        $file_name = $surat['file_preview_pdf'];
        $mime_type = 'application/pdf';
    }
}

// If file exists on disk, serve it
if (!empty($file_path) && file_exists($file_path)) {
    // Clean output buffer to avoid corruption
    if (ob_get_level()) ob_end_clean();
    
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));
    
    readfile($file_path);
    exit();
} else {
    // If no physical file, redirect to preview page (HTML version)
    // This allows user to still "see" the template and print to PDF manually
    header("Location: preview_suket.php?id=" . $id);
    exit();
}
