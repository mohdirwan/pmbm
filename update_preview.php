<!DOCTYPE html>
<html>

<head>
    <title>Update Preview Function</title>
    <style>
        body {
            font-family: Arial;
            padding: 20px;
            background: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
        }

        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
        }

        button:hover {
            background: #0056b3;
        }

        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }

        code {
            color: #e83e8c;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔧 Update Preview Function</h1>
        <p>Script ini akan menambahkan function <code>generateFilePreviews()</code> ke register.php</p>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $file = 'register.php';

            if (!file_exists($file)) {
                echo '<div class="alert alert-danger">❌ File register.php tidak ditemukan!</div>';
            } else {
                // Read current file
                $content = file_get_contents($file);

                // Function to append
                $newFunction = <<<'JS'

        // Generate file previews
        function generateFilePreviews(formData) {
            const container = document.getElementById('filesPreviewContainer');
            let filesHtml = '';
            
            // Get all file inputs
            const fileInputs = document.querySelectorAll('input[type="file"]');
            
            if (fileInputs.length === 0) {
                filesHtml = '<div class="col-12"><p class="text-muted">Tidak ada dokumen yang diupload.</p></div>';
            } else {
                fileInputs.forEach(input => {
                    const file = input.files[0];
                    if (file) {
                        const fileName = file.name;
                        const fileSize = (file.size / 1024).toFixed(2) + ' KB';
                        const fileExt = fileName.split('.').pop().toUpperCase();
                        const label = input.closest('.card')?.querySelector('label')?.textContent.trim() || input.name;
                        
                        // Icon based on file type
                        let icon = 'fa-file-alt';
                        let iconColor = 'text-primary';
                        if (fileExt === 'PDF') {
                            icon = 'fa-file-pdf';
                            iconColor = 'text-danger';
                        } else if (['JPG', 'JPEG', 'PNG'].includes(fileExt)) {
                            icon = 'fa-file-image';
                            iconColor = 'text-success';
                        }
                        
                        filesHtml += `
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas ${icon} fa-2x ${iconColor} me-3"></i>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold small">${label}</div>
                                            <div class="text-muted small">${fileName}</div>
                                            <div class="badge bg-info text-dark">${fileExt} - ${fileSize}</div>
                                        </div>
                                        <i class="fas fa-check-circle text-success fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                });
                
                if (filesHtml === '') {
                    filesHtml = '<div class="col-12"><p class="text-muted">Tidak ada dokumen yang diupload.</p></div>';
                }
            }
            
            container.innerHTML = filesHtml;
        }
JS;

                // Find where to insert (before closing )
                if (strpos($content, 'function generateFilePreviews') !== false) {
                    echo '<div class="alert alert-info">ℹ️ Function generateFilePreviews sudah ada!</div>';
                } else {
                    // Find "function closePreview()"
                    $insertPos = strpos($content, 'function closePreview()');

                    if ($insertPos !== false) {
                        // Insert before closePreview
                        $newContent = substr($content, 0, $insertPos) . $newFunction . "\n\n        " . substr($content, $insertPos);

                        // Backup original
                        file_put_contents($file . '.backup', $content);

                        // Write new content
                        file_put_contents($file, $newContent);

                        echo '<div class="alert alert-success">';
                        echo '✅ <strong>SUCCESS!</strong><br>';
                        echo '• Function generateFilePreviews() berhasil ditambahkan<br>';
                        echo '• Backup dibuat: register.php.backup<br>';
                        echo '• Silakan refresh halaman register.php dan test preview';
                        echo '</div>';

                        echo '<div class="alert alert-info">';
                        echo '<strong>Next Step:</strong><br>';
                        echo '1. Buka <code>http://localhost/pmbm/register.php</code><br>';
                        echo '2. Isi form sampai Step 5<br>';
                        echo '3. Upload beberapa file<br>';
                        echo '4. Klik "Lanjut ke Pakta Integritas"<br>';
                        echo '5. Accept pakta → Klik lagi → Preview harus muncul dengan file list';
                        echo '</div>';
                    } else {
                        echo '<div class="alert alert-danger">❌ Tidak bisa menemukan posisi untuk insert function</div>';
                    }
                }
            }
        } else {
            ?>
            <div class="alert alert-info">
                <strong>ℹ️ Catatan:</strong><br>
                • Function ini akan menampilkan list file yang diupload di modal preview<br>
                • Backup otomatis akan dibuat<br>
                • Pastikan register.php ada di folder yang sama dengan script ini
            </div>

            <form method="POST">
                <button type="submit">🚀 Update Sekarang</button>
            </form>
        <?php } ?>

        <hr style="margin: 30px 0;">

        <h3>Manual Alternative:</h3>
        <p>Jika auto-update tidak berhasil, copy function ini dan paste sebelum <code>function closePreview()</code>:
        </p>
        <pre><code>function generateFilePreviews(formData) {
    const container = document.getElementById('filesPreviewContainer');
    let filesHtml = '';
    
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    if (fileInputs.length === 0) {
        filesHtml = '&lt;div class="col-12"&gt;Tidak ada dokumen&lt;/div&gt;';
    } else {
        fileInputs.forEach(input =&gt; {
            const file = input.files[0];
            if (file) {
                const fileName = file.name;
                const fileSize = (file.size / 1024).toFixed(2) + ' KB';
                const label = input.closest('.card')?.querySelector('label')?.textContent || input.name;
                
                filesHtml += `&lt;div class="col-md-6"&gt;${label}: ${fileName} (${fileSize})&lt;/div&gt;`;
            }
        });
    }
    
    container.innerHTML = filesHtml;
}</code></pre>
    </div>
</body>

</html>