<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

$success_msg = '';
$error_msg = '';

// Handle bulk verification
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'verify_all_pending') {
    try {
        $stmt = $pdo->prepare("UPDATE pendaftar SET status = 'Terverifikasi' WHERE status = 'Pending'");
        $stmt->execute();
        $affected = $stmt->rowCount();

        log_activity("Verifikasi Massal", "Admin melakukan verifikasi otomatis untuk $affected murid yang berstatus Pending");
        $success_msg = "Berhasil memverifikasi $affected murid yang berstatus Pending!";
    } catch (Exception $e) {
        $error_msg = "Gagal melakukan verifikasi massal: " . $e->getMessage();
    }
}

// Pagination setup
$limit = 10; // Data per halaman
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Prepare query with search filter
$search = $_GET['search'] ?? '';
$jalur = $_GET['jalur'] ?? '';
$status = $_GET['status'] ?? '';
$finalisasi = $_GET['finalisasi'] ?? '';

$query = "SELECT p.*, j.nama_jalur 
          FROM pendaftar p 
          LEFT JOIN jalur_pendaftaran j ON p.jalur_id = j.id 
          WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (p.nama_lengkap LIKE ? OR p.nisn LIKE ? OR p.no_pendaftaran LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($jalur) {
    $query .= " AND p.jalur_id = ?";
    $params[] = $jalur;
}

if ($status) {
    $query .= " AND p.status = ?";
    $params[] = $status;
}

if ($finalisasi) {
    if ($finalisasi == 'ya') {
        $query .= " AND p.finalisasi = 'ya'";
    } else {
        $query .= " AND (p.finalisasi = 'belum' OR p.finalisasi IS NULL)";
    }
}

// Get total count for pagination
$countQuery = str_replace("SELECT p.*, j.nama_jalur", "SELECT COUNT(*)", $query);
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalData = $countStmt->fetchColumn();
$totalPages = ceil($totalData / $limit);

// Add pagination to query - use integer directly (safe because already casted as int)
$query .= " ORDER BY p.id DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Manajemen Data Pendaftar - PMBM Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            background: #0f5132;
            color: white;
            padding-top: 20px;
            position: fixed;
            width: 250px;
            z-index: 1000;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            padding: 12px 20px;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-left: 4px solid #ffc107;
        }

        .nav-link i {
            width: 25px;
            text-align: center;
            margin-right: 10px;
        }
    </style>
</head>

<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-primary fw-bold mb-0">Data Pendaftar</h2>
                    <p class="text-muted small">Kelola seluruh data calon peserta didik baru.</p>
                </div>
                <div>
                    <a href="export_excel.php?search=<?= urlencode($search) ?>&jalur=<?= urlencode($jalur) ?>&status=<?= urlencode($status) ?>&finalisasi=<?= urlencode($finalisasi) ?>"
                        class="btn btn-outline-success"><i class="fas fa-file-excel me-2"></i>Export Excel</a>
                    <a href="print_list.php?search=<?= urlencode($search) ?>&jalur=<?= urlencode($jalur) ?>&status=<?= urlencode($status) ?>&finalisasi=<?= urlencode($finalisasi) ?>"
                        target="_blank" class="btn btn-outline-danger ms-2"><i class="fas fa-file-pdf me-2"></i>Print
                        PDF</a>
                </div>
            </div>

            <!-- Success Message Notification -->
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($_GET['msg']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Bulk Action Success -->
            <?php if ($success_msg): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($success_msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Bulk Action Error -->
            <?php if ($error_msg): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error_msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Cari Murid</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" class="form-control"
                                    placeholder="Nama / NISN / No. Daftar" value="<?= htmlspecialchars($search) ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Filter Jalur</label>
                            <select name="jalur" class="form-select">
                                <option value="">Semua Jalur</option>
                                <?php
                                $stmt_j = $pdo->query("SELECT * FROM jalur_pendaftaran ORDER BY nama_jalur ASC");
                                while ($row_j = $stmt_j->fetch()) {
                                    $selected = ($jalur == $row_j['id']) ? 'selected' : '';
                                    echo "<option value='{$row_j['id']}' {$selected}>" . htmlspecialchars($row_j['nama_jalur']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">Filter Status</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="Pending" <?= $status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Terverifikasi" <?= $status == 'Terverifikasi' ? 'selected' : '' ?>>
                                    Terverifikasi</option>
                                <option value="Diterima" <?= $status == 'Diterima' ? 'selected' : '' ?>>Diterima</option>
                                <option value="Ditolak" <?= $status == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">Filter Finalisasi</label>
                            <select name="finalisasi" class="form-select">
                                <option value="">Semua</option>
                                <option value="ya" <?= $finalisasi == 'ya' ? 'selected' : '' ?>>Sudah</option>
                                <option value="belum" <?= $finalisasi == 'belum' ? 'selected' : '' ?>>Belum</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-secondary w-100">Terapkan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bulk Verification Button -->
            <div class="mb-3">
                <form method="POST"
                    onsubmit="return confirm('Apakah Anda yakin ingin memverifikasi SEMUA murid yang berstatus Pending? Tindakan ini akan mengubah status mereka menjadi Terverifikasi.');">
                    <input type="hidden" name="action" value="verify_all_pending">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check-double me-2"></i>
                        Verifikasi Otomatis Semua Murid Pending
                    </button>
                    <button type="button" class="btn btn-outline-primary ms-2" id="btnRevealAll">
                        <i class="fas fa-eye me-2"></i> Tampilkan Semua Password
                    </button>
                    <small class="text-muted ms-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Tombol ini akan memverifikasi semua murid yang berstatus "Pending" (kecuali yang ditolak)
                    </small>
                </form>
            </div>

            <!-- Table -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">No. Daftar</th>
                                    <th>Nama Murid & NISN</th>
                                    <th>Password</th>
                                    <th>TTL & JK</th>
                                    <th>Asal Sekolah</th>
                                    <th>Waktu Daftar</th>
                                    <th>Jalur</th>
                                    <th>Finalisasi</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($students) > 0): ?>
                                    <?php foreach ($students as $s): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-primary">
                                                <?= $s['no_pendaftaran'] ?>
                                            </td>
                                            <td>
                                                <div class="fw-bold">
                                                    <?= $s['nama_lengkap'] ?>
                                                </div>
                                                <div class="small text-muted">
                                                    <?= $s['nisn'] ?>
                                                </div>
                                            </td>
                                             <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <code class="small text-primary password-field" data-raw="<?= htmlspecialchars($s['password_plain'] ?? '') ?>">********</code>
                                                    <button class="btn btn-sm btn-link p-0 text-muted toggle-password" type="button">
                                                        <i class="far fa-eye"></i>
                                                    </button>
                                                </div>
                                                <div class="small text-muted mt-1" style="font-size: 0.65rem;">
                                                    <i class="fas fa-lock me-1"></i>Hashed in Database
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <?= $s['tempat_lahir'] ?>,
                                                    <?= date('d/m/Y', strtotime($s['tanggal_lahir'])) ?>
                                                </div>
                                                <div class="badge bg-light text-dark border">
                                                    <?= $s['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?= $s['asal_sekolah'] ?>
                                            </td>
                                            <td>
                                                <div class="small fw-bold">
                                                    <i class="far fa-clock me-1 text-muted"></i>
                                                    <?= date('d/m/Y', strtotime($s['tanggal_daftar'])) ?>
                                                </div>
                                                <div class="small text-muted">
                                                    <?= date('H:i', strtotime($s['tanggal_daftar'])) ?> WIB
                                                </div>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill px-3">
                                                    <?= htmlspecialchars($s['nama_jalur'] ?? 'N/A') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (isset($s['finalisasi']) && $s['finalisasi'] == 'ya'): ?>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3">
                                                            <i class="fas fa-check-circle me-1"></i>Sudah
                                                        </span>
                                                        <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1 btn-change-finalisasi" 
                                                            style="font-size: 0.75rem; border-radius: 4px;"
                                                            data-id="<?= $s['id'] ?>" 
                                                            data-nama="<?= htmlspecialchars($s['nama_lengkap']) ?>"
                                                            data-target="belum"
                                                            title="Batal Finalisasi">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    </div>
                                                    <?php
                                                    $oleh = $s['finalisasi_oleh'] ?? null;
                                                    if ($oleh === 'manual'): ?>
                                                        <div class="mt-1">
                                                            <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size: 0.6rem;">
                                                                <i class="fas fa-user me-1"></i>Mandiri
                                                            </span>
                                                        </div>
                                                    <?php elseif ($oleh === 'sistem'): ?>
                                                        <div class="mt-1">
                                                            <span class="badge bg-danger text-white" style="font-size: 0.6rem;">
                                                                <i class="fas fa-robot me-1"></i>Otomatis Sistem
                                                            </span>
                                                        </div>
                                                    <?php elseif ($oleh === 'admin'): ?>
                                                        <div class="mt-1">
                                                            <span class="badge bg-secondary bg-opacity-15 text-secondary" style="font-size: 0.6rem;">
                                                                <i class="fas fa-user-shield me-1"></i>Oleh Admin
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-3">
                                                            <i class="fas fa-times-circle me-1"></i>Belum
                                                        </span>
                                                        <button type="button" class="btn btn-xs btn-outline-success py-0 px-1 btn-change-finalisasi" 
                                                            style="font-size: 0.75rem; border-radius: 4px;"
                                                            data-id="<?= $s['id'] ?>" 
                                                            data-nama="<?= htmlspecialchars($s['nama_lengkap']) ?>"
                                                            data-target="ya"
                                                            title="Set Finalisasi">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $badgeClass = match ($s['status']) {
                                                    'Diterima' => 'success',
                                                    'Terverifikasi' => 'primary',
                                                    'Ditolak' => 'danger',
                                                    default => 'warning text-dark'
                                                };
                                                ?>
                                                <span class="badge bg-<?= $badgeClass ?>">
                                                    <?= $s['status'] ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="btn-group">
                                                    <a href="detail_pendaftar.php?id=<?= $s['id'] ?>"
                                                        class="btn btn-sm btn-outline-primary" title="Detail & Verifikasi"><i
                                                            class="fas fa-eye"></i></a>
                                                    <button type="button" class="btn btn-sm btn-outline-warning btn-edit"
                                                        title="Edit Data" data-id="<?= $s['id'] ?>"
                                                        data-nama="<?= htmlspecialchars($s['nama_lengkap']) ?>"
                                                        data-jalur="<?= $s['jalur_id'] ?>"
                                                        data-nisn="<?= htmlspecialchars($s['nisn']) ?>"
                                                        data-nik="<?= htmlspecialchars($s['nik']) ?>"
                                                        data-finalisasi="<?= htmlspecialchars($s['finalisasi'] ?? 'belum') ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="../../cetak_bukti.php?reg=<?= $s['no_pendaftaran'] ?>"
                                                        target="_blank" class="btn btn-sm btn-outline-success"
                                                        title="Cetak Bukti"><i class="fas fa-print"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-5 text-muted">Belum ada data pendaftar yang
                                            sesuai filter.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <span class="text-muted small">
                                Menampilkan <?= count($students) ?> dari <?= $totalData ?> data 
                                (Halaman <?= $page ?> dari <?= $totalPages ?>)
                            </span>
                        </div>
                        <div class="col-md-6">
                            <nav aria-label="Pagination">
                                <ul class="pagination pagination-sm mb-0 justify-content-end">
                                    <!-- Previous Button -->
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $jalur ? '&jalur=' . urlencode($jalur) : '' ?><?= $status ? '&status=' . urlencode($status) : '' ?><?= $finalisasi ? '&finalisasi=' . urlencode($finalisasi) : '' ?>">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link"><i class="fas fa-chevron-left"></i> Previous</span>
                                        </li>
                                    <?php endif; ?>

                                    <!-- Page Numbers (show max 5 pages) -->
                                    <?php
                                    $startPage = max(1, $page - 2);
                                    $endPage = min($totalPages, $page + 2);
                                    
                                    for ($i = $startPage; $i <= $endPage; $i++):
                                    ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $jalur ? '&jalur=' . urlencode($jalur) : '' ?><?= $status ? '&status=' . urlencode($status) : '' ?><?= $finalisasi ? '&finalisasi=' . urlencode($finalisasi) : '' ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Next Button -->
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $jalur ? '&jalur=' . urlencode($jalur) : '' ?><?= $status ? '&status=' . urlencode($status) : '' ?><?= $finalisasi ? '&finalisasi=' . urlencode($finalisasi) : '' ?>">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">Next <i class="fas fa-chevron-right"></i></span>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form id="formEditPendaftar">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold">Edit Data Pendaftar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-4">Mengedit data untuk: <span id="editNama"
                                class="fw-bold text-dark"></span></p>

                        <input type="hidden" name="id" id="editId">

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Jalur Pendaftaran</label>
                            <select name="jalur_id" id="editJalur" class="form-select" required>
                                <?php
                                $stmt_j2 = $pdo->query("SELECT * FROM jalur_pendaftaran ORDER BY nama_jalur ASC");
                                while ($row_j2 = $stmt_j2->fetch()) {
                                    echo "<option value='{$row_j2['id']}'>" . htmlspecialchars($row_j2['nama_jalur']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">NISN</label>
                            <input type="text" name="nisn" id="editNisn" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">NIK</label>
                            <input type="text" name="nik" id="editNik" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Status Finalisasi</label>
                            <select name="finalisasi" id="editFinalisasi" class="form-select" required>
                                <option value="belum">Belum Finalisasi</option>
                                <option value="ya">Sudah Finalisasi (Ya)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            // Populate modal using delegation for better compatibility
            $(document).on('click', '.btn-edit', function () {
                const button = $(this);
                const id = button.data('id');
                const nama = button.data('nama');
                const jalur = button.data('jalur');
                const nisn = button.data('nisn');
                const nik = button.data('nik');
                const finalisasi = button.data('finalisasi');

                $('#editId').val(id);
                $('#editNama').text(nama);
                $('#editJalur').val(jalur || "");
                $('#editNisn').val(nisn);
                $('#editNik').val(nik);
                $('#editFinalisasi').val(finalisasi || "belum");

                const modalEl = document.getElementById('modalEdit');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            });

            // Handle form submission
            $('#formEditPendaftar').on('submit', function (e) {
                e.preventDefault();

                const formData = $(this).serialize();

                $.ajax({
                    url: 'api_update_pendaftar.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: response.message
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan sistem.'
                        });
                    }
                });
            });

            // Simple Toggle Password Reveal
            $('.toggle-password').on('click', function() {
                const $btn = $(this);
                const $field = $btn.siblings('.password-field');
                const raw = $field.data('raw');
                const isHidden = $field.text() === '********';

                if (isHidden) {
                    $field.text(raw || '[Password Lama / Terenkripsi]');
                    $btn.html('<i class="far fa-eye-slash"></i>');
                } else {
                    $field.text('********');
                    $btn.html('<i class="far fa-eye"></i>');
                }
            });

            // Reveal All Passwords
            $('#btnRevealAll').on('click', function() {
                const $btn = $(this);
                const isRevealing = $btn.find('i').hasClass('fa-eye');

                if (isRevealing) {
                    $('.password-field').each(function() {
                        const $field = $(this);
                        const raw = $field.data('raw');
                        $field.text(raw || '[Password Lama / Terenkripsi]');
                    });
                    $('.toggle-password').html('<i class="far fa-eye-slash"></i>');
                    $btn.html('<i class="fas fa-eye-slash me-2"></i> Sembunyikan Semua Password');
                } else {
                    $('.password-field').text('********');
                    $('.toggle-password').html('<i class="far fa-eye"></i>');
                    $btn.html('<i class="fas fa-eye me-2"></i> Tampilkan Semua Password');
                }
            });

            // Handle quick toggle finalisasi
            $(document).on('click', '.btn-change-finalisasi', function() {
                const button = $(this);
                const id = button.data('id');
                const nama = button.data('nama');
                const target = button.data('target');
                const actionText = target === 'ya' ? 'memfinalisasi' : 'membatalkan finalisasi';
                const statusText = target === 'ya' ? 'Sudah' : 'Belum';

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: `Anda akan mengubah status finalisasi untuk ${nama} menjadi "${statusText}".`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Ubah!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'api_toggle_finalisasi.php',
                            type: 'POST',
                            data: { id: id, finalisasi: target },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal!',
                                        text: response.message
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: 'Terjadi kesalahan sistem.'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>
