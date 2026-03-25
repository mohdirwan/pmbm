-- 1. Table Jalur Pendaftaran
CREATE TABLE IF NOT EXISTS `jalur_pendaftaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_jalur` varchar(50) NOT NULL,
  `kuota` int(11) NOT NULL DEFAULT 0,
  `syarat` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `jalur_pendaftaran` (`nama_jalur`, `kuota`) VALUES
('Zonasi', 150),
('Prestasi', 50),
('Afirmasi', 30),
('Perpindahan Orang Tua', 10)
ON DUPLICATE KEY UPDATE nama_jalur=nama_jalur;

-- 2. Table Kelas / Rombel
CREATE TABLE IF NOT EXISTS `kelas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kelas` varchar(50) NOT NULL,
  `kapasitas` int(11) NOT NULL DEFAULT 32,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Activity Log
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11),
  `action` varchar(255) NOT NULL,
  `details` text,
  `ip_address` varchar(45),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Update Users Role to support more roles
ALTER TABLE `users` MODIFY COLUMN `role` enum('admin','operator','verifikator','kepsek') NOT NULL DEFAULT 'admin';

-- 5. Update Pendaftar for new features
ALTER TABLE `pendaftar`
ADD COLUMN `jalur_id` int(11),
ADD COLUMN `catatan_verifikasi` text,
ADD COLUMN `status_daftar_ulang` enum('Belum','Sudah') DEFAULT 'Belum',
ADD COLUMN `nilai_rapor_rata2` float DEFAULT 0.0,
ADD COLUMN `jarak_rumah` float DEFAULT 0.0 COMMENT 'in km';
