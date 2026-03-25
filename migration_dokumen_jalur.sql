-- Menambahkan kolom-kolom dokumen tambahan untuk berbagai jalur pendaftaran
-- Dijalankan untuk mengakomodasi requirement dokumen berbeda per jalur

ALTER TABLE `pendaftar`
ADD COLUMN `file_nilai_rata` varchar(255) DEFAULT NULL COMMENT 'Surat Keterangan Nilai Rata-rata',
ADD COLUMN `file_ranking` varchar(255) DEFAULT NULL COMMENT 'Surat Keterangan Ranking',
ADD COLUMN `file_surat_prestasi` varchar(255) DEFAULT NULL COMMENT 'Surat Keterangan Prestasi',
ADD COLUMN `file_sertifikat_prestasi` varchar(255) DEFAULT NULL COMMENT 'Sertifikat Prestasi',
ADD COLUMN `file_surat_tahfidz` varchar(255) DEFAULT NULL COMMENT 'Surat Keterangan Tahfidz',
ADD COLUMN `file_sertifikat_tahfidz` varchar(255) DEFAULT NULL COMMENT 'Sertifikat Tahfidz';

-- Informasi: Kolom yang sudah ada sebelumnya tidak akan diubah
-- - foto_siswa (sudah ada)
-- - file_kk (sudah ada)
-- - file_akta (sudah ada)
-- - file_rapor (sudah ada)
