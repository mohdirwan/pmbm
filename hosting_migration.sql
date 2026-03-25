-- ============================================
-- SQL UNTUK HOSTING
-- ============================================
-- Jalankan script ini di phpMyAdmin hosting
-- Pastikan sudah memilih database yang benar
-- ============================================

-- Cek tabel yang sudah ada
SHOW TABLES;

-- ============================================
-- 1. TABEL PANDUAN & BROSUR (BARU)
-- ============================================

CREATE TABLE IF NOT EXISTS `panduan_brosur` (
    `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `judul` VARCHAR(255) NOT NULL,
    `tipe` ENUM('file', 'video') DEFAULT 'file',
    `file_path` VARCHAR(500) DEFAULT NULL,
    `video_url` VARCHAR(500) DEFAULT NULL,
    `icon_class` VARCHAR(100) DEFAULT 'fa-book-open',
    `color_class` VARCHAR(50) DEFAULT 'primary',
    `urutan` INT(11) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data default
INSERT INTO `panduan_brosur` (`judul`, `tipe`, `icon_class`, `color_class`, `urutan`) VALUES
('Petunjuk Teknis PMBM', 'file', 'fa-book-open', 'primary', 1),
('Brosur Pendaftaran', 'file', 'fa-file-pdf', 'success', 2)
ON DUPLICATE KEY UPDATE `judul` = VALUES(`judul`);

-- ============================================
-- 2. CEK TABEL YANG HARUS ADA
-- ============================================

-- Tabel users (admin)
SELECT COUNT(*) as total_users FROM `users`;

-- Tabel settings
SELECT COUNT(*) as total_settings FROM `settings`;

-- Tabel pendaftar
SELECT COUNT(*) as total_pendaftar FROM `pendaftar`;

-- Tabel jalur_pendaftaran
SELECT COUNT(*) as total_jalur FROM `jalur_pendaftaran`;

-- Tabel panduan_brosur (yang baru)
SELECT COUNT(*) as total_panduan FROM `panduan_brosur`;

-- ============================================
-- 3. VERIFIKASI DATA
-- ============================================

-- Lihat isi tabel panduan_brosur
SELECT * FROM `panduan_brosur` ORDER BY `urutan` ASC;

-- Lihat settings
SELECT * FROM `settings` WHERE `setting_key` LIKE '%panduan%' OR `setting_key` LIKE '%brosur%';

-- ============================================
-- SELESAI!
-- ============================================
-- Jika semua query berhasil, tabel sudah siap
-- Selanjutnya update file config.php dengan kredensial hosting
-- ============================================
