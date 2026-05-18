<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$message = "";

// Fetch Jalur Pendaftaran
$jalur_list = $pdo->query("SELECT * FROM jalur_pendaftaran ORDER BY nama_jalur ASC")->fetchAll();

// --- HANDLE GENERATE PASSWORDS ---
if (isset($_POST['generate_pass'])) {
    try {
        $stmt = $pdo->query("SELECT id FROM pendaftar");
        $count = 0;
        // Skip updating ujian_password column since it doesn't exist
        $count = 0;
        $message = "<div class='alert alert-info border-0 shadow-sm rounded-4'><i class='fas fa-info-circle me-2'></i> Fitur Generate Password dinonaktifkan untuk CBT Eksternal (Password standar: 26 + NISN).</div>";
    } catch (Exception $e) {
        $message = "<div class='alert alert-danger border-0 shadow-sm rounded-4'>Error: " . $e->getMessage() . "</div>";
    }
}

// --- HANDLE EXPORT CSV ---
if (isset($_GET['export'])) {
    try {
        $selected_jalurs = isset($_GET['jalur_ids']) ? $_GET['jalur_ids'] : [];

        // Clear any previous output buffers to avoid corrupted files
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=data_peserta_cbt_' . date('Ymd') . '.csv');

        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['No Pendaftaran', 'Nama Lengkap', 'NISN', 'Username', 'Password Ujian', 'Jalur']);

        // Base Query
        $sql = "SELECT p.no_pendaftaran, p.nama_lengkap, p.nisn, j.nama_jalur 
                FROM pendaftar p 
                LEFT JOIN jalur_pendaftaran j ON p.jalur_id = j.id 
                WHERE p.status IN ('Pending', 'Terverifikasi')";
        
        $params = [];
        if (!empty($selected_jalurs)) {
            $ids = [];
            $has_tahfidz_tl = false;
            foreach($selected_jalurs as $val) {
                if ($val === 'tahfidz_tidak_lulus') {
                    $has_tahfidz_tl = true;
                } else {
                    $ids[] = (int)$val;
                }
            }

            $conditions = [];
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $conditions[] = "p.jalur_id IN ($placeholders)";
                $params = $ids;
            }

            if ($has_tahfidz_tl) {
                $conditions[] = "(p.jalur_id = 11 AND p.status_tahfidz = 'Tidak Lulus')";
            }

            if (!empty($conditions)) {
                $sql .= " AND (" . implode(' OR ', $conditions) . ")";
            }
        }

        $sql .= " ORDER BY p.id ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        while ($row = $stmt->fetch()) {
            fputcsv($output, [
                $row['no_pendaftaran'],
                $row['nama_lengkap'],
                $row['nisn'],
                $row['no_pendaftaran'],
                '26' . $row['nisn'],
                $row['nama_jalur'] ?: 'Umum'
            ]);
        }
        fclose($output);
        exit();
    } catch (Exception $e) {
        die("Gagal Export: " . $e->getMessage());
    }
}

// --- HANDLE DOWNLOAD TEMPLATE ---
if (isset($_GET['template'])) {
    try {
        if (ob_get_level())
            ob_end_clean();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=template_isi_nilai_' . date('Ymd') . '.csv');

        $output = fopen('php://output', 'w');
        // Add UTF-8 BOM
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header
        fputcsv($output, ['No Pendaftaran', 'Nama Murid', 'NISN', 'Username', 'Nilai']);

        // Fetch Real Data (Pending & Verified)
        $sql = "SELECT no_pendaftaran, nama_lengkap, nisn FROM pendaftar WHERE status IN ('Pending', 'Terverifikasi')";
        $params = [];

        // Filter by Jenis Kelamin
        if (isset($_GET['jk']) && in_array($_GET['jk'], ['Laki-laki', 'Perempuan'])) {
            $sql .= " AND jenis_kelamin = ?";
            $params[] = $_GET['jk'];
        }
        
        $selected_jalurs = isset($_GET['jalur_ids']) ? $_GET['jalur_ids'] : [];
        if (!empty($selected_jalurs)) {
            $ids = [];
            $has_tahfidz_tl = false;
            foreach($selected_jalurs as $val) {
                if ($val === 'tahfidz_tidak_lulus') {
                    $has_tahfidz_tl = true;
                } else {
                    $ids[] = (int)$val;
                }
            }

            $conditions = [];
            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $conditions[] = "jalur_id IN ($placeholders)";
                $params = $ids;
            }

            if ($has_tahfidz_tl) {
                $conditions[] = "(jalur_id = 11 AND status_tahfidz = 'Tidak Lulus')";
            }

            if (!empty($conditions)) {
                $sql .= " AND (" . implode(' OR ', $conditions) . ")";
            }
        }

        $sql .= " ORDER BY id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        while ($row = $stmt->fetch()) {
            fputcsv($output, [
                $row['no_pendaftaran'],
                $row['nama_lengkap'],
                $row['nisn'],
                $row['no_pendaftaran'], // Username usually matches no_pendaftaran
                '' // Leave Nilai empty for the user to fill
            ]);
        }

        fclose($output);
        exit();
    } catch (Exception $e) {
        die("Gagal membuat template: " . $e->getMessage());
    }
}

// --- HANDLE IMPORT NILAI ---
if (isset($_POST['import_nilai']) && isset($_FILES['file_nilai'])) {
    try {
        $file = $_FILES['file_nilai']['tmp_name'];
        $handle = fopen($file, "r");

        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom != "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $count = 0;
        $header = fgetcsv($handle, 1000, ","); // Skip header

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($data) < 5)
                continue; // Basic validation

            $no_pendaftar = $data[0];
            $nilai = $data[4];

            $stmt = $pdo->prepare("UPDATE pendaftar SET nilai_ujian = ? WHERE no_pendaftaran = ?");
            $stmt->execute([$nilai, $no_pendaftar]);
            $count++;
        }
        fclose($handle);
        $message = "<div class='alert alert-info border-0 shadow-sm rounded-4'><i class='fas fa-info-circle me-2'></i> Berhasil meng-import $count data nilai ujian!</div>";
    } catch (Exception $e) {
        $message = "<div class='alert alert-danger border-0 shadow-sm rounded-4'>Gagal Import: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Integrasi CBT - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        .main-content {
            margin-left: 260px;
            padding: 30px;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .card-premium {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            background: #fff;
            padding: 30px;
        }

        .icon-shape {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content ps-5">
        <div class="container-fluid">
            <h2 class="text-primary fw-bold mb-1">Import Nilai CBT External</h2>
            <p class="text-muted mb-4">Halaman ini digunakan khusus untuk mengunggah hasil nilai ujian dari aplikasi CBT
                luar ke sistem PMBM.</p>

            <?= $message ?>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card card-premium h-100 opacity-75">
                        <div class="icon-shape bg-secondary bg-opacity-10 text-secondary">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h5 class="fw-bold text-muted">1. Eksport Data ke CBT (Non-Aktif)</h5>
                        <p class="text-muted small mb-4">Fitur ini dinonaktifkan karena menggunakan CBT eksternal.</p>

                        <button type="button"
                            onclick="alert('Mohon maaf karena menggunakan CBT eksternal menu ini tidak bisa di akses')"
                            class="btn btn-outline-secondary w-100 rounded-pill mb-2">
                            <i class="fas fa-key me-2"></i> Generate Password Ujian
                        </button>
                        <div class="form-text small text-center mb-3 text-muted">Generate password dinonaktifkan.</div>

                        <form method="GET" action="">
                            <input type="hidden" name="export" value="1">
                            <div class="mb-3 border rounded-4 p-3 bg-light bg-opacity-50">
                                <label class="form-label small fw-bold text-primary mb-2">
                                    <i class="fas fa-filter me-1"></i> Pilih Jalur Ujian:
                                </label>
                                <div class="row g-2">
                                    <?php foreach ($jalur_list as $jl): ?>
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="jalur_ids[]"
                                                    value="<?= $jl['id'] ?>" id="jalur_<?= $jl['id'] ?>" checked>
                                                <label class="form-check-label small text-muted" for="jalur_<?= $jl['id'] ?>">
                                                    <?= htmlspecialchars($jl['nama_jalur']) ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="jalur_ids[]"
                                                value="tahfidz_tidak_lulus" id="jalur_tahfidz_tl">
                                            <label class="form-check-label small text-danger fw-bold" for="jalur_tahfidz_tl">
                                                Jalur Tahfidz (Tdk Lulus)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 shadow-sm">
                                <i class="fas fa-download me-2"></i> Download Data Peserta (CSV)
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-premium h-100">
                        <div class="icon-shape bg-success-subtle text-success">
                            <i class="fas fa-file-import"></i>
                        </div>
                        <h5 class="fw-bold">2. Import Nilai dari CBT</h5>
                        <p class="text-muted small mb-3">Masukkan kembali hasil nilai ujian dari aplikasi CBT dalam
                            format CSV.</p>

                        <div class="mb-3">
                            <form method="GET" action="">
                                <input type="hidden" name="template" value="1">
                                <div class="mb-3 border rounded-4 p-3 bg-light bg-opacity-50 text-start">
                                    <label class="form-label small fw-bold text-success mb-2">
                                        <i class="fas fa-filter me-1"></i> Saring Data Template:
                                    </label>
                                    <div class="row g-2">
                                        <?php foreach ($jalur_list as $jl): ?>
                                            <div class="col-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="jalur_ids[]"
                                                        value="<?= $jl['id'] ?>" id="tpl_jalur_<?= $jl['id'] ?>" checked>
                                                    <label class="form-check-label small text-muted" for="tpl_jalur_<?= $jl['id'] ?>">
                                                        <?= htmlspecialchars($jl['nama_jalur']) ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="jalur_ids[]"
                                                    value="tahfidz_tidak_lulus" id="tpl_jalur_tahfidz_tl">
                                                <label class="form-check-label small text-danger fw-bold" for="tpl_jalur_tahfidz_tl">
                                                    Jalur Tahfidz (Tdk Lulus)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-4 w-100 py-2">
                                    <i class="fas fa-file-download me-1"></i> Download Template CSV Terpilih
                                </button>
                            </form>
                        </div>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">File Hasil Nilai (CSV)</label>
                                <input type="file" name="file_nilai" class="form-control rounded-3" required>
                                <div class="form-text small mt-2">Format: No_Daftar, Nama, NISN, Username, <b>Nilai</b>
                                </div>
                            </div>
                            <button type="submit" name="import_nilai" class="btn btn-success w-100 rounded-pill py-2">
                                <i class="fas fa-upload me-2"></i> Mulai Import Nilai
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card card-premium border-0">
                        <h5 class="fw-bold mb-4">Preview Status Akun Ujian</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light text-muted small">
                                    <tr>
                                        <th>Nama Murid</th>
                                        <th>Username (No. Daftar)</th>
                                        <th>Password Ujian</th>
                                        <th>Nilai Ujian</th>
                                        <th>Status Akun</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    try {
                                        $prevQuery = "SELECT p.nama_lengkap, p.no_pendaftaran, p.nilai_ujian, p.nisn 
                                                      FROM pendaftar p 
                                                      WHERE p.status IN ('Pending', 'Terverifikasi') 
                                                      ORDER BY p.id DESC LIMIT 10";
                                        $prev = $pdo->query($prevQuery)->fetchAll();
                                        foreach ($prev as $p):
                                            ?>
                                            <tr>
                                                <td class="fw-bold"><?= htmlspecialchars($p['nama_lengkap']) ?></td>
                                                <td><code><?= htmlspecialchars($p['no_pendaftaran']) ?></code></td>
                                                <td><span
                                                        class="badge bg-light text-dark fw-normal">26<?= htmlspecialchars($p['nisn']) ?></span>
                                                </td>
                                                <td class="text-primary fw-bold"><?= $p['nilai_ujian'] ?: '0' ?></td>
                                                <td>
                                                    <span class="badge bg-success-subtle text-success rounded-pill px-3">Siap
                                                        Eksport</span>
                                                </td>
                                            </tr>
                                        <?php endforeach;
                                    } catch (Exception $e) {
                                        echo "<tr><td colspan='5' class='text-center text-danger'>Gagal memuat data: " . $e->getMessage() . "</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>