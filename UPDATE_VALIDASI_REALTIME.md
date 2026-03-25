# Update: Validasi Real-Time Minimal Nilai

## Fitur Baru yang Ditambahkan

### 🎯 Validasi Client-Side dengan Popup Alert

Sekarang sistem memiliki **validasi real-time** saat siswa mengisi nilai rapor di form pendaftaran.

## Cara Kerja

### 1. **Saat Input Nilai**
Setiap kali siswa mengisi atau mengubah nilai rapor:
- Sistem langsung menghitung rata-rata
- Membandingkan dengan minimal nilai yang ditetapkan admin

### 2. **Jika Nilai DI BAWAH Minimal**
✅ Muncul **popup alert** dengan informasi:
```
⚠️ NILAI TIDAK MEMENUHI SYARAT!

Nilai rata-rata Anda: [nilai siswa]
Minimal yang diperlukan: [minimal dari admin]

Maaf, nilai rata-rata Anda belum memenuhi persyaratan minimal yang ditetapkan.
Silakan periksa kembali nilai rapor Anda atau hubungi panitia PMBM jika ada kesalahan.
```

✅ **Tombol Submit DISABLED** dan berubah menjadi:
```
[🚫 Nilai Tidak Memenuhi Syarat]
```

✅ **Display Rata-rata berwarna MERAH**
- Background: Merah muda (#ffebee)
- Text: Merah tua (#c62828)
- Font: Bold

### 3. **Jika Nilai MEMENUHI Minimal**
✅ **Tombol Submit ENABLED** dengan teks normal:
```
[📧 Kirim Pendaftaran]
```

✅ **Display Rata-rata berwarna HIJAU**
- Background: Hijau muda (#e8f5e9)
- Text: Hijau tua (#2e7d32)
- Font: Bold

## Screenshot Behavior

### Ketika Nilai Kurang (contoh: minimal 87.00, input 76.00)
1. **Popup muncul otomatis** saat semua 5 semester sudah diisi
2. **Rata-rata display: 76.00 (background merah)**
3. **Tombol submit: DISABLED (abu-abu)**

### Ketika Nilai Cukup (contoh: minimal 87.00, input 88.00)
1. **Tidak ada popup**
2. **Rata-rata display: 88.00 (background hijau)**
3. **Tombol submit: ENABLED (hijau)**

## Kondisi Validasi Aktif

Validasi HANYA berjalan jika:
1. ✅ Status validasi di admin = **Aktif**
2. ✅ Minimal nilai di admin > **0**
3. ✅ Semua **5 semester sudah diisi**

## File yang Dimodifikasi

### `register.php`
**Fungsi `calculateNilai()` diperbaharui dengan:**
- Cek minimal nilai dari database (real-time)
- Cek status validasi
- Validasi setelah semua 5 semester diisi
- Popup alert menggunakan JavaScript `alert()`
- Toggle tombol submit (enable/disable)
- Visual feedback dengan warna (merah/hijau)

## Keunggulan Update Ini

1. **User Friendly**: Siswa langsung tahu apakah nilainya cukup atau tidak
2. **Real-time Feedback**: Tidak perlu submit dulu baru tahu ditolak
3. **Visual Clear**: Warna merah/hijau memberi petunjuk jelas
4. **Prevent Submit**: Tombol disabled mencegah submit sia-sia
5. **Informative**: Alert memberikan info lengkap nilai siswa vs minimal

## Testing

### Test Case 1: Nilai Kurang
1. Admin set minimal: 87.00, status: Aktif
2. Siswa isi nilai: 76, 76, 76, 76, 76
3. ✅ **Expected**: Popup muncul, rata-rata merah, tombol disabled

### Test Case 2: Nilai Cukup
1. Admin set minimal: 87.00, status: Aktif
2. Siswa isi nilai: 88, 88, 88, 88, 88
3. ✅ **Expected**: Tidak popup, rata-rata hijau, tombol enabled

### Test Case 3: Validasi Nonaktif
1. Admin set minimal: 87.00, status: **Nonaktif**
2. Siswa isi nilai: 76, 76, 76, 76, 76
3. ✅ **Expected**: Tidak popup, tombol tetap enabled (no validation)

## Note Pengembang

- Alert menggunakan JavaScript native `alert()` (simple & universal)
- Validasi berjalan di client-side untuk UX yang lebih baik
- Server-side validation tetap ada di `process_register.php` sebagai double check
- Visual feedback menggunakan inline style untuk instant response

---

**Update Date**: 01 Februari 2026
**Status**: ✅ Production Ready
