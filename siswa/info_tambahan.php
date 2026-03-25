<?php
$page_title = "Informasi Tambahan";
require_once 'layout_top.php';

// Security Check: Only allow if not verified/accepted and PPDB is open
$ppdb_status = get_setting('ppdb_status', 'tutup');
$status = $siswa['status'] ?? 'Pending';
if ($status == 'Terverifikasi' || $status == 'Diterima' || $ppdb_status != 'buka') {
    header("Location: dashboard.php");
    exit();
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sql = "UPDATE pendaftar SET 
            npsn_sekolah = ?, 
            email = ?, 
            no_hp_siswa = ?
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $_POST['npsn_sekolah'],
        $_POST['email'],
        $_POST['no_hp_siswa'],
        $_SESSION['siswa_id']
    ]);

    if ($result) {
        $message = "<div class='alert alert-success border-0 shadow-sm rounded-4'><i class='fas fa-check-circle me-2'></i> Informasi tambahan berhasil diperbarui!</div>";
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM pendaftar WHERE id = ?");
        $stmt->execute([$_SESSION['siswa_id']]);
        $siswa = $stmt->fetch();
    } else {
        $message = "<div class='alert alert-danger border-0 shadow-sm rounded-4'>Gagal memperbarui data.</div>";
    }
}
?>

<div class="row">
    <div class="col-lg-7">
        <div class="card glass-card border-0 p-4">
            <h5 class="fw-bold mb-4">Lengkapi Data Pendukung</h5>

            <?= $message ?>

            <form method="POST">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">NPSN SEKOLAH ASAL (SD/MI)</label>
                        <input type="text" name="npsn_sekolah" class="form-control"
                            value="<?= htmlspecialchars($siswa['npsn_sekolah']) ?>"
                            placeholder="Masukan NPSN Sekolah Asal">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">ALAMAT EMAIL (Aktif)</label>
                        <input type="email" name="email" class="form-control"
                            value="<?= htmlspecialchars($siswa['email']) ?>" placeholder="contoh@email.com">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">NO. HP MURID (Jika ada)</label>
                        <input type="text" name="no_hp_siswa" class="form-control"
                            value="<?= htmlspecialchars($siswa['no_hp_siswa']) ?>" placeholder="08xxxxxxxxxx">
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm">
                            <i class="fas fa-save me-2"></i> Update Informasi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card glass-card border-0 p-4 bg-info bg-opacity-10 text-dark mb-4">
            <h6 class="fw-bold mb-3"><i class="fas fa-certificate me-2 text-info"></i> Prestasi (Optional)</h6>
            <p class="small opacity-75">Jika Anda memiliki sertifikat prestasi (Akademik/Non-Akademik), silakan bawa
                sertifikat asli saat proses wawancara/verifikasi berkas fisik.</p>
        </div>

        <div class="card glass-card border-0 p-4">
            <h6 class="fw-bold mb-3"><i class="fas fa-question-circle me-2 text-warning"></i> Mengapa data ini penting?
            </h6>
            <p class="small text-muted mb-0">NPSN Sekolah asal digunakan untuk sinkronisasi data dengan Dapodik/EMIS.
                Email dan No HP digunakan untuk notifikasi pengumuman penting.</p>
        </div>
    </div>
</div>

<?php require_once 'layout_bottom.php'; ?>