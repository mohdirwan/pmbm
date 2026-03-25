<?php
$page_title = "Status Administrasi";
require_once 'layout_top.php';
?>

<div class="row">
    <div class="col-12">
        <?php
        $tahap_admin = get_setting('tahap_administrasi', 'verifikasi');
        $is_verified = ($siswa['status'] == 'Terverifikasi' || $siswa['status'] == 'Diterima');
        $is_tahfidz = stripos(($siswa['nama_jalur'] ?? ''), 'tahfi') !== false;
        $is_tanpa_tes = stripos(($siswa['nama_jalur'] ?? ''), 'Tanpa Tes') !== false;

        // Defaults
        $title = "Proses Verifikasi Berkas";
        $header_class = "bg-warning";
        $iconStr = "fa-clock";
        $note = "Berkas Anda sedang dalam antrian pemeriksaan oleh tim verifikator.";

        if ($tahap_admin == 'pengumuman') {
            $failed_tahfidz = ($is_tahfidz && ($siswa['status_tahfidz'] ?? '') == 'Tidak Lulus');
            
            // NEW LOGIC: If student has a participant number, they DEFINITELY passed administration
            // even if their current status is 'Ditolak' (which means they failed at the final exam stage)
            $has_exam_number = !empty($siswa['no_pendaftaran']);
            $passed_admin_stage = ($is_verified || $failed_tahfidz || $has_exam_number);

            if ($passed_admin_stage) {
                $title = "Hasil Seleksi Administrasi";
                $header_class = "bg-success text-white";
                $iconStr = "fa-check-circle text-success";
                
                if ($failed_tahfidz) {
                    $note = get_setting('narasi_tahfizh_tidak_lolos', "Mohon Maaf, Ananda tidak lulus tes tahfizh. Namun, Administrasi Ananda <strong>Telah Diverifikasi</strong>. Selanjutnya Ananda dapat mengikuti tes akademik sesuai jadwal yang telah ditentukan.");
                } else {
                    $note = get_setting('narasi_lulus_administrasi', 'Selamat, Ananda dinyatakan lulus tahap administrasi pada proses Penerimaan Murid Baru di MTsN 1 Kota Pekanbaru. Silakan melanjutkan ke tahap berikutnya sesuai jadwal dan ketentuan yang telah ditetapkan.');
                }
            } else if ($siswa['status'] == 'Ditolak') {
                $title = "Hasil Seleksi Administrasi";
                $header_class = "bg-danger text-white";
                $iconStr = "fa-times-circle text-danger";
                $note = get_setting('narasi_tidak_lulus_administrasi', 'Mohon Maaf Ananda Tidak Lulus Seleksi Administrasi');
                if (!empty($siswa['catatan_admin'])) {
                    $note .= "<br><br><strong>Catatan Panitia:</strong><br>" . nl2br(htmlspecialchars($siswa['catatan_admin']));
                }
            }
        }
        ?>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden animate-fade-in">
            <!-- Header section matching image style -->
            <div class="card-header <?= $header_class ?> py-3 border-0">
                <h5 class="mb-0 fw-bold fs-6">
                    <i class="fas fa-check-circle me-1 opacity-75"></i> <?= $title ?>
                </h5>
            </div>

            <!-- Body section -->
            <div class="card-body p-4 bg-white">
                <div class="d-flex align-items-center mb-4">
                    <div class="me-4 flex-shrink-0">
                        <!-- Icon styling based on status -->
                        <?php if ($header_class == "bg-success text-white"): ?>
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 45px; height: 45px; opacity: 0.6;">
                                <i class="fas fa-check fs-5"></i>
                            </div>
                        <?php elseif ($header_class == "bg-danger text-white"): ?>
                            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 45px; height: 45px; opacity: 0.6;">
                                <i class="fas fa-times fs-5"></i>
                            </div>
                        <?php else: ?>
                            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 45px; height: 45px; opacity: 0.6;">
                                <i class="fas fa-clock fs-5"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="fs-6 text-dark lh-base">
                        <?= $note ?>
                    </div>
                </div>

                <?php 
                $is_lulus_tahfidz = ($is_tahfidz && ($siswa['status_tahfidz'] ?? '') == 'Lulus');
                $needs_exam = !($is_tanpa_tes || $is_lulus_tahfidz);

                if ($header_class == "bg-success text-white" && $tahap_admin == 'pengumuman' && $needs_exam): ?>
                    <div class="ms-md-5 ps-md-3">
                        <a href="status_ujian.php" class="btn btn-success rounded-pill px-4 me-2 mb-2">
                            Lihat Jadwal Ujian <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php require_once 'layout_bottom.php'; ?>