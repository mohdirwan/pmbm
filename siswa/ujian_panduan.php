<?php
$page_title = "Panduan Ujian Online";
require_once 'layout_top.php';
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card glass-card border-0 p-4">
            <h5 class="fw-bold mb-4">Langkah-langkah Ujian CBT</h5>

            <div class="timeline">
                <div class="mb-4 d-flex align-items-start">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 32px; height: 32px; flex-shrink: 0;">1</div>
                    <div>
                        <h6 class="fw-bold mb-1">Persiapkan Perangkat</h6>
                        <p class="small text-muted">Gunakan Laptop atau Smartphone dengan kuota internet yang cukup dan
                            stabil.</p>
                    </div>
                </div>

                <div class="mb-4 d-flex align-items-start">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 32px; height: 32px; flex-shrink: 0;">2</div>
                    <div>
                        <h6 class="fw-bold mb-1">Login ke Sistem CBT</h6>
                        <p class="small text-muted">Klik menu "Mulai Ujian" dan masukan Username & Password Anda.</p>
                    </div>
                </div>

                <div class="mb-4 d-flex align-items-start">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 32px; height: 32px; flex-shrink: 0;">3</div>
                    <div>
                        <h6 class="fw-bold mb-1">Pilih Mata Pelajaran</h6>
                        <p class="small text-muted">Pilih paket soal yang aktif sesuai dengan jadwal yang telah
                            ditentukan.</p>
                    </div>
                </div>

                <div class="mb-0 d-flex align-items-start">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width: 32px; height: 32px; flex-shrink: 0;">4</div>
                    <div>
                        <h6 class="fw-bold mb-1">Selesaikan & Log Out</h6>
                        <p class="small text-muted">Klik "Selesai" setelah semua jawaban terisi dan pastikan data
                            terkirim.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card bg-dark text-white border-0 p-4 rounded-4 shadow-lg mb-4">
            <h6 class="fw-bold mb-3"><i class="fas fa-video me-2 text-warning"></i> Video Tutorial</h6>
            <p class="small opacity-75">Tonton video panduan cara mengikuti ujian online agar tidak terjadi kendala
                teknis.</p>
            <button class="btn btn-warning w-100 rounded-pill"><i class="fas fa-play me-2"></i> Putar Video</button>
        </div>

        <div class="card glass-card border-0 p-4">
            <h6 class="fw-bold mb-3 text-danger"><i class="fas fa-ban me-2"></i> Larangan:</h6>
            <ul class="small text-muted ps-3 mb-0">
                <li class="mb-2">Membuka tab browser lain.</li>
                <li class="mb-2">Berbagi akun dengan orang lain.</li>
                <li>Mencontek atau bekerja sama.</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once 'layout_bottom.php'; ?>