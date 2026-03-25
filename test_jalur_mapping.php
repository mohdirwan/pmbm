<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Mapping Jalur Pendaftaran</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #0b2c24;
            border-bottom: 3px solid #ffc107;
            padding-bottom: 10px;
        }

        .jalur-section {
            margin: 20px 0;
            padding: 20px;
            background: #f9f9f9;
            border-left: 4px solid #0b2c24;
            border-radius: 5px;
        }

        .jalur-title {
            font-size: 1.3em;
            font-weight: bold;
            color: #0b2c24;
            margin-bottom: 10px;
        }

        .doc-list {
            list-style: none;
            padding: 0;
        }

        .doc-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .doc-list li:before {
            content: "✓ ";
            color: #28a745;
            font-weight: bold;
            margin-right: 10px;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            background: #ffc107;
            color: #000;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>📋 Mapping Dokumen per Jalur Pendaftaran</h1>
        <p>Berikut adalah mapping dokumen yang perlu diupload untuk setiap jalur pendaftaran:</p>

        <?php
        // Simulate the document mapping logic
        $jalur_mapping = [
            'Jalur Akademik' => [
                'Pas Foto',
                'Rapor Asli',
                'Surat Keterangan Nilai Rata-rata',
                'Surat Keterangan Ranking',
                'Kartu Keluarga',
                'Akta Kelahiran'
            ],
            'Jalur Minat Bakat Bidang Akademik' => [
                'Pas Foto',
                'Rapor Asli',
                'Surat Keterangan Nilai Rata-rata',
                'Surat Keterangan Prestasi',
                'Sertifikat Prestasi',
                'Kartu Keluarga',
                'Akta Kelahiran'
            ],
            'Jalur Minat Bakat Bidang Akademik Tanpa Tes Tertulis' => [
                'Pas Foto',
                'Rapor Asli',
                'Surat Keterangan Nilai Rata-rata',
                'Surat Keterangan Prestasi',
                'Sertifikat Prestasi',
                'Kartu Keluarga',
                'Akta Kelahiran'
            ],
            'Jalur Minat Bakat Bidang Non-Akademik' => [
                'Pas Foto',
                'Rapor Asli',
                'Surat Keterangan Nilai Rata-rata',
                'Surat Keterangan Prestasi',
                'Sertifikat Prestasi',
                'Kartu Keluarga',
                'Akta Kelahiran'
            ],
            'Jalur Minat Bakat Bidang Non-Akademik Tanpa Tes Tertulis' => [
                'Pas Foto',
                'Rapor Asli',
                'Surat Keterangan Nilai Rata-rata',
                'Surat Keterangan Prestasi',
                'Sertifikat Prestasi',
                'Kartu Keluarga',
                'Akta Kelahiran'
            ],
            'Jalur Tahfidz' => [
                'Pas Foto',
                'Rapor Asli',
                'Surat Keterangan Nilai Rata-rata',
                'Surat Keterangan Tahfidz',
                'Sertifikat Tahfidz',
                'Kartu Keluarga',
                'Akta Kelahiran'
            ]
        ];

        foreach ($jalur_mapping as $jalur => $dokumen) {
            echo '<div class="jalur-section">';
            echo '<div class="jalur-title">' . htmlspecialchars($jalur) . ' <span class="badge">' . count($dokumen) . ' Dokumen</span></div>';
            echo '<ul class="doc-list">';
            foreach ($dokumen as $doc) {
                echo '<li>' . htmlspecialchars($doc) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
        ?>

        <div style="margin-top: 30px; padding: 20px; background: #e3f2fd; border-radius: 5px;">
            <h3 style="margin-top: 0; color: #1976d2;">ℹ️ Informasi</h3>
            <p><strong>Status Migration Database:</strong> ✅ Berhasil</p>
            <p><strong>File Upload Path:</strong> <code>uploads/</code></p>
            <p><strong>Max File Size:</strong> 2 MB</p>
            <p><strong>Allowed Formats:</strong> JPG, PNG, PDF</p>
        </div>
    </div>
</body>

</html>