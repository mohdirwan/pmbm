# Upload Berkas - Dynamic Requirements Update

## Masalah:
File `siswa/upload_berkas.php` menggunakan hardcoded document requirements untuk setiap jalur pendaftaran (switch case). Ini tidak sync dengan data `syarat` di tabel `jalur_pendaftaran`.

## Solusi:
Update code untuk parsing syarat dari database dan generate list dokumen secara dinamis.

## Perubahan yang Diperlukan:

### 1. Ambil data jalur lengkap dengan syarat dari database
```php
// OLD
$stmt_jalur = $pdo->prepare("SELECT jp.nama_jalur FROM pendaftar p 
                               LEFT JOIN jalur_pendaftaran jp ON p.jalur_id = jp.id 
                               WHERE p.id = ?");

// NEW  
$stmt_jalur = $pdo->prepare("SELECT jp.* FROM pendaftar p 
                               LEFT JOIN jalur_pendaftaran jp ON p.jalur_id = jp.id 
                               WHERE p.id = ?");
```

### 2. Map syarat ke field database
Buat mapping otomatis dari nama syarat ke field database:
- "Pas Foto" → `foto_siswa`
- "Rapor Asli" → `file_rapor`
- "Surat Keterangan Nilai Rata-rata" → `file_nilai_rata`
- "Surat Keterangan Ranking" → `file_ranking`
- "Surat Keterangan Prestasi" → `file_surat_prestasi`
- "Sertifikat Prestasi" → `file_sertifikat_prestasi`
- "Surat Keterangan Tahfidz" → `file_surat_tahfidz`
- "Sertifikat Tahfidz" → `file_sertifikat_tahfidz`
- "Kartu Keluarga" → `file_kk`
- "Akta Kelahiran" \ "Surat Kenal Lahir" → `file_akta`

### 3. Parse syarat dari database
```php
// Parse syarat (comma separated)
$syarat_array = array_map('trim', explode(',', $jalur_data['syarat']));

// Build docs array dynamically
$docs = [];
$field_mapping = [...]; // mapping array

foreach ($syarat_array as $syarat) {
    // Extract main requirement name
    // Map to field
    // Add to $docs array
}
```

### 4. Handle Detail dalam Kurung
Syarat seperti "Pas Foto terbaru (3x4, latar merah, seragam sekolah/madrasah)" perlu di-parse:
- Nama utama: "Pas Foto terbaru"
- Detail: "(3x4, latar merah, seragam sekolah/madrasah)"

## Benefit:
✅ Admin bisa update syarat di menu Jalur tanpa coding
✅ Upload berkas otomatis sync dengan syarat jalur
✅ Maintainable & flexible

## Catatan:
- Pastikan field database sudah ada untuk semua jenis syarat
- Bisa tambah field baru jika diperlukan via migration
