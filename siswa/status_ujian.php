<?php
$page_title = "Informasi & Jadwal Ujian";
require_once 'layout_top.php';

// Proteksi: Hanya bisa diakses jika hasil administrasi sudah diumumkan, siswa lolos, dan butuh ujian
if ($tahap_admin != 'pengumuman' || !$is_verified || !$needs_exam) {
    echo "<script>window.location='status_administrasi.php';</script>";
    exit();
}

$tata_tertib = get_setting('cbt_rules');
$tutorial_pdf = get_setting('cbt_tutorial_pdf');

function format_indo_date($date) {
    if (!$date || $date == '-') return '-';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return $date;

    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $time = strtotime($date);
    $day_name = $days[date('w', $time)];
    $day = date('j', $time);
    $month_name = $months[(int)date('m', $time)];
    $year = date('Y', $time);
    
    return "$day_name, $day $month_name $year";
}
?>

<style>
    .rotate-15 { transform: rotate(15deg); }
    .animate-pulse { animation: pulse-animation 2s infinite; }
    @keyframes pulse-animation {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.7; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>

<div class="row g-4 animate-fade-in">
    <!-- Main Info & Schedule -->
    <div class="col-lg-8">
        <?php 
        $current_ppdb_status = get_setting('ppdb_status', 'belum');
        if ($current_ppdb_status === 'pengumuman' && isset($siswa['nilai_ujian'])): 
        ?>
            <div class="card glass-card border-0 p-4 mb-4 overflow-hidden position-relative text-white"
                style="background: linear-gradient(135deg, #0b2c24 0%, #1a4d40 100%);">
                <div class="position-absolute top-0 end-0 opacity-10 mt-n4 me-n4">
                    <i class="fas fa-trophy fa-10x text-white rotate-15"></i>
                </div>

                <div class="row align-items-center position-relative">
                    <div class="col-md-8">
                        <?php
                        $score = (float) $siswa['nilai_ujian'];
                        $label = "Bagus!";
                        $label_class = "bg-info text-white";
                        if ($score >= 85) {
                            $label = "Luar Biasa!";
                            $label_class = "bg-warning text-dark";
                        } elseif ($score >= 75) {
                            $label = "Sangat Baik!";
                            $label_class = "bg-success text-white";
                        }
                        ?>
                        <div class="badge <?= $label_class ?> px-3 py-2 rounded-pill fw-bold mb-3 shadow-sm">
                            <i class="fas fa-star me-1 animate-pulse"></i> <?= $label ?> Hasil Ujian Tersedia
                        </div>
                        <h4 class="fw-bold mb-1">Hasil Nilai Ujian Akademik (CBT)</h4>
                        <p class="text-white-50 small mb-4">Nilai hasil pengerjaan ujian Anda telah diproses oleh sistem.</p>

                        <div class="d-flex align-items-baseline gap-2 mb-0">
                            <h1 class="display-2 fw-bold mb-0 text-warning" style="text-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                                <?= number_format($score, 0) ?>
                            </h1>
                            <span class="fs-4 text-white-50">/ 100</span>
                        </div>
                    </div>
                    <div class="col-md-4 text-center d-none d-md-block">
                        <div class="p-4 rounded-circle d-inline-flex align-items-center justify-content-center border border-white border-opacity-10 shadow-lg"
                            style="background: rgba(255,255,255,0.05); width: 140px; height: 140px;">
                            <i class="fas fa-medal text-warning display-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card glass-card border-0 p-4 mb-4 h-100">
            <div class="d-flex align-items-center mb-4">
                <div class="bg-primary bg-opacity-10 p-3 rounded-4 me-3">
                    <i class="fas fa-calendar-alt text-primary fs-3"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">Informasi Pelaksanaan Ujian</h5>
                    <p class="text-muted small mb-0">Baca dengan teliti jadwal dan lokasi pelaksanaan ujian.</p>
                </div>
            </div>

            <?php if (!empty($siswa['test_hari'])): ?>
                <div class="p-4 bg-primary bg-opacity-10 rounded-4 border border-primary border-opacity-25 mb-4 shadow-sm">
                    <h6 class="fw-bold text-primary mb-3"><i class="fas fa-clock me-2"></i> Jadwal Ujian Anda:</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="p-3 bg-white rounded-4 text-center border">
                                <small class="text-muted d-block uppercase" style="font-size: 10px; letter-spacing: 1px;">Hari/Tanggal</small>
                                <span class="fw-bold text-dark"><?= format_indo_date($siswa['test_hari']) ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-white rounded-4 text-center border">
                                <small class="text-muted d-block uppercase" style="font-size: 10px; letter-spacing: 1px;">Sesi</small>
                                <span class="fw-bold text-dark"><?= $siswa['test_sesi'] ?></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-white rounded-4 text-center border">
                                <small class="text-muted d-block uppercase" style="font-size: 10px; letter-spacing: 1px;">Jam Pelaksanaan</small>
                                <span class="fw-bold text-success"><?= date('H:i', strtotime($siswa['test_jam_mulai'])) ?> - <?= date('H:i', strtotime($siswa['test_jam_selesai'])) ?> WIB</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-white rounded-4 text-center border">
                                <small class="text-muted d-block uppercase" style="font-size: 10px; letter-spacing: 1px;">Lokasi / Ruangan</small>
                                <span class="fw-bold text-primary"><?= $siswa['test_ruangan'] ?: '-' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-4 bg-light rounded-4 border-start border-4 border-warning mb-4 shadow-sm">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fas fa-clock text-warning fa-2x"></i>
                        <div class="fs-6 lh-base text-dark">
                            <strong>Jadwal Sedang Diproses</strong><br>
                            Jadwal pelaksanaan tes akademik Anda (Hari, Sesi, dan Ruangan) sedang dalam proses pembagian oleh panitia. Silakan cek kembali halaman ini secara berkala.
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="rules-container p-3 rounded-4 bg-white border small text-muted overflow-auto mb-4"
                style="max-height: 250px;">
                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-gavel me-2"></i>Tata Tertib Ujian:</h6>
                <?= $tata_tertib ? nl2br($tata_tertib) : 'Belum ada tata tertib yang diunggah.' ?>
            </div>

            <?php 
            $tutorial_pdf = get_setting('cbt_tutorial_pdf');
            if ($tutorial_pdf && get_setting('cbt_info_visibility', 'aktif') == 'aktif'): 
            ?>
                <div class="p-3 rounded-4 bg-primary bg-opacity-10 border border-primary border-opacity-25 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="bg-white p-2 rounded-3 me-3 text-danger">
                            <i class="fas fa-file-pdf fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Panduan & Tutorial Ujian</h6>
                            <p class="text-muted small mb-0">Klik tombol di samping untuk mengunduh panduan.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= BASE_URL ?>uploads/docs/<?= $tutorial_pdf ?>"
                            class="btn btn-outline-primary rounded-pill px-3 btn-sm" target="_blank">
                            <i class="fas fa-eye me-1"></i> Preview
                        </a>
                        <a href="<?= BASE_URL ?>uploads/docs/<?= $tutorial_pdf ?>"
                            class="btn btn-primary rounded-pill px-3 btn-sm" download>
                            <i class="fas fa-download me-1"></i> Download
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Exam Account Info -->
    <div class="col-lg-4">
        <div class="card glass-card border-0 p-4 bg-dark text-white shadow-lg mb-4">
            <h5 class="fw-bold mb-4 d-flex align-items-center">
                <i class="fas fa-key me-2 text-warning"></i> Akun Ujian CBT
            </h5>

            <div class="p-3 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-10 mb-4">
                <small class="text-white-50 d-block mb-1">Username (No. Pendaftaran)</small>
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-warning"><?= htmlspecialchars($siswa['no_pendaftaran']) ?></h5>
                    <button class="btn btn-sm btn-link text-white-50 p-0"
                        onclick="copyToClipboard('<?= $siswa['no_pendaftaran'] ?>')"><i
                            class="far fa-copy"></i></button>
                </div>
            </div>

            <div class="p-3 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-10 mb-4">
                <small class="text-white-50 d-block mb-1">Password Ujian CBT</small>
                <div class="d-flex justify-content-between align-items-center">
                    <?php if(!empty($siswa['password_cbt'])): ?>
                        <h5 class="fw-bold mb-0 text-warning"><?= htmlspecialchars($siswa['password_cbt']) ?></h5>
                        <button class="btn btn-sm btn-link text-white-50 p-0"
                            onclick="copyToClipboard('<?= $siswa['password_cbt'] ?>')"><i class="far fa-copy"></i></button>
                    <?php else: ?>
                        <span class="text-warning small italic">Belum Digenerate</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="alert alert-warning border-0 small py-2 rounded-3 text-dark mb-0">
                <i class="fas fa-info-circle me-2"></i> Gunakan akun di atas untuk login ke aplikasi CBT saat ujian
                berlangsung.
            </div>

            <hr class="my-4 opacity-25">

            <a href="../cetak_kartu_ujian.php?reg=<?= $siswa['no_pendaftaran'] ?>" target="_blank"
                class="btn btn-warning w-100 rounded-pill fw-bold py-2 shadow-sm">
                <i class="fas fa-print me-2"></i> Cetak Kartu Ujian
            </a>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Teks berhasil disalin!');
        });
    }
</script>
<?php require_once 'layout_bottom.php'; ?>