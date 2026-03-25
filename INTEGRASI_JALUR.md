# Integrasi Jalur Pendaftaran (Database → Form Register)

## 📌 Overview

Sistem jalur pendaftaran sudah **fully integrated** antara:
- **Database** (`jalur_pendaftaran` table)
- **Admin Panel** (CRUD Jalur)
- **Form Pendaftaran** (Dropdown pilihan jalur)

---

## 🔄 Alur Kerja Sistem

```
Admin Panel (Jalur Pendaftaran)
         ↓
    CRUD Jalur
         ↓
    Database (jalur_pendaftaran)
         ↓
    Query di register.php
         ↓
    Dropdown "Pilih Jalur"
         ↓
    Siswa Memilih Jalur
         ↓
    Data Tersimpan di pendaftar.jalur_id
```

---

## 💾 Struktur Database

### Tabel: `jalur_pendaftaran`

```sql
CREATE TABLE jalur_pendaftaran (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_jalur VARCHAR(255),
    kuota INT,
    syarat TEXT
);
```

**Contoh Data:**
```sql
INSERT INTO jalur_pendaftaran VALUES
(1, 'Jalur Akademik', 100, 'Pas Foto,Rapor Asli,Surat Keterangan Nilai Rata-rata,Surat Keterangan Ranking'),
(2, 'Jalur Minat Bakat Bidang Akademik', 50, 'Pas Foto,Rapor Asli,Surat Keterangan Prestasi'),
(3, 'Jalur Tahfidz', 70, 'Pas Foto,Rapor Asli,Surat Tahfidz,Sertifikat Tahfidz');
```

---

## 🔧 Implementasi di Code

### 1. **Admin Panel - Kelola Jalur**

**File:** `admin/jalur/index.php`

**Fitur CRUD:**
- ✅ **CREATE** - Tambah jalur baru
- ✅ **READ** - Lihat daftar jalur
- ✅ **UPDATE** - Edit jalur existing
- ✅ **DELETE** - Hapus jalur

**Contoh:**
```php
// Insert jalur baru
INSERT INTO jalur_pendaftaran (nama_jalur, kuota, syarat) 
VALUES ('Jalur Prestasi', 50, 'Piagam,Sertifikat')

// Update jalur
UPDATE jalur_pendaftaran 
SET kuota = 60 
WHERE id = 3

// Delete jalur
DELETE FROM jalur_pendaftaran 
WHERE id = 5
```

---

### 2. **Form Pendaftaran - Pilih Jalur**

**File:** `register.php`

#### **Query Database (Baris 15-17):**
```php
// Fetch Jalur Pendaftaran
$stmt_jalur = $pdo->query("SELECT * FROM jalur_pendaftaran ORDER BY id ASC");
$list_jalur = $stmt_jalur->fetchAll();
```

#### **Dropdown HTML (Baris 68-79):**
```html
<label class="form-label">
    <i class="fas fa-route me-2"></i>Pilih Jalur Pendaftaran
</label>
<select name="jalur_id" class="form-select form-control-lg bg-light" required>
    <option value="">-- Pilih Jalur --</option>
    <?php foreach ($list_jalur as $j): ?>
        <option value="<?= $j['id'] ?>">
            <?= htmlspecialchars($j['nama_jalur']) ?> (Kuota: <?= $j['kuota'] ?> Siswa)
        </option>
    <?php endforeach; ?>
</select>
```

**Output Example:**
```
-- Pilih Jalur --
Jalur Akademik (Kuota: 100 Siswa)
Jalur Minat Bakat Bidang Akademik (Kuota: 50 Siswa)
Jalur Tahfidz (Kuota: 70 Siswa)
```

---

## 📊 Visual Flow

### **Admin Side:**

```
┌─────────────────────────────────────┐
│   Admin Panel - Jalur Pendaftaran  │
├─────────────────────────────────────┤
│                                     │
│  [+ Tambah Jalur]                   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Nama: Jalur Prestasi        │   │
│  │ Kuota: 50 Siswa             │   │
│  │ Syarat: Piagam,Sertifikat   │   │
│  │ [💾 Simpan]                 │   │
│  └─────────────────────────────┘   │
│                                     │
│  Tabel Jalur:                       │
│  ┌──────┬────────────┬───────┬────┐│
│  │ Nama │ Kuota      │ Syarat│Aksi││
│  ├──────┼────────────┼───────┼────┤│
│  │Akad. │100 Siswa   │...    │✏️🗑️││
│  │Tahfz.│ 70 Siswa   │...    │✏️🗑️││
│  └──────┴────────────┴───────┴────┘│
└─────────────────────────────────────┘
         ↓ CRUD operations
    [DATABASE]
```

### **Student Side:**

```
┌─────────────────────────────────────┐
│    Form Pendaftaran - Step 1       │
├─────────────────────────────────────┤
│                                     │
│  🛣️ Pilih Jalur Pendaftaran *       │
│  ┌─────────────────────────────┐   │
│  │ -- Pilih Jalur --        ▼ │   │
│  ├─────────────────────────────┤   │
│  │ Jalur Akademik (100 Siswa)  │   │
│  │ Jalur Tahfidz (70 Siswa)    │   │
│  │ Jalur Prestasi (50 Siswa)   │   │
│  └─────────────────────────────┘   │
│                                     │
│  ℹ️ Pilih sesuai kriteria Anda      │
│                                     │
│  [Data lainnya...]                  │
│                                     │
│         [Lanjut →]                  │
└─────────────────────────────────────┘
         ↓ Submit to process_register.php
    [DATABASE: pendaftar.jalur_id]
```

---

## 🎯 Keuntungan Sistem Dynamic

### ✅ **Sebelumnya (Hardcoded):**
```php
// Manual, ribet kalau mau tambah jalur baru
<option value="1">Jalur Akademik</option>
<option value="2">Jalur Prestasi</option>
// Harus edit code setiap ada perubahan ❌
```

### ✅ **Sekarang (Dynamic dari DB):**
```php
// Otomatis update dari database
<?php foreach ($list_jalur as $j): ?>
    <option value="<?= $j['id'] ?>">
        <?= $j['nama_jalur'] ?> (Kuota: <?= $j['kuota'] ?> Siswa)
    </option>
<?php endforeach; ?>
// Admin bisa ubah via panel tanpa edit code ✅
```

---

## 📋 Use Case Scenarios

### **Scenario 1: Tambah Jalur Baru**

**Admin Action:**
1. Login → Menu **Sekolah** → **Jalur Pendaftaran**
2. Klik **"+ Tambah Jalur"**
3. Isi form:
   - Nama: **"Jalur Inklusi"**
   - Kuota: **20**
   - Syarat: **"Surat Keterangan Disabilitas,Rapor"**
4. Submit

**Result:**
- Data masuk database
- **Otomatis muncul** di dropdown form pendaftaran
- Siswa bisa langsung pilih jalur baru

---

### **Scenario 2: Update Kuota**

**Admin Action:**
1. Edit jalur **"Jalur Tahfidz"**
2. Ubah kuota: **70 → 80** siswa
3. Submit

**Result:**
- Dropdown di form register update otomatis:
  - Sebelum: `Jalur Tahfidz (Kuota: 70 Siswa)`
  - Sesudah: `Jalur Tahfidz (Kuota: 80 Siswa)`

---

### **Scenario 3: Hapus Jalur**

**Admin Action:**
1. Delete jalur **"Jalur Prestasi"**
2. Konfirmasi

**Result:**
- Jalur hilang dari dropdown
- **Siswa tidak bisa memilih** jalur tersebut lagi

---

## 🔍 Relasi Database

```sql
-- Tabel jalur_pendaftaran
id | nama_jalur          | kuota | syarat
---+---------------------+-------+--------
1  | Jalur Akademik      | 100   | ...
2  | Jalur Tahfidz       | 70    | ...

-- Tabel pendaftar (siswa yang daftar)
id | nama_lengkap | jalur_id | ...
---+--------------+----------+----
1  | Ahmad        | 1        | ... (memilih Jalur Akademik)
2  | Fatimah      | 2        | ... (memilih Jalur Tahfidz)
```

**Relasi:**
```
pendaftar.jalur_id → jalur_pendaftaran.id
```

---

## ⚙️ Filter Jalur di Admin (Data Pendaftar)

**File:** `admin/pendaftar/index.php` (jika ada)

**Dropdown Filter:**
```php
<select name="filter_jalur">
    <option value="">Semua Jalur</option>
    <?php
    $jalur_list = $pdo->query("SELECT * FROM jalur_pendaftaran")->fetchAll();
    foreach ($jalur_list as $j) {
        echo "<option value='{$j['id']}'>{$j['nama_jalur']}</option>";
    }
    ?>
</select>
```

**Query Filter:**
```php
$where = [];
if (!empty($_GET['filter_jalur'])) {
    $where[] = "jalur_id = " . intval($_GET['filter_jalur']);
}

$sql = "SELECT * FROM pendaftar";
if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
```

---

## 🎨 Tampilan di Form Register

**Dropdown akan menampilkan:**
```
┌──────────────────────────────────────────────┐
│  Pilih Jalur Pendaftaran                  ▼ │
├──────────────────────────────────────────────┤
│  -- Pilih Jalur --                           │
│  Jalur Akademik (Kuota: 100 Siswa)           │
│  Jalur Minat Bakat Akademik (Kuota: 50 Siswa)│
│  Jalur Tahfidz (Kuota: 70 Siswa)             │
│  Jalur Prestasi (Kuota: 50 Siswa)            │
└──────────────────────────────────────────────┘
```

**Features:**
- ✅ Menampilkan **nama jalur**
- ✅ Menampilkan **kuota** (berapa kursi tersedia)
- ✅ **Otomatis update** saat admin ubah data
- ✅ **Sorted by ID** (sesuai urutan di admin)

---

## 🧪 Testing

### Test 1: Cek Integration
```
1. Buka admin → Jalur Pendaftaran
2. Tambah jalur baru: "Test Jalur" (Kuota: 10)
3. Buka register.php
4. ✅ Expected: "Test Jalur (Kuota: 10 Siswa)" muncul di dropdown
```

### Test 2: Update Jalur
```
1. Edit "Test Jalur" → Ubah kuota jadi 20
2. Refresh register.php
3. ✅ Expected: "Test Jalur (Kuota: 20 Siswa)"
```

### Test 3: Delete Jalur
```
1. Delete "Test Jalur"
2. Refresh register.php
3. ✅ Expected: "Test Jalur" hilang dari dropdown
```

---

## 📝 Summary

| Aspek | Sebelum | Sesudah |
|-------|---------|---------|
| **Sumber Data** | Hardcoded | Database |
| **Update Jalur** | Edit Code | Via Admin Panel |
| **Info Kuota** | Tidak ada | Ditampilkan |
| **Maintenance** | Sulit | Mudah |
| **Admin Friendly** | ❌ | ✅ |

---

**Status:** ✅ **FULLY INTEGRATED & WORKING**

Jalur pendaftaran sudah 100% terintegrasi antara database, admin panel, dan form register!

---

**Date:** 01 Februari 2026
