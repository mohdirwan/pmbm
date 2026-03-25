# Update Upload Berkas - Disesuaikan dengan Jalur Pendaftaran

## Perubahan yang Dilakukan

### 1. File: `siswa/upload_berkas.php`
**Perubahan Utama:**
- Menambahkan query untuk mendapatkan informasi jalur pendaftaran siswa dari database
- Membuat logika switch-case untuk menentukan dokumen yang perlu diupload sesuai jalur
- Menambahkan informasi jalur pendaftaran di bagian header halaman

**Dokumen per Jalur Pendaftaran:**

#### Jalur Akademik
1. Pas Foto
2. Rapor Asli
3. Surat Keterangan Nilai Rata-rata
4. Surat Keterangan Ranking
5. Kartu Keluarga
6. Akta Kelahiran

#### Jalur Minat Bakat Bidang Akademik
1. Pas Foto
2. Rapor Asli
3. Surat Keterangan Nilai Rata-rata
4. Surat Keterangan Prestasi
5. Sertifikat Prestasi
6. Kartu Keluarga
7. Akta Kelahiran

#### Jalur Minat Bakat Bidang Akademik Tanpa Tes Tertulis
1. Pas Foto
2. Rapor Asli
3. Surat Keterangan Nilai Rata-rata
4. Surat Keterangan Prestasi
5. Sertifikat Prestasi
6. Kartu Keluarga
7. Akta Kelahiran

#### Jalur Minat Bakat Bidang Non-Akademik
1. Pas Foto
2. Rapor Asli
3. Surat Keterangan Nilai Rata-rata
4. Surat Keterangan Prestasi
5. Sertifikat Prestasi
6. Kartu Keluarga
7. Akta Kelahiran

#### Jalur Minat Bakat Bidang Non-Akademik Tanpa Tes Tertulis
1. Pas Foto
2. Rapor Asli
3. Surat Keterangan Nilai Rata-rata
4. Surat Keterangan Prestasi
5. Sertifikat Prestasi
6. Kartu Keluarga
7. Akta Kelahiran

#### Jalur Tahfidz
1. Pas Foto
2. Rapor Asli
3. Surat Keterangan Nilai Rata-rata
4. Surat Keterangan Tahfidz
5. Sertifikat Tahfidz
6. Kartu Keluarga
7. Akta Kelahiran

### 2. File: `migration_dokumen_jalur.sql`
**Kolom Baru yang Ditambahkan ke Tabel `pendaftar`:**
- `file_nilai_rata` - Surat Keterangan Nilai Rata-rata
- `file_ranking` - Surat Keterangan Ranking
- `file_surat_prestasi` - Surat Keterangan Prestasi
- `file_sertifikat_prestasi` - Sertifikat Prestasi
- `file_surat_tahfidz` - Surat Keterangan Tahfidz
- `file_sertifikat_tahfidz` - Sertifikat Tahfidz

**Kolom yang Sudah Ada Sebelumnya:**
- `foto_siswa`
- `file_kk` (Kartu Keluarga)
- `file_akta` (Akta Kelahiran)
- `file_rapor` (Rapor Asli)

### 3. File: `run_migration_dokumen.php`
Script PHP untuk menjalankan migration database secara otomatis.

## Cara Penggunaan

### 1. Jalankan Migration Database (SUDAH DIJALANKAN)
```bash
C:\xampp\php\php.exe run_migration_dokumen.php
```

### 2. Testing
1. Login sebagai siswa yang sudah terdaftar
2. Cek jalur pendaftaran yang dipilih saat registrasi
3. Buka menu "Upload Berkas"
4. Pastikan hanya dokumen yang sesuai jalur yang ditampilkan

## Catatan Penting

1. **Backward Compatibility**: Sistem tetap mendukung data yang sudah ada sebelumnya
2. **Default Behavior**: Jika jalur pendaftaran tidak ditemukan, sistem akan menampilkan dokumen default (Jalur Akademik)
3. **File Storage**: Semua file disimpan di folder `uploads/` dengan format nama: `{timestamp}_{field_name}_{siswa_id}.{ext}`

## File yang Dimodifikasi
- ✅ `siswa/upload_berkas.php` - Logic upload berkas disesuaikan dengan jalur
- ✅ `migration_dokumen_jalur.sql` - Migration untuk menambah kolom baru
- ✅ `run_migration_dokumen.php` - Script runner migration

## Status Migration
✅ **BERHASIL DIJALANKAN** - 1 statement executed successfully
- Kolom baru berhasil ditambahkan ke tabel `pendaftar`
