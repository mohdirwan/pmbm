<?php
/**
 * SUPER SIMPLE FIX: Add Kontak WA Columns
 * No AFTER clause - just add at the end of table
 */

require_once 'includes/config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>

<head>
    <title>Simple Database Fix</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
        }

        h1 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .msg {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 5px solid;
        }

        .success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }

        .info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
            font-weight: bold;
        }

        .btn:hover {
            background: #5568d3;
        }

        pre {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 12px;
        }

        code {
            background: #f5f5f5;
            padding: 2px 5px;
            border-radius: 3px;
            color: #d63384;
        }
    </style>
</head>

<body>
    <div class="box">
        <h1>🔧 Database Fix</h1>

        <?php
        try {
            // Check if columns exist
            $check = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                          WHERE TABLE_SCHEMA = DATABASE() 
                          AND TABLE_NAME = 'pendaftar' 
                          AND COLUMN_NAME IN ('kontak_wa', 'nama_kontak_wa')");

            $existing = $check->fetchAll(PDO::FETCH_COLUMN);
            $hasKontakWa = in_array('kontak_wa', $existing);
            $hasNamaKontakWa = in_array('nama_kontak_wa', $existing);

            echo "<div class='info'>";
            echo "<strong>Status Check:</strong><br>";
            echo "• kontak_wa: " . ($hasKontakWa ? "✅ Exists" : "❌ Missing") . "<br>";
            echo "• nama_kontak_wa: " . ($hasNamaKontakWa ? "✅ Exists" : "❌ Missing");
            echo "</div>";

            if ($hasKontakWa && $hasNamaKontakWa) {
                echo "<div class='success'>";
                echo "<strong>✅ All Done!</strong><br>";
                echo "Both columns already exist. No action needed.";
                echo "</div>";
                echo "<a href='register.php' class='btn'>← Back to Form</a>";
                exit;
            }

            // Add missing columns (simple version - no AFTER clause)
            $changes = [];

            if (!$hasKontakWa) {
                echo "<div class='info'>Adding kontak_wa...</div>";
                $pdo->exec("ALTER TABLE pendaftar ADD COLUMN kontak_wa VARCHAR(15) DEFAULT NULL");
                $changes[] = 'kontak_wa';
                echo "<div class='success'>✅ kontak_wa added!</div>";
            }

            if (!$hasNamaKontakWa) {
                echo "<div class='info'>Adding nama_kontak_wa...</div>";
                $pdo->exec("ALTER TABLE pendaftar ADD COLUMN nama_kontak_wa VARCHAR(100) DEFAULT NULL");
                $changes[] = 'nama_kontak_wa';
                echo "<div class='success'>✅ nama_kontak_wa added!</div>";
            }

            if (!empty($changes)) {
                echo "<div class='success'>";
                echo "<strong>✅ Success!</strong><br>";
                echo "Added " . count($changes) . " column(s):<br>";
                foreach ($changes as $col) {
                    echo "• <code>$col</code><br>";
                }
                echo "</div>";

                echo "<div class='info'>";
                echo "<strong>Next Steps:</strong><br>";
                echo "1. Go back to registration form<br>";
                echo "2. Fill and submit the form<br>";
                echo "3. It should work now!";
                echo "</div>";
            }

            echo "<a href='register.php' class='btn'>← Back to Registration Form</a>";

        } catch (PDOException $e) {
            echo "<div class='error'>";
            echo "<strong>❌ Database Error:</strong><br>";
            echo htmlspecialchars($e->getMessage());
            echo "</div>";

            echo "<div class='info'>";
            echo "<strong>💡 Manual Fix:</strong><br>";
            echo "Run this in phpMyAdmin:<br>";
            echo "<pre>";
            echo "ALTER TABLE pendaftar ADD COLUMN kontak_wa VARCHAR(15);\n";
            echo "ALTER TABLE pendaftar ADD COLUMN nama_kontak_wa VARCHAR(100);";
            echo "</pre>";
            echo "</div>";

            echo "<a href='register.php' class='btn'>Try Form Anyway →</a>";

        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<strong>❌ Error:</strong><br>";
            echo htmlspecialchars($e->getMessage());
            echo "</div>";
        }
        ?>

    </div>
</body>

</html>