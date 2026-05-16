<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$success_msg = '';
$error_msg = '';

// --- HANDLE POST ACTIONS ---
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'add_room') {
        $nama = clean_input($_POST['nama_ruangan']);
        $kapasitas = (int)$_POST['kapasitas_pc'];
        try {
            $stmt = $pdo->prepare("INSERT INTO ujian_ruangan (nama_ruangan, kapasitas_pc) VALUES (?, ?)");
            $stmt->execute([$nama, $kapasitas]);
            $success_msg = "Ruangan berhasil ditambahkan!";
        } catch (Exception $e) { $error_msg = "Gagal: " . $e->getMessage(); }
    } elseif ($_POST['action'] == 'edit_room') {
        $id = (int)$_POST['id'];
        $nama = clean_input($_POST['nama_ruangan']);
        $kapasitas = (int)$_POST['kapasitas_pc'];
        try {
            $stmt = $pdo->prepare("UPDATE ujian_ruangan SET nama_ruangan = ?, kapasitas_pc = ? WHERE id = ?");
            $stmt->execute([$nama, $kapasitas, $id]);
            $success_msg = "Ruangan berhasil diperbarui!";
        } catch (Exception $e) { $error_msg = "Gagal: " . $e->getMessage(); }
    } elseif ($_POST['action'] == 'delete_room') {
        $id = (int)$_POST['id'];
        $pdo->prepare("DELETE FROM ujian_ruangan WHERE id = ?")->execute([$id]);
        $success_msg = "Ruangan berhasil dihapus!";
    } elseif ($_POST['action'] == 'add_sesi') {
        $nama = clean_input($_POST['nama_sesi']);
        $mulai = $_POST['waktu_mulai'];
        $selesai = $_POST['waktu_selesai'];
        try {
            $stmt = $pdo->prepare("INSERT INTO ujian_sesi (nama_sesi, waktu_mulai, waktu_selesai) VALUES (?, ?, ?)");
            $stmt->execute([$nama, $mulai, $selesai]);
            $success_msg = "Sesi berhasil ditambahkan!";
        } catch (Exception $e) { $error_msg = "Gagal: " . $e->getMessage(); }
    } elseif ($_POST['action'] == 'delete_sesi') {
        $id = (int)$_POST['id'];
        $pdo->prepare("DELETE FROM ujian_sesi WHERE id = ?")->execute([$id]);
        $success_msg = "Sesi berhasil dihapus!";
    }
}

// Fetch Rooms & Sessions
$rooms = $pdo->query("SELECT * FROM ujian_ruangan ORDER BY nama_ruangan ASC")->fetchAll();
$sessions = $pdo->query("SELECT * FROM ujian_sesi ORDER BY waktu_mulai ASC")->fetchAll();
$jml_sesi = count($sessions);

// Calculate Totals
$total_pc_per_sesi = 0;
foreach ($rooms as $r) {
    $total_pc_per_sesi += (int)$r['kapasitas_pc'];
}
$total_kapasitas_harian = $total_pc_per_sesi * $jml_sesi;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Ruangan & Sesi - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .main-content { margin-left: 260px; padding: 30px; background: #f8f9fa; min-height: 100vh; }
        .card-premium { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); background: #fff; margin-bottom: 25px; }
        .table-custom thead { background: #f1f5f9; }
        .badge-capacity { background: #e0f2fe; color: #0369a1; font-weight: 700; border-radius: 10px; padding: 8px 15px; }
        .btn-action { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; }
        .btn-edit-room { background: #f0f9ff; color: #0ea5e9; border: 1px solid #e0f2fe; }
        .btn-edit-room:hover { background: #0ea5e9; color: #fff; }
        .btn-delete { background: #fef2f2; color: #ef4444; border: 1px solid #fee2e2; }
        .btn-delete:hover { background: #ef4444; color: #fff; }
        .stats-card { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: white; border-radius: 20px; padding: 20px; position: relative; overflow: hidden; }
        .stats-card i { position: absolute; right: -10px; bottom: -10px; font-size: 5rem; opacity: 0.1; }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content ps-5">
        <div class="container-fluid">
            <h2 class="text-primary fw-bold mb-1">Pengaturan Ruangan & Sesi Ujian</h2>
            <p class="text-muted mb-4">Kelola kapasitas laboratorium dan pembagian sesi ujian secara spesifik.</p>

            <?php if ($success_msg): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= $success_msg ?> 
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= $error_msg ?> 
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Ringkasan Kapasitas -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="stats-card shadow-sm">
                        <i class="fas fa-desktop"></i>
                        <h6 class="text-uppercase fw-bold opacity-75 small">Total Kapasitas Per Sesi</h6>
                        <h2 class="fw-bold mb-0"><?= $total_pc_per_sesi ?> <small class="fs-6 fw-normal">Siswa</small></h2>
                        <p class="mb-0 mt-2 small opacity-75">Gabungan dari seluruh laboratorium yang aktif.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stats-card shadow-sm" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="fas fa-users"></i>
                        <h6 class="text-uppercase fw-bold opacity-75 small">Total Kapasitas Harian</h6>
                        <h2 class="fw-bold mb-0"><?= $total_kapasitas_harian ?> <small class="fs-6 fw-normal">Siswa / Hari</small></h2>
                        <p class="mb-0 mt-2 small opacity-75"><?= $total_pc_per_sesi ?> siswa × <?= $jml_sesi ?> sesi ujian.</p>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- 1. Manajemen Ruangan (Labor) -->
                <div class="col-lg-7">
                    <div class="card card-premium p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="fw-bold mb-0">Daftar Laboratorium / Ruangan</h5>
                                <small class="text-muted">Kapasitas dihitung berdasarkan jumlah PC tersedia.</small>
                            </div>
                            <button class="btn btn-sm btn-success rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddRoom">
                                <i class="fas fa-plus me-1"></i> Tambah Labor
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-custom align-middle">
                                <thead>
                                    <tr>
                                        <th>Nama Labor</th>
                                        <th class="text-center">Kapasitas PC</th>
                                        <th class="text-center">Total Kapasitas</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    foreach ($rooms as $r): 
                                        $final_kap = $r['kapasitas_pc'] * $jml_sesi;
                                    ?>
                                    <tr>
                                        <td class="fw-bold text-dark">
                                            <i class="fas fa-door-open text-primary me-2 opacity-50"></i><?= htmlspecialchars($r['nama_ruangan']) ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-semibold text-secondary"><?= $r['kapasitas_pc'] ?></span> <small class="text-muted">PC</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge-capacity"><?= $final_kap ?> Siswa/Hari</span>
                                            <br><small class="text-muted" style="font-size: 0.7rem;">(<?= $r['kapasitas_pc'] ?>/sesi × <?= $jml_sesi ?> sesi)</small>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                <button class="btn-action btn-edit-room" 
                                                        onclick="editRoom(<?= $r['id'] ?>, '<?= addslashes($r['nama_ruangan']) ?>', <?= $r['kapasitas_pc'] ?>)"
                                                        title="Edit Ruangan">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" onsubmit="return confirm('Hapus ruangan ini?')" class="d-inline">
                                                    <input type="hidden" name="action" value="delete_room">
                                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                    <button type="submit" class="btn-action btn-delete" title="Hapus Ruangan">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($rooms)): ?>
                                        <tr><td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fas fa-info-circle mb-2 d-block fa-2x opacity-25"></i>
                                            Belum ada data ruangan.
                                        </td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 2. Manajemen Sesi -->
                <div class="col-lg-5">
                    <div class="card card-premium p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="fw-bold mb-0">Pembagian Sesi Ujian</h5>
                                <small class="text-muted">Total sesi yang aktif saat ini.</small>
                            </div>
                            <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddSesi">
                                <i class="fas fa-plus me-1"></i> Tambah Sesi
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-custom align-middle">
                                <thead>
                                    <tr>
                                        <th>Nama Sesi</th>
                                        <th class="text-center">Waktu</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sessions as $s): ?>
                                    <tr>
                                        <td class="fw-bold text-dark">
                                            <i class="far fa-clock text-info me-2 opacity-50"></i><?= htmlspecialchars($s['nama_sesi']) ?>
                                        </td>
                                        <td class="text-center small">
                                            <span class="badge bg-light text-dark border">
                                                <?= date('H:i', strtotime($s['waktu_mulai'])) ?> - <?= date('H:i', strtotime($s['waktu_selesai'])) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" onsubmit="return confirm('Hapus sesi ini?')" class="d-inline">
                                                <input type="hidden" name="action" value="delete_sesi">
                                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                                <button type="submit" class="btn-action btn-delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if(empty($sessions)): ?>
                                        <tr><td colspan="3" class="text-center py-5 text-muted">
                                            <i class="fas fa-info-circle mb-2 d-block fa-2x opacity-25"></i>
                                            Belum ada data sesi.
                                        </td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Add Room -->
    <div class="modal fade" id="modalAddRoom" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form method="POST">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold">Tambah Ruangan / Labor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" value="add_room">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Laboratorium</label>
                            <input type="text" name="nama_ruangan" class="form-control rounded-3" placeholder="Contoh: Labor Komputer 1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kapasitas PC (Unit)</label>
                            <input type="number" name="kapasitas_pc" class="form-control rounded-3" placeholder="Jumlah komputer yang tersedia" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="submit" class="btn btn-success w-100 rounded-pill py-2 fw-bold">Simpan Ruangan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Room -->
    <div class="modal fade" id="modalEditRoom" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form method="POST">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold">Edit Ruangan / Labor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" value="edit_room">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Laboratorium</label>
                            <input type="text" name="nama_ruangan" id="edit_nama" class="form-control rounded-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kapasitas PC (Unit)</label>
                            <input type="number" name="kapasitas_pc" id="edit_kapasitas" class="form-control rounded-3" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">Update Ruangan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Add Sesi -->
    <div class="modal fade" id="modalAddSesi" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form method="POST">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold">Tambah Sesi Ujian</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" value="add_sesi">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Sesi</label>
                            <input type="text" name="nama_sesi" class="form-control rounded-3" placeholder="Contoh: Sesi 1" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Waktu Mulai</label>
                                <input type="time" name="waktu_mulai" class="form-control rounded-3" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Waktu Selesai</label>
                                <input type="time" name="waktu_selesai" class="form-control rounded-3" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">Simpan Sesi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editRoom(id, nama, kapasitas) {
            $('#edit_id').val(id);
            $('#edit_nama').val(nama);
            $('#edit_kapasitas').val(kapasitas);
            $('#modalEditRoom').modal('show');
        }
    </script>

</body>
</html>
