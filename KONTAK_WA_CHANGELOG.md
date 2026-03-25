# Kontak WhatsApp - Dokumentasi Perubahan

## 📌 Overview

Ditambahkan field baru **"Kontak yang Bisa Dihubungi (WhatsApp)"** pada form registrasi Step 3. Nomor WhatsApp ini akan digunakan untuk mengirimkan notifikasi pendaftaran, **menggantikan penggunaan nomor WA Ayah** yang sebelumnya.

---

## 🎯 Tujuan Perubahan

### Masalah Sebelumnya:
- ❌ Notifikasi WA selalu dikirim ke nomor HP Ayah
- ❌ Tidak fleksibel jika yang mendaftar bukan orang tua (misal: wali, kakak, dll)
- ❌ Tidak ada pilihan untuk menentukan kontak utama

### Solusi:
- ✅ User bisa **memilih sendiri** nomor WA yang ingin dihubungi
- ✅ Field terpisah untuk **nama pemilik nomor** (lebih personal)
- ✅ Notifikasi WA lebih **tepat sasaran**
- ✅ Template WA lebih **informatif** (mention nama kontak dan nama siswa)

---

## 📝 Perubahan yang Dilakukan

### 1. **File: `register.php`** (Step 3)

#### UI yang Ditambahkan:

```html
<!-- Alert Info -->
<div class="alert alert-info">
    Nomor WhatsApp di bawah ini akan digunakan untuk  
    mengirimkan informasi pendaftaran dan notifikasi penting lainnya.
</div>

<!-- Section: Kontak yang Bisa Dihubungi -->
<h5 class="text-success">
    <i class="fab fa-whatsapp"></i> Kontak yang Bisa Dihubungi
</h5>

<!-- Field 1: Nomor WhatsApp -->
<input type="text" 
       name="kontak_wa" 
       placeholder="Contoh: 081234567890"
       maxlength="13" 
       required>

<!-- Field 2: Nama Pemilik Nomor -->
<input type="text" 
       name="nama_kontak_wa" 
       placeholder="Nama pemilik nomor WhatsApp"
       required>
```

**Features:**
- ✅ Icon WhatsApp hijau pada input
- ✅ Validation: hanya angka (0-9)
- ✅ Max length: 13 digit
- ✅ Placeholder yang jelas
- ✅ Alert info untuk penjelasan
- ✅ Required fields

---

### 2. **File: `process_register.php`**

#### A. SQL Query Update

**Before:**
```sql
INSERT INTO pendaftar (
    ...
    no_hp_ayah, nama_ibu, nik_ibu, ...
) VALUES (?, ?, ?, ...)
```

**After:**
```sql
INSERT INTO pendaftar (
    ...
    no_hp_ayah, nama_ibu, nik_ibu, 
    kontak_wa, nama_kontak_wa,      -- NEW FIELDS
    jalur_id, ...
) VALUES (?, ?, ?, ?, ?, ...)
```

#### B. Execute Array Update

**Added:**
```php
clean_input($_POST['kontak_wa']),
clean_input($_POST['nama_kontak_wa']),
```

#### C. WhatsApp Notification Logic Update

**Before:**
```php
$no_hp = clean_input($_POST['no_hp_ayah']);

$template = "Halo {nama},
Pendaftaran Anda pada PMBM MTsN 1 Kota Pekanbaru telah berhasil...";

$message = str_replace(
    ['{nama}', '{link_login}', '{username}', '{password}'],
    [$nama_lengkap, $login_url, $no_pendaftaran, $nisn],
    $template
);
```

**After:**
```php
$no_hp = clean_input($_POST['kontak_wa']);          // Changed
$nama_kontak = clean_input($_POST['nama_kontak_wa']); // New

$template = "Halo {nama_kontak},                     // Changed

Bendaftaran a.n. {nama_siswa} pada PMBM MTsN 1 Kota Pekanbaru... // Changed
Berikut rincian akun login siswa:...";                // Changed

$message = str_replace(
    ['{nama_kontak}', '{nama_siswa}', '{link_login}', '{username}', '{password}'], // Changed
    [$nama_kontak, $nama_lengkap, $login_url, $no_pendaftaran, $nisn], // Changed
    $template
);
```

**Improvements:**
- ✅ Greet by contact name (more personal)
- ✅ Clearly state it's for student registration
- ✅ Differentiate between contact name and student name

---

### 3. **Database Migration**

#### New Columns:

| Column | Type | Description |
|--------|------|-------------|
| `kontak_wa` | VARCHAR(15) | Nomor WhatsApp yang bisa dihubungi |
| `nama_kontak_wa` | VARCHAR(100) | Nama pemilik nomor WhatsApp |

#### Migration Files:
1. ✅ `migration_add_kontak_wa.sql` - SQL script
2. ✅ `run_migration_kontak_wa.php` - Migration runner

**Run Migration:**
```
http://localhost/pmbm/run_migration_kontak_wa.php
```

Or via CLI:
```bash
php run_migration_kontak_wa.php
```

---

## 📊 WhatsApp Template Comparison

### Before:
```
Assalamu'alaikum warahmatullahi wabarakatuh.

Halo Ahmad Rizki,
Pendaftaran Anda pada PMBM MTsN 1 Kota Pekanbaru telah berhasil diproses.

Berikut rincian akun login Anda:
Link Login: http://...

Username: REG20260207123
Password: 1234567890

...
```

**Issues:**
- ❌ Assumes message recipient is the student
- ❌ Uses student name (might be sent to parent)
- ❌ Not clear for parents/guardians

### After:
```
Assalamu'alaikum warahmatullahi wabarakatuh.

Halo Ibu Siti,                                  ← Contact name
Pendaftaran a.n. Ahmad Rizki pada PMBM MTsN 1 Kota Pekanbaru   ← Student name
telah berhasil diproses.

Berikut rincian akun login siswa:               ← Clear it's student's login
Link Login: http://...

Username: REG20260207123
Password: 1234567890

...
```

**Improvements:**
- ✅ Personal greeting to contact
- ✅ Clear that it's for student registration
- ✅ Better for parents/guardians
- ✅ More professional

---

## 🎨 UI Preview

### Step 3 Layout:

```
┌──────────────────────────────────────────────┐
│ 👤 Informasi Ayah                            │
│ [Nama Ayah] [NIK Ayah]                       │
│ [Pekerjaan] [Penghasilan] [No HP/WA Ayah]    │
├──────────────────────────────────────────────┤
│ 👩 Informasi Ibu                             │
│ [Nama Ibu] [NIK Ibu]                         │
│ [Pekerjaan] [No HP/WA Ibu]                   │
├──────────────────────────────────────────────┤
│ ℹ️ PENTING!                                   │
│ Nomor WhatsApp di bawah ini akan digunakan   │
│ untuk mengirimkan informasi pendaftaran...   │
├──────────────────────────────────────────────┤
│ 📱 Kontak yang Bisa Dihubungi                │
│ 📱 [081234567890]     👤 [Nama Pemilik]      │
│    Format: 08xxxxxxxxxx     Ayah/Ibu/Wali    │
└──────────────────────────────────────────────┘
   [Kembali]                      [Lanjut →]
```

---

## 🧪 Testing

### Test Case 1: Field Validation

1. Buka register.php
2. Isi Step 1-2
3. Di Step 3, coba input kontak_wa dengan huruf
4. **Expected:** Hanya angka yang bisa diketik
5. Coba input lebih dari 13 digit
6. **Expected:** Max 13 digit terpotong

### Test Case 2: Required Validation

1. Isi semua field Step 3 kecuali kontak_wa
2. Click "Lanjut"
3. **Expected:** Error required, tidak bisa lanjut
4. Isi kontak_wa tapi kosongkan nama_kontak_wa
5. Click "Lanjut"
6. **Expected:** Error required

### Test Case 3: WhatsApp Notification

1. Isi complete form dengan:
   - Nama Siswa: Ahmad Rizki
   - Kontak WA: 081234567890
   - Nama Kontak: Ibu Siti
2. Submit registration
3. **Expected:** WA sent to 081234567890
4. **Content:** "Halo Ibu Siti, Pendaftaran a.n. Ahmad Rizki..."

### Test Case 4: Database Storage

1. Submit registration
2. Check database: `SELECT kontak_wa, nama_kontak_wa FROM pendaftar ORDER BY id DESC LIMIT 1`
3. **Expected:** Values correctly saved

---

## 📋 Migration Checklist

Before going live, ensure:

- [ ] Run `run_migration_kontak_wa.php`
- [ ] Verify columns exist in database
- [ ] Test registration form
- [ ] Test WhatsApp notification
- [ ] Check WA template readability
- [ ] Test with different contact scenarios (Ayah, Ibu, Wali)

---

## 🎯 Benefits

### For Users:
- ✅ **Flexibility:** Choose any contact number
- ✅ **Clarity:** Know who will receive notification
- ✅ **Convenience:** No need to use parent's number if registered by sibling/relative

### For Admin:
- ✅ **Better communication:** Direct to right person
- ✅ **Professional:** Personalized messages
- ✅ **Tracking:** Know who the primary contact is

### For System:
- ✅ **Decoupling:** Not tied to parent's number
- ✅ **Scalability:** Can add more contact types in future
- ✅ **Maintainability:** Clear data structure

---

## 🔄 Backward Compatibility

**Impact on Existing Data:**
- ✅ New columns are NULL-able (existing records won't break)
- ✅ Old records will use no_hp_ayah for WA notification
- ✅ New records will use kontak_wa
- ⚠️ Consider migrating old data if needed:

```sql
-- Optional: Migrate old registrations
UPDATE pendaftar 
SET kontak_wa = no_hp_ayah, 
    nama_kontak_wa = 'Orang Tua'
WHERE kontak_wa IS NULL;
```

---

## 📞 Example Scenarios

### Scenario 1: Registered by Mother
- **Kontak WA:** 081234567890
- **Nama Kontak:** Ibu Siti
- **WA Message:** "Halo Ibu Siti, Pendaftaran a.n. Ahmad..."

### Scenario 2: Registered by Father
- **Kontak WA:** 082345678901
- **Nama Kontak:** Bapak Ahmad
- **WA Message:** "Halo Bapak Ahmad, Pendaftaran a.n. Rizki..."

### Scenario 3: Registered by Guardian
- **Kontak WA:** 083456789012
- **Nama Kontak:** Om Hasan (Wali)
- **WA Message:** "Halo Om Hasan (Wali), Pendaftaran a.n. Aisyah..."

### Scenario 4: Registered by Older Sibling
- **Kontak WA:** 084567890123
- **Nama Kontak:** Kak Fadli (Kakak)
- **WA Message:** "Halo Kak Fadli (Kakak), Pendaftaran a.n. Dina..."

---

## ✅ Conclusion

Perubahan ini meningkatkan **fleksibilitas** dan **user experience** sistem pendaftaran dengan memungkinkan user memilih kontak yang ingin dihubungi, bukan hanya terpaku pada nomor WA Ayah.

**Status:** ✅ **READY TO USE**

---

**Date:** 07 Februari 2026 - 21:00 WIB  
**Modified Files:**
- ✅ `register.php` - Added kontak WA fields in Step 3
- ✅ `process_register.php` - Updated INSERT query and WA notification
- 📄 `migration_add_kontak_wa.sql` - Database migration
- 📄 `run_migration_kontak_wa.php` - Migration runner
- 📄 `KONTAK_WA_CHANGELOG.md` - This documentation
