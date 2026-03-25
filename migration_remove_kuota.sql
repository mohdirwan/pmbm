-- Migration: Remove Kuota Column from Jalur Pendaftaran
-- Date: 2026-02-08
-- Reason: Tidak ada batasan kuota untuk pendaftar

ALTER TABLE `jalur_pendaftaran` DROP COLUMN `kuota`;
