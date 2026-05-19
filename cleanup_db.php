<?php
require_once 'includes/config.php';

// Security check: Only logged-in admin or local environment can run this script
$is_local = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1' || $_SERVER['HTTP_HOST'] === 'localhost');
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if (!$is_local && !$is_admin) {
    die("Akses ditolak. Hanya administrator atau akses lokal yang dapat menjalankan skrip ini.");
}

try {
    // Fetch all registration records
    $stmt = $pdo->query("SELECT id, no_pendaftaran, nama_lengkap, penghasilan_ayah, penghasilan_ibu FROM pendaftar");
    $students = $stmt->fetchAll();

    $updated_count = 0;
    $details = [];

    // Helper to decode recursively double/triple encoded HTML entities
    function decode_db_entities($str) {
        if (!$str) return '';
        $decoded = htmlspecialchars_decode($str, ENT_QUOTES);
        while ($decoded !== $str) {
            $str = $decoded;
            $decoded = htmlspecialchars_decode($str, ENT_QUOTES);
        }
        return $decoded;
    }

    foreach ($students as $s) {
        $old_ayah = $s['penghasilan_ayah'];
        $old_ibu = $s['penghasilan_ibu'];
        
        $new_ayah = decode_db_entities($old_ayah);
        $new_ibu = decode_db_entities($old_ibu);
        
        // If there's a difference, update the database record
        if ($new_ayah !== $old_ayah || $new_ibu !== $old_ibu) {
            $update = $pdo->prepare("UPDATE pendaftar SET penghasilan_ayah = ?, penghasilan_ibu = ? WHERE id = ?");
            $update->execute([$new_ayah, $new_ibu, $s['id']]);
            
            $details[] = [
                'no_pendaftaran' => $s['no_pendaftaran'],
                'nama' => $s['nama_lengkap'],
                'ayah_sebelum' => $old_ayah,
                'ayah_sesudah' => $new_ayah,
                'ibu_sebelum' => $old_ibu,
                'ibu_sesudah' => $new_ibu
            ];
            $updated_count++;
        }
    }

    // Output a premium-styled success page
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Database Cleanup - PMBM</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            body {
                background: #f4f7f6;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            .cleanup-card {
                border: none;
                border-radius: 20px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.05);
                background: white;
            }
            .icon-circle {
                width: 80px;
                height: 80px;
                background: #eefdf7;
                color: #2e7d32;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
            }
        </style>
    </head>
    <body>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card cleanup-card p-5 text-center mb-4">
                        <div class="icon-circle">
                            <i class="fas fa-database fa-3x"></i>
                        </div>
                        <h2 class="fw-bold text-success mb-3">Pembersihan Database Berhasil!</h2>
                        <p class="text-muted fs-5">Sistem telah memindai data pendaftar dan membersihkan karakter HTML ganda dari kolom penghasilan.</p>
                        
                        <div class="bg-light p-3 rounded-4 d-inline-block px-4 mx-auto my-3">
                            <span class="fs-4 fw-bold text-dark"><?= $updated_count ?></span>
                            <span class="text-muted"> Data Berhasil Diperbarui</span>
                        </div>
                    </div>

                    <?php if ($updated_count > 0): ?>
                        <div class="card cleanup-card p-4">
                            <h5 class="fw-bold mb-4"><i class="fas fa-list me-2 text-primary"></i>Rincian Data yang Diperbarui</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>No. Reg</th>
                                            <th>Nama Lengkap</th>
                                            <th>Penghasilan Ayah</th>
                                            <th>Penghasilan Ibu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($details as $detail): ?>
                                            <tr>
                                                <td><span class="badge bg-secondary"><?= htmlspecialchars($detail['no_pendaftaran']) ?></span></td>
                                                <td class="fw-bold"><?= htmlspecialchars($detail['nama']) ?></td>
                                                <td>
                                                    <?php if ($detail['ayah_sebelum'] !== $detail['ayah_sesudah']): ?>
                                                        <span class="text-danger text-decoration-line-through small"><?= htmlspecialchars($detail['ayah_sebelum']) ?></span>
                                                        <i class="fas fa-long-arrow-alt-right mx-2 text-success"></i>
                                                        <span class="text-success fw-bold"><?= htmlspecialchars($detail['ayah_sesudah']) ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted"><?= htmlspecialchars($detail['ayah_sesudah']) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($detail['ibu_sebelum'] !== $detail['ibu_sesudah']): ?>
                                                        <span class="text-danger text-decoration-line-through small"><?= htmlspecialchars($detail['ibu_sebelum']) ?></span>
                                                        <i class="fas fa-long-arrow-alt-right mx-2 text-success"></i>
                                                        <span class="text-success fw-bold"><?= htmlspecialchars($detail['ibu_sesudah']) ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted"><?= htmlspecialchars($detail['ibu_sesudah']) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
} catch (Exception $e) {
    echo "Gagal membersihkan database: " . $e->getMessage();
}
?>
