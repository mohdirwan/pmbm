# Dokumentasi Perubahan Upload Berkas di Registration

## 📌 Overview

Upload berkas telah **dipindahkan** dari halaman siswa (setelah login) ke dalam form registrasi (`register.php`) sebagai **Step 5** - Upload Berkas Persyaratan.

**✨ NEW FEATURE:** Step indicators (icon progress bar) sekarang **CLICKABLE** untuk navigasi cepat!

---

## 🎯 Fitur Navigation

### **Clickable Step Indicators**

User sekarang bisa klik langsung pada icon step di progress bar untuk:
- ✅ **Navigasi cepat** ke step manapun yang sudah dilewati
- ✅ **Visual feedback** dengan hover effect (scale & shadow)
- ✅ **Smooth transition** antar step

**Cara Penggunaan:**
1. Klik icon step yang ingin dituju (💡, 🏫, 👥, 📋, 📤)
2. Form akan otomatis pindah ke step tersebut
3. Progress bar tetap update sesuai posisi

---

## 🔄 Alur Pendaftaran Baru

### Sebelumnya:
```
Step 1: Data Siswa
Step 2: Asal Sekolah  
Step 3: Data Orang Tua
Step 4: Rekap Nilai Rapor
     ↓
Submit → Login → Upload Berkas
```

### Sekarang:
```
Step 1: Data Siswa
Step 2: Asal Sekolah
Step 3: Data Orang Tua
Step 4: Rekap Nilai Rapor
Step 5: Upload Berkas Persyaratan ← BARU!
     ↓
Submit (selesai sekaligus dengan dokumen)
```

---

## ✨ Perubahan yang Dilakukan

### 1. **File: `register.php`**

#### a. Progress Bar (Step Indicator)
- Ditambahkan **Step 5 indicator** dengan icon `fa-file-upload`

```html
<div class="step" id="step5-indicator" title="Upload Berkas">
    <i class="fas fa-file-upload"></i>
</div>
```

#### b. Button Navigation Step 4
- Tombol submit di Step 4 diubah menjadi tombol "Lanjut" ke Step 5

```html
<!-- Sebelumnya -->
<button type="submit">Kirim Pendaftaran</button>

<!-- Sekarang -->
<button type="button" onclick="nextStep(5)">Lanjut</button>
```

#### c. Step 5: Upload Berkas
Ditambahkan section baru untuk upload dokumen:

**Fitur:**
- **Dinamis berdasarkan Jalur Pendaftaran**
- Field upload menyesuaikan dengan jalur yang dipilih di Step 1
- Validation: JPG, PNG, PDF (Max 2MB per file)
- Required checkbox konfirmasi kebenaran dokumen

**Daftar Dokumen per Jalur:**

| Jalur | Dokumen yang Diminta |
|-------|---------------------|
| **Jalur Akademik** | Pas Foto, Rapor, Surat Nilai Rata-rata, Surat Ranking, KK, Akta |
| **Jalur Minat Bakat Akademik** | Pas Foto, Rapor, Surat Nilai Rata-rata, Surat Prestasi, Sertifikat Prestasi, KK, Akta |
| **Jalur Tahfidz** | Pas Foto, Rapor, Surat Nilai Rata-rata, Surat Tahfidz, Sertifikat Tahfidz, KK, Akta |
| **Default** | Pas Foto, Rapor, Surat Nilai Rata-rata, KK, Akta |

#### d. JavaScript
Ditambahkan fungsi `loadDocumentUploadFields()`:
- Membaca jalur pendaftaran yang dipilih
- Generate field upload dokumen sesuai jalur
- Auto-load saat user masuk ke Step 5

```javascript
function loadDocumentUploadFields() {
    // Extract jalur name from selected option
    const jalurName = selectedOption.text.split('(')[0].trim();
    
    // Map documents based on jalur
    const docs = documentMappings[jalurName] || defaultDocs;
    
    // Build HTML dynamically
    docs.forEach(doc => {
        html += `<input type="file" name="document_${doc.field}" required>`;
    });
}
```

#### e. Clickable Step Indicators
**NEW!** Ditambahkan fungsi `goToStep()` untuk navigasi via click pada step indicator:

**HTML Update:**
```html
<!-- Setiap step indicator sekarang punya onclick handler -->
<div class="step" id="step1-indicator" 
     onclick="goToStep(1)" 
     style="cursor: pointer;">
    <i class="fas fa-user-graduate"></i>
</div>
```

**JavaScript Function:**
```javascript
function goToStep(step) {
    // Hide all steps
    document.querySelectorAll('.form-step').forEach(el => el.classList.add('d-none'));
    
    // Show target step
    const targetStep = document.getElementById('step' + step);
    if (targetStep) {
        targetStep.classList.remove('d-none');
        
        // Update indicators
        document.querySelectorAll('.step').forEach((el, index) => {
            if (index + 1 <= step) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });

        // Load documents if going to step 5
        if (step === 5) {
            loadDocumentUploadFields();
        }

        // Scroll to top
        window.scrollTo(0, 0);
    }
}
```

---

### 3. **File: `assets/css/style.css`**

Ditambahkan hover effects untuk step indicators:

```css
.step:hover {
    transform: scale(1.15);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.step.active:hover {
    transform: scale(1.15);
    box-shadow: 0 4px 15px rgba(15, 81, 50, 0.4);
}
```

**Efek yang dihasilkan:**
- 🔍 **Scale up (15%)** saat hover
- 💫 **Shadow effect** untuk depth
- ⚡ **Smooth transition** (0.3s)
- 👆 **Cursor pointer** untuk feedback visual

---

### 2. **File: `process_register.php`**

Ditambahkan proses upload file setelah insert data pendaftar:

#### a. Get Inserted ID
```php
$pendaftar_id = $pdo->lastInsertId();
```

#### b. Upload File Handling
```php
// Setup upload directory
$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Allowed types & max size
$allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
$maxFileSize = 2097152; // 2MB
```

#### c. Process Each Document
```php
foreach ($documentFields as $field) {
    $fileInputName = 'document_' . $field;
    
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === 0) {
        // Validate file type & size
        // Generate unique filename
        // Move uploaded file
        // Store filename in array
    }
}
```

#### d. Update Database
```php
// Update pendaftar with uploaded file names
if (!empty($uploadedFiles)) {
    $updateSql = "UPDATE pendaftar SET " . implode(', ', $updateParts) . " WHERE id = ?";
    $updateStmt->execute($updateValues);
}
```

---

## 📊 Database Fields

File dokumen disimpan di tabel `pendaftar` dengan field berikut:

| Field | Keterangan |
|-------|-----------|
| `foto_siswa` | Pas foto siswa |
| `file_rapor` | Scan rapor asli |
| `file_nilai_rata` | Surat keterangan nilai rata-rata |
| `file_ranking` | Surat keterangan ranking (Jalur Akademik) |
| `file_surat_prestasi` | Surat keterangan prestasi (Jalur Prestasi) |
| `file_sertifikat_prestasi` | Sertifikat prestasi (Jalur Prestasi) |
| `file_surat_tahfidz` | Surat keterangan tahfidz (Jalur Tahfidz) |
| `file_sertifikat_tahfidz` | Sertifikat tahfidz (Jalur Tahfidz) |
| `file_kk` | Kartu Keluarga |
| `file_akta` | Akta Kelahiran |

---

## 🎯 Keuntungan Sistem Baru

### ✅ **Efisiensi untuk Pengguna**
- **One-time process**: Siswa cukup mendaftar sekali dengan lengkap
- **Tidak perlu login lagi**: Langsung upload dokumen saat registrasi
- **Lebih cepat**: Semua proses selesai dalam satu form

### ✅ **Validasi Lebih Baik**
- Upload dokumen menjadi **bagian dari validasi** pendaftaran
- Admin langsung dapat melihat dokumen pendaftar
- Mengurangi data pendaftar yang tidak lengkap

### ✅ **UX/UI Lebih Baik**
- Progress bar yang jelas (5 langkah)
- Form field dinamis sesuai jalur
- Validasi file otomatis (type & size)

---

## 🔧 Testing

### Test Case 1: Registrasi dengan Upload
1. Buka `register.php`
2. Isi Step 1-4 seperti biasa
3. Klik "Lanjut" di Step 4
4. **Expected**: Muncul Step 5 dengan field upload dokumen sesuai jalur
5. Upload semua dokumen yang required
6. Submit form
7. **Expected**: Data dan file tersimpan di database

### Test Case 2: Dynamic Document List
1. Pilih "Jalur Akademik" di Step 1
2. Lanjut sampai Step 5
3. **Expected**: Muncul 6 dokumen (Foto, Rapor, Nilai Rata, Ranking, KK, Akta)
4. Kembali ke Step 1
5. Ubah ke "Jalur Tahfidz"
6. Lanjut sampai Step 5
7. **Expected**: Muncul 7 dokumen dengan Surat & Sertifikat Tahfidz

### Test Case 3: Clickable Navigation
1. Buka `register.php`
2. Isi Step 1 dan klik "Lanjut"
3. Di Step 2, **klik icon Step 1** di progress bar
4. **Expected**: Form kembali ke Step 1, progress bar update
5. Lanjut ke Step 3
6. **Klik icon Step 2** di progress bar
7. **Expected**: Form kembali ke Step 2
8. **Hover mouse** di atas icon step
9. **Expected**: Icon membesar (scale 1.15x) dengan shadow effect

---

## 📝 Notes

- File upload maksimal **2MB per file**
- Format yang diterima: **JPG, PNG, PDF**
- File disimpan di folder `uploads/` dengan naming: `timestamp_fieldname_pendaftarid.ext`
- Dokumen yang **tidak required** akan di-skip jika tidak diupload (tidak error)
- Semua upload di-handle dalam **satu transaksi database** (commit/rollback)

---

## 🚀 Next Steps (Optional)

Jika ingin pengembangan lebih lanjut:

1. **Preview before upload**: Tambahkan preview gambar/PDF sebelum submit
2. **Progress indicator**: Tampilkan progress bar saat upload file
3. **Compress image**: Auto-compress gambar yang terlalu besar
4. **Drag & drop**: Implementasi drag-and-drop untuk upload
5. **Validation feedback**: Tampilkan error message per-file jika gagal upload

---

**Status:** ✅ **FULLY IMPLEMENTED & READY TO USE**

Upload berkas sudah berhasil dipindahkan ke dalam form registrasi sebagai Step 5!

---

**Date:** 07 Februari 2026  
**Last Updated:** 07 Februari 2026 (19:35 WIB)

**Modified Files:**
- ✅ `register.php` - Added Step 5 & clickable navigation
- ✅ `process_register.php` - Document upload handling
- ✅ `assets/css/style.css` - Hover effects for step indicators
- 📄 `UPLOAD_BERKAS_REGISTER.md` - Documentation
- 📄 `migration_add_document_fields.sql` - Database migration
- 📄 `run_migration_add_documents.php` - Migration runner

**Features Added:**
1. ✅ Step 5: Upload Berkas Persyaratan
2. ✅ Dynamic document list based on jalur
3. ✅ **Clickable step indicators** for easy navigation
4. ✅ **Hover effects** with scale & shadow
5. ✅ File upload validation & processing
6. ✅ Database integration
