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

$pengumuman_start = '';
if ($ppdb_status == 'finalisasi') {
    $pengumuman_start = get_setting('stage_pengumuman_start', '');
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
        
        /* Countdown Styles */
        .countdown-item {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
            border-radius: 12px;
            padding: 10px 15px;
            text-align: center;
            min-width: 65px;
            border: 1px solid rgba(25, 135, 84, 0.2);
        }
        .countdown-val {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1;
        }
        .countdown-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <!-- Left Side: Login Form -->
            <div class="login-form">
                <?php 
                $hide_form = false;
                $show_cd = get_setting('show_countdown_pengumuman', 'tidak');
                if ($ppdb_status == 'finalisasi' && $show_cd == 'aktif' && !empty($pengumuman_start) && strtotime($pengumuman_start) > time()) {
                    $hide_form = true;
                }
                ?>

                <div id="login-form-container" <?= $hide_form ? 'style="display:none;"' : '' ?>>
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
                </div>

                <?php if ($ppdb_status == 'finalisasi' && $show_cd == 'aktif' && !empty($pengumuman_start)): ?>
                    <div class="alert alert-success border-0 shadow-sm mb-4 text-center" style="border-radius: 16px; background: #e8f5e9;">
                        <h6 class="fw-bold text-success mb-3"><i class="fas fa-hourglass-half me-2"></i>Menuju Pengumuman Kelulusan</h6>
                        <div class="d-flex justify-content-center gap-2" id="pengumuman-countdown" data-target="<?= htmlspecialchars($pengumuman_start) ?>">
                            <div class="countdown-item">
                                <div class="countdown-val" id="cd-days">00</div>
                                <div class="countdown-label">Hari</div>
                            </div>
                            <div class="countdown-item">
                                <div class="countdown-val" id="cd-hours">00</div>
                                <div class="countdown-label">Jam</div>
                            </div>
                            <div class="countdown-item">
                                <div class="countdown-val" id="cd-minutes">00</div>
                                <div class="countdown-label">Menit</div>
                            </div>
                            <div class="countdown-item">
                                <div class="countdown-val" id="cd-seconds">00</div>
                                <div class="countdown-label">Detik</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div id="login-form-wrapper" <?= $hide_form ? 'style="display:none;"' : '' ?>>
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

<?php if ($ppdb_status == 'finalisasi' && isset($show_cd) && $show_cd == 'aktif' && !empty($pengumuman_start)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const targetDateStr = document.getElementById('pengumuman-countdown').getAttribute('data-target');
        if (!targetDateStr) return;
        
        // Ensure proper date parsing across browsers (Safari needs / instead of -)
        const formattedDateStr = targetDateStr.replace(/-/g, "/");
        const targetDate = new Date(formattedDateStr).getTime();
        
        if (isNaN(targetDate)) return; // Invalid date

        const timer = setInterval(function() {
            const now = new Date().getTime();
            const distance = targetDate - now;
            
            if (distance < 0) {
                clearInterval(timer);
                document.getElementById('pengumuman-countdown').innerHTML = '<div class="fw-bold text-success w-100 py-2">Waktu pengumuman telah tiba! Silakan login untuk melihat hasil Anda.</div>';
                
                // Show the form
                const formContainer = document.getElementById('login-form-container');
                if (formContainer) formContainer.style.display = 'block';
                const formWrapper = document.getElementById('login-form-wrapper');
                if (formWrapper) formWrapper.style.display = 'block';
                
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('cd-days').innerText = days.toString().padStart(2, '0');
            document.getElementById('cd-hours').innerText = hours.toString().padStart(2, '0');
            document.getElementById('cd-minutes').innerText = minutes.toString().padStart(2, '0');
            document.getElementById('cd-seconds').innerText = seconds.toString().padStart(2, '0');
        }, 1000);
    });
</script>
<?php endif; ?>

</html>