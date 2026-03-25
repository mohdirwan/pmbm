-- 1. Tambahkan kolom nilai ujian untuk integrasi CBT
ALTER TABLE pendaftar ADD COLUMN nilai_ujian DECIMAL(5,2) DEFAULT 0;

-- 2. Tambahkan kolom untuk status daftar ulang
ALTER TABLE pendaftar ADD COLUMN status_daftar_ulang ENUM('Belum', 'Sudah') DEFAULT 'Belum';
ALTER TABLE pendaftar ADD COLUMN tanggal_daftar_ulang DATETIME NULL;

-- 3. Pastikan tabel settings memiliki struktur yang benar (jika belum ada)
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 4. Insert default settings jika belum ada
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES 
('announcement_status', 'closed'),
('announcement_title', 'Pengumuman Kelulusan'),
('announcement_body', 'Hasil seleksi belum diumumkan.'),
('cbt_url', ''),
('cbt_token', ''),
('cbt_client_id', '');
