<?php
$page_title = "Identitas Orang Tua / Wali";
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
            nama_ayah = ?, nik_ayah = ?, pekerjaan_ayah = ?, penghasilan_ayah = ?, no_hp_ayah = ?,
            nama_ibu = ?, nik_ibu = ?, pekerjaan_ibu = ?, no_hp_ibu = ?
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $_POST['nama_ayah'],
        $_POST['nik_ayah'],
        $_POST['pekerjaan_ayah'],
        $_POST['penghasilan_ayah'],
        $_POST['no_hp_ayah'],
        $_POST['nama_ibu'],
        $_POST['nik_ibu'],
        $_POST['pekerjaan_ibu'],
        $_POST['no_hp_ibu'],
        $_SESSION['siswa_id']
    ]);

    if ($result) {
        $message = "<div class='alert alert-success border-0 shadow-sm rounded-4'><i class='fas fa-check-circle me-2'></i> Data orang tua berhasil diperbarui!</div>";
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
    <div class="col-12 mb-4">
        <?= $message ?>
    </div>

    <form method="POST" class="row g-4">
        <!-- Informasi Ayah -->
        <div class="col-lg-6">
            <div class="card glass-card border-0 p-4 h-100">
                <div class="d-flex align-items-center mb-4 text-primary">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-user-tie fs-5"></i>
                    </div>
                    <h5 class="fw-bold mb-0">Informasi Ayah Kandung</h5>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">NAMA AYAH <span
                                class="text-danger">*</span></label>
                        <input type="text" name="nama_ayah" class="form-control"
                            value="<?= htmlspecialchars($siswa['nama_ayah']) ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">NIK AYAH (16 Digit) <span
                                class="text-danger">*</span></label>
                        <input type="text" name="nik_ayah" class="form-control"
                            value="<?= htmlspecialchars($siswa['nik_ayah']) ?>" maxlength="16" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">PEKERJAAN <span
                                class="text-danger">*</span></label>
                        <select name="pekerjaan_ayah" class="form-select" required>
                            <option value="PNS" <?= $siswa['pekerjaan_ayah'] == 'PNS' ? 'selected' : '' ?>>PNS</option>
                            <option value="TNI/Polri" <?= $siswa['pekerjaan_ayah'] == 'TNI/Polri' ? 'selected' : '' ?>>
                                TNI/Polri</option>
                            <option value="Wiraswasta" <?= $siswa['pekerjaan_ayah'] == 'Wiraswasta' ? 'selected' : '' ?>>
                                Wiraswasta</option>
                            <option value="Petani" <?= $siswa['pekerjaan_ayah'] == 'Petani' ? 'selected' : '' ?>>Petani
                            </option>
                            <option value="Buruh" <?= $siswa['pekerjaan_ayah'] == 'Buruh' ? 'selected' : '' ?>>Buruh
                            </option>
                            <option value="Lainnya" <?= $siswa['pekerjaan_ayah'] == 'Lainnya' ? 'selected' : '' ?>>Lainnya
                            </option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">PENGHASILAN <span
                                class="text-danger">*</span></label>
                        <select name="penghasilan_ayah" class="form-select" required>
                            <option value="< 1 Juta" <?= $siswa['penghasilan_ayah'] == '< 1 Juta' ? 'selected' : '' ?>>
                                < 1 Juta </option>
                            <option value="1-3 Juta" <?= $siswa['penghasilan_ayah'] == '1-3 Juta' ? 'selected' : '' ?>> 1 -
                                3 Juta </option>
                            <option value="3-5 Juta" <?= $siswa['penghasilan_ayah'] == '3-5 Juta' ? 'selected' : '' ?>> 3 -
                                5 Juta </option>
                            <option value="> 5 Juta" <?= $siswa['penghasilan_ayah'] == '> 5 Juta' ? 'selected' : '' ?>> > 5
                                Juta </option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">NO. HP / WHATSAPP <span
                                class="text-danger">*</span></label>
                        <input type="text" name="no_hp_ayah" class="form-control"
                            value="<?= htmlspecialchars($siswa['no_hp_ayah']) ?>" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informasi Ibu -->
        <div class="col-lg-6">
            <div class="card glass-card border-0 p-4 h-100">
                <div class="d-flex align-items-center mb-4 text-danger">
                    <div class="bg-danger bg-opacity-10 p-2 rounded-circle me-3">
                        <i class="fas fa-user-nurse fs-5"></i>
                    </div>
                    <h5 class="fw-bold mb-0">Informasi Ibu Kandung</h5>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">NAMA IBU <span
                                class="text-danger">*</span></label>
                        <input type="text" name="nama_ibu" class="form-control"
                            value="<?= htmlspecialchars($siswa['nama_ibu']) ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">NIK IBU (16 Digit) <span
                                class="text-danger">*</span></label>
                        <input type="text" name="nik_ibu" class="form-control"
                            value="<?= htmlspecialchars($siswa['nik_ibu']) ?>" maxlength="16" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">PEKERJAAN <span
                                class="text-danger">*</span></label>
                        <select name="pekerjaan_ibu" class="form-select" required>
                            <option value="IRT" <?= $siswa['pekerjaan_ibu'] == 'IRT' ? 'selected' : '' ?>>Ibu Rumah Tangga
                            </option>
                            <option value="PNS" <?= $siswa['pekerjaan_ibu'] == 'PNS' ? 'selected' : '' ?>>PNS</option>
                            <option value="Wiraswasta" <?= $siswa['pekerjaan_ibu'] == 'Wiraswasta' ? 'selected' : '' ?>>
                                Wiraswasta</option>
                            <option value="Lainnya" <?= $siswa['pekerjaan_ibu'] == 'Lainnya' ? 'selected' : '' ?>>Lainnya
                            </option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">NO. HP / WHATSAPP <span
                                class="text-danger">*</span></label>
                        <input type="text" name="no_hp_ibu" class="form-control"
                            value="<?= htmlspecialchars($siswa['no_hp_ibu']) ?>" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 text-center mt-5 mb-5">
            <button type="submit" class="btn btn-success btn-lg px-5 rounded-pill shadow-lg">
                <i class="fas fa-save me-2"></i> Simpan Data Orang Tua
            </button>
        </div>
    </form>
</div>

<?php require_once 'layout_bottom.php'; ?>