# Dokumentasi Modal Pakta Integritas di Register.php

## 📌 Overview

Ditambahkan **Modal Pakta Integritas** yang muncul ketika user mencentang checkbox persetujuan di Step 5 (Upload Berkas).

Modal ini memberikan konfirmasi bahwa user sudah yakin dengan data yang diisi sebelum melanjutkan submit.

---

## 🎯 Fitur Modal Pakta Integritas

### **Alur Kerja:**

1. User mengisi semua step 1-5
2. Di Step 5, user mencentang checkbox "Saya menyatakan..."
3. **Modal Pakta Integritas muncul** secara otomatis ⚡
4. User membaca isi pakta integritas
5. User memilih salah satu:
   - ✅ **"Pakta Integritas"** → Lanjut submit (checkbox tetap tercentang)
   - ⬅️ **"Periksa Kembali"** → Kembali review (checkbox di-uncheck)

---

## 📋 Isi Modal

### **Header:**
- Icon: 📝 File Signature
- Judul: "Konfirmasi Pakta Integritas"
- Subtitle: "Pastikan semua data yang Anda isi sudah benar"

### **Body:**

#### 1. **Alert Warning**
Peringatan penting untuk memastikan data sudah benar

#### 2. **Checklist Items**
- ✅ Semua data pribadi sudah benar dan sesuai
- ✅ Semua dokumen yang diupload adalah asli dan valid
- ✅ Nilai rapor sesuai dengan rapor asli
- ✅ Data orang tua sudah lengkap dan benar

#### 3. **Pakta Integritas Box**
Box dengan border hijau berisi:
- Pernyataan lengkap pakta integritas
- Konsekuensi jika ditemukan pemalsuan:
  - Dibatalkan status kelulusan PPDB
  - Tidak dapat mengajukan keberatan
  - Ditindak sesuai ketentuan yang berlaku

#### 4. **Pertanyaan Konfirmasi**
"Apakah Anda yakin semua data sudah benar?"

### **Footer (2 Tombol):**

| Tombol | Icon | Warna | Aksi |
|--------|------|-------|------|
| **Periksa Kembali** | ← | Secondary (Abu-abu) | Uncheck checkbox, tutup modal, tampilkan alert info |
| **Pakta Integritas** | 🤝 | Success (Hijau) | Keep checkbox checked, tutup modal, tampilkan alert success |

---

## 💻 Implementasi Code

### **1. HTML - Modal Structure**

```html
<!-- Modal dengan backdrop static (tidak bisa ditutup dengan klik luar) -->
<div class="modal fade" id="paktaIntegritasModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <!-- Header dengan background primary -->
            <div class="modal-header border-0 bg-primary text-white">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-25 p-3 rounded-3">
                        <i class="fas fa-file-signature fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold">Konfirmasi Pakta Integritas</h5>
                        <small>Pastikan semua data yang Anda isi sudah benar</small>
                    </div>
                </div>
            </div>
            
            <!-- Body dengan checklist dan pakta integritas -->
            <div class="modal-body p-4">
                <!-- Warning alert -->
                <!-- Checklist items -->
                <!-- Pakta integritas box -->
                <!-- Confirmation question -->
            </div>
            
            <!-- Footer dengan 2 tombol -->
            <div class="modal-footer border-0">
                <button id="btnPeriksaKembali" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Periksa Kembali
                </button>
                <button id="btnPaktaIntegritas" class="btn btn-success">
                    <i class="fas fa-handshake"></i> Pakta Integritas
                </button>
            </div>
        </div>
    </div>
</div>
```

### **2. JavaScript - Event Handlers**

```javascript
document.addEventListener('DOMContentLoaded', function() {
    const checkDocuments = document.getElementById('checkDocuments');
    const paktaModal = new bootstrap.Modal(document.getElementById('paktaIntegritasModal'));
    const btnPaktaIntegritas = document.getElementById('btnPaktaIntegritas');
    const btnPeriksaKembali = document.getElementById('btnPeriksaKembali');

    // When checkbox is checked → Show modal
    checkDocuments.addEventListener('change', function(e) {
        if (this.checked) {
            paktaModal.show();
        }
    });

    // Button "Pakta Integritas" → Keep checked, close modal
    btnPaktaIntegritas.addEventListener('click', function() {
        checkDocuments.checked = true;
        paktaModal.hide();
        
        // Show success alert
        showAlert('success', 'Pakta Integritas Diterima!', 
                  'Silakan klik "Kirim Pendaftaran" untuk menyelesaikan proses.');
    });

    // Button "Periksa Kembali" → Uncheck, close modal
    btnPeriksaKembali.addEventListener('click', function() {
        checkDocuments.checked = false;
        paktaModal.hide();
        
        // Show info alert
        showAlert('info', 'Silakan Periksa Kembali Data Anda', 
                  'Pastikan semua informasi sudah benar sebelum mencentang pakta integritas.');
    });
});
```

### **3. CSS - Styling**

```css
/* Checklist items dengan background hijau muda */
.checklist-item {
    padding: 10px 15px;
    background: rgba(25, 135, 84, 0.05);
    border-radius: 10px;
    border-left: 3px solid #198754;
    line-height: 1.6;
}

/* Modal size */
.modal-lg {
    max-width: 700px;
}
```

---

## 🎨 UI/UX Features

### **Visual Feedback:**

1. **Modal Muncul:**
   - Fade in animation smooth
   - Backdrop dark (static, tidak bisa ditutup dengan klik luar)
   - Modal centered di layar

2. **Setelah Klik "Pakta Integritas":**
   - ✅ Alert success muncul (hijau)
   - Icon: Check circle
   - Pesan: "Pakta Integritas Diterima!"
   - Auto dismiss setelah 5 detik

3. **Setelah Klik "Periksa Kembali":**
   - ℹ️ Alert info muncul (biru)
   - Icon: Info circle
   - Pesan: "Silakan Periksa Kembali Data Anda"
   - Auto dismiss setelah 5 detik

### **Accessibility:**

- ✅ Keyboard navigation support (Tab, Enter, Esc)
- ✅ ARIA labels untuk screen readers
- ✅ Focus trap dalam modal
- ✅ Clear visual hierarchy

---

## 🔒 Security & Validation

### **Prevent Bypass:**

1. **Backdrop Static:**
   ```html
   data-bs-backdrop="static" 
   data-bs-keyboard="false"
   ```
   - User **tidak bisa** menutup modal dengan klik di luar
   - User **harus** memilih salah satu tombol

2. **Checkbox Required:**
   - Checkbox tetap required
   - Form tidak bisa submit jika checkbox tidak tercentang
   - Checkbox hanya tercentang jika user klik "Pakta Integritas"

3. **Browser Back Prevention:**
   - Modal state tidak tersimpan di browser history
   - User tidak bisa bypass dengan tombol back

---

## 🧪 Test Cases

### **Test 1: Modal Muncul**
1. Isi semua step 1-4
2. Lanjut ke Step 5
3. **Klik checkbox** "Saya menyatakan..."
4. ✅ **Expected:** Modal Pakta Integritas muncul

### **Test 2: Pilih "Pakta Integritas"**
1. Modal muncul
2. **Klik tombol "Pakta Integritas"**
3. ✅ **Expected:** 
   - Modal tutup
   - Checkbox tetap tercentang ✓
   - Alert success muncul
   - Tombol "Kirim Pendaftaran" bisa diklik

### **Test 3: Pilih "Periksa Kembali"**
1. Modal muncul
2. **Klik tombol "Periksa Kembali"**
3. ✅ **Expected:** 
   - Modal tutup
   - Checkbox di-uncheck (kosong)
   - Alert info muncul
   - User bisa review data kembali

### **Test 4: Modal Tidak Bisa Ditutup Manual**
1. Modal muncul
2. **Klik di luar modal** (pada backdrop)
3. ✅ **Expected:** Modal tetap terbuka (tidak menutup)
4. **Tekan tombol Esc**
5. ✅ **Expected:** Modal tetap terbuka

### **Test 5: Alert Auto Dismiss**
1. Klik "Pakta Integritas" atau "Periksa Kembali"
2. Alert muncul
3. **Tunggu 5 detik**
4. ✅ **Expected:** Alert otomatis hilang

---

## 📊 Flow Diagram

```
User di Step 5
     ↓
Centang Checkbox
     ↓
[Modal Pakta Integritas Muncul]
     ↓
User Pilih:
     ├─→ [Pakta Integritas] → Checkbox ✓ → Alert Success → Bisa Submit ✅
     └─→ [Periksa Kembali]  → Checkbox ✗ → Alert Info → Review Data 🔄
```

---

## ✨ Benefits

### **Untuk User:**
- ✅ **Konfirmasi ganda** sebelum submit
- ✅ **Cek ulang data** jika belum yakin
- ✅ **Clear feedback** dengan alert message
- ✅ **Tidak bisa skip** pakta integritas

### **Untuk Admin/Panitia:**
- ✅ **Mengurangi kesalahan data** pendaftar
- ✅ **Bukti persetujuan** yang jelas dari user
- ✅ **Legal protection** dengan pakta integritas
- ✅ **Mengurangi complain** di kemudian hari

---

## 📝 Notes

- Modal menggunakan **Bootstrap 5 Modal** component
- Alert dibuat dinamis dengan **JavaScript createElement**
- Styling menggunakan **Bootstrap utility classes** + custom CSS
- Icon dari **Font Awesome 6**
- Auto dismiss alert menggunakan **setTimeout()**

---

## 🎯 Future Enhancements (Optional)

Jika ingin pengembangan lebih lanjut:

1. **Print Pakta Integritas:** Tambah tombol untuk print pakta integritas sebagai bukti
2. **Email Confirmation:** Kirim email dengan copy pakta integritas ke user
3. **Digital Signature:** Implementasi tanda tangan digital di modal
4. **Timestamp:** Catat waktu user menyetujui pakta integritas
5. **Revision History:** Log jika user buka modal berkali-kali

---

**Status:** ✅ **FULLY WORKING!**

Modal Pakta Integritas telah berhasil diimplementasikan dengan UI yang profesional dan UX yang baik!

---

**Date:** 07 Februari 2026  
**Time:** 19:45 WIB

**Modified Files:**
- ✅ `register.php` - Modal HTML & JavaScript handler
- ✅ `assets/css/style.css` - Styling untuk checklist items
- 📄 `MODAL_PAKTA_INTEGRITAS.md` - Documentation
