# Fitur Minimal Nilai Rata-rata

## Deskripsi
Fitur ini memungkinkan Admin untuk mengatur nilai rata-rata minimal yang harus dipenuhi oleh calon siswa saat pendaftaran. Jika nilai rata-rata siswa di bawah minimal yang ditetapkan, sistem akan menolak pendaftaran secara otomatis.

## File yang Dibuat/Dimodifikasi

### 1. **admin/minimal_nilai.php** (BARU)
Halaman pengaturan minimal nilai di panel admin dengan fitur:
- Input minimal nilai rata-rata (0-100)
- Toggle untuk mengaktifkan/non-aktifkan validasi
- Preview pesan error
- Informasi real-time current setting

### 2. **admin/includes/sidebar.php** (DIMODIFIKASI)
- Ditambahkan menu "Minimal Nilai Rata-rata" di submenu Sekolah
- Posisi: Setelah menu "Seleksi & Ranking"

### 3. **process_register.php** (DIMODIFIKASI)
- Ditambahkan validasi minimal nilai sebelum insert data
- Jika nilai tidak memenuhi syarat:
  - Menampilkan halaman error yang informatif
  - Menampilkan perbandingan nilai siswa vs minimal
  - Tombol kembali ke form pendaftaran

### 4. **register.php** (DIMODIFIKASI)
- Ditambahkan alert info di Step 4 (Input Nilai Rapor)
- Alert hanya muncul jika validasi aktif dan minimal > 0
- Menampilkan badge dengan angka minimal nilai

## Cara Penggunaan

### Untuk Admin:

1. **Akses Menu**
   - Login sebagai Admin
   - Buka menu **Sekolah** → **Minimal Nilai Rata-rata**

2. **Set Minimal Nilai**
   - Input nilai minimal (contoh: 75.00)
   - Pilih status validasi:
     - **Aktif**: Sistem akan menolak pendaftaran dengan nilai di bawah minimal
     - **Nonaktif**: Sistem terima semua nilai tanpa validasi
   - Klik **Simpan Pengaturan**

3. **Monitor**
   - Info panel di kanan menampilkan setting saat ini
   - Preview pesan error yang akan diterima siswa

### Untuk Siswa:

1. **Saat Mendaftar**
   - Di Step 4 (Input Nilai Rapor), jika validasi aktif akan muncul alert biru
   - Alert menampilkan minimal nilai yang diperlukan
   - Input nilai rapor semester 4, 5, dan 6

2. **Jika Nilai Kurang**
   - Sistem menghitung rata-rata otomatis
   - Jika kurang dari minimal, pendaftaran ditolak
   - Muncul halaman error dengan:
     - ⚠️ Icon peringatan
     - Perbandingan nilai (nilai siswa vs minimal)
     - Tombol kembali ke form pendaftaran

3. **Jika Nilai Memenuhi Syarat**
   - Pendaftaran dilanjutkan normal
   - Dapat melanjutkan ke tahap berikutnya

## Struktur Database

Data disimpan di tabel `settings`:

```sql
key: 'minimal_nilai_rata'
value: '75.00' (contoh)

key: 'status_validasi_nilai'
value: 'aktif' atau 'nonaktif'
```

## Contoh Skenario

### Skenario 1: Validasi Aktif, Nilai memenuhi
- Admin set minimal: **75.00**
- Status: **Aktif**
- Siswa input nilai dengan rata-rata: **80.50**
- ✅ Pendaftaran DITERIMA

### Skenario 2: Validasi Aktif, Nilai tidak memenuhi
- Admin set minimal: **75.00**
- Status: **Aktif**
- Siswa input nilai dengan rata-rata: **70.00**
- ❌ Pendaftaran DITOLAK dengan pesan error

### Skenario 3: Validasi Nonaktif
- Admin set minimal: **75.00**
- Status: **Nonaktif**
- Siswa input nilai dengan rata-rata: **50.00** (apapun)
- ✅ Pendaftaran DITERIMA (tidak ada validasi)

## Screenshot Halaman

### Admin Panel
- Formulir input minimal nilai
- Toggle aktif/nonaktif
- Current setting display
- Preview pesan error

### Halaman Pendaftaran (Siswa)
- Alert info biru di Step 4
- Badge menampilkan minimal nilai
- Peringatan untuk memenuhi syarat

### Halaman Error (Jika Tidak Memenuhi)
- Design modern gradient purple
- Icon warning besar
- Perbandingan nilai (2 kolom)
- Tombol kembali yang jelas

## Keunggulan Fitur

1. **Fleksibel**: Admin bisa aktif/nonaktif validasi kapan saja
2. **User Friendly**: Pesan error yang jelas dan informatif
3. **Transparan**: Siswa tahu minimal nilai sejak awal
4. **Professional**: Design error page yang modern
5. **Data Driven**: Setting tersimpan di database

## Catatan Pengembang

- Validasi dilakukan di **server side** (process_register.php)
- Tidak ada validasi client-side untuk mencegah bypass
- Nilai dihitung dari rata-rata 5 semester (K4-S1, K4-S2, K5-S1, K5-S2, K6-S1)
- Format angka menggunakan 2 desimal
- Error page menggunakan Bootstrap 5 dan Font Awesome 6

## Testing

Untuk menguji fitur:

1. Set minimal nilai di admin (misalnya: 75.00)
2. Aktifkan validasi
3. Coba daftar dengan nilai:
   - Di atas 75: Harus berhasil
   - Di bawah 75: Harus ditolak
4. Nonaktifkan validasi
5. Coba daftar dengan nilai apapun: Harus berhasil

---

**Dibuat**: 01 Februari 2026
**Status**: ✅ Ready for Production
