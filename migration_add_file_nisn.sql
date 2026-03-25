-- Migration: Add file_nisn column for NISN print out upload
-- Date: 2026-02-08

ALTER TABLE `pendaftar` 
ADD COLUMN `file_nisn` VARCHAR(255) NULL AFTER `file_akta`;
