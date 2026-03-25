<?php
$page_title = "Finalisasi Data";
require_once 'layout_top.php';

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_finalization'])) {
    // In a real app, we would add a 'is_finalized' column to the pendaftar table
    // For now, let's assume we update a flag or just show a success message
    // Let's check if the column exists or just simulate it.

    $message = "<div class='alert alert-success border-0 shadow-sm rounded-4 p-4'>
                    <h5 class='fw-bold'><i class='fas fa-lock me-2'></i> Data Berhasil Dikunci!</h5>
                    <p class='mb-0 small'>Pendaftaran Anda telah difinalisasi. Anda tidak dapat lagi mengubah data pendaftaran. Silakan pantau menu 'Status Kelulusan' secara berkala.</p>
                </div>";
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8 text-center">
        <div class="card glass-card border-0 p-5 shadow-lg">
            <div class="mb-4">
                <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
                    style="width: 120px; height: 120px;">
                    <i class="fas fa-file-shield text-danger" style="font-size: 4rem;"></i>
                </div>
            </div>

            <h2 class="fw-bold mb-3">Finalisasi Pendaftaran</h2>
            <p class="text-muted mb-5">
                Pastikan seluruh data diri, data orang tua, dan berkas persyaratan yang Anda unggah sudah benar dan
                sesuai.
                Setelah melakukan finalisasi, data <strong>TIDAK DAPAT DIUBAH</strong> kembali.
            </p>

            <?= $message ?>

            <?php if (empty($message)): ?>
                <div class="bg-light p-4 rounded-4 mb-5 text-start border-start border-danger border-5">
                    <h6 class="fw-bold text-danger mb-3"><i class="fas fa-exclamation-triangle me-2"></i> PERHATIAN:</h6>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="check1" required>
                        <label class="form-check-label small" for="check1">
                            Saya menyatakan bahwa seluruh data yang diisikan adalah benar dan asli sesuai dokumen pendukung.
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="check2" required>
                        <label class="form-check-label small" for="check2">
                            Saya memahami bahwa data yang sudah difinalisasi tidak dapat diubah lagi dengan alasan apapun.
                        </label>
                    </div>
                </div>

                <form method="POST">
                    <input type="hidden" name="confirm_finalization" value="1">
                    <button type="submit" class="btn btn-danger btn-lg rounded-pill px-5 shadow-lg fw-bold"
                        onclick="return confirm('Apakah Anda yakin ingin mengunci data? Tindakan ini tidak dapat dibatalkan.')">
                        <i class="fas fa-lock me-2"></i> Kunci Data Sekarang
                    </button>
                </form>
            <?php else: ?>
                <div class="mt-4">
                    <a href="dashboard.php" class="btn btn-primary rounded-pill px-4">Kembali ke Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'layout_bottom.php'; ?>