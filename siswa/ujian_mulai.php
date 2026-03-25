<?php
$page_title = "Mulai Ujian Online";
require_once 'layout_top.php';
?>

<div class="card glass-card border-0 p-5 text-center">
    <div class="mb-5">
        <div class="exam-icon-container d-inline-block position-relative">
            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                style="width: 150px; height: 150px;">
                <i class="fas fa-rocket text-primary fs-1"></i>
            </div>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger shadow">
                LIVE
            </span>
        </div>
    </div>

    <h2 class="fw-bold">Halaman Akses Tes CBT</h2>
    <p class="text-muted mx-auto mb-5" style="max-width: 600px;">
        Tombol di bawah ini akan mengarahkan Anda ke sistem ujian terpusat. Harap pastikan Anda sudah login di dashboard
        ini menggunakan perangkat yang sama untuk kemudahan akses.
    </p>

    <div class="d-grid gap-3 col-md-5 mx-auto">
        <a href="https://cbt.mtsn1pku.sch.id" target="_blank"
            class="btn btn-primary btn-lg rounded-pill shadow-lg py-3 fw-bold">
            <i class="fas fa-external-link-alt me-2"></i> MASUK SISTEM UJIAN
        </a>
        <p class="small text-muted fst-italic">Ingat: Gunakan NISN sebagai password login CBT.</p>
    </div>

    <div class="mt-5 row text-start">
        <div class="col-md-4 mb-3">
            <div class="d-flex align-items-center">
                <i class="fas fa-wifi text-success me-3 fs-4"></i>
                <div class="small fw-medium">Internet Stabil</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="d-flex align-items-center">
                <i class="fas fa-battery-three-quarters text-warning me-3 fs-4"></i>
                <div class="small fw-medium">Baterai Penuh</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="d-flex align-items-center">
                <i class="fas fa-lock text-info me-3 fs-4"></i>
                <div class="small fw-medium">Lingkungan Kondusif</div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout_bottom.php'; ?>