ALTER TABLE `pendaftar` 
ADD COLUMN `jalur_pendaftaran` enum('Zonasi','Afirmasi','Prestasi','Perpindahan Orang Tua') NOT NULL DEFAULT 'Zonasi' AFTER `role`;
