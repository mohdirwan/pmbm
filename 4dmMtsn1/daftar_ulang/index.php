<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

// Handle Action
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];

    if ($action == 'confirm') {
        $stmt = $pdo->prepare("UPDATE pendaftar SET status_daftar_ulang = 'Sudah', tanggal_daftar_ulang = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Murid berhasil dikonfirmasi daftar ulang.";
    } elseif ($action == 'cancel') {
        $stmt = $pdo->prepare("UPDATE pendaftar SET status_daftar_ulang = 'Belum', tanggal_daftar_ulang = NULL WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Status daftar ulang dibatalkan.";
    }

    header("Location: index.php?msg=" . urlencode($msg));
    exit();
}

$search = $_GET['search'] ?? '';

// Query Data (Only Accepted Students)
$query = "SELECT p.*, j.nama_jalur 
          FROM pendaftar p 
          LEFT JOIN jalur_pendaftaran j ON p.jalur_id = j.id 
          WHERE p.status = 'Diterima'";

$params = [];

if ($search) {
    $query .= " AND (p.nama_lengkap LIKE ? OR p.no_pendaftaran LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY p.status_daftar_ulang DESC, p.nama_lengkap ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

// Statistics
$total_accepted = $pdo->query("SELECT COUNT(*) FROM pendaftar WHERE status = 'Diterima'")->fetchColumn();
$total_reregistered = $pdo->query("SELECT COUNT(*) FROM pendaftar WHERE status = 'Diterima' AND status_daftar_ulang = 'Sudah'")->fetchColumn();
$total_pending = $total_accepted - $total_reregistered;

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Manajemen Daftar Ulang - Admin PMBM</title>
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

        .card-stat {
            border: none;
            border-radius: 15px;
            transition: all 0.3s;
        }

        .card-stat:hover {
            transform: translateY(-5px);
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4 text-primary fw-bold"><i class="fas fa-user-check me-2"></i>Manajemen Daftar Ulang</h2>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_GET['msg']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card card-stat bg-primary text-white p-3 h-100">
                        <div class="d-flex align-items-center">
                            <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3">
                                <i class="fas fa-user-graduate fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-uppercase mb-1 opacity-75 small fw-bold">Total Diterima</h6>
                                <h3 class="mb-0 fw-bold"><?= $total_accepted ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-stat bg-success text-white p-3 h-100">
                        <div class="d-flex align-items-center">
                            <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3">
                                <i class="fas fa-check-double fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-uppercase mb-1 opacity-75 small fw-bold">Sudah Daftar Ulang</h6>
                                <h3 class="mb-0 fw-bold"><?= $total_reregistered ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-stat bg-warning text-dark p-3 h-100">
                        <div class="d-flex align-items-center">
                            <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3">
                                <i class="fas fa-clock fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-uppercase mb-1 opacity-75 small fw-bold">Belum Daftar Ulang</h6>
                                <h3 class="mb-0 fw-bold"><?= $total_pending ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0 fw-bold">Data Murid Lulus Seleksi</h5>
                        </div>
                        <div class="col-md-6">
                            <form method="GET" class="d-flex">
                                <input type="text" name="search" class="form-control rounded-pill me-2"
                                    placeholder="Cari Nama / No. Pendaftaran..."
                                    value="<?= htmlspecialchars($search) ?>">
                                <button type="submit" class="btn btn-primary rounded-pill"><i
                                        class="fas fa-search"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">No. Pendaftaran</th>
                                <th>Nama Murid</th>
                                <th>Jalur Masuk</th>
                                <th>Status DU</th>
                                <th>Tanggal DU</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($students) > 0): ?>
                                <?php foreach ($students as $s): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary"><?= $s['no_pendaftaran'] ?></td>
                                        <td>
                                            <div class="fw-bold"><?= $s['nama_lengkap'] ?></div>
                                            <div class="small text-muted"><?= $s['nisn'] ?></div>
                                        </td>
                                        <td><span
                                                class="badge bg-info bg-opacity-10 text-info rounded-pill"><?= $s['nama_jalur'] ?></span>
                                        </td>
                                        <td>
                                            <?php if ($s['status_daftar_ulang'] == 'Sudah'): ?>
                                                <span class="badge bg-success rounded-pill px-3"><i class="fas fa-check me-1"></i>
                                                    Sudah</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark rounded-pill px-3"><i
                                                        class="fas fa-clock me-1"></i> Belum</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= $s['tanggal_daftar_ulang'] ? date('d/m/Y H:i', strtotime($s['tanggal_daftar_ulang'])) : '-' ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <?php if ($s['status_daftar_ulang'] == 'Belum'): ?>
                                                <a href="index.php?action=confirm&id=<?= $s['id'] ?>"
                                                    class="btn btn-sm btn-success rounded-pill px-3"
                                                    onclick="return confirm('Konfirmasi daftar ulang murid ini?')">
                                                    <i class="fas fa-check"></i> Konfirmasi
                                                </a>
                                            <?php else: ?>
                                                <a href="index.php?action=cancel&id=<?= $s['id'] ?>"
                                                    class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                    onclick="return confirm('Batalkan status daftar ulang?')">
                                                    <i class="fas fa-times"></i> Batal
                                                </a>
                                            <?php endif; ?>
                                            <a href="../pendaftar/detail_pendaftar.php?id=<?= $s['id'] ?>"
                                                class="btn btn-sm btn-light text-primary rounded-circle" title="Detail"><i
                                                    class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">Tidak ada murid yang ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>