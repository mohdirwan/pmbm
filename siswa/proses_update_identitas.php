<?php
require_once '../includes/config.php';
require_once '../includes/security.php';

// Auth Check for Student
if (!isset($_SESSION['siswa_id']) || $_SESSION['role'] !== 'siswa') {
    die("Unauthorized access.");
}

$siswa_id = $_SESSION['siswa_id'];

// Initial Security Check (re-confirming status and PPDB period)
$stmt_check = $pdo->prepare("SELECT status FROM pendaftar WHERE id = ?");
$stmt_check->execute([$siswa_id]);
$current_status = $stmt_check->fetchColumn();

$ppdb_status = get_setting('ppdb_status', 'tutup');
if ($current_status == 'Terverifikasi' || $current_status == 'Diterima' || $ppdb_status != 'buka') {
    header("Location: identitas.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF Check
    if (!verify_csrf_token($_POST['csrf_token'])) {
        die("Security token validation failed.");
    }

    try {
        $pdo->beginTransaction();

        // Fetch current data for fallback
        $stmt_current = $pdo->prepare("SELECT * FROM pendaftar WHERE id = ?");
        $stmt_current->execute([$siswa_id]);
        $old_data = $stmt_current->fetch();

        if (!$old_data) {
            die("Data pendaftar tidak ditemukan.");
        }

        // Helper function for fallback
        $get_val = function ($key, $default) {
            return (isset($_POST[$key]) && trim($_POST[$key]) !== '') ? $_POST[$key] : $default;
        };

        // 1. Recalculate Grades (with fallback to old data)
        $grades = [
            'nilai_k4_s1' => floatval($get_val('nilai_k4_s1', $old_data['nilai_k4_s1'])),
            'nilai_k4_s2' => floatval($get_val('nilai_k4_s2', $old_data['nilai_k4_s2'])),
            'nilai_k5_s1' => floatval($get_val('nilai_k5_s1', $old_data['nilai_k5_s1'])),
            'nilai_k5_s2' => floatval($get_val('nilai_k5_s2', $old_data['nilai_k5_s2'])),
            'nilai_k6_s1' => floatval($get_val('nilai_k6_s1', $old_data['nilai_k6_s1']))
        ];

        $jumlah_nilai = array_sum($grades);
        $rata_rata = $jumlah_nilai / count($grades);

        // 2. Update Basic Info
        $sql = "UPDATE pendaftar SET 
            jalur_id = ?,
            nisn = ?,
            nik = ?,
            nama_lengkap = ?,
            tempat_lahir = ?,
            tanggal_lahir = ?,
            jenis_kelamin = ?,
            agama = ?,
            no_hp = ?,
            alamat = ?,
            kecamatan = ?,
            kabupaten_kota = ?,
            provinsi = ?,
            anak_ke = ?,
            status_keluarga = ?,
            hobi = ?,
            status_tinggal = ?,
            jarak_sekolah = ?,
            transportasi_rumah = ?,
            asal_sekolah = ?,
            npsn_sekolah = ?,
            alamat_sekolah = ?,
            no_kk = ?,
            status_orang_tua = ?,
            nama_ayah = ?,
            nik_ayah = ?,
            tempat_lahir_ayah = ?,
            tanggal_lahir_ayah = ?,
            pendidikan_ayah = ?,
            pekerjaan_ayah = ?,
            penghasilan_ayah = ?,
            no_hp_ayah = ?,
            alamat_ayah = ?,
            nama_ibu = ?,
            nik_ibu = ?,
            tempat_lahir_ibu = ?,
            tanggal_lahir_ibu = ?,
            pendidikan_ibu = ?,
            pekerjaan_ibu = ?,
            penghasilan_ibu = ?,
            no_hp_ibu = ?,
            alamat_ibu = ?,
            nama_wali = ?,
            nik_wali = ?,
            tempat_lahir_wali = ?,
            tanggal_lahir_wali = ?,
            pendidikan_wali = ?,
            pekerjaan_wali = ?,
            penghasilan_wali = ?,
            no_hp_wali = ?,
            alamat_wali = ?,
            kontak_wa = ?,
            nama_kontak_wa = ?,
            nilai_k4_s1 = ?,
            nilai_k4_s2 = ?,
            nilai_k5_s1 = ?,
            nilai_k5_s2 = ?,
            nilai_k6_s1 = ?,
            nilai_jumlah = ?,
            nilai_rapor_rata2 = ?
            WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            intval($get_val('jalur_id', $old_data['jalur_id'])),
            clean_input($get_val('nisn', $old_data['nisn'])),
            clean_input($get_val('nik', $old_data['nik'])),
            strtoupper(clean_input($get_val('nama_lengkap', $old_data['nama_lengkap']))),
            clean_input($get_val('tempat_lahir', $old_data['tempat_lahir'])),
            clean_input($get_val('tanggal_lahir', $old_data['tanggal_lahir'])),
            clean_input($get_val('jenis_kelamin', $old_data['jenis_kelamin'])),
            clean_input($get_val('agama', $old_data['agama'])),
            clean_input($get_val('no_hp', $old_data['no_hp'])),
            clean_input($get_val('alamat', $old_data['alamat'])),
            clean_input($get_val('kecamatan', $old_data['kecamatan'])),
            clean_input($get_val('kabupaten_kota', $old_data['kabupaten_kota'])),
            clean_input($get_val('provinsi', $old_data['provinsi'])),
            intval($get_val('anak_ke', $old_data['anak_ke'])),
            clean_input($get_val('status_keluarga', $old_data['status_keluarga'])),
            clean_input($get_val('hobi', $old_data['hobi'])),
            clean_input($get_val('status_tinggal', $old_data['status_tinggal'])),
            clean_input($get_val('jarak_sekolah', $old_data['jarak_sekolah'])),
            clean_input($get_val('transportasi_rumah', $old_data['transportasi_rumah'])),
            clean_input($get_val('asal_sekolah', $old_data['asal_sekolah'])),
            clean_input($get_val('npsn_sekolah', $old_data['npsn_sekolah'])),
            clean_input($get_val('alamat_sekolah', $old_data['alamat_sekolah'])),
            clean_input($get_val('no_kk', $old_data['no_kk'])),
            clean_input($get_val('status_orang_tua', $old_data['status_orang_tua'])),
            clean_input($get_val('nama_ayah', $old_data['nama_ayah'])),
            clean_input($get_val('nik_ayah', $old_data['nik_ayah'])),
            clean_input($get_val('tempat_lahir_ayah', $old_data['tempat_lahir_ayah'])),
            clean_input($get_val('tanggal_lahir_ayah', $old_data['tanggal_lahir_ayah'])),
            clean_input($get_val('pendidikan_ayah', $old_data['pendidikan_ayah'])),
            clean_input($get_val('pekerjaan_ayah', $old_data['pekerjaan_ayah'])),
            clean_input($get_val('penghasilan_ayah', $old_data['penghasilan_ayah'])),
            clean_input($get_val('no_hp_ayah', $old_data['no_hp_ayah'])),
            clean_input($get_val('alamat_ayah', $old_data['alamat_ayah'])),
            clean_input($get_val('nama_ibu', $old_data['nama_ibu'])),
            clean_input($get_val('nik_ibu', $old_data['nik_ibu'])),
            clean_input($get_val('tempat_lahir_ibu', $old_data['tempat_lahir_ibu'])),
            clean_input($get_val('tanggal_lahir_ibu', $old_data['tanggal_lahir_ibu'])),
            clean_input($get_val('pendidikan_ibu', $old_data['pendidikan_ibu'])),
            clean_input($get_val('pekerjaan_ibu', $old_data['pekerjaan_ibu'])),
            clean_input($get_val('penghasilan_ibu', $old_data['penghasilan_ibu'])),
            clean_input($get_val('no_hp_ibu', $old_data['no_hp_ibu'])),
            clean_input($get_val('alamat_ibu', $old_data['alamat_ibu'])),
            clean_input($get_val('nama_wali', $old_data['nama_wali'])),
            clean_input($get_val('nik_wali', $old_data['nik_wali'])),
            clean_input($get_val('tempat_lahir_wali', $old_data['tempat_lahir_wali'])),
            clean_input($get_val('tanggal_lahir_wali', $old_data['tanggal_lahir_wali'])),
            clean_input($get_val('pendidikan_wali', $old_data['pendidikan_wali'])),
            clean_input($get_val('pekerjaan_wali', $old_data['pekerjaan_wali'])),
            clean_input($get_val('penghasilan_wali', $old_data['penghasilan_wali'])),
            clean_input($get_val('no_hp_wali', $old_data['no_hp_wali'])),
            clean_input($get_val('alamat_wali', $old_data['alamat_wali'])),
            clean_input($get_val('kontak_wa', $old_data['kontak_wa'])),
            clean_input($get_val('nama_kontak_wa', $old_data['nama_kontak_wa'])),
            $grades['nilai_k4_s1'],
            $grades['nilai_k4_s2'],
            $grades['nilai_k5_s1'],
            $grades['nilai_k5_s2'],
            $grades['nilai_k6_s1'],
            $jumlah_nilai,
            $rata_rata,
            $siswa_id
        ]);

        $pdo->commit();
        header("Location: identitas.php?msg=success");
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Failed to update data: " . $e->getMessage());
    }
}
?>