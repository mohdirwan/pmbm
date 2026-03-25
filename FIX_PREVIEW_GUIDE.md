# 🔧 FIX PREVIEW - COPY PASTE GUIDE

## Problem:
Preview modal hanya menampilkan Data Siswa sederhana. Missing:
- ❌ Data Siswa lengkap (Jalur, Provinsi, HP, Email)
- ❌ Asal Sekolah
- ❌ Data Orang Tua
- ❌ Rekap Nilai
- ❌ Dokumen Upload

## Solution:
Replace function `showPreviewData()` di register.php

## LANGKAH-LANGKAH:

### 1. Open `register.php`

### 2. Find Function (CTRL+F):
Cari text: `function showPreviewData()`
Akan ketemu di sekitar **line 1271**

### 3. SELECT CODE TO DELETE:
**FROM:** Line 1271 - `function showPreviewData() {`
**TO:** Line 1326 - `}` (closing bracket function showPreviewData)

**TOTAL: 56 lines**

Visual:
```
1270:         // Show preview data
1271:         function showPreviewData() {    ← START SELECT HERE
1272:             const form = ...
...
...
1325:             previewModal.show();
1326:         }                               ← END SELECT HERE (termasuk bracket)
1327: 
1328:         // Close preview and go back to edit
```

### 4. DELETE SELECTED CODE
Press `DELETE` or `BACKSPACE`

### 5. PASTE NEW CODE
- Open file: `PREVIEW_FUNCTION_READY.js`
- Copy ALL content (CTRL+A, CTRL+C)
- Paste di posisi yang tadi di-delete (CTRL+V)

### 6. SAVE
Press `CTRL+S`

### 7. TEST
```
1. Buka: http://localhost/pmbm/register.php
2. Fill form sampai Step 5
3. Upload beberapa file
4. Accept Pakta Integritas
5. Klik "Lanjut ke Pakta Integritas"
6. Modal preview harus muncul dengan:
   ✅ Data Siswa LENGKAP (14 fields)
   ✅ Asal Sekolah (2 fields)
   ✅ Data Orang Tua (8 fields)
   ✅ Rekap Nilai (table + jumlah + rata-rata)
   ✅ File Upload (list dengan icon)
```

## VISUAL GUIDE:

**BEFORE (Current - INCOMPLETE):**
```javascript
function showPreviewData() {
    const form = document.getElementById('pmbmForm');
    const formData = new FormData(form);
    
    let html = `
        <div class="card">
            <div class="card-header">Data Siswa</div>
            <div class="card-body">
                <!-- ONLY 7 fields: NISN, NIK, Nama, JK, TTL, Agama, Alamat -->
            </div>
        </div>
        
        <div class="alert">Catatan...</div>
    `;
    
    document.getElementById('previewDataContent').innerHTML = html;
    
    const previewModal = new bootstrap.Modal(...);
    previewModal.show();
}
```

**AFTER (New - COMPLETE):**
```javascript
function showPreviewData() {
    const form = document.getElementById('pmbmForm');
    const formData = new FormData(form);
    
    let html = '';
    
    // CARD 1: Data Siswa LENGKAP (14 fields)
    html += `<div class="card">...</div>`;
    
    // CARD 2: Asal Sekolah
    html += `<div class="card">...</div>`;
    
    // CARD 3: Data Orang Tua
    html += `<div class="card">...</div>`;
    
    // CARD 4: Rekap Nilai
    html += `<div class="card"><table>...</table></div>`;
    
    // CARD 5: Dokumen Upload
    html += `<div class="card"><div id="filesPreviewContainer">...</div></div>`;
    
    // Info
    html += `<div class="alert">...</div>`;
    
    document.getElementById('previewDataContent').innerHTML = html;
    
    // Generate file list dengan icon
    setTimeout(() => {
        // File preview logic
    }, 100);
    
    const previewModal = new bootstrap.Modal(...);
    previewModal.show();
}
```

## Expected Result:

**Preview Modal Will Show:**

```
┌─────────────────────────────────────────┐
│ 👁️ Preview Data Pendaftaran            │
├─────────────────────────────────────────┤
│                                         │
│ 📘 Data Siswa                          │
│ ┌─────────────────────────────────────┐ │
│ │ Jalur: Zonasi                       │ │
│ │ NISN: xxx  NIK: xxx                 │ │
│ │ Nama: xxx  JK: xxx  Agama: xxx      │ │
│ │ TTL: xxx, xxx                       │ │
│ │ Alamat: xxx                         │ │
│ │ Kec: xxx  Kab: xxx  Prov: xxx       │ │
│ │ HP: xxx  Email: xxx                 │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ 🏫 Asal Sekolah                        │
│ ┌─────────────────────────────────────┐ │
│ │ SD Negeri 123                       │ │
│ │ Jl. Pendidikan No. 456              │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ 👨‍👩‍👦 Data Orang Tua                     │
│ ┌─────────────────────────────────────┐ │
│ │ Data Ayah: ...                      │ │
│ │ Data Ibu: ...                       │ │
│ │ Data Wali: ...                      │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ 📊 Rekap Nilai Rapor                   │
│ ┌─────────────────────────────────────┐ │
│ │ Sem IV-1: 88.00                     │ │
│ │ Sem IV-2: 89.00                     │ │
│ │ Sem V-1:  90.00                     │ │
│ │ Sem V-2:  88.00                     │ │
│ │ Sem VI-1: 91.00                     │ │
│ │ Jumlah: 446.00                      │ │
│ │ Rata-Rata: 89.20                    │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ 📁 Dokumen Upload                      │
│ ┌───────────┐ ┌───────────┐           │
│ │ 📷 Foto   │ │ 📄 Rapor  │           │
│ │ foto.jpg  │ │ rapor.pdf │           │
│ │ JPG-245KB │ │ PDF-512KB │           │
│ │ ✓         │ │ ✓         │           │
│ └───────────┘ └───────────┘           │
│                                         │
│ ℹ️ Periksa kembali semua data...       │
│                                         │
│ [Edit Data] [Kirim Pendaftaran]        │
└─────────────────────────────────────────┘
```

## Troubleshooting:

**Q: Line number tidak sama?**
A: Cari dengan CTRL+F: `function showPreviewData()`

**Q: Setelah paste ada error?**
A: Pastikan tidak ada bracket `}` yang double atau missing

**Q: Preview masih kosong?**
A: Clear browser cache (CTRL+SHIFT+R)

**Q: File list tidak muncul?**
A: Normal, akan muncul setelah setTimeout 100ms

---

**File Ready:**
- `PREVIEW_FUNCTION_READY.js` ← Code lengkap siap copy-paste

**Action:**
1. Open register.php
2. Find line 1271
3. Delete line 1271-1326
4. Paste code dari PREVIEW_FUNCTION_READY.js
5. Save
6. Test!

🚀 **GO!**
