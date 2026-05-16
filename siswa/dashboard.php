<?php
$page_title = "Ringkasan Pendaftaran";
require_once 'layout_top.php';

$ppdb_status = get_setting('ppdb_status', 'belum');

// Dynamic Step Highlighting logic
$active_steps = [];
switch ($ppdb_status) {
    case 'buka':
        $active_steps = [1, 2, 3];
        break;
    case 'verifikasi':
        $active_steps = [4, 5];
        break;
    case 'pengumuman_adm':
        $active_steps = [6];
        break;
    case 'cbt':
        $active_steps = [7];
        break;
    case 'finalisasi':
        $active_steps = [8];
        break;
    case 'pengumuman':
        $active_steps = [9];
        break;
}

// Date Range Helper
function get_stage_date_range($stage_key, $default_text)
{
    $start = get_setting("stage_{$stage_key}_start");
    $end = get_setting("stage_{$stage_key}_end");
    $active_scheme = get_setting('active_scheme', '1');

    // Dynamic overrides based on active scheme and dependent stages
    if ($stage_key == 'buka') {
        if ($active_scheme == '1') {
            $start = get_setting('scheme_1_start') . ' ' . get_setting('scheme_daily_start');
            $end = get_setting('scheme_1_end') . ' ' . get_setting('scheme_daily_end');
        } elseif ($active_scheme == '2') {
            $start = get_setting('scheme_2_start') . ' ' . get_setting('scheme_2_start_time', '00:01');
            $end = get_setting('scheme_2_end') . ' ' . get_setting('scheme_2_end_time', '23:59');
        } else {
            $start = get_setting('scheme_period_start') . ' ' . get_setting('scheme_period_start_time', '00:01');
            $end = get_setting('scheme_period_end') . ' ' . get_setting('scheme_period_end_time', '23:59');
        }
    } elseif ($stage_key == 'verifikasi') {
        if ($active_scheme == '1') {
            $start = get_setting('scheme_1_end') . ' ' . get_setting('scheme_daily_end');
        } elseif ($active_scheme == '2') {
            $start = get_setting('scheme_2_end') . ' ' . get_setting('scheme_2_end_time', '23:59');
        } else {
            $start = get_setting('scheme_period_end') . ' ' . get_setting('scheme_period_end_time', '23:59');
        }
    } elseif ($stage_key == 'pengumuman_adm') {
        $start = get_setting('stage_verifikasi_end', '');
    } elseif ($stage_key == 'cbt') {
        $start = get_setting('stage_pengumuman_adm_end', '');
    }

    $start = !empty(trim($start)) ? $start : null;
    $end = !empty(trim($end)) ? $end : null;

    if (!$start && !$end)
        return $default_text;

    if ($start && $end) {
        $start_fmt = date('d/m/Y', strtotime($start));
        $end_fmt = date('d/m/Y', strtotime($end));
        if ($start_fmt == $end_fmt) {
            return $start_fmt . ' (Jam ' . date('H:i', strtotime($start)) . ' - ' . date('H:i', strtotime($end)) . ')';
        }
        return date('d M', strtotime($start)) . ' - ' . date('d M Y', strtotime($end));
    }

    return $start ? date('d M Y', strtotime($start)) : date('d M Y', strtotime($end));
}
?>

<style>
    /* Timeline Flow Enhancement */
    .timeline-container {
        position: relative;
        padding-bottom: 20px;
    }

    .timeline-line {
        position: absolute;
        top: 40px;
        left: 5%;
        right: 5%;
        height: 2px;
        background: #dee2e6;
        z-index: 0;
    }

    .timeline-line-progress {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        background: #198754;
        transition: width 1s ease;
    }

    .info-step-card {
        background: white;
        border-radius: 20px;
        border: 2px solid transparent;
        padding: 24px 15px;
        text-align: center;
        position: relative;
        z-index: 1;
        height: 100%;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    }

    .info-step-card.active {
        border-color: #ffc107;
        box-shadow: 0 10px 30px rgba(255, 193, 7, 0.15);
        transform: translateY(-5px);
        background: #fffdf5;
    }

    .info-step-card.done {
        border-color: #198754;
        background: #f8fff9;
    }

    .step-icon-wrapper {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 1.2rem;
        position: relative;
        transition: all 0.3s ease;
    }

    .active .step-icon-wrapper {
        background: #ffc107;
        color: #0b2c24;
        box-shadow: 0 0 0 5px rgba(255, 193, 7, 0.2);
        animation: pulse-active 2s infinite;
    }

    .done .step-icon-wrapper {
        background: #198754;
        color: white;
    }

    .upcoming .step-icon-wrapper {
        background: #e9ecef;
        color: #adb5bd;
    }

    @keyframes pulse-active {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
        }

        70% {
            box-shadow: 0 0 0 15px rgba(255, 193, 7, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
        }
    }

    .step-title {
        font-weight: 800;
        font-size: 0.85rem;
        color: #0b2c24;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .step-date-info {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 100px;
        display: inline-block;
        margin-top: 8px;
    }

    .active .step-date-info {
        background: rgba(255, 193, 7, 0.15);
        color: #856404;
    }

    .done .step-date-info {
        background: rgba(25, 135, 84, 0.1);
        color: #155724;
    }

    .upcoming .step-date-info {
        background: #f8f9fa;
        color: #6c757d;
    }

    /* Welcome Header */
    .welcome-hero {
        background: linear-gradient(135deg, #0b2c24 0%, #1a4d40 100%);
        border-radius: 30px;
        padding: 40px;
        color: white;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }

    .welcome-hero::after {
        content: 'PMBM';
        position: absolute;
        top: -20px;
        right: -20px;
        font-size: 10rem;
        font-weight: 900;
        opacity: 0.05;
        color: white;
        transform: rotate(-15deg);
    }
</style>

<div class="animate-fade-in">
    <!-- Welcome Header -->
    <div class="welcome-hero shadow-lg mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold">NO. Pendaftaran:
                        <?= htmlspecialchars($siswa['no_pendaftaran']) ?></span>
                    <span
                        class="badge bg-secondary px-3 py-2 rounded-pill fw-bold border border-light border-opacity-25">Jalur:
                        <?= htmlspecialchars($siswa['nama_jalur'] ?? '-') ?></span>
                </div>
                <h1 class="display-5 fw-bold mb-2">Ahlan wa Sahlan, <?= explode(' ', $siswa['nama_lengkap'])[0] ?>!</h1>
                <?php
                // Determine which narrative to show based on pathway mapping
                $special_ids = explode(',', get_setting('narasi_special_jalur_ids', ''));
                $general_ids = explode(',', get_setting('narasi_general_jalur_ids', ''));
                $display_narasi = "";

                if (stripos(($siswa['nama_jalur'] ?? ''), 'tahfi') !== false && ($siswa['status_tahfidz'] ?? '') == 'Tidak Lulus' && !in_array($ppdb_status, ['buka', 'verifikasi'])) {
                    // Specific case: Tahfidz but failed test (Show only after verification stage is over)
                    $display_narasi = "Mohon Maaf, Ananda tidak lulus tes tahfizh. Selanjutnya Ananda dapat mengikuti tes akademik sesuai jadwal yang telah ditentukan.";
                } elseif (in_array($siswa['jalur_id'], $special_ids)) {
                    // Priority 1: Special Narrative
                    $display_narasi = get_setting('narasi_pendaftaran_tahfizh', "Silakan mengikuti tahapan seleksi selanjutnya sesuai jadwal.");
                } elseif (in_array($siswa['jalur_id'], $general_ids)) {
                    // Priority 2: General Narrative
                    $default_general = "Alhamdulillah, pendaftaran Ananda telah kami terima. Saat ini Ananda terdaftar melalui . Silakan cek jadwal kegiatan di bawah untuk mengetahui tahapan selanjutnya ya!";
                    $raw_narasi = get_setting('narasi_pendaftaran_berhasil', $default_general);
                    $nama_jalur_clean = preg_replace('/^jalur\s+/i', '', htmlspecialchars($siswa['nama_jalur'] ?? ''));
                    $display_narasi = str_replace(['…', '...', '[jalur]'], "<strong>" . $nama_jalur_clean . "</strong>", $raw_narasi);
                }

                if (!empty($display_narasi)):
                    $is_tahfidz_fail = (stripos(($siswa['nama_jalur'] ?? ''), 'tahfi') !== false && ($siswa['status_tahfidz'] ?? '') == 'Tidak Lulus');
                    $bg_class = $is_tahfidz_fail ? "bg-danger bg-opacity-20 border-danger" : "bg-white bg-opacity-10 border-white border-opacity-25";
                    $icon_class = $is_tahfidz_fail ? "fa-exclamation-triangle text-warning pulse" : "fa-info-circle text-warning";
                    $text_class = $is_tahfidz_fail ? "text-white fw-bold" : "opacity-90";
                    ?>
                    <div class="mt-3 p-3 rounded-4 <?= $bg_class ?> border shadow-sm">
                        <div class="d-flex gap-3">
                            <i class="fas <?= $icon_class ?> fa-lg mt-1"></i>
                            <div class="small <?= $text_class ?>"><?= nl2br($display_narasi) ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mt-4">
                    <a href="../cetak_bukti.php?reg=<?= $siswa['no_pendaftaran'] ?>" target="_blank"
                        class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fas fa-print me-2"></i> Cetak Kartu Pendaftaran
                    </a>
                </div>
            </div>
            <div class="col-md-4 text-md-end d-none d-md-block">
                <img src="https://cdn-icons-png.flaticon.com/512/1041/1041916.png"
                    style="width: 150px; opacity: 0.9; filter: brightness(0) invert(1);" alt="Icon">
            </div>
        </div>
    </div>

    <!-- Progress Tahapan Pelaksanaan -->
    <div class="mb-5 timeline-container">
        <h5 class="fw-bold mb-4 d-flex align-items-center">
            <i class="fas fa-route me-2 text-success"></i> Alur Proses Pendaftaran Anda
        </h5>

        <div class="timeline-line d-none d-md-block">
            <?php
            // Check student status and pathway
            // We force is_rejected to false here so the timeline always shows ALL stages (6 stages) 
            // until the official 'pengumuman' stage is active.
            $is_rejected = false; 

            // Logic for simplified flow (skip CBT stages)
            $is_jalur_tahfidz = stripos(($siswa['nama_jalur'] ?? ''), 'tahfi') !== false;
            $is_jalur_tanpa_tes = stripos(($siswa['nama_jalur'] ?? ''), 'Tanpa Tes') !== false;

            // Simplified flow applies to:
            // 1. Tanpa Tes tracks (once verified)
            // 2. Tahfidz track (once verified AND NOT explicitly failed the tahfizh test)
            $show_simplified_flow = false;
            if ($siswa['status'] == 'Terverifikasi') {
                if ($is_jalur_tanpa_tes) {
                    $show_simplified_flow = true;
                } elseif ($is_jalur_tahfidz && ($siswa['status_tahfidz'] ?? '') != 'Tidak Lulus') {
                    $show_simplified_flow = true;
                }
            }

            if ($show_simplified_flow) {
                // Simplified flow: skip Hasil Admin & Tes Akademik
                $total_stages = 4;
                $all_stages = ['buka', 'verifikasi', 'finalisasi', 'pengumuman'];
            } else {
                // Normal flow
                $total_stages = 6;
                $all_stages = ['buka', 'verifikasi', 'pengumuman_adm', 'cbt', 'finalisasi', 'pengumuman'];
            }

            $current_idx = 0;
            foreach ($all_stages as $idx => $s)
                if ($ppdb_status == $s)
                    $current_idx = $idx;
            
            // Calculate progress based on current index
            $progress_width = ($total_stages > 1) ? ($current_idx / ($total_stages - 1)) * 90 : 0;
            ?>
            <div class="timeline-line-progress" style="width: <?= $progress_width ?>%;"></div>
        </div>

        <div class="row g-3 position-relative">
            <?php
            if ($show_simplified_flow) {
                // Simplified stages for verified Tahfidz / Tanpa Tes students
                $stages = [
                    ['key' => 'buka', 'label' => 'Pendaftaran', 'icon' => 'fa-edit', 'date_key' => 'buka'],
                    ['key' => 'verifikasi', 'label' => 'Verifikasi', 'icon' => 'fa-user-check', 'date_key' => 'verifikasi'],
                    ['key' => 'finalisasi', 'label' => 'Finalisasi', 'icon' => 'fa-file-signature', 'date_key' => 'finalisasi'],
                    ['key' => 'pengumuman', 'label' => 'Hasil Akhir', 'icon' => 'fa-award', 'date_key' => 'pengumuman']
                ];
            } else {
                // Standard stages for all other students
                $stages = [
                    ['key' => 'buka', 'label' => 'Pendaftaran', 'icon' => 'fa-edit', 'date_key' => 'buka'],
                    ['key' => 'verifikasi', 'label' => 'Verifikasi', 'icon' => 'fa-user-check', 'date_key' => 'verifikasi'],
                    ['key' => 'pengumuman_adm', 'label' => 'Hasil Admin', 'icon' => 'fa-clipboard-check', 'date_key' => 'pengumuman_adm'],
                    ['key' => 'cbt', 'label' => 'Tes Akademik', 'icon' => 'fa-laptop', 'date_key' => 'cbt'],
                    ['key' => 'finalisasi', 'label' => 'Finalisasi', 'icon' => 'fa-file-signature', 'date_key' => 'finalisasi'],
                    ['key' => 'pengumuman', 'label' => 'Hasil Akhir', 'icon' => 'fa-award', 'date_key' => 'pengumuman']
                ];
            }

            $current_found = false;
            foreach ($stages as $stage):
                $is_active = ($ppdb_status == $stage['key']);
                $is_done = !$is_active && !$current_found;
                if ($is_active)
                    $current_found = true;

                $card_state = $is_active ? 'active' : ($is_done ? 'done' : 'upcoming');
                $date_range = get_stage_date_range($stage['date_key'], 'Segera Hadir');
                ?>
                <div class="col-6 col-md">
                    <div class="info-step-card <?= $card_state ?>">
                        <div class="step-icon-wrapper">
                            <i class="fas <?= $is_done ? 'fa-check' : $stage['icon'] ?>"></i>
                        </div>
                        <div class="step-title"><?= $stage['label'] ?></div>

                        <div class="step-date-info">
                            <i class="far fa-calendar-alt me-1"></i> <?= $date_range ?>
                        </div>

                        <?php if ($is_active): ?>
                            <div class="mt-2">
                                <span class="badge bg-warning text-dark border-0 pulse-light" style="font-size: 0.6rem;">ANDA DI
                                    SINI</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php
    $ppdb_status = get_setting('ppdb_status', 'belum');
    $is_tahfidz = stripos(($siswa['nama_jalur'] ?? ''), 'tahfi') !== false;
    $is_tanpa_tes = stripos(($siswa['nama_jalur'] ?? ''), 'Tanpa Tes') !== false;
    $show_announcement = false;
    $ann_title = "";
    $ann_color = "primary";
    $ann_icon = "fa-bullhorn";
    $ann_message = "";
    $ann_detail = ""; // For admin notes or additional info
    
    // 1. Stage: Announcement of Administrative Results
    // Fix: Only show this box IF we are exactly in 'pengumuman_adm' stage. 
    // If we are in 'cbt' or 'finalisasi', this old announcement should be hidden for rejected students.
    if ($ppdb_status == 'pengumuman_adm') {
        $show_announcement = true;
        $ann_title = "Hasil Seleksi Administrasi";

        if ($siswa['status'] == 'Ditolak') {
            $ann_color = "danger";
            $ann_icon = "fa-times-circle";
            $ann_message = get_setting('narasi_tidak_lulus_administrasi', "Mohon Maaf Ananda Tidak Lulus Seleksi Administrasi");
            if (!empty($siswa['catatan_admin'])) {
                $ann_detail = "<strong>Catatan Panitia:</strong><br>" . nl2br(htmlspecialchars($siswa['catatan_admin']));
            }
        } else if ($siswa['status'] == 'Terverifikasi' || $siswa['status'] == 'Diterima') {
            $ann_color = "success";
            $ann_icon = "fa-check-circle";

            // Check for Tahfizh specific results
            if ($is_tahfidz && $siswa['status_tahfidz'] == 'Tidak Lulus') {
                $ann_message = get_setting('narasi_tahfizh_tidak_lolos', "Mohon Maaf, Ananda tidak lulus tes tahfizh. Selanjutnya Ananda dapat mengikuti tes akademik sesuai jadwal yang telah ditentukan.");
            } else if ($is_tahfidz && $siswa['status_tahfidz'] == 'Lulus') {
                $ann_message = "Alhamdulillah! Ananda dinyatakan <strong>LULUS SELEKSI TAHFIDZ</strong>. <br><br>🎉 <strong>Selamat!</strong> Ananda <u>tidak perlu mengikuti Tes Akademik</u> dan langsung menunggu pengumuman hasil akhir. Terima kasih telah menjadi bagian dari keluarga besar MTsN 1 Kota Pekanbaru melalui jalur Tahfidz.";
            } else if ($is_tanpa_tes) {
                $nama_jalur = htmlspecialchars($siswa['nama_jalur'] ?? '');
                $ann_message = "Alhamdulillah! Ananda dinyatakan <strong>LULUS " . $nama_jalur . "</strong>.<br><br>" .
                    "🎉 <strong>Selamat!</strong> Ananda <u>tidak perlu mengikuti Tes Akademik</u> dan langsung menunggu pengumuman hasil akhir. Terima kasih telah menjadi bagian dari keluarga besar MTsN 1 Kota Pekanbaru melalui <strong>" . $nama_jalur . "</strong>.";
            } else {
                $ann_message = get_setting('narasi_lulus_administrasi', "Selamat, Ananda dinyatakan lulus tahap administrasi pada proses Penerimaan Murid Baru di MTsN 1 Kota Pekanbaru. Silakan melanjutkan ke tahap berikutnya sesuai jadwal dan ketentuan yang telah ditetapkan.");
            }
        } else {
            // Still pending
            $ann_icon = "fa-hourglass-half";
            $ann_color = "warning text-dark";
            $ann_message = "Berkas pendaftaran Ananda sedang dalam proses verifikasi oleh panitia. Silakan cek kembali secara berkala.";
        }
    }

    // 2. Stage: Exam Info / CBT
    if ($ppdb_status == 'cbt') {
        $no_exam_needed = ($is_tanpa_tes || ($is_tahfidz && ($siswa['status_tahfidz'] ?? '') == 'Lulus'));
        
        // Check if student passed admin (either status is verified or they have an exam number)
        $has_passed_admin = ($siswa['status'] != 'Ditolak' || !empty($siswa['no_pendaftaran']));
        
        if ($has_passed_admin && !$no_exam_needed) {
            $show_announcement = true;
            $ann_title = "Informasi Tes Akademik";
            $ann_color = "info";
            $ann_icon = "fa-laptop-code";
            
            if (!empty($siswa['test_hari'])) {
                $ann_message = "Alhamdulillah, <strong>jadwal ujian Ananda telah tersedia!</strong><br><br>Silakan klik tombol <strong>Lihat Jadwal Ujian</strong> di bawah untuk melihat detail hari, sesi, jam, serta lokasi labor/ruangan tempat Ananda melaksanakan ujian.";
            } else {
                $ann_message = "Jadwal pelaksanaan tes akademik Ananda sedang dalam proses pembagian oleh panitia. Silakan cek kembali halaman ini secara berkala untuk melihat jadwal, sesi, dan lokasi ruangan ujian Anda.";
            }
        }
    }

    // 2.5 Stage: Finalisasi
    if ($ppdb_status == 'finalisasi') {
        $has_passed_admin = ($siswa['status'] != 'Ditolak' || !empty($siswa['no_pendaftaran']));
        
        if ($has_passed_admin) {
            $show_announcement = true;
            $ann_title = "Finalisasi Pendaftaran";
            $ann_color = "primary";
            $ann_icon = "fa-clipboard-check";
            $ann_message = get_setting('narasi_finalisasi', "Ananda telah melaksanakan tes akademik. Mohon kesediaannya untuk secara berkala mengecek jadwal pengumuman kelulusan.");
        }
    }

    // 3. Stage: Final Result / Re-registration
    if ($ppdb_status == 'pengumuman') {
        $show_announcement = true;
        $ann_title = "Pengumuman Hasil Akhir PMBM";
        
        $is_lulus_langsung = (in_array($siswa['jalur_id'], [8, 10]) || (($siswa['jalur_id'] == 11) && ($siswa['status_tahfidz'] ?? '') == 'Lulus'));
        $status_is_ok = in_array($siswa['status'], ['Terverifikasi', 'Diterima', 'Lulus']);

        if ($status_is_ok && $is_lulus_langsung || ($siswa['status'] == 'Diterima' || $siswa['status'] == 'Lulus')) {
            $ann_color = "success";
            $ann_icon = "fa-award";
            if ($is_lulus_langsung) {
                $nama_jalur = htmlspecialchars($siswa['nama_jalur'] ?? 'Jalur Prestasi');
                $ann_message = "Alhamdulillah! Ananda dinyatakan <strong>LULUS SELEKSI ".strtoupper($nama_jalur)."</strong>. Selamat bergabung di keluarga besar MTsN 1 Kota Pekanbaru.";
            } else {
                $ann_message = get_setting('narasi_lulus_test_akademik', "Selamat, Ananda lulus tes akademik di MTsN 1 Kota Pekanbaru. Silakan melakukan daftar ulang sesuai jadwal yang telah ditentukan.");
            }
            $ann_detail = "<div class='mt-3 p-3 bg-success bg-opacity-10 rounded-4 border border-success border-opacity-25'>" .
                "<h6 class='fw-bold mb-2 text-success'><i class='fas fa-info-circle me-2'></i>Informasi Daftar Ulang:</h6>" .
                get_setting('narasi_info_daftar_ulang', "Bagi Ananda yang lulus tes akademik, silakan melakukan daftar ulang pada hari Rabu – Jumat, 01 – 03 April 2026 pukul 08.00 – 15.00 WIB di MTsN 1 Kota Pekanbaru.") .
                "</div>";
        } else if ($siswa['status'] == 'Ditolak' || $siswa['status'] == 'Tidak Lulus') {
            $ann_color = "danger";
            $ann_icon = "fa-times-circle";
            $ann_message = get_setting('narasi_tidak_lulus_test_akademik', "Mohon maaf, Ananda tidak lulus tes akademik di MTsN 1 Kota Pekanbaru.");
        }
    }

    if ($show_announcement): ?>
        <!-- Dynamic Announcement Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden animate-fade-in">
            <div
                class="card-header bg-<?= $ann_color ?> <?= (stripos($ann_color, 'warning') !== false) ? '' : 'text-white' ?> py-3">
                <h5 class="mb-0 fw-bold"><i class="fas <?= $ann_icon ?> me-2"></i> <?= $ann_title ?></h5>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-1 d-none d-md-block text-center">
                        <i class="fas <?= $ann_icon ?> text-<?= explode(' ', $ann_color)[0] ?> fa-3x opacity-50"></i>
                    </div>
                    <div class="col-md-11">
                        <div class="fs-5 text-dark lh-base">
                            <?= nl2br($ann_message) ?>
                        </div>
                        <?php if ($ann_detail): ?>
                            <div class="mt-3 small text-muted">
                                <?= $ann_detail ?>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons based on context -->
                        <div class="mt-4">
                            <?php
                            $is_verified_for_exam = ($siswa['status'] == 'Terverifikasi' || $siswa['status'] == 'Diterima');
                            $needs_exam = !($is_tanpa_tes || ($is_tahfidz && ($siswa['status_tahfidz'] ?? '') == 'Lulus'));
                            $cbt_visible = (get_setting('cbt_info_visibility', 'aktif') == 'aktif');

                            // 1. Button during Exam Phase (Jadwal)
                            if (in_array($ppdb_status, ['pengumuman_adm', 'cbt']) && $is_verified_for_exam && $needs_exam): ?>
                                <a href="status_ujian.php" class="btn btn-success rounded-pill px-4 me-2">
                                    Lihat Jadwal Ujian <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            <?php endif; ?>

                            <?php 
                            // 2. Button during/after Final Phase (Nilai)
                            if (in_array($ppdb_status, ['pengumuman', 'finalisasi']) && isset($siswa['nilai_ujian']) && $needs_exam): ?>
                                <a href="status_ujian.php" class="btn btn-outline-primary rounded-pill px-4 me-2">
                                    <i class="fas fa-poll me-2"></i> Lihat Hasil & Nilai Ujian
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>



    <!-- Quick Support -->
    <div class="row mt-4">
        <div class="col-12">
            <div
                class="card glass-card border-0 p-4 bg-dark text-white d-flex flex-md-row align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold mb-1"><i class="fas fa-headset me-2 text-warning"></i>Butuh Bantuan?</h5>
                    <p class="mb-0 small opacity-75">Hubungi panitia jika mengalami kesulitan selama proses pendaftaran.
                    </p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="https://wa.me/<?= get_setting('contact_phone', '08123456789') ?>" target="_blank"
                        class="btn btn-success rounded-pill px-4 fw-bold me-2">
                        <i class="fab fa-whatsapp me-2"></i> WhatsApp
                    </a>
                    <a href="identitas.php" class="btn btn-outline-light rounded-pill px-4 fw-bold">
                        <i class="fas fa-user-circle me-2"></i> Lihat Biodata
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout_bottom.php'; ?>