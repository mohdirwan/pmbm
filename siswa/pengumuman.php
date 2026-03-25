<?php
$page_title = "Pengumuman & Daftar Ulang";
require_once 'layout_top.php';

// Pastikan pengumuman sudah dibuka secara resmi
$ppdb_status = get_setting('ppdb_status', 'belum');
$ann_status = get_setting('announcement_status', 'closed');
if ($ann_status == 'closed' || $ppdb_status !== 'pengumuman') {
    echo "<script>alert('Pengumuman hasil seleksi belum resmi dibuka.'); window.location.href='dashboard.php';</script>";
    exit;
}
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card glass-card border-0 p-4 mb-4 overflow-hidden">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4"><i
                        class="fas fa-bullhorn me-3 text-warning"></i><?= get_setting('announcement_title', 'Pengumuman Kelulusan') ?>
                </h5>

                <?php if ($siswa['status'] == 'Diterima'): ?>
                    <div class="alert alert-success border-0 rounded-4 p-4 mb-5">
                        <?= get_setting('announcement_body', 'Selamat, Anda dinyatakan lulus seleksi.') ?>
                    </div>

                    <h6 class="fw-bold mb-3">Tata Cara Daftar Ulang:</h6>
                    <div class="row g-4 mb-5">
                        <div class="col-md-4">
                            <div class="p-4 bg-white border rounded-4 text-center h-100 shadow-sm">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex p-3 mb-3"><i
                                        class="fas fa-file-download shadow-sm"></i></div>
                                <h6 class="fw-bold fs-6">1. Cetak SKL</h6>
                                <p class="small text-muted mb-0">Unduh Surat Keterangan Lulus dari dashboard ini.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-4 bg-white border rounded-4 text-center h-100 shadow-sm">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex p-3 mb-3"><i
                                        class="fas fa-money-check-alt shadow-sm"></i></div>
                                <h6 class="fw-bold fs-6">2. Pembayaran</h6>
                                <p class="small text-muted mb-0">Lengkapi administrasi sekolah & seragam.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-4 bg-white border rounded-4 text-center h-100 shadow-sm">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex p-3 mb-3"><i
                                        class="fas fa-university shadow-sm"></i></div>
                                <h6 class="fw-bold fs-6">3. Lapor Diri</h6>
                                <p class="small text-muted mb-0">Serahkan berkas fisik asli ke gedung pusat MTsN 1 Kota
                                    Pekanbaru.</p>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <a href="cetak_bukti_lulus.php" class="btn btn-primary btn-lg rounded-pill px-5 shadow">
                            <i class="fas fa-print me-2"></i> Cetak Bukti Kelulusan (SKL)
                        </a>
                    </div>

                <?php elseif ($siswa['status'] == 'Pending'): ?>
                    <div class="text-center py-5">
                        <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-state-2130362-1800926.png"
                            style="width: 200px; opacity: 0.5;" alt="Waiting">
                        <h5 class="fw-bold mt-4 text-muted">Pengumuman Belum Tersedia</h5>
                        <p class="text-muted">Hasil seleksi diprediksi akan diumumkan pada tanggal <strong>15 Februari
                                2026</strong>. <br>Harap bersabar dan pantau terus dashboard Anda.</p>
                    </div>
                <?php elseif ($siswa['status'] == 'Ditolak' || $siswa['status'] == 'Tidak Lulus'): ?>
                    <div class="alert alert-danger border-0 rounded-4 p-4 text-center">
                        <i class="fas fa-times-circle fa-4x mb-3 opacity-50"></i>
                        <h4 class="fw-bold">Mohon Maaf, Ananda Belum Berhasil.</h4>
                        <p class="mb-3">
                            <?= get_setting('narasi_tidak_lulus_test_akademik', 'Mohon maaf, Ananda tidak lulus seleksi di MTsN 1 Kota Pekanbaru.') ?>
                        </p>
                        <hr>
                        <p class="small text-muted mb-0">Terima kasih telah berpartisipasi dalam PMBM. Teruslah semangat
                            belajar di sekolah manapun Ananda berada.</p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary border-0 rounded-4 p-4 text-center">
                        <h4 class="fw-bold">Data Tidak Ditemukan atau Status Tidak Valid.</h4>
                        <p class="mb-0">Silakan hubungi administrator jika Anda merasa ini adalah kesalahan sistem.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout_bottom.php'; ?>