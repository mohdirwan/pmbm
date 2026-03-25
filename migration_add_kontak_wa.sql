-- Migration: Add kontak_wa fields to pendaftar table
-- Date: 2026-02-07
-- Purpose: Menambahkan kolom untuk nomor WhatsApp kontak dan nama pemilik untuk notifikasi

ALTER TABLE `pendaftar` 
ADD COLUMN IF NOT EXISTS `kontak_wa` VARCHAR(15) NULL DEFAULT NULL COMMENT 'Nomor WhatsApp yang bisa dihubungi' AFTER `no_hp_ibu`,
ADD COLUMN IF NOT EXISTS `nama_kontak_wa` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Nama pemilik nomor WhatsApp' AFTER `kontak_wa`;

-- Verify columns added
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
    AND COLUMN_NAME IN ('kontak_wa', 'nama_kontak_wa')
ORDER BY ORDINAL_POSITION;
