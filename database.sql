-- Database Schema for PPDB MTsN 1 Kota Pekanbaru

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'admin',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin user (password: admin123 - CHANGE IMMEDIATELY)
INSERT INTO `users` (`username`, `password`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

CREATE TABLE IF NOT EXISTS `pendaftar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_pendaftaran` varchar(20) NOT NULL,
  `nisn` varchar(20) NOT NULL,
  `nik` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `tempat_lahir` varchar(50) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `agama` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `desa_kelurahan` varchar(50),
  `kecamatan` varchar(50),
  `kabupaten_kota` varchar(50),
  `provinsi` varchar(50),
  `kode_pos` varchar(10),
  `no_hp_siswa` varchar(15),
  `email` varchar(100),
  
  -- Data Sekolah Asal
  `asal_sekolah` varchar(100) NOT NULL,
  `npsn_sekolah` varchar(20),
  
  -- Data Ayah
  `nama_ayah` varchar(100),
  `nik_ayah` varchar(20),
  `tahun_lahir_ayah` year,
  `pendidikan_ayah` varchar(20),
  `pekerjaan_ayah` varchar(50),
  `penghasilan_ayah` varchar(50),
  `no_hp_ayah` varchar(15),
  
  -- Data Ibu
  `nama_ibu` varchar(100),
  `nik_ibu` varchar(20),
  `tahun_lahir_ibu` year,
  `pendidikan_ibu` varchar(20),
  `pekerjaan_ibu` varchar(50),
  `penghasilan_ibu` varchar(50),
  `no_hp_ibu` varchar(15),
  
  -- Data Wali (Optional)
  `nama_wali` varchar(100),
  `nik_wali` varchar(20),
  `pekerjaan_wali` varchar(50),
  `no_hp_wali` varchar(15),
  
  -- Berkas & Status
  `foto_siswa` varchar(255),
  `file_kk` varchar(255),
  `file_akta` varchar(255),
  `file_rapor` varchar(255),
  `status` enum('Pending','Terverifikasi','Ditolak','Diterima') DEFAULT 'Pending',
  `tanggal_daftar` timestamp DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_pendaftaran` (`no_pendaftaran`),
  UNIQUE KEY `nisn` (`nisn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
