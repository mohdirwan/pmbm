<?php
// Mencegah output Notice/Warning merusak response JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set custom error handler to always return JSON on failure
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Fatal Error: ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line']]);
        exit;
    }
});

ob_start();

require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

// Set limits for each batch execution
set_time_limit(300);
ini_set('memory_limit', '1024M');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$jalur_id = isset($_REQUEST['jalur_id']) ? intval($_REQUEST['jalur_id']) : 0;

if ($action !== 'download_file') {
    if ($jalur_id <= 0) {
        die("Jalur ID tidak valid.");
    }

    // Fetch Jalur Info
    $stmt_j = $pdo->prepare("SELECT nama_jalur FROM jalur_pendaftaran WHERE id = ?");
    $stmt_j->execute([$jalur_id]);
    $jalur = $stmt_j->fetch();

    if (!$jalur) {
        die("Jalur tidak ditemukan.");
    }

    $nama_jalur_clean = preg_replace('/[^a-zA-Z0-9]/', '_', $jalur['nama_jalur']);
    $zip_filename = "Rekap_Berkas_" . $nama_jalur_clean . "_" . date('Ymd_His') . ".zip";
}

// Document columns to check
$doc_columns = [
    'foto_siswa' => 'Foto_Siswa',
    'file_kk' => 'Kartu_Keluarga',
    'file_akta' => 'Akta_Lahir',
    'file_nisn' => 'NISN',
    'file_rapor' => 'Rapor',
    'file_nilai_rata' => 'SK_Rata_Rata',
    'file_ranking' => 'SK_Ranking',
    'file_surat_prestasi' => 'Surat_Prestasi',
    'file_sertifikat_prestasi' => 'Sertifikat_Prestasi',
    'file_surat_tahfidz' => 'Surat_Tahfidz',
    'file_sertifikat_tahfidz' => 'Sertifikat_Tahfidz',
    'file_pakta' => 'Pakta_Integritas'
];

if ($action === 'init') {
    // Get total students for this jalur
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pendaftar WHERE jalur_id = ? AND no_pendaftaran IS NOT NULL AND no_pendaftaran != ''");
    $stmt->execute([$jalur_id]);
    $total_students = $stmt->fetchColumn();

    $session_id = uniqid('zip_');
    $temp_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $session_id;
    if (!is_dir($temp_dir)) {
        mkdir($temp_dir, 0777, true);
    }
    
    // Jangan membuat file ZIP di sini, cukup buat foldernya.
    
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'total' => $total_students,
        'session_id' => $session_id,
        'zip_filename' => $zip_filename
    ]);
    exit;
}

if ($action === 'process_batch') {
    $session_id = $_POST['session_id'] ?? '';
    $offset = intval($_POST['offset'] ?? 0);
    $limit = intval($_POST['limit'] ?? 50);
    
    if (empty($session_id)) {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'No session ID']);
        exit;
    }
    
    $temp_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $session_id;
    if (!is_dir($temp_dir)) {
        mkdir($temp_dir, 0777, true);
    }
    
    $stmt = $pdo->prepare("SELECT no_pendaftaran, nama_lengkap, " . implode(', ', array_keys($doc_columns)) . " 
                           FROM pendaftar 
                           WHERE jalur_id = ? AND no_pendaftaran IS NOT NULL AND no_pendaftaran != ''
                           ORDER BY id ASC
                           LIMIT $limit OFFSET $offset");
    $stmt->execute([$jalur_id]);
    $students = $stmt->fetchAll();
    
    $files_added = 0;
    
    if (count($students) > 0) {
        foreach ($students as $s) {
            $folder_name = $s['no_pendaftaran'] . "_" . preg_replace('/[^a-zA-Z0-9]/', '_', $s['nama_lengkap']);
            $student_dir = $temp_dir . DIRECTORY_SEPARATOR . $folder_name;
            
            if (!is_dir($student_dir)) {
                mkdir($student_dir, 0777, true);
            }
            
            foreach ($doc_columns as $col => $label) {
                if (!isset($s[$col]) || empty($s[$col])) continue;
                
                $file_name = trim($s[$col]);
                if ($file_name === '') continue;
                
                $file_path = realpath('../../uploads/' . $file_name);
                if ($file_path && file_exists($file_path)) {
                    $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                    $dest_name = $label;
                    if ($ext) $dest_name .= '.' . $ext;
                    
                    $dest_path = $student_dir . DIRECTORY_SEPARATOR . $dest_name;
                    
                    // Gunakan hardlink agar cepat dan tidak memakan memori/disk tambahan. Jika gagal, fallback ke copy
                    if (!file_exists($dest_path)) {
                        $linked = false;
                        if (function_exists('link')) {
                            try {
                                $linked = @link($file_path, $dest_path);
                            } catch (Exception $e) {} catch (Error $e) {}
                        }
                        
                        if (!$linked) {
                            @copy($file_path, $dest_path);
                        }
                    }
                    $files_added++;
                }
            }
        }
    }
    
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'processed' => count($students),
        'files_added' => $files_added
    ]);
    exit;
}

if ($action === 'compress') {
    $session_id = $_POST['session_id'] ?? '';
    if (empty($session_id)) {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'No session ID']);
        exit;
    }

    // Set unlimited time untuk zip ukuran besar (misal 10GB+)
    set_time_limit(0);
    ini_set('memory_limit', '-1');

    $temp_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $session_id;
    $temp_zip = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $session_id . '.zip';

    if (!is_dir($temp_dir)) {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Folder data tidak ditemukan']);
        exit;
    }

    try {
        $zip = new ZipArchive();
        if ($zip->open($temp_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // Tambahkan file txt penanda
            $zip->addFromString("info.txt", "Rekap Berkas Jalur " . $jalur['nama_jalur'] . "\nDibuat pada: " . date('Y-m-d H:i:s'));
            
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($temp_dir), RecursiveIteratorIterator::LEAVES_ONLY);
            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($temp_dir) + 1);
                    if ($filePath && file_exists($filePath)) {
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            }
            $zip->close();
        } else {
            while (ob_get_level()) { ob_end_clean(); }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Gagal membuat file ZIP']);
            exit;
        }
    } catch (Exception $e) {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Error sistem: ' . $e->getMessage()]);
        exit;
    }

    // Hapus folder temporary setelah dikompresi untuk menghemat disk
    try {
        $dir_iterator = new RecursiveDirectoryIterator($temp_dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                @rmdir($file->getRealPath());
            } else {
                @unlink($file->getRealPath());
            }
        }
        @rmdir($temp_dir);
    } catch (Exception $e) {}

    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'download_file') {
    $session_id = $_GET['session_id'] ?? '';
    $dl_filename = $_GET['filename'] ?? 'Rekap.zip';
    
    if (empty($session_id)) {
        die("Invalid session");
    }
    
    $temp_zip = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $session_id . '.zip';
    
    if (file_exists($temp_zip)) {
        $size = filesize($temp_zip);
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($dl_filename) . '"');
        header('Content-Length: ' . $size);
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        while (ob_get_level()) { ob_end_clean(); }

        $handle = fopen($temp_zip, 'rb');
        if ($handle) {
            while (!feof($handle) && !connection_aborted()) {
                echo fread($handle, 1048576);
                flush();
            }
            fclose($handle);
        } else {
            readfile($temp_zip);
        }
        @unlink($temp_zip);
        exit;
    } else {
        die("File ZIP tidak ditemukan setelah proses batching selesai.");
    }
}

// Jika diakses tanpa parameter action yang benar
die("Metode tidak diizinkan. Harap gunakan tombol rekap dari halaman depan.");
