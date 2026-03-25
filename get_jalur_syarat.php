<?php
header('Content-Type: application/json');
require_once 'includes/config.php';

// Get jalur_id from request
$jalur_id = intval($_GET['jalur_id'] ?? 0);

if ($jalur_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid jalur_id']);
    exit;
}

try {
    // Get jalur data with syarat
    $stmt = $pdo->prepare("SELECT id, nama_jalur, syarat FROM jalur_pendaftaran WHERE id = ?");
    $stmt->execute([$jalur_id]);
    $jalur = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$jalur) {
        echo json_encode(['success' => false, 'error' => 'Jalur not found']);
        exit;
    }

    // Field mapping - same as upload_berkas.php
    $fieldMapping = [
        // Pakta Integritas (Move to top to avoid 'akta' substring match)
        'pakta integritas' => ['field' => 'file_pakta', 'label' => 'Pakta Integritas'],

        // Photos & Basic Docs  
        'pas foto' => ['field' => 'foto_siswa', 'label' => 'Pas Foto'],
        'rapor' => ['field' => 'file_rapor', 'label' => 'Rapor Asli'],

        // Surat Keterangan - Specific first
        'surat keterangan tahfidz' => ['field' => 'file_surat_tahfidz', 'label' => 'Surat Keterangan Tahfidz'],
        'surat keterangan prestasi' => ['field' => 'file_surat_prestasi', 'label' => 'Surat Keterangan Prestasi'],
        'nilai rata-rata' => ['field' => 'file_nilai_rata', 'label' => 'Surat Keterangan Nilai Rata-rata'],
        'rata rata nilai' => ['field' => 'file_nilai_rata', 'label' => 'Surat Keterangan Rata-rata Nilai'],
        'pas rata nila' => ['field' => 'file_nilai_rata', 'label' => 'Surat Keterangan Nilai Rata-rata'],
        'ranking' => ['field' => 'file_ranking', 'label' => 'Surat Keterangan Ranking'],
        'peringkat' => ['field' => 'file_ranking', 'label' => 'Surat Keterangan Peringkat'],

        // Sertifikat
        'sertifikat prestasi akademik' => ['field' => 'file_sertifikat_prestasi', 'label' => 'Sertifikat Prestasi Akademik'],
        'sertifikat prestasi non-akademik' => ['field' => 'file_sertifikat_prestasi', 'label' => 'Sertifikat Prestasi Non-Akademik'],
        'sertifikat prestasi' => ['field' => 'file_sertifikat_prestasi', 'label' => 'Sertifikat Prestasi'],
        'sertifikat tahfidz' => ['field' => 'file_sertifikat_tahfidz', 'label' => 'Sertifikat Tahfidz'],
        'prestasi akademik' => ['field' => 'file_sertifikat_prestasi', 'label' => 'Sertifikat Prestasi Akademik'],
        'prestasi non-akademik' => ['field' => 'file_sertifikat_prestasi', 'label' => 'Sertifikat Prestasi Non-Akademik'],

        // Tahfidz
        'tahfidz' => ['field' => 'file_sertifikat_tahfidz', 'label' => 'Sertifikat Tahfidz'],

        // ID Documents
        'kartu keluarga' => ['field' => 'file_kk', 'label' => 'Kartu Keluarga'],
        'kk' => ['field' => 'file_kk', 'label' => 'Kartu Keluarga (KK)'],
        'akta' => ['field' => 'file_akta', 'label' => 'Akta Kelahiran'],
        'surat kenal lahir' => ['field' => 'file_akta', 'label' => 'Akta Kelahiran / Surat Kenal Lahir'],

        // NISN
        'nisn' => ['field' => 'file_nisn', 'label' => 'Print Out NISN'],
        'print out nisn' => ['field' => 'file_nisn', 'label' => 'Print Out NISN']
    ];

    // Parse syarat
    $docs = [];
    $addedFields = [];
    $syaratText = $jalur['syarat'] ?? '';

    if (!empty($syaratText)) {
        $syaratList = array_map('trim', explode(',', $syaratText));

        foreach ($syaratList as $syarat) {
            // Determine status
            $status = 'tambahan'; // Default for no suffix
            if (stripos($syarat, '(wajib)') !== false) {
                $status = 'wajib';
            } elseif (stripos($syarat, '(pilihan)') !== false) {
                $status = 'pilihan';
            }

            // Clean
            $syaratClean = preg_replace('/\s*\(.*?\)\s*/', '', $syarat);
            $syaratClean = preg_replace('/\s+\d+\s*$/', '', $syaratClean);
            $syaratClean = trim(preg_replace('/\s+/', ' ', $syaratClean));
            $syaratClean = str_ireplace('/n', '<br>', $syaratClean);
            $syaratLower = strtolower(strip_tags($syaratClean));

            // Match
            foreach ($fieldMapping as $keyword => $mapping) {
                if (stripos($syaratLower, $keyword) !== false) {
                    if (!in_array($mapping['field'], $addedFields)) {
                        $docs[] = [
                            'label' => $syaratClean,
                            'field' => $mapping['field'],
                            'status' => $status
                        ];
                        $addedFields[] = $mapping['field'];
                        break;
                    }
                }
            }
        }
    }

    // Ensure Pas Foto is always present as a mandatory field
    if (!in_array('foto_siswa', $addedFields)) {
        array_unshift($docs, [
            'label' => 'Pas Foto 3x4 (Latar Merah)',
            'field' => 'foto_siswa',
            'status' => 'wajib'
        ]);
        $addedFields[] = 'foto_siswa';
    }

    // Fallback
    if (empty($docs)) {
        $docs = [
            ['label' => 'Pas Foto', 'field' => 'foto_siswa', 'status' => 'wajib'],
            ['label' => 'Rapor Asli', 'field' => 'file_rapor', 'status' => 'wajib'],
            ['label' => 'Kartu Keluarga', 'field' => 'file_kk', 'status' => 'wajib'],
            ['label' => 'Akta Kelahiran', 'field' => 'file_akta', 'status' => 'wajib']
        ];
    }

    echo json_encode([
        'success' => true,
        'jalur' => $jalur,
        'documents' => $docs
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
