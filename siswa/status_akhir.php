<?php
$page_title = "Status Akhir PMBM";
require_once 'layout_top.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card glass-card border-0 p-5 text-center shadow">
            <?php
            $status = $siswa['status'];
            $ppdb_status = get_setting('ppdb_status', 'belum');

            $status_class = "text-muted";
            $status_bg = "bg-light";
            $status_icon = "fa-hourglass-half";
            $status_text = "MASIH DALAM PROSES SELEKSI";
            $status_desc = "Saat ini panitia masih melakukan pengolahan data hasil tes akademik. Tetap semangat dan pantau terus halaman ini berkala.";

            // Tampilkan hasil HANYA jika sudah masuk tahap pengumuman global (untuk mencegah kegaduhan/kebocoran data)
            if ($ppdb_status == 'pengumuman') {
                $is_lulus_langsung = (in_array($siswa['jalur_id'], [8, 10]) || (($siswa['jalur_id'] == 11) && ($siswa['status_tahfidz'] ?? '') == 'Lulus'));
                $status_is_ok = in_array($status, ['Terverifikasi', 'Diterima', 'Lulus']);

                if ($status_is_ok && $is_lulus_langsung || ($status == 'Diterima' || $status == 'Lulus')) {
                    $status_class = "text-success";
                    $status_bg = "bg-success bg-opacity-10";
                    $status_icon = "fa-award";
                    $status_text = "SELAMAT, ANANDA DINYATAKAN LULUS";
                    
                    if ($is_lulus_langsung) {
                        $nama_jalur = htmlspecialchars($siswa['nama_jalur'] ?? 'Jalur Prestasi');
                        $status_desc = "Alhamdulillah! Ananda dinyatakan <strong>LULUS SELEKSI ".strtoupper($nama_jalur)."</strong>. Selamat bergabung di keluarga besar MTsN 1 Kota Pekanbaru.";
                    } else {
                        $status_desc = get_setting('narasi_lulus_test_akademik', "Selamat, Ananda lulus tes akademik di MTsN 1 Kota Pekanbaru. Silakan melakukan daftar ulang sesuai jadwal yang telah ditentukan.");
                    }
                    
                    $status_desc .= "<br><br>" . get_setting('narasi_info_daftar_ulang', "Bagi Ananda yang lulus tes akademik, silakan melakukan daftar ulang pada hari Rabu – Jumat, 01 – 03 April 2026 pukul 08.00 – 15.00 WIB di MTsN 1 Kota Pekanbaru.");
                } elseif ($status == 'Ditolak' || $status == 'Tidak Lulus') {
                    $status_class = "text-danger";
                    $status_bg = "bg-danger bg-opacity-10";
                    $status_icon = "fa-times-circle";
                    $status_text = "MAAF, ANANDA TIDAK LULUS";
                    $status_desc = get_setting('narasi_tidak_lulus_test_akademik', "Mohon maaf, Ananda tidak lulus tes akademik di MTsN 1 Kota Pekanbaru.");
                }
            }
            ?>

            <div class="mb-4">
                <div class="<?= $status_bg ?> p-4 rounded-circle d-inline-flex align-items-center justify-content-center"
                    style="width: 120px; height: 120px;">
                    <i class="fas <?= $status_icon ?> <?= $status_class ?>" style="font-size: 4rem;"></i>
                </div>
            </div>

            <h3 class="fw-bold mb-2 <?= $status_class ?>">
                <?= $status_text ?>
            </h3>
            <p class="text-muted mx-auto mb-4" style="max-width: 600px;">
                <?= $status_desc ?>
            </p>

            
    </div>
</div>

<?php require_once 'layout_bottom.php'; ?>