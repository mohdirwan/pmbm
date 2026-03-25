-- Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL UNIQUE,
  `setting_value` text,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('school_name', 'MTsN 1 Kota Pekanbaru'),
('ppdb_year', '2026/2027'),
('wave_name', 'Gelombang 1'),
('countdown_date', 'Mar 1, 2026 00:00:00'),
('contact_phone', '(0761) 123456'),
('contact_email', 'ppdb@mtsn1pekanbaru.sch.id'),
('hero_title', 'Mewujudkan Generasi Islami & Berprestasi'),
('hero_desc', 'Selamat datang di Portal Penerimaan Peserta Didik Baru MTsN 1 Kota Pekanbaru. Bergabunglah bersama kami untuk masa depan yang gemilang.')
ON DUPLICATE KEY UPDATE setting_key=setting_key;
