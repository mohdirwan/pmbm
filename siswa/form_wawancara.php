<?php
$page_title = "Download Form Wawancara";
require_once 'layout_top.php';
?>

<div class="card glass-card border-0 p-5 text-center">
    <div class="mb-4">
        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
            style="width: 100px; height: 100px;">
            <i class="fas fa-file-pdf text-primary fs-1"></i>
        </div>
    </div>
    <h3 class="fw-bold">Formulir Wawancara PMBM</h3>
    <p class="text-muted mx-auto mb-4" style="max-width: 600px;">
        Silakan unduh dan cetak formulir ini untuk dibawa saat pelaksanaan Tes Wawancara.
        Formulir ini berisi poin-poin penilaian yang akan diisi oleh penguji.
    </p>

    <div class="alert alert-info border-0 shadow-sm rounded-4 d-inline-block px-4 mb-5">
        <i class="fas fa-info-circle me-2"></i> Kertas: A4, Cetak sebanyak 1 rangkap.
    </div>

    <div class="d-grid gap-2 col-md-4 mx-auto">
        <a href="cetak_wawancara.php" target="_blank" class="btn btn-primary btn-lg rounded-pill shadow">
            <i class="fas fa-print me-2"></i> Cetak Sekarang
        </a>
    </div>

    <div class="mt-5 text-start p-4 bg-light rounded-4">
        <h6 class="fw-bold mb-3"><i class="fas fa-list-check me-2 text-success"></i> Dokumen yang harus dilampirkan
            bersama form ini:</h6>
        <ul class="small text-muted mb-0">
            <li class="mb-2">Fotokopi Akta Kelahiran & Kartu Keluarga (KK)</li>
            <li class="mb-2">Fotokopi Rapor Semester Terakhir</li>
            <li class="mb-2">Pas Foto 3x4 (2 Lembar)</li>
            <li>Sertifikat Prestasi Asli (Jika ada)</li>
        </ul>
    </div>
</div>

<?php require_once 'layout_bottom.php'; ?>