# Pemisahan Menu Target Waktu & Batasan Umur

## Update yang Dilakukan

Sebelumnya, menu **Target Waktu** dan **Batasan Umur** berada dalam satu halaman **Pengaturan Website** (`settings.php`). Sekarang sudah dipisahkan menjadi **2 halaman tersendiri** untuk kemudahan pengaturan.

---

## File Baru yang Dibuat

### 1. **admin/target_waktu.php**
**Fitur:**
- ✅ Set tanggal & waktu penutupan pendaftaran
- ✅ Date & time picker dengan Flatpickr
- ✅ Tombol quick action (Set Default, Set Hari Ini)
- ✅ Preview countdown real-time
- ✅ Tampilan tanggal target saat ini

**Layout:**
```
┌─────────────────────────────────────┬────────────────────┐
│ Form Pengaturan Target Waktu        │  Target Saat Ini   │
│                                     │  - Display tanggal │
│ - Input date/time picker            │  - Display waktu   │
│ - Tombol Set Default                │                    │
│ - Tombol Set Hari Ini               │  Preview Countdown │
│ - Tombol Simpan                     │  - Live countdown  │
└─────────────────────────────────────┴────────────────────┘
```

### 2. **admin/batasan_umur.php**
**Fitur:**
- ✅ Toggle Aktif/Nonaktif validasi umur
- ✅ Set tanggal cut-off batasan
- ✅ Set umur maksimal (dalam tahun)
- ✅ Contoh perhitungan umur
- ✅ Display setting saat ini

**Layout:**
```
┌─────────────────────────────────────┬────────────────────┐
│ Form Pengaturan Batasan Umur        │  Setting Saat Ini  │
│                                     │  - Status validasi │
│ - Toggle Aktif/Nonaktif             │  - Tanggal cutoff  │
│ - Input tanggal cut-off             │  - Umur maksimal   │
│ - Input umur maksimal               │                    │
│ - Tombol Simpan                     │  Contoh Hitung     │
│                                     │  - Siswa A: Ditolak│
│                                     │  - Siswa B: Diterima│
└─────────────────────────────────────┴────────────────────┘
```

---

## Menu di Sidebar

Menu **Sekolah** sekarang memiliki urutan:
1. Jalur Pendaftaran
2. Skema PMBM
3. Kuota & Kelas
4. Seleksi & Ranking
5. **Minimal Nilai Rata-rata** ⭐
6. **Target Waktu** ⭐ NEW
7. **Batasan Umur** ⭐ NEW
8. Status Pelaksanaan
9. Panduan & Brosur

---

## Fitur Unggulan

### **Target Waktu:**
1. **Live Countdown Preview** - Lihat countdown langsung saat setting
2. **Quick Actions** - Set default atau set hari ini dengan 1 klik
3. **Visual Clear** - Display besar & jelas untuk target saat ini
4. **Date/Time Picker** - Input mudah dengan Flatpickr (Bahasa Indonesia)

### **Batasan Umur:**
1. **Visual Feedback** - Badge berwarna untuk status (Aktif/Nonaktif)
2. **Contoh Perhitungan** - Membantu admin memahami cara kerja batasan
3. **Flexible** - Bisa set tanggal cut-off & umur maksimal terpisah
4. **Info Panel** - Display setting saat ini dengan jelas

---

## Database Storage

Data disimpan di tabel `settings`:

### Target Waktu:
```sql
key: 'countdown_date'
value: '2026-03-01 00:00:00'
```

### Batasan Umur:
```sql
key: 'age_limit_status'
value: 'aktif' atau 'nonaktif'

key: 'age_cutoff_date'  
value: '2026-07-01'

key: 'max_age_limit'
value: '15'
```

---

## Cara Penggunaan

### **Target Waktu**

**Akses:**
```
Admin → Sekolah → Target Waktu
```

**Langkah:**
1. Klik field **Tanggal & Waktu Penutupan**
2. Pilih tanggal & waktu dari date picker
3. Atau klik tombol **Set Default** / **Set Hari Ini**
4. Klik **Simpan Target Waktu**
5. Lihat preview countdown di panel kanan

**Contoh Setting:**
```
Target: 01/03/2026 23:59
Preview: "45 Hari 12 Jam 30 Menit"
```

---

### **Batasan Umur**

**Akses:**
```
Admin → Sekolah → Batasan Umur
```

**Langkah:**
1. Pilih **Status**: Aktif atau Nonaktif
2. Set **Tanggal Cut-off**: Contoh 01/07/2026
3. Set **Umur Maksimal**: Contoh 15 tahun
4. Klik **Simpan Pengaturan**

**Contoh Setting:**
```
Status: Aktif
Tanggal Cut-off: 01/07/2026
Umur Maksimal: 15 tahun

Artinya:
- Per 01 Juli 2026, siswa harus berumur maksimal 15 tahun
- Siswa lahir sebelum 01/07/2011 akan DITOLAK
- Siswa lahir setelah 01/07/2011 akan DITERIMA
```

---

## File yang Dimodifikasi

1. ✅ **admin/target_waktu.php** (BARU)
2. ✅ **admin/batasan_umur.php** (BARU)
3. ✅ **admin/includes/sidebar.php** (DIMODIFIKASI - tambah 2 menu)

---

## Dependencies

Kedua halaman menggunakan:
- **Bootstrap 5** - UI Framework
- **Font Awesome 6** - Icons
- **Flatpickr** - Date/Time Picker (with Indonesian locale)

---

## Screenshot

### Target Waktu
```
┌────────────────────────────────────────────┐
│ Target Waktu Pendaftaran         [Breadcrumb]│
├────────────────────────────────────────────┤
│ ┌────────────────┐  ┌─────────────────┐   │
│ │ Form Settings  │  │  Target Saat Ini│   │
│ │                │  │  01/03/2026     │   │
│ │ [Date Picker]  │  │  23:59 WIB      │   │
│ │ [Set Default]  │  │                 │   │
│ │ [Set Hari Ini] │  │  Preview:       │   │
│ │ [💾 Simpan]    │  │  45H 12J 30M    │   │
│ └────────────────┘  └─────────────────┘   │
└────────────────────────────────────────────┘
```

### Batasan Umur
```
┌────────────────────────────────────────────┐
│ Batasan Umur Pendaftaran         [Breadcrumb]│
├────────────────────────────────────────────┤
│ ┌────────────────┐  ┌─────────────────┐   │
│ │ Form Settings  │  │  Setting Saat Ini│   │
│ │                │  │  Status: AKTIF  │   │
│ │ [Toggle Aktif] │  │  Cutoff: 01/07  │   │
│ │ [Date Cutoff]  │  │  Max Umur: 15   │   │
│ │ [Max Age: 15]  │  │                 │   │
│ │ [⚠️ Simpan]    │  │  Contoh Hitung  │   │
│ └────────────────┘  └─────────────────┘   │
└────────────────────────────────────────────┘
```

---

## Testing

### **Test Target Waktu:**
1. Akses menu Target Waktu
2. Set tanggal: 01/03/2026 23:59
3. Simpan
4. Cek preview countdown berjalan
5. Refresh halaman - settings tetap tersimpan

### **Test Batasan Umur:**
1. Akses menu Batasan Umur
2. Set: Aktif, Cutoff: 01/07/2026, Max: 15
3. Simpan
4. Coba daftar dengan tanggal lahir: 15/08/2010 → Harus ditolak
5. Coba daftar dengan tanggal lahir: 15/08/2011 → Harus diterima

---

## Notes

- Kedua halaman **standalone** dengan struktur mirip `minimal_nilai.php`
- Tidak ada dependency ke `settings.php` lagi
- Data tetap tersimpan di tabel `settings` (backward compatible)
- UI konsisten dengan halaman admin lainnya

---

**Update Date:** 01 Februari 2026  
**Status:** ✅ Production Ready
