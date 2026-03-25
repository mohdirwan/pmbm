<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

// Fitur ini hanya untuk perbaikan manual oleh admin
$success_msg = "";
$error_msg = "";

if (isset($_POST['fix_id'])) {
    $id = intval($_POST['fix_id']);
    try {
        // Ambil data lama
        $stmt = $pdo->prepare("SELECT file_akta, file_pakta FROM pendaftar WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if ($data && !empty($data['file_akta']) && empty($data['file_pakta'])) {
            // Pindahkan file_akta ke file_pakta
            $update = $pdo->prepare("UPDATE pendaftar SET file_pakta = ?, file_akta = NULL WHERE id = ?");
            $update->execute([$data['file_akta'], $id]);
            $success_msg = "Siswa ID $id berhasil diperbaiki (File dipindahkan ke Pakta Integritas).";
        }
    } catch (Exception $e) {
        $error_msg = "Gagal memperbaiki: " . $e->getMessage();
    }
}

// Ambil daftar siswa yang terindikasi tertukar
// Kriteria: file_akta ada isinya, file_pakta kosong, dan jalurnya mengandung kata 'Pakta' dalam syarat
$query = "SELECT p.id, p.no_pendaftaran, p.nama_lengkap, p.file_akta, p.file_pakta, j.nama_jalur 
          FROM pendaftar p 
          JOIN jalur_pendaftaran j ON p.jalur_id = j.id 
          WHERE (p.file_akta IS NOT NULL AND p.file_akta != '') 
          AND (p.file_pakta IS NULL OR p.file_pakta = '') 
          AND j.syarat LIKE '%pakta%'";
$candidates = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Debug Tool - Fix File Mapping</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f4f7f6;
            padding: 40px 0;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="mb-4">
                    <a href="index.php" class="btn btn-outline-secondary btn-sm"><i
                            class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard</a>
                </div>

                <div class="card p-4">
                    <h4 class="fw-bold text-primary mb-3"><i class="fas fa-tools me-2"></i>Debug Tool: Perbaikan Pakta
                        Integritas Tertukar</h4>
                    <p class="text-muted small">Halaman ini menampilkan siswa yang terdeteksi memiliki file di kolom
                        <b>Akta</b> tetapi kosong di kolom <b>Pakta</b> (pada jalur yang mewajibkan Pakta Integritas).
                        Ini biasanya terjadi karena bug keyword matching yang sudah kita perbaiki tadi.</p>

                    <?php if ($success_msg): ?>
                        <div class="alert alert-success">
                            <?= $success_msg ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($error_msg): ?>
                        <div class="alert alert-danger">
                            <?= $error_msg ?>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Pendaftaran</th>
                                    <th>Nama Lengkap</th>
                                    <th>Jalur</th>
                                    <th>Status File</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($candidates)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">Tidak ada data yang terindikasi tertukar.
                                            Semua sudah aman!</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($candidates as $c): ?>
                                        <tr>
                                            <td><code><?= $c['no_pendaftaran'] ?></code></td>
                                            <td class="fw-bold">
                                                <?= $c['nama_lengkap'] ?>
                                            </td>
                                            <td><span class="badge bg-info text-dark">
                                                    <?= $c['nama_jalur'] ?>
                                                </span></td>
                                            <td>
                                                <div class="small">
                                                    <span class="text-danger">Di Akta:</span>
                                                    <?= $c['file_akta'] ?><br>
                                                    <span class="text-muted">Di Pakta:</span> (Kosong)
                                                </div>
                                            </td>
                                            <td>
                                                <form method="POST"
                                                    onsubmit="return confirm('Pindahkan file ini ke kolom Pakta Integritas dan kosongkan Akta?')">
                                                    <input type="hidden" name="fix_id" value="<?= $c['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-warning rounded-pill">
                                                        <i class="fas fa-sync-alt me-1"></i> Perbaiki
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 alert alert-warning">
                    <h6 class="fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Perhatian:</h6>
                    <ul class="mb-0 small">
                        <li>Gunakan tombol "Perbaiki" hanya jika Anda yakin file yang ada di kolom Akta tersebut adalah
                            Pakta Integritas.</li>
                        <li>Siswa yang memang sudah mengupload keduanya (Akta dan Pakta) tidak akan muncul di sini.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>

</html>