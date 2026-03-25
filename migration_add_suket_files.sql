-- Migration: Add File Upload Fields to Surat Keterangan
-- Date: 2026-02-08

ALTER TABLE `surat_keterangan` 
ADD COLUMN `file_preview_pdf` VARCHAR(255) DEFAULT NULL AFTER `template_file`,
ADD COLUMN `file_template_docx` VARCHAR(255) DEFAULT NULL AFTER `file_preview_pdf`;
