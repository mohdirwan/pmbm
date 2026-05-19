<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$id = $_POST['id'] ?? null;
$finalisasi = $_POST['finalisasi'] ?? null; // Can be 'ya' or 'belum'

if (!$id || !in_array($finalisasi, ['ya', 'belum'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap atau tidak valid.']);
    exit;
}

try {
    // Get student details for logging
    $stmt_student = $pdo->prepare("SELECT nama_lengkap, no_pendaftaran FROM pendaftar WHERE id = ?");
    $stmt_student->execute([$id]);
    $student = $stmt_student->fetch();
    
    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Data pendaftar tidak ditemukan.']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE pendaftar SET finalisasi = ? WHERE id = ?");
    $result = $stmt->execute([$finalisasi, $id]);

    if ($result) {
        $status_label = ($finalisasi == 'ya') ? 'Sudah Finalisasi' : 'Belum Finalisasi';
        
        // Log activity if log_activity function exists
        if (function_exists('log_activity')) {
            log_activity("Update Finalisasi", "Admin mengubah status finalisasi murid {$student['nama_lengkap']} ({$student['no_pendaftaran']}) menjadi {$status_label}");
        }
        
        echo json_encode([
            'success' => true, 
            'message' => "Status finalisasi {$student['nama_lengkap']} berhasil diubah menjadi " . ($finalisasi == 'ya' ? 'Sudah' : 'Belum') . "."
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengubah status finalisasi.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
