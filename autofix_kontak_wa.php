<?php
/**
 * AUTO-FIX Script: Add Kontak WA Columns
 * Langsung fix tanpa perlu manual migration
 */

require_once 'includes/config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>

<head>
    <title>Auto-Fix Database</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px;
            margin: 0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        h1 {
            color: #667eea;
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
        }

        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            margin-top: 20px;
            font-weight: bold;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            border-left: 4px solid #667eea;
        }

        .step {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔧 Auto-Fix Database - Kontak WA Columns</h1>

        <?php
        try {
            echo "<div class='info'><strong>📋 Checking database...</strong></div>";

            // Check if columns exist
            $checkSql = "SELECT COLUMN_NAME 
                 FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_SCHEMA = DATABASE() 
                   AND TABLE_NAME = 'pendaftar' 
                   AND COLUMN_NAME IN ('kontak_wa', 'nama_kontak_wa')";

            $stmt = $pdo->query($checkSql);
            $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $hasKontakWa = in_array('kontak_wa', $existingColumns);
            $hasNamaKontakWa = in_array('nama_kontak_wa', $existingColumns);

            echo "<div class='step'>";
            echo "<strong>Current Status:</strong><br>";
            echo "• Column <code>kontak_wa</code>: " . ($hasKontakWa ? "✅ EXISTS" : "❌ MISSING") . "<br>";
            echo "• Column <code>nama_kontak_wa</code>: " . ($hasNamaKontakWa ? "✅ EXISTS" : "❌ MISSING") . "<br>";
            echo "</div>";

            $pdo->beginTransaction();
            $changes = [];

            // Add kontak_wa if missing
            if (!$hasKontakWa) {
                echo "<div class='info'>➕ Adding column <code>kontak_wa</code>...</div>";
                $pdo->exec("ALTER TABLE pendaftar 
                    ADD COLUMN kontak_wa VARCHAR(15) DEFAULT NULL 
                    COMMENT 'Nomor WhatsApp yang bisa dihubungi'
                    AFTER no_hp");
                $changes[] = "Added kontak_wa column";
                echo "<div class='success'>✅ Column <code>kontak_wa</code> added successfully!</div>";
            }

            // Add nama_kontak_wa if missing
            if (!$hasNamaKontakWa) {
                echo "<div class='info'>➕ Adding column <code>nama_kontak_wa</code>...</div>";
                $pdo->exec("ALTER TABLE pendaftar 
                    ADD COLUMN nama_kontak_wa VARCHAR(100) DEFAULT NULL 
                    COMMENT 'Nama pemilik nomor WhatsApp'
                    AFTER " . ($hasKontakWa ? "kontak_wa" : "no_hp"));
                $changes[] = "Added nama_kontak_wa column";
                echo "<div class='success'>✅ Column <code>nama_kontak_wa</code> added successfully!</div>";
            }

            $pdo->commit();

            if (empty($changes)) {
                echo "<div class='success'>";
                echo "<h3>✅ All Columns Already Exist!</h3>";
                echo "<p>Database is already up-to-date. No changes needed.</p>";
                echo "</div>";
            } else {
                echo "<div class='success'>";
                echo "<h3>✅ Database Updated Successfully!</h3>";
                echo "<p><strong>Changes made:</strong></p>";
                echo "<ul>";
                foreach ($changes as $change) {
                    echo "<li>$change</li>";
                }
                echo "</ul>";
                echo "</div>";
            }

            // Verify final state
            echo "<div class='step'>";
            echo "<h4>🔍 Final Verification:</h4>";
            $verifySql = "SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_COMMENT 
                  FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'pendaftar' 
                    AND COLUMN_NAME IN ('kontak_wa', 'nama_kontak_wa')
                  ORDER BY ORDINAL_POSITION";

            $stmt = $pdo->query($verifySql);
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($columns)) {
                echo "<table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>";
                echo "<tr style='background: #667eea; color: white;'>";
                echo "<th style='padding: 10px; text-align: left;'>Column</th>";
                echo "<th style='padding: 10px; text-align: left;'>Type</th>";
                echo "<th style='padding: 10px; text-align: left;'>Comment</th>";
                echo "</tr>";

                foreach ($columns as $col) {
                    echo "<tr style='border-bottom: 1px solid #ddd;'>";
                    echo "<td style='padding: 10px;'><code>{$col['COLUMN_NAME']}</code></td>";
                    echo "<td style='padding: 10px;'>{$col['COLUMN_TYPE']}</td>";
                    echo "<td style='padding: 10px;'>{$col['COLUMN_COMMENT']}</td>";
                    echo "</tr>";
                }

                echo "</table>";
            }
            echo "</div>";

            echo "<div class='info'>";
            echo "<h4>📝 Next Steps:</h4>";
            echo "<ol>";
            echo "<li>Go back to registration form</li>";
            echo "<li>Fill out the form completely</li>";
            echo "<li>Submit - it should work now!</li>";
            echo "</ol>";
            echo "</div>";

            echo "<a href='register.php' class='btn'>← Back to Registration Form</a>";

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            echo "<div class='error'>";
            echo "<h3>❌ Error Occurred</h3>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            echo "</div>";

            echo "<div class='info'>";
            echo "<h4>💡 Try Manual Fix:</h4>";
            echo "<p>Open phpMyAdmin and run these queries:</p>";
            echo "<pre>";
            echo "ALTER TABLE pendaftar \n";
            echo "ADD COLUMN kontak_wa VARCHAR(15) DEFAULT NULL \n";
            echo "COMMENT 'Nomor WhatsApp yang bisa dihubungi'\n";
            echo "AFTER no_hp;\n\n";
            echo "ALTER TABLE pendaftar \n";
            echo "ADD COLUMN nama_kontak_wa VARCHAR(100) DEFAULT NULL \n";
            echo "COMMENT 'Nama pemilik nomor WhatsApp'\n";
            echo "AFTER kontak_wa;";
            echo "</pre>";
            echo "</div>";
        }
        ?>

    </div>
</body>

</html>