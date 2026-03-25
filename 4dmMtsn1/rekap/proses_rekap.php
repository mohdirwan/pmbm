<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

// Set time and memory limit as ZIP generation can be intensive
// No limit for execution time and increase memory to 1GB or more
set_time_limit(0);
ini_set('memory_limit', '1024M');

// Prevent any output before the headers
while (ob_get_level()) {
    ob_end_clean();
}

$jalur_id = isset($_GET['jalur_id']) ? intval($_GET['jalur_id']) : 0;

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

// Fetch Students
$stmt = $pdo->prepare("SELECT no_pendaftaran, nama_lengkap, " . implode(', ', array_keys($doc_columns)) . " 
                       FROM pendaftar 
                       WHERE jalur_id = ? AND no_pendaftaran IS NOT NULL AND no_pendaftaran != ''");
$stmt->execute([$jalur_id]);
$students = $stmt->fetchAll();

if (empty($students)) {
    die("Tidak ada data pendaftar dengan nomor pendaftaran di jalur ini.");
}

// Create ZIP
$zip = new ZipArchive();
$temp_zip = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zip_filename;

// Ensure temp_zip is writable
if (file_exists($temp_zip)) {
    @unlink($temp_zip);
}

if ($zip->open($temp_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Gagal membuat file ZIP temporary di: " . $temp_zip);
}

$files_added = 0;
foreach ($students as $s) {
    $folder_name = $s['no_pendaftaran'] . "_" . preg_replace('/[^a-zA-Z0-9]/', '_', $s['nama_lengkap']);

    foreach ($doc_columns as $col => $label) {
        if (!isset($s[$col]) || empty($s[$col]))
            continue;

        $file_name = trim($s[$col]);
        if ($file_name === '')
            continue;

        $file_path = '../../uploads/' . $file_name;
        if (file_exists($file_path)) {
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $zip_entry_name = $folder_name . '/' . $label;
            if ($ext)
                $zip_entry_name .= '.' . $ext;

            if (!$zip->addFile($file_path, $zip_entry_name)) {
                // Log failure but continue
                error_log("Gagal menambahkan file ke ZIP: " . $file_path);
            } else {
                $files_added++;
            }
        }
    }
}

// Close the ZIP - this is where the real processing happens
// For many files, this can take a long time
if ($files_added > 0) {
    if (!$zip->close()) {
        die("Gagal menyimpan file ZIP (ZipArchive::close() failed). Kemungkinan disk penuh atau batasan sistem.");
    }
} else {
    $zip->close();
    if (file_exists($temp_zip))
        @unlink($temp_zip);
    die("Gagal: Tidak ada file fisik yang ditemukan di server untuk pendaftar di jalur ini.");
}

// Download file
if (file_exists($temp_zip)) {
    $size = filesize($temp_zip);
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
    header('Content-Length: ' . $size);
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Clean any buffers again just in case
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Use a loop to read the file in chunks to avoid memory issues
    $handle = fopen($temp_zip, 'rb');
    if ($handle) {
        while (!feof($handle) && !connection_aborted()) {
            echo fread($handle, 1048576); // 1MB chunks
            flush();
        }
        fclose($handle);
    } else {
        readfile($temp_zip);
    }

    // Clean up
    @unlink($temp_zip);
    exit;
} else {
    die("File ZIP tidak ditemukan setelah proses pembuatan selesai.");
}
