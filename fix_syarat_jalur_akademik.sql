-- Fix Syarat Jalur Akademik (Clean Data)
-- Date: 2026-02-08
-- Menghapus typo dan angka yang tidak perlu

UPDATE `jalur_pendaftaran` 
SET `syarat` = 'Pas Foto 3x4 Berlatar Merah, Rapor Asli, Surat Keterangan Rata-rata Nilai, Surat Keterangan Ranking/Peringkat, Sertifikat Prestasi Akademik, Kartu Keluarga, Akta Kelahiran, Print Out NISN'
WHERE `nama_jalur` = 'Jalur Akademik';
