-- Migration: Add document fields to pendaftar table
-- Date: 2026-02-07
-- Purpose: Menambahkan kolom untuk menyimpan nama file dokumen yang diupload saat registrasi

-- Check if columns exist before adding
ALTER TABLE `pendaftar` 
ADD COLUMN IF NOT EXISTS `foto_siswa` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Pas Foto',
ADD COLUMN IF NOT EXISTS `file_rapor` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Scan Rapor Asli',
ADD COLUMN IF NOT EXISTS `file_nilai_rata` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Surat Keterangan Nilai Rata-rata',
ADD COLUMN IF NOT EXISTS `file_ranking` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Surat Keterangan Ranking (Jalur Akademik)',
ADD COLUMN IF NOT EXISTS `file_surat_prestasi` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Surat Keterangan Prestasi',
ADD COLUMN IF NOT EXISTS `file_sertifikat_prestasi` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Sertifikat Prestasi',
ADD COLUMN IF NOT EXISTS `file_surat_tahfidz` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Surat Keterangan Tahfidz',
ADD COLUMN IF NOT EXISTS `file_sertifikat_tahfidz` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Sertifikat Tahfidz',
ADD COLUMN IF NOT EXISTS `file_kk` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Kartu Keluarga',
ADD COLUMN IF NOT EXISTS `file_akta` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Akta Kelahiran';

-- Verify columns
SELECT 
    COLUMN_NAME, 
    COLUMN_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM 
    INFORMATION_SCHEMA.COLUMNS
WHERE 
    TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'pendaftar'
    AND COLUMN_NAME IN (
        'foto_siswa', 
        'file_rapor', 
        'file_nilai_rata', 
        'file_ranking',
        'file_surat_prestasi',
        'file_sertifikat_prestasi',
        'file_surat_tahfidz',
        'file_sertifikat_tahfidz',
        'file_kk',
        'file_akta'
    )
ORDER BY ORDINAL_POSITION;
