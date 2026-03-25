<?php
require_once 'includes/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        header("Location: admin/dashboard.php");
        exit();
    } else {
        $error = "Username atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - PMBM MTsN 1 Kota Pekanbaru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            background: linear-gradient(135deg, var(--primary-color), #092c1e);
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

        .social-login a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid #ccc;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #555;
            margin: 0 5px;
            transition: all 0.3s;
        }

        .social-login a:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <!-- Left Side: Login Form -->
            <div class="login-form">
                <div class="text-center mb-4">
                    <h2 class="fw-bold" style="color: var(--primary-color);">Masuk ke Admin</h2>
                    <p class="text-muted small">Silakan masukkan kredensial Anda untuk melanjutkan ke dashboard.</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger py-2 text-center" style="font-size: 0.9rem;">
                        <i class="fas fa-exclamation-circle me-1"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold uppercase">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="fas fa-user text-muted"></i></span>
                            <input type="text" name="username" class="form-control border-start-0 bg-light"
                                placeholder="Masukkan username" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold uppercase">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="fas fa-lock text-muted"></i></span>
                            <input type="password" name="password" class="form-control border-start-0 bg-light"
                                placeholder="Masukkan password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-premium w-100 mb-3 rounded-pill shadow-sm">LOGIN</button>

                    <div class="text-center">
                        <a href="index.php" class="text-muted small text-decoration-none">Kembali ke Beranda</a>
                    </div>
                </form>
            </div>

            <!-- Right Side: Welcome Panel -->
            <div class="login-sidebar">
                <h2 class="fw-bold mb-3">Ahlan Wa Sahlan!</h2>
                <p class="mb-4 opacity-75">Panel Administrator untuk mengelola PMBM MTsN 1 Kota Pekanbaru. Kelola data
                    siswa,
                    pengaturan website, dan pengumuman dengan mudah.</p>
                <div class="mt-4">
                    <img src="https://cdni.iconscout.com/illustration/premium/thumb/login-3305943-2757111.png"
                        alt="Admin Login" class="img-fluid"
                        style="max-height: 200px; filter: drop-shadow(0 10px 10px rgba(0,0,0,0.2));">
                </div>
            </div>
        </div>
    </div>
</body>

</html>