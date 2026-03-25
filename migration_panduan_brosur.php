<?php
require_once 'includes/config.php';

try {
    // Create table for dynamic panduan & brosur
    $sql = "CREATE TABLE IF NOT EXISTS panduan_brosur (
        id INT AUTO_INCREMENT PRIMARY KEY,
        judul VARCHAR(255) NOT NULL,
        tipe ENUM('file', 'video') DEFAULT 'file',
        file_path VARCHAR(500),
        video_url VARCHAR(500),
        icon_class VARCHAR(100) DEFAULT 'fa-book-open',
        color_class VARCHAR(50) DEFAULT 'primary',
        urutan INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);
    echo "✅ Tabel 'panduan_brosur' berhasil dibuat!<br>";

    // Insert default data
    $defaultData = [
        [
            'judul' => 'Petunjuk Teknis PMBM',
            'tipe' => 'file',
            'icon_class' => 'fa-book-open',
            'color_class' => 'primary',
            'urutan' => 1
        ],
        [
            'judul' => 'Brosur Pendaftaran',
            'tipe' => 'file',
            'icon_class' => 'fa-file-pdf',
            'color_class' => 'success',
            'urutan' => 2
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO panduan_brosur (judul, tipe, icon_class, color_class, urutan) VALUES (?, ?, ?, ?, ?)");

    foreach ($defaultData as $data) {
        $stmt->execute([
            $data['judul'],
            $data['tipe'],
            $data['icon_class'],
            $data['color_class'],
            $data['urutan']
        ]);
    }

    echo "✅ Data default berhasil ditambahkan!<br>";
    echo "<br><strong>Migrasi selesai!</strong><br>";
    echo "<a href='admin/panduan_brosur.php'>Ke Halaman Panduan & Brosur</a>";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>