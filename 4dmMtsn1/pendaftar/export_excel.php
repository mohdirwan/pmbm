<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

// Export menu pendaftar diminta memuat semua data yang sudah diinput.
// Filter hanya dipakai jika dikirim eksplisit dan bernilai.
$search = trim($_GET['search'] ?? '');
$jalur = trim($_GET['jalur'] ?? '');
$status = trim($_GET['status'] ?? '');
$finalisasi = trim($_GET['finalisasi'] ?? '');

$query = "SELECT p.*, j.nama_jalur 
          FROM pendaftar p 
          LEFT JOIN jalur_pendaftaran j ON p.jalur_id = j.id 
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (p.nama_lengkap LIKE ? OR p.nisn LIKE ? OR p.no_pendaftaran LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($jalur) {
    $query .= " AND p.jalur_id = ?";
    $params[] = $jalur;
}

if ($status) {
    $query .= " AND p.status = ?";
    $params[] = $status;
}

if ($finalisasi) {
    if ($finalisasi == 'ya') {
        $query .= " AND p.finalisasi = 'ya'";
    } else {
        $query .= " AND (p.finalisasi = 'belum' OR p.finalisasi IS NULL)";
    }
}

$query .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

// Jika filter dari URL menghasilkan kosong, tetap export seluruh pendaftar agar file tidak kosong.
if (empty($students) && ($search !== '' || $jalur !== '' || $status !== '' || $finalisasi !== '')) {
    $query = "SELECT p.*, j.nama_jalur 
              FROM pendaftar p 
              LEFT JOIN jalur_pendaftaran j ON p.jalur_id = j.id 
              ORDER BY p.id DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $students = $stmt->fetchAll();
}

function export_label_from_field($field)
{
    $labels = [
        'no' => 'No',
        'no_pendaftaran' => 'No Pendaftaran',
        'nama_jalur' => 'Jalur Pendaftaran',
        'nisn' => 'NISN',
        'nik' => 'NIK',
        'no_kk' => 'No KK',
        'password_plain' => 'Password Login',
        'nama_lengkap' => 'Nama Lengkap',
        'tempat_lahir' => 'Tempat Lahir',
        'tanggal_lahir' => 'Tanggal Lahir',
        'jenis_kelamin' => 'Jenis Kelamin',
        'agama' => 'Agama',
        'anak_ke' => 'Anak Ke',
        'status_keluarga' => 'Status Dalam Keluarga',
        'hobi' => 'Hobi',
        'no_hp' => 'No HP Murid',
        'no_hp_siswa' => 'No HP Siswa',
        'email' => 'Email',
        'kontak_wa' => 'Nomor WA Aktif',
        'nama_kontak_wa' => 'Nama Kontak WA',
        'alamat' => 'Alamat Murid',
        'desa_kelurahan' => 'Desa/Kelurahan Murid',
        'kecamatan' => 'Kecamatan Murid',
        'kabupaten_kota' => 'Kabupaten/Kota Murid',
        'provinsi' => 'Provinsi Murid',
        'kode_pos' => 'Kode Pos',
        'status_tinggal' => 'Status Tempat Tinggal',
        'jarak_sekolah' => 'Jarak ke Sekolah',
        'jarak_rumah' => 'Jarak Rumah',
        'transportasi_rumah' => 'Transportasi dari Rumah',
        'asal_sekolah' => 'Asal Sekolah',
        'npsn_sekolah' => 'NPSN Sekolah',
        'alamat_sekolah' => 'Alamat Sekolah Asal',
        'status_orang_tua' => 'Status Orang Tua',
        'nama_ayah' => 'Nama Ayah',
        'nik_ayah' => 'NIK Ayah',
        'tempat_lahir_ayah' => 'Tempat Lahir Ayah',
        'tanggal_lahir_ayah' => 'Tanggal Lahir Ayah',
        'tahun_lahir_ayah' => 'Tahun Lahir Ayah',
        'pendidikan_ayah' => 'Pendidikan Ayah',
        'pekerjaan_ayah' => 'Pekerjaan Ayah',
        'penghasilan_ayah' => 'Penghasilan Ayah',
        'no_hp_ayah' => 'No HP Ayah',
        'alamat_ayah' => 'Alamat Ayah',
        'desa_kelurahan_ayah' => 'Desa/Kelurahan Ayah',
        'kecamatan_ayah' => 'Kecamatan Ayah',
        'kabupaten_kota_ayah' => 'Kabupaten/Kota Ayah',
        'provinsi_ayah' => 'Provinsi Ayah',
        'nama_ibu' => 'Nama Ibu',
        'nik_ibu' => 'NIK Ibu',
        'tempat_lahir_ibu' => 'Tempat Lahir Ibu',
        'tanggal_lahir_ibu' => 'Tanggal Lahir Ibu',
        'tahun_lahir_ibu' => 'Tahun Lahir Ibu',
        'pendidikan_ibu' => 'Pendidikan Ibu',
        'pekerjaan_ibu' => 'Pekerjaan Ibu',
        'penghasilan_ibu' => 'Penghasilan Ibu',
        'no_hp_ibu' => 'No HP Ibu',
        'alamat_ibu' => 'Alamat Ibu',
        'desa_kelurahan_ibu' => 'Desa/Kelurahan Ibu',
        'kecamatan_ibu' => 'Kecamatan Ibu',
        'kabupaten_kota_ibu' => 'Kabupaten/Kota Ibu',
        'provinsi_ibu' => 'Provinsi Ibu',
        'nama_wali' => 'Nama Wali',
        'nik_wali' => 'NIK Wali',
        'tempat_lahir_wali' => 'Tempat Lahir Wali',
        'tanggal_lahir_wali' => 'Tanggal Lahir Wali',
        'pendidikan_wali' => 'Pendidikan Wali',
        'pekerjaan_wali' => 'Pekerjaan Wali',
        'penghasilan_wali' => 'Penghasilan Wali',
        'no_hp_wali' => 'No HP Wali',
        'alamat_wali' => 'Alamat Wali',
        'desa_kelurahan_wali' => 'Desa/Kelurahan Wali',
        'kecamatan_wali' => 'Kecamatan Wali',
        'kabupaten_kota_wali' => 'Kabupaten/Kota Wali',
        'provinsi_wali' => 'Provinsi Wali',
        'nilai_k4_s1' => 'Nilai Kelas 4 Semester 1',
        'nilai_k4_s2' => 'Nilai Kelas 4 Semester 2',
        'nilai_k5_s1' => 'Nilai Kelas 5 Semester 1',
        'nilai_k5_s2' => 'Nilai Kelas 5 Semester 2',
        'nilai_k6_s1' => 'Nilai Kelas 6 Semester 1',
        'nilai_jumlah' => 'Jumlah Nilai Rapor',
        'nilai_rapor_rata2' => 'Rata-rata Nilai Rapor',
        'nilai_ujian' => 'Nilai Ujian',
        'status_tahfidz' => 'Status Tahfidz',
        'password_cbt' => 'Password CBT',
        'test_hari' => 'Hari/Tanggal Ujian',
        'test_sesi' => 'Sesi Ujian',
        'test_jam_mulai' => 'Jam Mulai Ujian',
        'test_jam_selesai' => 'Jam Selesai Ujian',
        'test_ruangan' => 'Ruangan Ujian',
        'finalisasi' => 'Finalisasi',
        'finalisasi_oleh' => 'Finalisasi Oleh',
        'status' => 'Status Pendaftaran',
        'catatan_admin' => 'Catatan Admin',
        'catatan_verifikasi' => 'Catatan Verifikasi',
        'status_daftar_ulang' => 'Status Daftar Ulang',
        'tanggal_daftar_ulang' => 'Tanggal Daftar Ulang',
        'tanggal_daftar' => 'Tanggal Daftar',
        'foto_siswa' => 'File Pas Foto',
        'file_kk' => 'File KK',
        'file_akta' => 'File Akta',
        'file_nisn' => 'File NISN',
        'file_rapor' => 'File Rapor',
        'file_nilai_rata' => 'File Nilai Rata-rata',
        'file_ranking' => 'File Ranking',
        'file_surat_prestasi' => 'File Surat Prestasi',
        'file_sertifikat_prestasi' => 'File Sertifikat Prestasi',
        'file_surat_tahfidz' => 'File Surat Tahfidz',
        'file_sertifikat_tahfidz' => 'File Sertifikat Tahfidz',
        'file_pakta' => 'File Pakta Integritas',
        'file_persyaratan' => 'File Persyaratan',
    ];

    return $labels[$field] ?? ucwords(str_replace('_', ' ', $field));
}

function export_cell_value($student, $field)
{
    $value = $student[$field] ?? '';

    if ($field === 'jenis_kelamin') {
        return $value === 'L' ? 'Laki-laki' : ($value === 'P' ? 'Perempuan' : $value);
    }

    if ($field === 'finalisasi') {
        return $value === 'ya' ? 'Sudah' : 'Belum';
    }

    if ($field === 'nilai_jumlah' && ($value === '' || $value === null)) {
        return floatval($student['nilai_k4_s1'] ?? 0) + floatval($student['nilai_k4_s2'] ?? 0) + floatval($student['nilai_k5_s1'] ?? 0) + floatval($student['nilai_k5_s2'] ?? 0) + floatval($student['nilai_k6_s1'] ?? 0);
    }

    return $value;
}

function export_format_value($value)
{
    if ($value === null || $value === '') {
        return '-';
    }

    if (is_numeric($value) && floor((float) $value) != (float) $value) {
        return number_format((float) $value, 2, '.', '');
    }

    return decode_multiple_entities((string) $value);
}

function export_should_force_text($field, $value)
{
    if ($value === null || $value === '') {
        return false;
    }

    $textFields = [
        'no_pendaftaran', 'nisn', 'nik', 'no_kk', 'no_hp', 'no_hp_siswa', 'kontak_wa',
        'no_hp_ayah', 'nik_ayah', 'no_hp_ibu', 'nik_ibu', 'no_hp_wali', 'nik_wali',
        'npsn_sekolah', 'kode_pos', 'password_plain', 'password_cbt'
    ];

    return in_array($field, $textFields, true);
}

$preferredColumns = [
    'no_pendaftaran', 'nama_jalur', 'nisn', 'password_plain', 'nik', 'no_kk',
    'nama_lengkap', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama',
    'anak_ke', 'status_keluarga', 'hobi', 'no_hp', 'email', 'kontak_wa', 'nama_kontak_wa',
    'alamat', 'desa_kelurahan', 'kecamatan', 'kabupaten_kota', 'provinsi', 'kode_pos',
    'status_tinggal', 'jarak_sekolah', 'transportasi_rumah',
    'asal_sekolah', 'npsn_sekolah', 'alamat_sekolah',
    'status_orang_tua',
    'nama_ayah', 'nik_ayah', 'tempat_lahir_ayah', 'tanggal_lahir_ayah', 'tahun_lahir_ayah',
    'pendidikan_ayah', 'pekerjaan_ayah', 'penghasilan_ayah', 'no_hp_ayah',
    'alamat_ayah', 'desa_kelurahan_ayah', 'kecamatan_ayah', 'kabupaten_kota_ayah', 'provinsi_ayah',
    'nama_ibu', 'nik_ibu', 'tempat_lahir_ibu', 'tanggal_lahir_ibu', 'tahun_lahir_ibu',
    'pendidikan_ibu', 'pekerjaan_ibu', 'penghasilan_ibu', 'no_hp_ibu',
    'alamat_ibu', 'desa_kelurahan_ibu', 'kecamatan_ibu', 'kabupaten_kota_ibu', 'provinsi_ibu',
    'nama_wali', 'nik_wali', 'tempat_lahir_wali', 'tanggal_lahir_wali',
    'pendidikan_wali', 'pekerjaan_wali', 'penghasilan_wali', 'no_hp_wali',
    'alamat_wali', 'desa_kelurahan_wali', 'kecamatan_wali', 'kabupaten_kota_wali', 'provinsi_wali',
    'nilai_k4_s1', 'nilai_k4_s2', 'nilai_k5_s1', 'nilai_k5_s2', 'nilai_k6_s1',
    'nilai_jumlah', 'nilai_rapor_rata2', 'nilai_ujian',
    'status_tahfidz', 'password_cbt', 'test_hari', 'test_sesi', 'test_jam_mulai', 'test_jam_selesai', 'test_ruangan',
    'finalisasi', 'finalisasi_oleh', 'status', 'catatan_admin', 'catatan_verifikasi',
    'status_daftar_ulang', 'tanggal_daftar_ulang', 'tanggal_daftar',
    'foto_siswa', 'file_kk', 'file_akta', 'file_nisn', 'file_rapor', 'file_nilai_rata',
    'file_ranking', 'file_surat_prestasi', 'file_sertifikat_prestasi',
    'file_surat_tahfidz', 'file_sertifikat_tahfidz', 'file_pakta', 'file_persyaratan',
];

$excludedColumns = ['id', 'password', 'jalur_id'];
$dbColumns = [];

try {
    $columnsStmt = $pdo->query("SHOW COLUMNS FROM pendaftar");
    foreach ($columnsStmt->fetchAll() as $column) {
        $dbColumns[] = $column['Field'];
    }
} catch (Exception $e) {
    $dbColumns = !empty($students) ? array_keys($students[0]) : $preferredColumns;
}

$availableColumns = array_values(array_diff($dbColumns, $excludedColumns));
$columns = [];

foreach ($preferredColumns as $field) {
    if ($field === 'nama_jalur' || in_array($field, $availableColumns, true)) {
        $columns[] = $field;
    }
}

foreach ($availableColumns as $field) {
    if (!in_array($field, $columns, true)) {
        $columns[] = $field;
    }
}

if (!in_array('nama_jalur', $columns, true)) {
    array_unshift($columns, 'nama_jalur');
}

// Filename
$filename = "Data_Pendaftar_PMBM_Lengkap_" . date('Ymd_His') . ".csv";

while (ob_get_level() > 0) {
    @ob_end_clean();
}

// Headers for Excel CSV
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");
header("Cache-Control: max-age=0");

echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');
$headerRow = ['No'];
foreach ($columns as $field) {
    $headerRow[] = export_label_from_field($field);
}
fputcsv($output, $headerRow, ";");

if (empty($students)) {
    $emptyRow = ['Tidak ada data sesuai filter'];
    for ($i = 0; $i < count($columns); $i++) {
        $emptyRow[] = '';
    }
    fputcsv($output, $emptyRow, ";");
} else {
    foreach ($students as $index => $s) {
        $row = [$index + 1];
        foreach ($columns as $field) {
            $rawValue = export_cell_value($s, $field);
            $value = export_format_value($rawValue);
            $row[] = export_should_force_text($field, $rawValue) ? "'" . $value : $value;
        }
        fputcsv($output, $row, ";");
    }
}

fclose($output);
exit;
