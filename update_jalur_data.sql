-- Update Jalur Pendaftaran dengan Data Lengkap
-- Date: 2026-02-08

-- Clear existing data
TRUNCATE TABLE jalur_pendaftaran;

-- Insert Jalur dengan Syarat Lengkap
INSERT INTO `jalur_pendaftaran` (`nama_jalur`, `syarat`) VALUES
(
    'Jalur Akademik',
    'Pas Foto terbaru (3x4, latar merah, seragam sekolah/madrasah), Rapor Asli, Surat Keterangan Rata-rata Nilai (minimal 87.00, ditandatangani & stempel basah Kepala Sekolah/Madrasah), Surat Keterangan Ranking/Peringkat untuk jalur melalui tes (ditandatangani & stempel basah, sesuai ketentuan peringkat), Sertifikat Prestasi Akademik jika ada/jalur tanpa tes (Juara 1-3, minimal tingkat Kab/Kota atau Provinsi, jenis lomba: OSN/OMI/MRC/OBA), Kartu Keluarga (KK), Akta Kelahiran / Surat Kenal Lahir, Print Out NISN'
),
(
    'Jalur Minat Bakat Bidang Akademik',
    'Pas Foto terbaru (3x4, latar merah, seragam sekolah/madrasah), Surat Keterangan Rata-rata Nilai (minimal 87.00, ditandatangani & stempel basah Kepala Sekolah/Madrasah), Surat Keterangan Prestasi (ditandatangani & stempel basah Kepala Sekolah/Madrasah), Sertifikat Prestasi Akademik (Juara 1-3, minimal tingkat Kab/Kota atau Provinsi, jenis lomba: OSN/OMI/MRC/OBA), Kartu Keluarga (KK), Akta Kelahiran / Surat Kenal Lahir'
),
(
    'Jalur Minat Bakat Bidang Akademik Tanpa Tes Tertulis', 
    'Pas Foto terbaru (3x4, latar merah, seragam sekolah/madrasah), Surat Keterangan Rata-rata Nilai (minimal 87.00, ditandatangani & stempel basah Kepala Sekolah/Madrasah), Surat Keterangan Prestasi (ditandatangani & stempel basah Kepala Sekolah/Madrasah), Sertifikat Prestasi Akademik (Juara 1-3, minimal tingkat Kab/Kota atau Provinsi, jenis lomba: OSN/OMI/MRC/OBA), Kartu Keluarga (KK), Akta Kelahiran / Surat Kenal Lahir'
),
(
    'Jalur Minat Bakat Bidang Non-Akademik',
    'Pas Foto terbaru (3x4, latar merah, seragam sekolah/madrasah), Surat Keterangan Rata-rata Nilai (minimal 87.00, ditandatangani & stempel basah Kepala Sekolah/Madrasah), Sertifikat Prestasi Non-Akademik (Juara 1-3, minimal tingkat Kab/Kota atau Provinsi, bidang Seni atau Olahraga: FLS3N/O2SN/MTQ Tilawah), Surat Keterangan Prestasi (ditandatangani & stempel basah Kepala Sekolah/Madrasah), Kartu Keluarga (KK), Akta Kelahiran / Surat Kenal Lahir'
),
(
    'Jalur Minat Bakat Bidang Non-Akademik Tanpa Tes Tertulis',
    'Pas Foto terbaru (3x4, latar merah, seragam sekolah/madrasah), Surat Keterangan Rata-rata Nilai (minimal 87.00, ditandatangani & stempel basah Kepala Sekolah/Madrasah), Sertifikat Prestasi Non-Akademik (Juara 1-3, minimal tingkat Kab/Kota atau Provinsi, bidang Seni atau Olahraga: FLS3N/O2SN/MTQ Tilawah), Surat Keterangan Prestasi (ditandatangani & stempel basah Kepala Sekolah/Madrasah), Kartu Keluarga (KK), Akta Kelahiran / Surat Kenal Lahir'
),
(
    'Jalur Tahfidz',
    'Pas Foto terbaru (3x4, latar merah, seragam sekolah/madrasah), Surat Keterangan Rata-rata Nilai (minimal 87.00, ditandatangani & stempel basah Kepala Sekolah/Madrasah), Sertifikat Tahfidz (minimal hafalan 3 juz, dikeluarkan oleh MI/SD/LPTQ), Kartu Keluarga (KK), Akta Kelahiran / Surat Kenal Lahir'
);
