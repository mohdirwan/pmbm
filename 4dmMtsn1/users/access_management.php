<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

// Only admin can access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

$message = '';

// Handle Access Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_access'])) {
    $role = $_POST['role'];
    $selected_menus = $_POST['menus'] ?? [];

    try {
        $pdo->beginTransaction();
        
        // Remove old access
        $stmt = $pdo->prepare("DELETE FROM role_access WHERE role = ?");
        $stmt->execute([$role]);
        
        // Insert new access
        $stmt = $pdo->prepare("INSERT INTO role_access (role, menu_key) VALUES (?, ?)");
        foreach ($selected_menus as $menu) {
            $stmt->execute([$role, $menu]);
        }
        
        $pdo->commit();
        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>Hak akses ' . ucfirst($role) . ' berhasil diperbarui!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}

// Get current access
function get_role_access($role) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT menu_key FROM role_access WHERE role = ?");
    $stmt->execute([$role]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$roles = ['operator', 'panitia'];
$menu_list = [
    'dashboard' => ['label' => 'Dashboard', 'icon' => 'fa-tachometer-alt'],
    'kesiswaan' => ['label' => 'Kesiswaan (Data Pendaftar, Verifikasi, Dokumen)', 'icon' => 'fa-user-graduate'],
    'sekolah' => ['label' => 'Sekolah (Jalur, Skema, Alur, Panduan, dll)', 'icon' => 'fa-school'],
    'ujian' => ['label' => 'Manajemen Ujian (Jadwal, Nilai, CBT)', 'icon' => 'fa-file-signature'],
    'pasca' => ['label' => 'Pasca Seleksi (Pengumuman, Daftar Ulang)', 'icon' => 'fa-bullhorn'],
    'sistem' => ['label' => 'Sistem (User Management, Laporan, Log, Settings)', 'icon' => 'fa-cogs']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Akses - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4 text-primary fw-bold"><i class="fas fa-user-shield me-2"></i>Manajemen Akses Menu</h2>
            
            <?= $message ?>

            <div class="row">
                <?php foreach ($roles as $role): ?>
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-user-tag me-2 text-primary"></i>Role: <?= ucfirst($role) ?>
                            </h5>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="role" value="<?= $role ?>">
                            <div class="card-body">
                                <p class="text-muted small mb-4">Centang menu yang boleh diakses oleh role ini:</p>
                                
                                <?php 
                                $current_access = get_role_access($role);
                                foreach ($menu_list as $key => $info): 
                                ?>
                                <div class="form-check mb-3 p-3 border rounded-3 hover-bg-light transition-all">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" name="menus[]" value="<?= $key ?>" 
                                           id="check_<?= $role ?>_<?= $key ?>" 
                                           <?= in_array($key, $current_access) ? 'checked' : '' ?>
                                           style="width: 1.2rem; height: 1.2rem;">
                                    <label class="form-check-label d-flex align-items-center" for="check_<?= $role ?>_<?= $key ?>">
                                        <i class="fas <?= $info['icon'] ?> me-3 text-secondary" style="width: 20px;"></i>
                                        <span class="fw-medium"><?= $info['label'] ?></span>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="card-footer bg-white border-0 pb-4 text-center">
                                <button type="submit" name="update_access" class="btn btn-primary rounded-pill px-5 shadow-sm">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="alert alert-info border-0 shadow-sm rounded-4 mt-2">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Catatan:</strong> Role <strong>Admin</strong> secara default memiliki akses ke semua menu dan tidak dapat dikurangi hak aksesnya demi keamanan sistem.
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .hover-bg-light:hover {
            background-color: #f8f9fa;
        }
        .transition-all {
            transition: all 0.2s ease;
        }
    </style>
</body>
</html>
