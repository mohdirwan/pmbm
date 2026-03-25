<?php
$page_title = "Informasi Daftar Ulang";
require_once 'layout_top.php';

// Pastikan siswa sudah lulus
if ($siswa['status'] != 'Diterima' && $siswa['status'] != 'Lulus') {
    echo "<script>alert('Anda belum dinyatakan lulus seleksi.'); window.location.href='status_akhir.php';</script>";
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card glass-card border-0 p-4 mb-4 shadow">
            <div class="card-body p-4">
                <div class="text-center mb-5">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex p-4 mb-3">
                        <i class="fas fa-clipboard-check fa-4x"></i>
                    </div>
                    <h2 class="fw-bold text-dark">Informasi Daftar Ulang</h2>
                    <p class="text-muted">MTsN 1 Kota Pekanbaru - Tahun Pelajaran 2026/2027</p>
                </div>

                <div class="row g-4">
                    <div class="col-md-12">
                        <div class="alert alert-primary border-0 rounded-4 p-4 shadow-sm mb-4">
                            <h5 class="fw-bold mb-3"><i class="fas fa-info-circle me-2"></i>Petunjuk Penting</h5>
                            <p class="mb-0">
                                Selamat kepada Ananda <strong><?= htmlspecialchars($siswa['nama_lengkap']) ?></strong> yang telah dinyatakan lulus seleksi. 
                                Silakan perhatikan jadwal dan persyaratan daftar ulang di bawah ini agar proses pendaftaran berjalan lancar.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-0 rounded-4 shadow-sm h-100">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-calendar-alt me-2"></i>Jadwal Daftar Ulang</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-3 d-flex align-items-start">
                                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                                        <div>
                                            <strong>Hari/Tanggal:</strong><br>
                                            Rabu – Jumat, 01 – 03 April 2026
                                        </div>
                                    </li>
                                    <li class="mb-3 d-flex align-items-start">
                                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                                        <div>
                                            <strong>Waktu:</strong><br>
                                            Pukul 08.00 – 15.00 WIB
                                        </div>
                                    </li>
                                    <li class="d-flex align-items-start">
                                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                                        <div>
                                            <strong>Tempat:</strong><br>
                                            Gedung Pusat MTsN 1 Kota Pekanbaru
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-0 rounded-4 shadow-sm h-100">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-list-ol me-2"></i>Persyaratan Berkas</h6>
                                <ul class="list-unstyled mb-0 small">
                                    <li class="mb-2 d-flex align-items-start">
                                        <i class="fas fa-file-alt text-muted me-2 mt-1"></i>
                                        <span>Fotokopi Rapor (dilegalisir)</span>
                                    </li>
                                    <li class="mb-2 d-flex align-items-start">
                                        <i class="fas fa-file-alt text-muted me-2 mt-1"></i>
                                        <span>Fotokopi Akte Kelahiran & Kartu Keluarga</span>
                                    </li>
                                    <li class="mb-2 d-flex align-items-start">
                                        <i class="fas fa-file-alt text-muted me-2 mt-1"></i>
                                        <span>Pas Foto terbaru ukuran 3x4 (4 lembar)</span>
                                    </li>
                                    <li class="mb-2 d-flex align-items-start">
                                        <i class="fas fa-file-alt text-muted me-2 mt-1"></i>
                                        <span>Surat Keterangan Lulus (Cetak dari Sistem)</span>
                                    </li>
                                    <li class="d-flex align-items-start">
                                        <i class="fas fa-file-alt text-muted me-2 mt-1"></i>
                                        <span>Berkas lain yang akan diinfokan di lokasi</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="card border-0 rounded-4 shadow-sm bg-light">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Catatan Tambahan</h6>
                                <p class="text-muted small mb-0">
                                    - Pendaftar yang tidak melakukan daftar ulang pada jadwal yang ditentukan dianggap mengundurkan diri.<br>
                                    - Peserta wajib didampingi oleh orang tua/wali saat proses daftar ulang.<br>
                                    - Gunakan seragam sekolah asal (SD/MI) yang rapi dan sopan saat datang ke madrasah.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <a href="status_akhir.php" class="btn btn-outline-secondary rounded-pill px-4 me-2">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                    <a href="cetak_bukti_lulus.php" class="btn btn-primary rounded-pill px-4 me-2 shadow">
                        <i class="fas fa-file-download me-2"></i>Cetak Bukti Lulus
                    </a>
                    <button class="btn btn-success rounded-pill px-5 shadow" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Cetak Info Ini
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout_bottom.php'; ?>
