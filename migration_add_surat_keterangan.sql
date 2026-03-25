-- Migration: Add Surat Keterangan Table
-- Date: 2026-02-08

CREATE TABLE IF NOT EXISTS `surat_keterangan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_surat` varchar(255) NOT NULL,
  `keterangan` text,
  `template_file` varchar(255) DEFAULT NULL,
  `urutan` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Default Data
INSERT INTO `surat_keterangan` (`nama_surat`, `keterangan`, `urutan`, `is_active`) VALUES
('Surat Keterangan PRESTASI', 'Surat keterangan prestasi dari sekolah asal untuk jalur PMBM Prestasi', 1, 1),
('Surat Keterangan PERINGKAT', 'Surat keterangan peringkat kelas dari sekolah asal', 2, 1),
('Surat Keterangan Nilai Rata-rata', 'Surat keterangan nilai rata-rata rapor dari sekolah asal', 3, 1),
('Surat Keterangan TAHFIDZ', 'Surat keterangan hafalan Al-Quran untuk jalur Tahfidz', 4, 1);
