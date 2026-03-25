<?php
require_once 'includes/config.php';

// Logic Status PMBM
$ppdb_status = get_setting('ppdb_status', 'belum');

// Dynamic Countdown Checking - Link to Active Scheme Dates
$active_scheme = get_setting('active_scheme', '1');
$scheme_start = '';
$scheme_end = '';

if ($active_scheme == '1') {
    $scheme_start = get_setting('scheme_1_start', date('Y-m-d')) . ' ' . get_setting('scheme_daily_start', '08:00');
    $scheme_end = get_setting('scheme_1_end', date('Y-m-d', strtotime('+7 days'))) . ' ' . get_setting('scheme_daily_end', '16:00');
} elseif ($active_scheme == '2') {
    $scheme_start = get_setting('scheme_2_start', date('Y-m-d')) . ' 00:00';
    $scheme_end = get_setting('scheme_2_end', date('Y-m-d', strtotime('+7 days'))) . ' 23:59';
} else {
    $scheme_start = get_setting('scheme_period_start', date('Y-m-d')) . ' 00:00';
    $scheme_end = get_setting('scheme_period_end', date('Y-m-d', strtotime('+7 days'))) . ' 23:59';
}

// Determine target date for countdown
$target_date = ($ppdb_status == 'belum') ? $scheme_start : $scheme_end;
$is_expired = false;
$show_countdown = ($ppdb_status == 'belum');
$countdown_label = "Target Pembukaan";

if (!empty($target_date)) {
    $is_expired = time() > strtotime($target_date);

    // Auto-Open Logic: If 'belum' and current time > scheme_start
    if ($ppdb_status == 'belum' && $is_expired) {
        try {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('ppdb_status', 'buka') ON DUPLICATE KEY UPDATE setting_value = 'buka'");
            $stmt->execute();
            $ppdb_status = 'buka';
            $show_countdown = false; // Registration opened, hide countdown
            // Recalculate target to end date
            $target_date = $scheme_end;
            $is_expired = time() > strtotime($target_date);
        } catch (Exception $e) {
            $ppdb_status = 'buka';
            $show_countdown = false;
        }
    }
}

// Default values
$hero_title = get_setting('hero_title', 'Mewujudkan Generasi Islami & Berprestasi');
$hero_desc = get_setting('hero_desc', 'Selamat datang di Portal Penerimaan Peserta Didik Baru MTsN 1 Kota Pekanbaru.');
$show_register = true;
$show_login = true;
$btn_text = "Daftar Sekarang";
$btn_link = BASE_URL . "register.php";

// Override based on status
if ($ppdb_status == 'belum') {
    $hero_title = "Pendaftaran Belum Dibuka";
    $hero_desc = "Persiapkan diri Anda, dan persiapkan semua persyaratannya, pendaftaran akan segera dibuka sesuai jadwal yang ditentukan.";
    $show_register = false;
    $show_login = false;
    $show_countdown = true;
} elseif ($ppdb_status == 'buka') {
    if ($is_expired) {
        $show_countdown = false;
        $hero_title = "Pendaftaran Telah Ditutup";
        $hero_desc = "Batas waktu pendaftaran telah berakhir. Terima kasih atas partisipasi Anda.";
        $show_register = false;
        $show_login = true;
    } else {
        $show_register = true;
        $show_login = true;
        $show_countdown = false;
    }
} elseif ($ppdb_status == 'verifikasi') {
    $hero_title = "Masa Verifikasi Data";
    $hero_desc = "Pendaftaran telah ditutup. Panitia sedang melakukan verifikasi berkas calon pendaftar.";
    $show_register = false;
    $show_login = true;
} elseif ($ppdb_status == 'pengumuman_adm') {
    $hero_title = "Hasil Seleksi Administrasi";
    $hero_desc = "Hasil pengumuman kelulusan administrasi sudah dapat dilihat melalui dashboard masing-masing.";
    $show_register = false;
    $show_login = true;
} elseif ($ppdb_status == 'cbt') {
    $hero_title = "Masa Pelaksanaan Tes";
    $hero_desc = "Pelaksanaan ujian CBT sedang berlangsung sesuai dengan jadwal yang telah ditentukan.";
    $show_register = false;
    $show_login = true;
} elseif ($ppdb_status == 'finalisasi') {
    $hero_title = "Finalisasi Data Akhir";
    $hero_desc = "Tahap pengolahan nilai akhir pendaftaran sebelum pengumuman kelulusan.";
    $show_register = false;
    $show_login = true;
} elseif ($ppdb_status == 'pengumuman') {
    $hero_title = "Pengumuman Kelulusan";
    $hero_desc = "Hasil seleksi akhir PMBM Tahun Ajaran " . get_setting('ppdb_year', '2026/2027') . " sudah tersedia.";
    $show_register = false;
    $show_login = true;
    $btn_text = "Cek Hasil Seleksi";
    $btn_link = BASE_URL . "login_siswa.php";
}

// Theme Switching Logic
$admin_theme = get_setting('admin_theme', 'theme1');

if ($admin_theme === 'theme2') {
    include 'index_theme2.php';
} else {
    include 'index_theme1.php';
}
?>