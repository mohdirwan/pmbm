-- Update Syarat Jalur Akademik untuk sync dengan form upload
-- Date: 2026-02-08

UPDATE `jalur_pendaftaran` 
SET `syarat` = 'Pas Foto 3x4 Berlatar Merah, Rapor Asli, Surat Keterangan Rata Rata Nilai, Surat Keterangan Ranking/Peringkat, Sertifikat Prestasi Akademik, Akta Kelahiran, Print Out NISN'
WHERE `nama_jalur` = 'Jalur Akademik';
