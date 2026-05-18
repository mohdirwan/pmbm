<?php
require_once 'includes/config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nisn = clean_input($_POST['nisn']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM pendaftar WHERE nisn = ?");
    $stmt->execute([$nisn]);
    $siswa = $stmt->fetch();

    if ($siswa && password_verify($password, $siswa['password'])) {
        $_SESSION['siswa_id'] = $siswa['id'];
        $_SESSION['role'] = 'siswa';
        $_SESSION['nama_lengkap'] = $siswa['nama_lengkap'];
        $_SESSION['login_alert_check'] = true;
        header("Location: siswa/dashboard.php");
        exit();
    } else {
        $error = "NISN atau Password salah!";
    }
}

// Check PMBM status for login access
$ppdb_status = get_setting('ppdb_status', 'belum');
$can_login = ($ppdb_status !== 'belum');

if (!$can_login) {
    $error = "Login ditutup sementara karena status PMBM sedang dalam masa " . strtoupper($ppdb_status) . ".";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Murid - PMBM MTsN 1 Kota Pekanbaru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php if ($favicon = get_setting('school_logo')): ?>
        <link rel="icon" type="image/x-icon" href="<?= BASE_URL . $favicon ?>">
    <?php endif; ?>
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f2f5;
        }

        .login-box {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 900px;
            max-width: 90%;
            display: flex;
            min-height: 550px;
        }

        .login-form {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-sidebar {
            flex: 1;
            background: linear-gradient(135deg, #198754, #0d6efd);
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-sidebar::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 10%, transparent 10%);
            background-size: 30px 30px;
            top: -50%;
            left: -50%;
            opacity: 0.3;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <!-- Left Side: Login Form -->
            <div class="login-form">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-success">Login Calon Murid</h2>
                    <p class="text-muted small">Silakan login menggunakan NISN dan Password yang telah Anda buat saat mendaftar.</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 text-center" style="font-size: 0.9rem;">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold uppercase">NISN</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="fas fa-id-card text-muted"></i></span>
                            <input type="text" name="nisn" class="form-control border-start-0 bg-light"
                                placeholder="10 Digit NISN" required <?= !$can_login && $ppdb_status != 'pengumuman' ? 'disabled' : '' ?>>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold uppercase">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="fas fa-lock text-muted"></i></span>
                            <input type="password" name="password" class="form-control border-start-0 bg-light"
                                placeholder="Password yang Anda buat" required <?= !$can_login && $ppdb_status != 'pengumuman' ? 'disabled' : '' ?>>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 mb-3 rounded-pill shadow-sm" <?= !$can_login && $ppdb_status != 'pengumuman' ? 'disabled' : '' ?>>MASUK KE DASHBOARD</button>

                    <div class="text-center">
                        <a href="index.php" class="text-muted small text-decoration-none"><i
                                class="fas fa-arrow-left me-1"></i> Kembali ke Beranda</a>
                    </div>
                </form>
            </div>

            <!-- Right Side: Welcome Panel -->
            <div class="login-sidebar">
                <h2 class="fw-bold mb-3">Selamat Datang!</h2>
                <p class="mb-4 opacity-75">Silakan masuk untuk melihat status verifikasi, dan melihat
                    hasil seleksi akhir Anda.</p>
                <div class="mt-4">
                    <img src="<?= BASE_URL ?>assets/img/hero-illustration.png" alt="Murid Login" class="img-fluid"
                        style="max-height: 250px; filter: drop-shadow(0 10px 10px rgba(0,0,0,0.2));">
                </div>
            </div>
        </div>
    </div>
</body>

</html>