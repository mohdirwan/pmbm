<?php
require_once '../../includes/config.php';
require_once '../../includes/auth_check.php';

// Only admin and operator can access this page
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'operator') {
    header("Location: ../dashboard.php");
    exit();
}

$message = '';

// Handle DELETE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = intval($_POST['delete_id']);

    // Security check: cannot delete admin
    $check = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $check->execute([$id]);
    $user_to_delete = $check->fetch();

    if ($user_to_delete['role'] === 'admin') {
        $message = '<div class="alert alert-danger">User Admin tidak dapat dihapus!</div>';
    } elseif ($_SESSION['role'] === 'operator' && $user_to_delete['role'] !== 'panitia') {
        $message = '<div class="alert alert-danger">Operator hanya dapat menghapus role Panitia!</div>';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $message = '<div class="alert alert-success">User berhasil dihapus!</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Gagal menghapus: ' . $e->getMessage() . '</div>';
        }
    }
}

// Handle ADD / EDIT
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && ($_POST['action'] == 'add' || $_POST['action'] == 'edit')) {
    $id = $_POST['id'] ?? null;
    $username = clean_input($_POST['username']);
    $role = $_POST['role'];
    $password = $_POST['password'] ?? '';

    // Validation
    $can_proceed = true;
    if ($_SESSION['role'] === 'operator' && $role !== 'panitia') {
        $message = '<div class="alert alert-danger">Operator hanya dapat mengelola role Panitia!</div>';
        $can_proceed = false;
    }

    if ($can_proceed) {
        try {
            if ($id) {
                // EDIT
                if (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ?, password = ? WHERE id = ?");
                    $stmt->execute([$username, $role, $hashed, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
                    $stmt->execute([$username, $role, $id]);
                }
                $message = '<div class="alert alert-success">User berhasil diupdate!</div>';
            } else {
                // ADD
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $stmt->execute([$username, $hashed, $role]);
                $message = '<div class="alert alert-success">User berhasil ditambahkan!</div>';
            }
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Gagal menyimpan: ' . $e->getMessage() . '</div>';
        }
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY role ASC, username ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Manajemen User - Admin PMBM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4 text-primary fw-bold"><i class="fas fa-users-cog me-2"></i>User Management</h2>

            <?= $message ?>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Daftar Pengguna</h6>
                    <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal"
                        data-bs-target="#modalUser" onclick="resetForm()">
                        <i class="fas fa-plus me-2"></i>Tambah User
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Dibuat Pada</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $index => $u): ?>
                                    <tr>
                                        <td class="ps-4"><?= $index + 1 ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($u['username']) ?></td>
                                        <td>
                                            <?php
                                            $badge = 'bg-secondary';
                                            if ($u['role'] === 'admin')
                                                $badge = 'bg-danger';
                                            if ($u['role'] === 'operator')
                                                $badge = 'bg-primary';
                                            if ($u['role'] === 'panitia')
                                                $badge = 'bg-info text-dark';
                                            ?>
                                            <span
                                                class="badge <?= $badge ?> px-3 rounded-pill"><?= ucfirst($u['role']) ?></span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                                        <td class="text-end pe-4">
                                            <?php if ($u['role'] !== 'admin'): ?>
                                                <?php if ($_SESSION['role'] === 'admin' || ($_SESSION['role'] === 'operator' && $u['role'] === 'panitia')): ?>
                                                    <button class="btn btn-sm btn-outline-warning rounded-circle me-1"
                                                        onclick="editUser(<?= $u['id'] ?>, '<?= $u['username'] ?>', '<?= $u['role'] ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger rounded-circle"
                                                        onclick="deleteUser(<?= $u['id'] ?>, '<?= $u['username'] ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted small">No Access</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted fw-normal">Protected</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Add/Edit -->
    <div class="modal fade" id="modalUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 bg-primary text-white pt-4 px-4">
                    <h5 class="modal-title fw-bold" id="modalTitle">Tambah User Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" id="form_action" value="add">
                    <input type="hidden" name="id" id="user_id">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Username</label>
                            <input type="text" name="username" id="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Role</label>
                            <select name="role" id="role" class="form-select" required>
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <option value="admin">Admin</option>
                                    <option value="operator">Operator</option>
                                <?php endif; ?>
                                <option value="panitia">Panitia</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Password</label>
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="Kosongkan jika tidak ingin ganti">
                            <small class="text-muted" id="pass_hint">Wajib diisi untuk user baru</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 px-4">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetForm() {
            document.getElementById('modalTitle').innerText = 'Tambah User Baru';
            document.getElementById('form_action').value = 'add';
            document.getElementById('user_id').value = '';
            document.getElementById('username').value = '';
            document.getElementById('role').value = 'panitia';
            document.getElementById('password').required = true;
            document.getElementById('pass_hint').innerText = 'Wajib diisi untuk user baru';
        }

        function editUser(id, username, role) {
            document.getElementById('modalTitle').innerText = 'Edit User';
            document.getElementById('form_action').value = 'edit';
            document.getElementById('user_id').value = id;
            document.getElementById('username').value = username;
            document.getElementById('role').value = role;
            document.getElementById('password').required = false;
            document.getElementById('pass_hint').innerText = 'Kosongkan jika tidak ingin ganti password';

            var modal = new bootstrap.Modal(document.getElementById('modalUser'));
            modal.show();
        }

        function deleteUser(id, username) {
            if (confirm('Yakin ingin menghapus user "' + username + '"?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="delete_id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>

</html>