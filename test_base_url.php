<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Test BASE_URL & CSS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: linear-gradient(135deg, #0f5132, #092c1e);
            color: white;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .info-box {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
        }

        .success {
            border-left-color: #20c997;
        }

        .error {
            border-left-color: #dc3545;
        }

        .test-btn {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 100px;
            padding: 14px 28px;
            font-weight: 600;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            margin: 10px 5px;
            transition: all 0.3s;
        }

        .test-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        }

        .test-btn.warning {
            border-left: 4px solid #ffc107;
        }

        .test-btn.success {
            border-left: 4px solid #20c997;
        }

        .test-btn.info {
            border-left: 4px solid #0dcaf0;
        }

        code {
            background: rgba(0, 0, 0, 0.3);
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔧 Test BASE_URL & CSS Loading</h1>

        <?php
        require_once 'includes/config.php';

        // Expected BASE_URL
        $expected_url = 'https://imsoftdev.my.id/pmbm/';
        $is_correct = (BASE_URL === $expected_url);
        ?>

        <div class="info-box <?= $is_correct ? 'success' : 'error' ?>">
            <h3>📍 BASE_URL Detection:</h3>
            <p><strong>Current BASE_URL:</strong> <code><?= BASE_URL ?></code></p>
            <p><strong>Expected BASE_URL:</strong> <code><?= $expected_url ?></code></p>
            <p><strong>Status:</strong>
                <?= $is_correct ? '✅ CORRECT' : '❌ WRONG - NEED FIX!' ?>
            </p>
        </div>

        <?php if (!$is_correct): ?>
            <div class="info-box error">
                <h4>⚠️ BASE_URL SALAH!</h4>
                <p>Edit file <code>includes/config.php</code> dan ganti dengan:</p>
                <pre><code>define('BASE_URL', 'https://imsoftdev.my.id/pmbm/');</code></pre>
            </div>
        <?php endif; ?>

        <div class="info-box">
            <h3>🎨 CSS File Path:</h3>
            <p><strong>CSS URL:</strong> <code><?= BASE_URL ?>assets/css/style.css</code></p>
            <p>
                <a href="<?= BASE_URL ?>assets/css/style.css" target="_blank" class="test-btn info">
                    🔗 Open CSS File
                </a>
            </p>
            <p><small>Jika link di atas terbuka dan menampilkan kode CSS = ✅ File ada<br>
                    Jika 404 Not Found = ❌ File tidak ada, harus upload ulang!</small></p>
        </div>

        <div class="info-box">
            <h3>🎯 Host & Server Info:</h3>
            <p><strong>HTTP_HOST:</strong> <code><?= $_SERVER['HTTP_HOST'] ?></code></p>
            <p><strong>SCRIPT_NAME:</strong> <code><?= $_SERVER['SCRIPT_NAME'] ?></code></p>
            <p><strong>HTTPS:</strong>
                <code><?= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'ON (HTTPS)' : 'OFF (HTTP)' ?></code>
            </p>
        </div>

        <div class="info-box">
            <h3>🧪 Test Buttons (Glassmorphism Style):</h3>
            <p>Tombol di bawah harus terlihat <strong>transparan dengan blur effect</strong>:</p>

            <a href="#" class="test-btn warning">
                📘 Petunjuk Teknis PMBM
            </a>

            <a href="#" class="test-btn info">
                📗 Brosur Pendaftaran
            </a>

            <a href="#" class="test-btn success">
                📜 Pakta Integritas & Ket.
            </a>

            <p><small>Jika tombol terlihat <strong>transparan dengan border putih</strong> = ✅ CSS Berhasil<br>
                    Jika tombol <strong>plain/solid/tidak transparan</strong> = ❌ CSS Tidak Load</small></p>
        </div>

        <div class="info-box">
            <h3>📁 File Upload Check:</h3>
            <?php
            $css_file = __DIR__ . '/assets/css/style.css';
            $css_exists = file_exists($css_file);
            ?>
            <p><strong>Server Path:</strong> <code><?= $css_file ?></code></p>
            <p><strong>File Exists:</strong>
                <?= $css_exists ? '✅ YES' : '❌ NO - FILE NOT UPLOADED!' ?>
            </p>
            <?php if ($css_exists): ?>
                <p><strong>File Size:</strong>
                    <?= number_format(filesize($css_file)) ?> bytes
                </p>
                <p><strong>Last Modified:</strong>
                    <?= date('Y-m-d H:i:s', filemtime($css_file)) ?>
                </p>
            <?php else: ?>
                <p class="error">⚠️ <strong>File CSS tidak ditemukan!</strong> Upload file <code>style.css</code> ke folder
                    <code>assets/css/</code></p>
            <?php endif; ?>
        </div>

        <div class="info-box success">
            <h3>✅ Action Items:</h3>
            <ol>
                <li>Pastikan <strong>BASE_URL</strong> sudah benar (lihat di atas)</li>
                <li>Pastikan file <code>style.css</code> sudah ter-upload</li>
                <li>Klik link "Open CSS File" di atas - harus terbuka</li>
                <li>Test buttons harus terlihat transparan dengan blur</li>
                <li><strong>HAPUS file ini setelah testing!</strong></li>
            </ol>
        </div>

        <div class="info-box error">
            <h3>🗑️ PENTING - DELETE FILE INI!</h3>
            <p>File <code>test_base_url.php</code> ini HANYA untuk testing!</p>
            <p><strong>HAPUS file ini setelah selesai debugging!</strong></p>
            <p>File ini tidak boleh ada di production karena expose informasi server.</p>
        </div>
    </div>

    <script>
        console.log('BASE_URL:', '<?= BASE_URL ?>');
        console.log('CSS Path:', '<?= BASE_URL ?>assets/css/style.css');
        console.log('Expected URL:', '<?= $expected_url ?>');
        console.log('Is Correct:', <?= $is_correct ? 'true' : 'false' ?>);
    </script>
</body>

</html>