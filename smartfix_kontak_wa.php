<?php
/**
 * SMART AUTO-FIX: Add Kontak WA Columns
 * Automatically detects table structure and adds columns safely
 */

require_once 'includes/config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>

<head>
    <title>Smart Auto-Fix Database</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px;
            margin: 0;
        }

        .container {
            max-width: 900px;
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
            border-left: 5px solid #28a745;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .error {
            background: #f8d7da;
            border-left: 5px solid #dc3545;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .info {
            background: #d1ecf1;
            border-left: 5px solid #17a2b8;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .warning {
            background: #fff3cd;
            border-left: 5px solid #ffc107;
            color: #856404;
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
            border: none;
            cursor: pointer;
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
            font-size: 12px;
        }

        .step {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        th {
            background: #667eea;
            color: white;
            padding: 10px;
            text-align: left;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
            color: #e83e8c;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔧 Smart Auto-Fix Database</h1>

        <?php
        try {
            echo "<div class='info'><strong>🔍 Step 1: Analyzing table structure...</strong></div>";

            // Get all columns from pendaftar table
            $columnsSql = "SELECT COLUMN_NAME, COLUMN_TYPE, ORDINAL_POSITION 
                   FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = DATABASE() 
                     AND TABLE_NAME = 'pendaftar'
                   ORDER BY ORDINAL_POSITION";

            $stmt = $pdo->query($columnsSql);
            $allColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($allColumns)) {
                throw new Exception("Table 'pendaftar' not found or empty!");
            }

            echo "<div class='step'>";
            echo "<strong>📋 Current Table Structure:</strong><br>";
            echo "<small>Found " . count($allColumns) . " columns in table 'pendaftar'</small>";
            echo "<details style='margin-top: 10px;'>";
            echo "<summary style='cursor: pointer; color: #667eea; font-weight: bold;'>Click to view all columns</summary>";
            echo "<table style='margin-top: 10px; font-size: 12px;'>";
            echo "<tr><th>Position</th><th>Column Name</th><th>Type</th></tr>";
            foreach ($allColumns as $col) {
                echo "<tr>";
                echo "<td>{$col['ORDINAL_POSITION']}</td>";
                echo "<td><code>{$col['COLUMN_NAME']}</code></td>";
                echo "<td>{$col['COLUMN_TYPE']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</details>";
            echo "</div>";

            // Check if target columns already exist
            $columnNames = array_column($allColumns, 'COLUMN_NAME');
            $hasKontakWa = in_array('kontak_wa', $columnNames);
            $hasNamaKontakWa = in_array('nama_kontak_wa', $columnNames);

            echo "<div class='step'>";
            echo "<strong>🎯 Target Columns Status:</strong><br>";
            echo "• <code>kontak_wa</code>: " . ($hasKontakWa ? "✅ <span style='color: green;'>EXISTS</span>" : "❌ <span style='color: red;'>MISSING</span>") . "<br>";
            echo "• <code>nama_kontak_wa</code>: " . ($hasNamaKontakWa ? "✅ <span style='color: green;'>EXISTS</span>" : "❌ <span style='color: red;'>MISSING</span>") . "<br>";
            echo "</div>";

            if ($hasKontakWa && $hasNamaKontakWa) {
                echo "<div class='success'>";
                echo "<h3>✅ All Columns Already Exist!</h3>";
                echo "<p>Your database is already up-to-date. No changes needed.</p>";
                echo "<p><a href='register.php' class='btn'>← Back to Registration Form</a></p>";
                echo "</div>";
                exit;
            }

            // Find best position to add columns (after email or last column)
            $afterColumn = null;
            $preferredColumns = ['email', 'no_hp', 'phone', 'telepon', 'hp'];

            foreach ($preferredColumns as $preferred) {
                if (in_array($preferred, $columnNames)) {
                    $afterColumn = $preferred;
                    break;
                }
            }

            // If no preferred column found, use the last column before nilai fields
            if (!$afterColumn) {
                foreach ($allColumns as $col) {
                    if (!preg_match('/nilai|dokumen|file/i', $col['COLUMN_NAME'])) {
                        $afterColumn = $col['COLUMN_NAME'];
                    } else {
                        break;
                    }
                }
            }

            echo "<div class='info'>";
            echo "<strong>📍 Insertion Strategy:</strong><br>";
            echo "New columns will be added AFTER: <code>$afterColumn</code>";
            echo "</div>";

            // Start transaction
            echo "<div class='info'><strong>⚡ Step 2: Applying changes...</strong></div>";
            $pdo->beginTransaction();
            $changes = [];

            // Add kontak_wa if missing
            if (!$hasKontakWa) {
                echo "<div class='step'>";
                echo "➕ Adding <code>kontak_wa</code> column...<br>";

                $sql = "ALTER TABLE pendaftar 
                ADD COLUMN kontak_wa VARCHAR(15) DEFAULT NULL 
                COMMENT 'Nomor WhatsApp yang bisa dihubungi'
                AFTER $afterColumn";

                echo "<small>SQL: <code>" . htmlspecialchars($sql) . "</code></small>";

                $pdo->exec($sql);
                $changes[] = "Added kontak_wa (VARCHAR 15) after $afterColumn";
                $afterColumn = 'kontak_wa'; // Next column goes after this one
        
                echo "<br>✅ <span style='color: green;'>Success!</span>";
                echo "</div>";
            }

            // Add nama_kontak_wa if missing
            if (!$hasNamaKontakWa) {
                echo "<div class='step'>";
                echo "➕ Adding <code>nama_kontak_wa</code> column...<br>";

                $sql = "ALTER TABLE pendaftar 
                ADD COLUMN nama_kontak_wa VARCHAR(100) DEFAULT NULL 
                COMMENT 'Nama pemilik nomor WhatsApp'
                AFTER $afterColumn";

                echo "<small>SQL: <code>" . htmlspecialchars($sql) . "</code></small>";

                $pdo->exec($sql);
                $changes[] = "Added nama_kontak_wa (VARCHAR 100) after $afterColumn";

                echo "<br>✅ <span style='color: green;'>Success!</span>";
                echo "</div>";
            }

            $pdo->commit();

            // Verify changes
            echo "<div class='info'><strong>🔍 Step 3: Verifying changes...</strong></div>";

            $verifySql = "SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_COMMENT, ORDINAL_POSITION
                  FROM INFORMATION_SCHEMA.COLUMNS 
                  WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'pendaftar' 
                    AND COLUMN_NAME IN ('kontak_wa', 'nama_kontak_wa')
                  ORDER BY ORDINAL_POSITION";

            $stmt = $pdo->query($verifySql);
            $newColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($newColumns) === 2) {
                echo "<div class='success'>";
                echo "<h3>✅ Migration Completed Successfully!</h3>";

                echo "<p><strong>Changes Applied:</strong></p>";
                echo "<ul>";
                foreach ($changes as $change) {
                    echo "<li>$change</li>";
                }
                echo "</ul>";

                echo "<p><strong>New Columns Details:</strong></p>";
                echo "<table>";
                echo "<tr><th>Column</th><th>Type</th><th>Position</th><th>Comment</th></tr>";
                foreach ($newColumns as $col) {
                    echo "<tr>";
                    echo "<td><code>{$col['COLUMN_NAME']}</code></td>";
                    echo "<td>{$col['COLUMN_TYPE']}</td>";
                    echo "<td>#{$col['ORDINAL_POSITION']}</td>";
                    echo "<td>{$col['COLUMN_COMMENT']}</td>";
                    echo "</tr>";
                }
                echo "</table>";

                echo "</div>";

                echo "<div class='info'>";
                echo "<h4>📝 Next Steps:</h4>";
                echo "<ol>";
                echo "<li>✅ Database is now ready</li>";
                echo "<li>✅ Go back to registration form</li>";
                echo "<li>✅ Fill and submit the form</li>";
                echo "<li>✅ Registration should work perfectly!</li>";
                echo "</ol>";
                echo "</div>";

                echo "<a href='register.php' class='btn'>← Back to Registration Form</a>";

            } else {
                throw new Exception("Verification failed: Expected 2 columns, found " . count($newColumns));
            }

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            echo "<div class='error'>";
            echo "<h3>❌ Error Occurred</h3>";
            echo "<p><strong>Error Message:</strong></p>";
            echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";

            if ($e->getCode()) {
                echo "<p><strong>Error Code:</strong> " . htmlspecialchars($e->getCode()) . "</p>";
            }

            echo "<details><summary style='cursor: pointer; color: #667eea;'>View Stack Trace</summary>";
            echo "<pre style='font-size: 10px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            echo "</details>";
            echo "</div>";

            echo "<div class='warning'>";
            echo "<h4>💡 Manual Fix Option:</h4>";
            echo "<p>If auto-fix fails, you can run these SQL queries manually in phpMyAdmin:</p>";
            echo "<pre>";
            echo "-- Replace 'alamat' with any existing column name from your table\n";
            echo "ALTER TABLE pendaftar \n";
            echo "ADD COLUMN kontak_wa VARCHAR(15) DEFAULT NULL \n";
            echo "COMMENT 'Nomor WhatsApp yang bisa dihubungi';\n\n";
            echo "ALTER TABLE pendaftar \n";
            echo "ADD COLUMN nama_kontak_wa VARCHAR(100) DEFAULT NULL \n";
            echo "COMMENT 'Nama pemilik nomor WhatsApp';";
            echo "</pre>";
            echo "<p><small><em>Note: The AFTER clause is optional. Without it, columns will be added at the end of the table.</em></small></p>";
            echo "</div>";

            echo "<a href='register.php' class='btn'>Try Registration Anyway →</a>";
        }
        ?>

    </div>
</body>

</html>