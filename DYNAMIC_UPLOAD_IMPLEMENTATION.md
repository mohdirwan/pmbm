# Dynamic Upload Berkas Implementation

## ✅ SELESAI DIIMPLEMENT!

### 📁 File yang Dibuat/Diupdate:

#### 1. **get_jalur_syarat.php** (NEW)
- **Fungsi:** API endpoint untuk fetch syarat jalur
- **Input:** `jalur_id` via GET parameter
- **Output:** JSON dengan list dokumen yang perlu diupload
- **Logic:** 
  - Menggunakan keyword mapping yang sama dengan `upload_berkas.php`
  - Parsing syarat dari database
  - Generate dynamic document list
  - Fallback ke dokumen minimal jika syarat kosong

#### 2. **register.php** (UPDATED)
- **Fungsi JavaScript Ditambahkan:**
  - `loadDocumentUploadFields()` - Load dokumen saat masuk Step 5
  - `generateDocumentFields()` - Generate HTML upload fields
  
### 🔧 Cara Kerja:

```
User Pilih Jalur di Step 1
         ↓
User Klik Step 5 (Upload Berkas)
         ↓
JavaScript loadDocumentUploadFields() dipanggil
         ↓
AJAX Fetch ke get_jalur_syarat.php?jalur_id=X
         ↓
Backend parse syarat → return document list
         ↓
JavaScript generate HTML upload fields
         ↓
User upload dokumen sesuai jalur
```

### 📋 Document Fields yang Di-generate:

**Format HTML:**
```html
<div class="col-md-6">
    <div class="card">
        <label>Nama Dokumen *</label>
        <input type="file" name="field_name" accept=".jpg,.jpeg,.png,.pdf" required>
        <div class="form-text">Format: JPG, PNG, PDF (Max 2MB)</div>
    </div>
</div>
```

**Required Fields (otomatis):**
- `foto_siswa` (Pas Foto) ✅
- `file_kk` (Kartu Keluarga) ✅
- `file_akta` (Akta Kelahiran) ✅

**Optional Fields:**
- Semua field lainnya sesuai syarat jalur

### 🎯 Fitur:

✅ **Dynamic Loading** - Dokumen muncul sesuai jalur yang dipilih
✅ **Loading State** - Spinner saat fetch data
✅ **Error Handling** - Alert jika gagal load
✅ **Responsive Layout** - Grid 2 kolom (col-md-6)
✅ **Visual Feedback** - Card design dengan icon
✅ **Validation** - Required fields otomatis
✅ **File Type Restriction** - Accept hanya JPG, PNG, PDF
✅ **Tips Section** - Panduan upload dokumen

### 📝 Contoh Output untuk Jalur Akademik:

Jika syarat di database:
```
Pas Foto 3x4 Berlatar Merah,
Rapor Asli,
Surat Keterangan Rata-rata Nilai,
Surat Keterangan Ranking/Peringkat,
Sertifikat Prestasi Akademik,
Kartu Keluarga,
Akta Kelahiran,
Print Out NISN
```

Maka form akan menampilkan 8 upload fields:
1. Pas Foto * (required)
2. Rapor Asli
3. Surat Keterangan Rata-rata Nilai
4. Surat Keterangan Ranking
5. Sertifikat Prestasi Akademik
6. Kartu Keluarga * (required)
7. Akta Kelahiran * (required)
8. Print Out NISN

### ⚠️ Catatan Penting:

1. **Database Harus Sudah Fix:**
   - Jalankan `run_migration_add_file_nisn.php`
   - Jalankan `run_fix_syarat_jalur_akademik.php`

2. **File Upload akan dikirim ke `process_register.php`:**
   - Pastikan `process_register.php` sudah handle upload file
   - Validasi file type & size di backend juga!

3. **Testing:**
   - Test dengan berbagai jalur
   - Pastikan dokumen yang muncul sesuai syarat di admin

### 🚀 Testing Guide:

1. Buka: `http://localhost/pmbm/register.php`
2. Isi data di Step 1, pilih **Jalur Akademik**
3. Lanjut sampai **Step 5: Upload Berkas**
4. **Expected Result:**
   - Loading spinner muncul
   - Banner "Jalur Pendaftaran Anda: Jalur Akademik"
   - 8 upload fields sesuai syarat
   - Tips section muncul di bawah

### 🔍 Debug API:

Test API endpoint langsung:
```
http://localhost/pmbm/get_jalur_syarat.php?jalur_id=1
```

Expected JSON Response:
```json
{
  "success": true,
  "jalur": {
    "id": "1",
    "nama_jalur": "Jalur Akademik",
    "syarat": "Pas Foto 3x4 Berlatar Merah, Rapor Asli, ..."
  },
  "documents": [
    {"label": "Pas Foto", "field": "foto_siswa"},
    {"label": "Rapor Asli", "field": "file_rapor"},
    ...
  ]
}
```

## ✨ READY TO TEST!
