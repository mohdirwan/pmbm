<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/config.php';
require_once 'includes/security.php';

// Initialize security (with HTTPS enforcement in production)
// Set to true when deploying to production with SSL
initialize_security(false);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("PROCESS: Request received");
    // CSRF Check
    if (!verify_csrf_token($_POST['csrf_token'])) {
        error_log("PROCESS: CSRF FAIL");
        log_security_event('CSRF_VIOLATION', 'Invalid CSRF token');
        die(json_encode(['error' => 'Invalid security token. Please refresh and try again.']));
    }
    error_log("PROCESS: CSRF OK");

    try {
        error_log("PROCESS: Starting DB transaction");
        $pdo->beginTransaction();

        // 1. Calculate Average Grade (Server side validation)
        $grades = [
            'nilai_k4_s1' => floatval($_POST['nilai_k4_s1']),
            'nilai_k4_s2' => floatval($_POST['nilai_k4_s2']),
            'nilai_k5_s1' => floatval($_POST['nilai_k5_s1']),
            'nilai_k5_s2' => floatval($_POST['nilai_k5_s2']),
            'nilai_k6_s1' => floatval($_POST['nilai_k6_s1'])
        ];

        $jumlah_nilai = array_sum($grades);
        $rata_rata = $jumlah_nilai / count($grades);

        // Validate Minimal Nilai (Check if validation is enabled)
        $status_validasi = get_setting('status_validasi_nilai', 'nonaktif');
        $minimal_nilai = floatval(get_setting('minimal_nilai_rata', '0'));

        if ($status_validasi == 'aktif' && $rata_rata < $minimal_nilai) {
            $pdo->rollBack();

            // Create error page or redirect with error message
            echo '<!DOCTYPE html>
            <html lang="id">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Nilai Tidak Memenuhi Syarat</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; }
                    .error-card { max-width: 600px; margin: 0 auto; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="error-card">
                        <div class="card border-0 shadow-lg rounded-4">
                            <div class="card-body p-5 text-center">
                                <div class="mb-4">
                                    <i class="fas fa-exclamation-triangle text-warning" style="font-size: 80px;"></i>
                                </div>
                                <h2 class="fw-bold mb-3 text-danger">Nilai Tidak Memenuhi Syarat</h2>
                                <p class="lead mb-4">Maaf, nilai rata-rata rapor Anda <strong>tidak memenuhi</strong> persyaratan minimal!</p>
                                <div class="alert alert-danger">
                                    <div class="row">
                                        <div class="col-6 border-end">
                                            <div class="fw-bold text-muted small">Nilai Rata-rata Anda</div>
                                            <div class="display-6 fw-bold text-danger">' . number_format($rata_rata, 2) . '</div>
                                        </div>
                                        <div class="col-6">
                                            <div class="fw-bold text-muted small">Minimal yang Ditetapkan</div>
                                            <div class="display-6 fw-bold text-success">' . number_format($minimal_nilai, 2) . '</div>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-muted mb-4">Silakan periksa kembali nilai rapor Anda. Jika Anda yakin ada kesalahan, hubungi panitia PMBM.</p>
                                <a href="register.php" class="btn btn-primary btn-lg px-5 rounded-pill">
                                    <i class="fas fa-arrow-left me-2"></i> Kembali ke Form Pendaftaran
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            </body>
            </html>';
            exit();
        }


        // 2. Insert Data
        $today = date('Ymd');
        $stmt_check = $pdo->prepare("SELECT no_pendaftaran FROM pendaftar WHERE no_pendaftaran LIKE ? ORDER BY no_pendaftaran DESC LIMIT 1");
        $stmt_check->execute([$today . '%']);
        $last_reg = $stmt_check->fetchColumn();

        if ($last_reg) {
            // Mengambil 5 karakter terakhir (nomor urut) dan menambah 1
            $last_num = (int) substr($last_reg, -5);
            $new_num = $last_num + 1;
        } else {
            $new_num = 1;
        }
        
        $no_pendaftaran = $today . str_pad($new_num, 5, '0', STR_PAD_LEFT);

        // --- PASSWORD HANDLING ---
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
             die(json_encode(['error' => 'Konfirmasi password tidak cocok!']));
        }
        if (strlen($password) < 6) {
             die(json_encode(['error' => 'Password minimal 6 karakter!']));
        }
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        // -------------------------

        $sql = "INSERT INTO pendaftar (
            no_pendaftaran, nisn, password, nik, no_kk, nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin, agama, 
            anak_ke, status_keluarga, hobi, no_hp,
            alamat, desa_kelurahan, kecamatan, kabupaten_kota, provinsi,
            status_tinggal, jarak_sekolah, transportasi_rumah,
            asal_sekolah, npsn_sekolah, alamat_sekolah,
            status_orang_tua,
            nama_ayah, nik_ayah, tempat_lahir_ayah, tanggal_lahir_ayah, pendidikan_ayah, pekerjaan_ayah, penghasilan_ayah, no_hp_ayah, alamat_ayah,
            nama_ibu, nik_ibu, tempat_lahir_ibu, tanggal_lahir_ibu, pendidikan_ibu, pekerjaan_ibu, penghasilan_ibu, no_hp_ibu, alamat_ibu,
            nama_wali, nik_wali, tempat_lahir_wali, tanggal_lahir_wali, pendidikan_wali, pekerjaan_wali, penghasilan_wali, no_hp_wali, alamat_wali,
            kontak_wa, nama_kontak_wa,
            jalur_id,
            nilai_k4_s1, nilai_k4_s2, nilai_k5_s1, nilai_k5_s2, nilai_k6_s1, nilai_jumlah, nilai_rapor_rata2
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            ?,
            ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?,
            ?,
            ?, ?, ?, ?, ?, ?, ?
        )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $no_pendaftaran,
            clean_input($_POST['nisn']),
            $password_hashed,
            clean_input($_POST['nik']),
            clean_input($_POST['no_kk']),
            strtoupper(clean_input($_POST['nama_lengkap'])),
            clean_input($_POST['tempat_lahir']),
            clean_input($_POST['tanggal_lahir']),
            clean_input($_POST['jenis_kelamin']),
            clean_input($_POST['agama']),
            clean_input($_POST['anak_ke']),
            clean_input($_POST['status_keluarga']),
            clean_input($_POST['hobi']),
            clean_input($_POST['no_hp']),
            clean_input($_POST['alamat']),
            clean_input($_POST['desa_kelurahan']),
            clean_input($_POST['kecamatan']),
            clean_input($_POST['kabupaten_kota']),
            clean_input($_POST['provinsi']),
            clean_input($_POST['status_tinggal']),
            clean_input($_POST['jarak_sekolah']),
            clean_input($_POST['transportasi_rumah']),
            clean_input($_POST['asal_sekolah']),
            clean_input($_POST['npsn_sekolah']),
            clean_input($_POST['alamat_sekolah']),
            clean_input($_POST['status_orang_tua']),
            clean_input($_POST['nama_ayah']),
            clean_input($_POST['nik_ayah']),
            clean_input($_POST['tempat_lahir_ayah']),
            clean_input($_POST['tanggal_lahir_ayah']),
            clean_input($_POST['pendidikan_ayah']),
            clean_input($_POST['pekerjaan_ayah']),
            clean_input($_POST['penghasilan_ayah']),
            clean_input($_POST['no_hp_ayah']),
            clean_input($_POST['alamat_ayah']),
            clean_input($_POST['nama_ibu']),
            clean_input($_POST['nik_ibu']),
            clean_input($_POST['tempat_lahir_ibu']),
            clean_input($_POST['tanggal_lahir_ibu']),
            clean_input($_POST['pendidikan_ibu']),
            clean_input($_POST['pekerjaan_ibu']),
            clean_input($_POST['penghasilan_ibu']),
            clean_input($_POST['no_hp_ibu']),
            clean_input($_POST['alamat_ibu']),
            clean_input($_POST['nama_wali']),
            clean_input($_POST['nik_wali']),
            clean_input($_POST['tempat_lahir_wali']),
            clean_input($_POST['tanggal_lahir_wali']),
            clean_input($_POST['pendidikan_wali']),
            clean_input($_POST['pekerjaan_wali']),
            clean_input($_POST['penghasilan_wali']),
            clean_input($_POST['no_hp_wali']),
            clean_input($_POST['alamat_wali']),
            clean_input($_POST['kontak_wa']),
            clean_input($_POST['nama_kontak_wa']),
            clean_input($_POST['jalur_id']),
            $grades['nilai_k4_s1'],
            $grades['nilai_k4_s2'],
            $grades['nilai_k5_s1'],
            $grades['nilai_k5_s2'],
            $grades['nilai_k6_s1'],
            $jumlah_nilai,
            $rata_rata
        ]);

        // Get the inserted ID
        $pendaftar_id = $pdo->lastInsertId();

        // 3. Document Upload Handling (Moved to Post-Login Dashboard)
        $pdo->commit();
        error_log("PROCESS: Database Transaction committed successfully (Initial Registration).");

        // === Send WhatsApp Notification ===
        try {
            require_once 'includes/wa_helper.php';
            $nama_lengkap = strtoupper(clean_input($_POST['nama_lengkap']));
            $nisn = clean_input($_POST['nisn']);
            $no_hp = clean_input($_POST['kontak_wa']);
            $nama_kontak = clean_input($_POST['nama_kontak_wa']);
            $jalur_id = intval($_POST['jalur_id']);

            // Get Jalur Name
            $stmt_j = $pdo->prepare("SELECT nama_jalur FROM jalur_pendaftaran WHERE id = ?");
            $stmt_j->execute([$jalur_id]);
            $nama_jalur = $stmt_j->fetchColumn();

            if (!empty($no_hp)) {
                $login_url = BASE_URL . 'login_siswa.php';

                // Determine which message to send
                $is_tahfidz = stripos($nama_jalur, 'tahfi') !== false;

                if ($is_tahfidz) {
                    $custom_msg = "Selamat, pendaftaran Ananda di MTsN 1 Kota Pekanbaru melalui jalur tahfizh telah berhasil dan tercatat dalam sistem. Silakan mengikuti tes tahfizh pada hari Senin – Selasa, 09 – 10 Maret 2026 pukul 08.00 – 12.00 WIB di MTsN 1 Kota Pekanbaru.";
                } else {
                    $custom_msg = "Selamat, pendaftaran Ananda di MTsN 1 Kota Pekanbaru melalui  " . $nama_jalur . " telah berhasil dan tercatat dalam sistem. Silakan menunggu informasi selanjutnya sesuai jadwal yang ditentukan.";
                }

                $message = "Assalamu'alaikum warahmatullahi wabarakatuh.

Halo {$nama_kontak},

{$custom_msg}

Berikut adalah rincian akun login untuk melengkapi berkas di Dashboard Murid:
Link Login: {$login_url}
Username: {$nisn} (NISN Ananda)
Password: [Password yang dibuat saat mendaftar]

Mohon simpan informasi akun ini dengan baik.

Wassalamu'alaikum warahmatullahi wabarakatuh.";

                send_wa_message($no_hp, $message);
            }
        } catch (Throwable $wa_err) {
            error_log("PROCESS: WhatsApp Error: " . $wa_err->getMessage());
            // Silently fail WA to not break registration
        }

        error_log("PROCESS: Redirecting to success.php with NO Registration: " . $no_pendaftaran);
        // Redirect to success page
        header("Location: success.php?reg=" . $no_pendaftaran);
        exit();

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("PROCESS: FATAL ERROR: " . $e->getMessage());
        die("Terjadi Kesalahan: " . $e->getMessage());
    }
}
?>