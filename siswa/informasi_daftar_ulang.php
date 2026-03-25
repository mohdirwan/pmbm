<?php
$page_title = "Informasi Daftar Ulang";
require_once 'layout_top.php';

// Pastikan pengumuman sudah dibuka secara global
$ppdb_status = get_setting('ppdb_status', 'belum');
$ann_status = get_setting('announcement_status', 'closed');
if ($ann_status == 'closed' || $ppdb_status !== 'pengumuman') {
    header("Location: dashboard.php");
    exit;
}

// Pastikan siswa sudah lulus
if ($siswa['status'] != 'Diterima' && $siswa['status'] != 'Lulus') {
    echo "<script>alert('Anda belum dinyatakan lulus seleksi.'); window.location.href='status_akhir.php';</script>";
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Header Informasi -->
        <div class="card glass-card border-0 p-4 mb-4 shadow overflow-hidden">
            <div class="position-absolute top-0 end-0 p-3 opacity-10">
                <i class="fas fa-file-invoice fa-9x"></i>
            </div>
            <div class="card-body p-4 position-relative">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="fw-bold text-dark mb-2">Informasi & Prosedur Daftar Ulang</h2>
                        <p class="text-muted mb-0">Selamat datang di tahap akhir pendaftaran. Silakan baca instruksi di bawah ini dengan teliti.</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="cetak_bukti_lulus.php" class="btn btn-primary rounded-pill px-4 shadow">
                            <i class="fas fa-print me-2"></i>Cetak Bukti Lulus
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Tahapan Alur -->
            <div class="col-md-12">
                <div class="card border-0 rounded-4 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-project-diagram me-2"></i>Alur Daftar Ulang</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row text-center">
                            <div class="col-md-3 mb-4 mb-md-0">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex p-3 mb-3"><i class="fas fa-file-alt fa-lg"></i></div>
                                <h6 class="fw-bold">1. Siapkan Berkas</h6>
                                <p class="small text-muted">Lengkapi semua dokumen fisik yang diminta.</p>
                            </div>
                            <div class="col-md-3 mb-4 mb-md-0">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex p-3 mb-3"><i class="fas fa-university fa-lg"></i></div>
                                <h6 class="fw-bold">2. Datang ke Lokasi</h6>
                                <p class="small text-muted">Hadir sesuai jadwal yang telah ditentukan.</p>
                            </div>
                            <div class="col-md-3 mb-4 mb-md-0">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex p-3 mb-3"><i class="fas fa-user-check fa-lg"></i></div>
                                <h6 class="fw-bold">3. Verifikasi Berkas</h6>
                                <p class="small text-muted">Penyerahan dokumen asli kepada panitia.</p>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex p-3 mb-3"><i class="fas fa-id-card fa-lg"></i></div>
                                <h6 class="fw-bold">4. Selesai</h6>
                                <p class="small text-muted">Menerima nomor induk & jadwal masuk.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jadwal -->
            <div class="col-md-6">
                <div class="card border-0 rounded-4 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-4 text-dark border-start border-primary border-4 ps-3">WAKTU & TEMPAT</h6>
                        <div class="d-flex mb-3">
                            <div class="me-3 text-primary"><i class="fas fa-calendar-day fa-2x"></i></div>
                            <div>
                                <div class="small text-muted fw-bold text-uppercase">Tanggal Pelaksanaan</div>
                                <div class="fw-bold text-dark">Rabu – Jumat, 01 – 03 April 2026</div>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="me-3 text-primary"><i class="fas fa-clock fa-2x"></i></div>
                            <div>
                                <div class="small text-muted fw-bold text-uppercase">Jam Operasional</div>
                                <div class="fw-bold text-dark">08.00 – 15.00 WIB</div>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="me-3 text-primary"><i class="fas fa-map-marker-alt fa-2x"></i></div>
                            <div>
                                <div class="small text-muted fw-bold text-uppercase">Lokasi</div>
                                <div class="fw-bold text-dark">Gedung Pusat MTsN 1 Kota Pekanbaru</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Persyaratan -->
            <div class="col-md-6">
                <div class="card border-0 rounded-4 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-4 text-dark border-start border-success border-4 ps-3">PERSYARATAN BERKAS (Fisik)</h6>
                        <div class="row g-2">
                            <div class="col-12"><div class="p-2 border rounded-3 bg-light"><i class="fas fa-check-square text-success me-2"></i> Bukti Kelulusan / SKL (Asli)</div></div>
                            <div class="col-12"><div class="p-2 border rounded-3 bg-light"><i class="fas fa-check-square text-success me-2"></i> Fotokopi Ijazah / SKL SD/MI (2 Lembar)</div></div>
                            <div class="col-12"><div class="p-2 border rounded-3 bg-light"><i class="fas fa-check-square text-success me-2"></i> Fotokopi Akte Kelahiran & KK (2 Lembar)</div></div>
                            <div class="col-12"><div class="p-2 border rounded-3 bg-light"><i class="fas fa-check-square text-success me-2"></i> Pas Foto Berwarna 3x4 (4 Lembar)</div></div>
                            <div class="col-12"><div class="p-2 border rounded-3 bg-light"><i class="fas fa-check-square text-success me-2"></i> Materai 10.000 (2 Lembar)</div></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kontak & Catatan -->
            <div class="col-md-12">
                <div class="alert alert-warning border-0 rounded-4 p-4 shadow-sm d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-3x me-4 opacity-50"></i>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Catatan Penting:</h6>
                        <p class="mb-0 small text-dark opacity-75">
                            Calon murid wajib hadir mengenakan seragam sekolah asal (SD/MI) rapi dan didampingi oleh Orang Tua/Wali.
                            Terlambat melakukan daftar ulang tanpa konfirmasi dianggap <strong>MENGUNDURKAN DIRI</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5 mb-5">
            <a href="status_akhir.php" class="btn btn-outline-secondary rounded-pill px-4 me-2">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <a href="upload_daftar_ulang.php" class="btn btn-success rounded-pill px-5 shadow">
                Lanjut ke Upload Berkas <i class="fas fa-arrow-right ms-2"></i>
            </a>
            <button class="btn btn-light border rounded-pill px-4 ms-2 shadow-sm" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Cetak Info Ini
            </button>
        </div>
    </div>
</div>

<?php require_once 'layout_bottom.php'; ?>
