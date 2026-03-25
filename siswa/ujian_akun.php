<?php
$page_title = "Akun Ujian Online";
require_once 'layout_top.php';
?>

<div class="row">
    <div class="col-lg-6">
        <div class="card glass-card border-0 p-4">
            <h5 class="fw-bold mb-4">Informasi Akun Computer Based Test (CBT)</h5>

            <div class="bg-primary bg-opacity-10 p-4 rounded-4 mb-4 text-center">
                <p class="text-muted small mb-2 uppercase fw-bold letter-spacing-1">Username Ujian</p>
                <h3 class="fw-bold text-primary mb-4">
                    <?= $siswa['no_pendaftaran'] ?>
                </h3>

                <p class="text-muted small mb-2 uppercase fw-bold letter-spacing-1">Password / PIN</p>
                <h3 class="fw-bold text-primary">
                    26<?= $siswa['nisn'] ?>
                </h3>
            </div>

            <div class="alert alert-warning border-0 small rounded-4 shadow-sm mb-4">
                <i class="fas fa-exclamation-circle me-2"></i> Akun ini hanya dapat digunakan pada saat jadwal ujian
                berlangsung. Harap simpan informasi ini baik-baik.
            </div>

            <a href="../cetak_kartu_ujian.php?reg=<?= $siswa['no_pendaftaran'] ?>" target="_blank"
                class="btn btn-primary w-100 rounded-pill py-2 fw-bold">
                <i class="fas fa-print me-2"></i> CETAK KARTU UJIAN
            </a>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card glass-card border-0 p-4 h-100">
            <h6 class="fw-bold mb-3">Instruksi Login</h6>
            <ol class="small text-muted ps-3">
                <li class="mb-2">Kunjungi halaman CBT melalui menu <strong>"Mulai Ujian"</strong>.</li>
                <li class="mb-2">Masukan Username dan Password sesuai data di samping.</li>
                <li class="mb-2">Pastikan koneksi internet stabil selama ujian berlangsung.</li>
                <li>Jika terjadi kegagalan login, segera hubungi proktor melalui grup WhatsApp.</li>
            </ol>
        </div>
    </div>
</div>

<?php require_once 'layout_bottom.php'; ?>