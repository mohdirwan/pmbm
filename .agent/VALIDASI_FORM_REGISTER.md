# 📋 Dokumentasi Validasi Form Register PMBM

## 🎯 Tujuan
Memastikan **semua field wajib terisi** sebelum user dapat melanjutkan ke step berikutnya, dengan **notifikasi yang jelas** tentang field mana yang masih kosong.

---

## ✅ Perubahan yang Dilakukan

### 1. **Field Baru yang Dibuat Wajib**

#### **Step 1: Data Siswa**
- ✨ **No HP Siswa** - Sekarang WAJIB diisi
  - Validasi: Hanya angka (0-9)
  - Panjang maksimal: 13 digit
  - Format: 08xxxxxxxxxx
  
- ✨ **Jarak ke Sekolah** - Sekarang WAJIB diisi
  - Format bebas: bisa "2 Km" atau "2.5 Km"
  - Placeholder diperjelas

---

### 2. **Validasi JavaScript di Setiap Step**

#### **Step 1 → Step 2 (Data Siswa)**
✅ Validasi semua field wajib:
- Jalur Pendaftaran
- NISN (10 digit)
- NIK (16 digit)
- Nama Lengkap
- Tempat Lahir
- Tanggal Lahir
- Jenis Kelamin
- Agama
- Anak Ke
- Status dalam Keluarga
- **No HP Siswa** ⭐ (BARU)
- Alamat
- Status Tempat Tinggal
- **Jarak ke Sekolah** ⭐ (BARU)
- Transportasi dari Rumah
- Kecamatan
- Kabupaten/Kota

**Notifikasi jika belum lengkap:**
```
❗ Form Belum Lengkap!
Harap lengkapi field berikut:
• NISN (Nomor Induk Siswa Nasional)
• No HP Siswa
• Jarak ke Sekolah (Km)
... (field lain yang kosong)
```

---

#### **Step 2 → Step 3 (Asal Sekolah)**
✅ Validasi field wajib:
- Nama Sekolah Asal (SD/MI)

**Notifikasi jika belum lengkap:**
```
❗ Form Belum Lengkap!
Harap lengkapi field berikut:
• Nama Sekolah Asal (SD/MI)
```

---

#### **Step 3 → Step 4 (Data Orang Tua)**
✅ Validasi semua field wajib:
- Nomor Kartu Keluarga (KK)
- Status Orang Tua
- Nama Lengkap Ayah
- NIK Ayah (16 digit)
- Nama Lengkap Ibu
- NIK Ibu (16 digit)
- Nomor WhatsApp Aktif
- Nama Pemilik Nomor WA

**Notifikasi jika belum lengkap:**
```
❗ Form Belum Lengkap!
Harap lengkapi field berikut:
• Nomor Kartu Keluarga (KK)
• NIK Ayah
• Nomor WhatsApp Aktif
... (field lain yang kosong)
```

---

#### **Step 4 → Step 5 (Rekap Nilai)**
✅ Validasi nilai rapor:
- Nilai Kelas IV Semester 1
- Nilai Kelas IV Semester 2
- Nilai Kelas V Semester 1
- Nilai Kelas V Semester 2
- Nilai Kelas VI Semester 1
- ✅ Cek nilai rata-rata minimal (jika diaktifkan di setting)

**Notifikasi jika belum lengkap:**
```
⚠️ Data Belum Lengkap
Silakan isi semua nilai rapor semester 1 s/d 5.
```

**Notifikasi jika nilai kurang:**
```
❌ Persyaratan Tidak Terpenuhi
Maaf, nilai rata-rata Anda belum memenuhi syarat minimal rata-rata untuk melanjutkan pendaftaran.
```

---

#### **Step 5 (Upload Berkas)**
✅ Validasi dokumen:
- Semua dokumen wajib harus diupload
- Minimal 1 dokumen pilihan (jika ada)
- Checkbox pernyataan keaslian dokumen harus dicentang

**Notifikasi jika belum lengkap:**
```
⚠️ Pernyataan Keaslian
Silakan centang pernyataan keaslian dokumen sebelum melanjutkan.
```

```
⚠️ Berkas Pilihan Belum Diisi
Silakan pilih dan unggah minimal satu dokumen pilihan untuk jalur pendaftaran ini.
```

---

## 🎨 Fitur Tambahan

### **Visual Feedback**
- ✅ Field yang kosong akan ditandai dengan border merah (`is-invalid` class)
- ✅ Field yang sudah diisi akan kembali normal
- ✅ Notifikasi muncul dengan SweetAlert2 yang lebih menarik

### **User Experience**
- 📱 Tombol "Lanjut" tidak akan berpindah step jika ada field kosong
- 🔴 Field wajib ditandai dengan asterisk merah (*)
- 📝 Daftar field kosong ditampilkan dalam bentuk list yang jelas
- ✨ Notifikasi friendly dengan button "Oke, Saya Mengerti"

---

## 🔧 Technical Details

### **Validasi Method:**
```javascript
function nextStep(step) {
    // Cek step yang dituju
    if (step === 2) { // Step 1 validation
        const currentStep = document.getElementById('step1');
        const requiredFields = currentStep.querySelectorAll('[required]');
        let emptyFields = [];
        
        // Loop semua field required
        requiredFields.forEach(field => {
            if (!field.value || field.value.trim() === '') {
                field.classList.add('is-invalid'); // Tandai field merah
                // Ambil label field
                const label = // ... logic untuk ambil label
                emptyFields.push(label.textContent);
            } else {
                field.classList.remove('is-invalid');
            }
        });

        // Jika ada field kosong, tampilkan notifikasi
        if (emptyFields.length > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Form Belum Lengkap!',
                html: // ... daftar field kosong
            });
            return; // Stop, tidak lanjut ke step berikutnya
        }
    }
    // ... validasi step lainnya
}
```

---

## 📊 Ringkasan Field Wajib

| Step | Total Field Wajib | Field Baru Wajib |
|------|-------------------|------------------|
| **Step 1: Data Siswa** | 17 field | 2 field (No HP, Jarak Sekolah) |
| **Step 2: Asal Sekolah** | 1 field | - |
| **Step 3: Orang Tua & Wali** | 8 field | - |
| **Step 4: Rekap Nilai** | 5 field | - |
| **Step 5: Upload Berkas** | Dinamis | - |
| **TOTAL** | **31+ field** | **2 field** |

---

## 🚀 Cara Kerja

1. **User mengisi form** di Step 1
2. **User klik "Lanjut"**
3. **JavaScript validate** semua field `[required]` di step tersebut
4. **Jika ada yang kosong:**
   - Field kosong ditandai merah
   - Muncul popup dengan list field yang kosong
   - User TIDAK bisa lanjut
5. **Jika semua terisi:**
   - Berpindah ke step berikutnya
   - Proses validasi berulang di setiap step

---

## ✨ Keuntungan

✅ **User tidak bisa skip form wajib**  
✅ **Notifikasi jelas untuk user**  
✅ **Mengurangi submission error**  
✅ **Data lebih lengkap dan valid**  
✅ **Pengalaman user lebih baik dengan feedback visual**  

---

## 📝 Catatan Penting

- ⚠️ Validasi hanya dilakukan saat klik tombol "Lanjut"
- ⚠️ User masih bisa kembali ke step sebelumnya tanpa validasi
- ⚠️ Validasi final tetap dilakukan di backend (process_register.php)
- ✅ Field opsional (tidak ada `required`) tetap bisa dikosongkan

---

## 🎓 Best Practice

1. **NISN**: Harus 10 digit angka
2. **NIK**: Harus 16 digit angka (Siswa, Ayah, Ibu)
3. **No KK**: Harus 16 digit angka
4. **No HP/WA**: Format 08xxxxxxxxxx (10-13 digit)
5. **Nilai Rapor**: Gunakan titik (.) untuk desimal, contoh: 87.50
6. **Upload File**: Max 2MB per file, format JPG/PNG/PDF

---

**Dibuat oleh:** Antigravity AI Assistant  
**Tanggal:** 11 Februari 2026  
**Versi:** 1.0
