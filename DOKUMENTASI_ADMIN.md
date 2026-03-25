# 📚 PANDUAN LENGKAP ADMIN PMBM
## MTsN 1 Kota Pekanbaru
### Sistem Penerimaan Murid Baru Madrasah (PMBM)

---

## 📋 DAFTAR ISI

1. [Dashboard](#1-dashboard)
2. [Menu Kesiswaan](#2-menu-kesiswaan)
3. [Menu Sekolah](#3-menu-sekolah)
4. [Menu Ujian](#4-menu-ujian)
5. [Menu Pasca Seleksi](#5-menu-pasca-seleksi)
6. [Menu Sistem](#6-menu-sistem)

---

# 1. 📊 DASHBOARD

## Apa itu Dashboard?
Dashboard adalah **halaman beranda** yang langsung muncul setelah Anda login sebagai admin. Ini seperti "ruang kontrol" yang menampilkan ringkasan semua data penting tentang pendaftaran siswa baru.

## Apa Yang Ada di Dashboard?

### 🔢 **4 Kotak Angka Besar (Statistik)**

Dashboard menampilkan 4 kotak warna-warni yang berisi angka penting:

1. **Kotak BIRU - Total Pendaftar**
   - Menampilkan berapa **total semua siswa** yang sudah mendaftar
   - Contoh: Jika ada 150 siswa yang daftar, akan muncul angka 150

2. **Kotak HIJAU - Diterima**
   - Menampilkan berapa siswa yang **sudah dinyatakan diterima**
   - Ini siswa yang lolos seleksi

3. **Kotak MERAH - Ditolak**
   - Menampilkan berapa siswa yang **tidak lolos seleksi**
   - Siswa yang tidak memenuhi syarat

4. **Kotak BIRU MUDA - Pending**
   - Menampilkan berapa siswa yang **masih menunggu** diproses
   - Ini siswa yang baru daftar tapi belum diverifikasi

💡 **Tips:** Kalau angka di kotak "Pending" banyak, artinya ada banyak siswa yang perlu segera diverifikasi!

---

### 📊 **Grafik Bulat (Donat)**

Di tengah dashboard ada **grafik berbentuk donat** yang menampilkan:

- **Berapa siswa yang daftar di setiap jalur**
  - Contoh: Jalur Prestasi: 50 siswa, Jalur Reguler: 100 siswa
- **Warna berbeda untuk setiap jalur** supaya mudah dibedakan
- **Klik pada bagian donat** untuk melihat detail

📌 **Kegunaan:** Admin bisa langsung tahu jalur mana yang paling banyak peminatnya!

---

### 📝 **Tabel Data Pendaftar Terbaru**

Di bagian bawah ada **tabel** yang menampilkan daftar siswa yang baru-baru ini mendaftar.

**Isi tabel:**
- No. Pendaftaran (contoh: PMBM/2026/001)
- Nama Siswa
- NISN
- Tempat/Tanggal Lahir
- Jenis Kelamin
- Asal Sekolah
- Waktu Daftar
- Jalur Pendaftaran
- Status (Pending/Diterima/Ditolak)

**Yang Bisa Dilakukan:**
- 🔍 **Cari Siswa:** Ketik nama/NISN di kotak pencarian
- 👁️ **Lihat Detail:** Klik tombol mata biru untuk lihat lengkap
- ✏️ **Edit Data:** Klik tombol kuning untuk ubah data
- 🖨️ **Cetak Bukti:** Klik tombol hijau untuk cetak kartu pendaftaran

---

### 📱 Tampilan di HP

**Jika buka dari HP/tablet:**
- Ada tombol **☰ (tiga garis)** di pojok kiri atas
- Klik tombol itu untuk membuka menu
- Semua kotak angka akan tersusun ke bawah (tidak sejajar)
- Tabel bisa di-scroll ke kanan-kiri kalau lebar

---

## Cara Menggunakan Dashboard

### ✅ **Langkah Harian Admin:**

**Pagi Hari:**
1. Login ke sistem
2. Lihat **Kotak "Pending"** → Berapa siswa yang menunggu?
3. Lihat **Grafik Donat** → Jalur mana yang ramai?
4. Scroll ke tabel → Lihat siapa saja yang baru daftar

**Siang/Sore:**
1. Cek lagi kotak "Pending"
2. Prioritaskan memproses siswa yang pending
3. Pastikan tidak ada pendaftar yang terlewat

---

## ⚠️ Kalau Ada Masalah

**Semua angka menunjukkan 0:**
→ Mungkin belum ada yang daftar ATAU ada masalah database  
→ Hubungi IT support

**Grafik tidak muncul:**
→ Refresh halaman (tekan F5)  
→ Coba buka dengan browser lain

**Tabel kosong:**
→ Cek apakah ada filter yang aktif  
→ Pastikan memang sudah ada siswa yang daftar

---

# 2. 👨‍🎓 MENU KESISWAAN

Menu Kesiswaan berisi semua hal yang berhubungan dengan **data siswa pendaftar**. Menu ini punya 3 sub-menu:

---

## 2.1 📚 DATA PENDAFTAR

### Apa Fungsinya?
Halaman ini untuk **melihat, mencari, dan mengelola semua data siswa** yang sudah mendaftar.

### Yang Ada di Halaman Ini:

#### 🔍 **Filter Pencarian** (Kotak di Atas)

Ada 3 kotak untuk memudahkan cari siswa:

1. **Cari Siswa**
   - Ketik: Nama / NISN / No. Pendaftaran
   - Sistem langsung cari otomatis

2. **Filter Jalur**
   - Pilih jalur tertentu (misal: hanya lihat Jalur Prestasi)
   - Atau pilih "Semua Jalur"

3. **Filter Status**
   - Pending → Siswa yang belum diproses
   - Terverifikasi → Sudah dicek, menunggu seleksi
   - Diterima → Lolos
   - Ditolak → Tidak lolos

💡 **Contoh Penggunaan:**  
Mau lihat semua siswa Jalur Prestasi yang masih Pending?  
→ Pilih "Jalur Prestasi" + "Status: Pending" → Klik "Terapkan"

---

#### 📥 **Tombol Export** (Pojok Kanan Atas)

**Ada 2 tombol:**

1. **Export Excel** (Hijau)
   - Unduh data siswa dalam bentuk file Excel
   - Bisa dibuka di Microsoft Excel / Google Sheets
   - Berguna untuk laporan

2. **Print PDF** (Merah)
   - Cetak daftar siswa dalam bentuk PDF
   - Bisa langsung print atau simpan

---

#### ⚡ **Verifikasi Otomatis** (Tombol Biru Besar)

**"Verifikasi Otomatis Semua Siswa Pending"**

**Fungsi:**
- Mengubah SEMUA siswa yang berstatus "Pending" menjadi "Terverifikasi"
- Cukup klik 1 tombol, semua langsung terverifikasi

**Kapan Digunakan:**
- Saat masa pendaftaran tutup
- Semua dokumen sudah dicek manual
- Ingin verifikasi massal sekaligus

⚠️ **HATI-HATI:** Tombol ini akan memproses SEMUA siswa pending tanpa kecuali!

---

#### 📊 **Tabel Data Lengkap**

Tabel ini menampilkan lebih detail dibanding dashboard:

**Kolom-kolom:**
- No. Pendaftaran
- Nama & NISN
- TTL (Tempat Tanggal Lahir) & Jenis Kelamin
- Asal Sekolah
- Waktu Daftar (tanggal + jam)
- Jalur Pendaftaran
- Status

**Tombol Aksi (Paling Kanan):**

1. 👁️ **Mata Biru** = Lihat Detail
   - Klik untuk melihat SEMUA data siswa
   - Foto, dokumen, nilai, dll

2. ✏️ **Pensil Kuning** = Edit
   - Ubah data siswa jika ada kesalahan
   - Hanya bisa edit: Jalur, NISN, NIK

3. 🖨️ **Print Hijau** = Cetak Bukti
   - Cetak kartu pendaftaran siswa
   - Format siap print

---

### 💼 Cara Kerja Sehari-hari:

**Pagi:**
1. Buka menu "Data Pendaftar"
2. Klik filter "Status: Pending"
3. Lihat berapa siswa baru

**Proses Verifikasi:**
1. Klik tombol **Mata Biru** untuk lihat detail
2. Periksa dokumen, foto, data lengkap
3. Jika OK → Ubah status jadi "Terverifikasi"
4. Jika ada masalah → Ubah jadi "Ditolak" + beri catatan

**Sore:**
1. Cek lagi apakah masih ada pending
2. Export Excel untuk laporan harian

---

## 2.2 ✅ VERIFIKASI DATA

### Apa Fungsinya?
Halaman khusus untuk melihat **siswa yang sudah terverifikasi** (sudah lolos pengecekan dokumen).

### Isi Halaman:

**Tabel Sederhana:**
- No. Pendaftaran
- Nama Siswa
- Jalur
- Tanggal Daftar
- Tombol "Verifikasi" (hijau)

### Kapan Digunakan?

Halaman ini untuk **double check** siswa yang sudah diverifikasi:
- Pastikan tidak ada yang kelewat
- Review ulang sebelum seleksi akhir

💡 **Tips:** Halaman ini lebih "clean" karena hanya tampilkan yang sudah OK saja

---

## 2.3 📁 MANAJEMEN DOKUMEN

### Apa Fungsinya?
Melihat dan mengelola **semua dokumen** yang diupload siswa (KITA, KK, Ijazah, dll).

### Kegunaan:
- Download dokumen tertentu
- Cek kelengkapan dokumen
- Verifikasi keaslian dokumen

---

# 3. 🏫 MENU SEKOLAH

Menu ini berisi **pengaturan-pengaturan** tentang jalur, kuota, dan aturan pendaftaran.

---

## 3.1 🛣️ JALUR PENDAFTARAN

### Apa Fungsinya?
Membuat dan mengelola **jalur-jalur pendaftaran** yang tersedia.

**Contoh Jalur:**
- Jalur Prestasi
- Jalur Reguler
- Jalur Afirmasi (Miskin/KIP)
- Jalur Anak Guru/Karyawan

---

### Yang Bisa Dilakukan:

#### ➕ **Tambah Jalur Baru**

1. Klik tombol **"+ Tambah Jalur"** (pojok kanan)
2. Isi form:
   - **Nama Jalur:** Contoh "Jalur Prestasi Akademik"
   - **Syarat Khusus:** Dokumen apa saja yang harus diupload
     - Pisahkan dengan koma (,)
     - Contoh: `Pas Foto, Rapor, Piagam Juara`

3. Klik **"Simpan"**

💡 **Contoh Syarat:**
```
Pas Foto 3x4,
Fotocopy Ijazah,
Surat Keterangan Ranking,
Sertifikat Prestasi
```

---

#### ✏️ **Edit Jalur**

Klik tombol **Pensil Kuning** di kolom Aksi:
- Ubah nama jalur
- Tambah/kurangi syarat
- Klik "Simpan"

---

#### 🗑️ **Hapus Jalur**

Klik tombol **Tong Sampah Merah**:
- Sistem akan tanya konfirmasi
- Hati-hati, data terhapus TIDAK bisa dikembalikan!

⚠️ **PERINGATAN:** Jangan hapus jalur yang sudah ada pendaftarnya!

---

### 📝 Tabel Jalur

Tabel menampilkan:
1. **No** - urutan
2. **Nama Jalur** - nama lengkap jalur
3. **Syarat Khusus** - ditampilkan dalam bentuk badge warna:
   - 🔴 **Merah** = Syarat WAJIB
   - 🔵 **Abu-abu** = Syarat PILIHAN (opsional)

---

### 💡 Tips Penggunaan:

**Sebelum Pendaftaran Dibuka:**
1. Buat semua jalur yang diperlukan
2. Isi syarat dengan lengkap dan jelas
3. koordinasi dengan panitia lain

**Saat Pendaftaran Berjalan:**
- JANGAN hapus atau ubah jalur
- Bisa membingungkan calon siswa

---

## 3.2 📅 SKEMA PMBM

### Apa Fungsinya?
Mengatur **waktu pembukaan dan penutupan** pendaftaran untuk setiap skema.

**Contoh Skema:**
- Skema 1: Gelombang Pertama (Jan-Feb)
- Skema 2: Gelombang Kedua (Mar-Apr)
- Skema 3: Gelombang Ketiga (Mei-Jun)

### Pengaturan:

Untuk setiap skema, atur:
- **Tanggal Mulai**
- **Tanggal Selesai**
- **Jam Mulai** (misal: 00:01 atau 08:00)
- **Jam Selesai** (misal: 23:59 atau 16:00)

💡 **Otomatis:** Sistem akan menutup pendaftaran jika waktu sudah habis!

---

## 3.3 🎯 SELEKSI & RANKING

### Apa Fungsinya?
Membuat **ranking siswa** berdasarkan nilai dan menentukan siapa yang diterima.

### Fitur Utama:

1. **Hitung Ranking Otomatis**
   - Sistem mengurutkan siswa berdasarkan nilai
   - Nilai tertinggi = Ranking 1

2. **Tentukan Passing Grade**
   - Set nilai minimum yang harus dicapai
   - Siswa di bawah nilai ini otomatis tidak lolos

3. **Lihat Hasil Seleksi**
   - Lihat daftar siswa yang lolos/tidak lolos
   - Export untuk pengumuman

---

## 3.4 📏 MINIMAL NILAI RATA-RATA

### Apa Fungsinya?
Menentukan **nilai rata-rata minimum** yang harus dimiliki siswa untuk bisa mendaftar.

### Cara Setting:

1. Masukkan angka nilai minimum
   - Contoh: 75 (berarti rata-rata rapor minimal 75)

2. Sistem akan **otomatis tolak** siswa yang nilai rata-ratanya di bawah batas ini

💡 **Contoh:**
- Jalur Prestasi: Minimal nilai 85
- Jalur Reguler: Minimal nilai 75

---

## 3.5 🎂 BATASAN UMUR

### Apa Fungsinya?
Mengatur **batas umur minimum dan maksimum** calon siswa.

### Pengaturan:

1. **Umur Minimal**
   - Contoh: 12 tahun
   
2. **Umur Maksimal**
   - Contoh: 15 tahun

3. **Tanggal Cut-Off**
   - Tanggal patokan penghitungan umur
   - Biasanya: 1 Juli tahun ajaran baru

💡 **Cara Kerja:**
Sistem akan hitung umur siswa pada tanggal cut-off. Jika di luar range, tidak bisa daftar.

---

## 3.6 📢 STATUS PELAKSANAAN

### Apa Fungsinya?
Menampilkan **timeline/jadwal** kegiatan PMBM kepada calon siswa.

### Isi:

Buat timeline seperti:
- ✅ 1 Jan - 28 Feb: Pendaftaran Online
- ✅ 1-5 Maret: Verifikasi Dokumen
- ⏳ 10 Maret: Ujian Masuk
- ⏳ 15 Maret: Pengumuman Hasil

📌 **Tampil di website** supaya calon siswa tahu jadwalnya!

---

## 3.7 📖 PANDUAN & BROSUR

### Apa Fungsinya?
Upload file **panduan pendaftaran** dan **brosur** yang bisa didownload calon siswa.

### Yang Bisa Diupload:

1. **Brosur Sekolah** (PDF/JPG)
2. **Panduan Pendaftaran** (PDF)
3. **FAQ** (Tanya Jawab)

💡 **Tips:** Buat brosur semenarik mungkin dengan design yang bagus!

---

## 3.8 📸 UPLOAD FOTO CONTOH

### Apa Fungsinya?
Upload **contoh foto yang benar** untuk panduan calon siswa.

### Upload:
  
- Foto pas background merah ✅
- Foto tidak pakai kacamata
- Foto tidak terlalu gelap/terang

📌 Foto contoh akan muncul di halaman pendaftaran!

---

## 3.9 📄 SURAT KETERANGAN

### Apa Fungsinya?
Mengelola template **surat keterangan** yang dibutuhkan siswa.

Contoh:
- Surat Keterangan Lulus
- Surat Keterangan Ranking
- Surat Keterangan Tidak Mampu

---

## 3.10 📝 KETERANGAN NARASI

### Apa Fungsinya?
Menulis **teks/narasi** yang muncul di website.

Contoh narasi:
- Selamat datang di PMBM MTsN 1...
- Syarat pendaftaran adalah...
- Hubungi kami di...

💡 Ada **editor teks** seperti Microsoft Word (bisa bold, italic, warna, dll)

---

# 4. 📝 MENU UJIAN

Menu untuk mengelola **ujian masuk** (jika ada).

---

## 4.1 📅 JADWAL & INFO UJIAN

### Apa Fungsinya?
Mengatur **jadwal ujian** untuk siswa.

### Input Data:

1. **Tanggal Ujian**
2. **Waktu Mulai & Selesai**
3. **Lokasi/Ruangan**
4. **Jenis Ujian** (Tertulis/CBT/Wawancara)
5. **Mata Pelajaran** yang diujikan

📌 Siswa bisa lihat jadwal mereka di website!

---

## 4.2 📊 DATA NILAI UJIAN

### Apa Fungsinya?
Input **nilai ujian** setiap siswa.

### Cara:

1. Cari siswa (by nama/no pendaftaran)
2. Input nilai per mata pelajaran
3. Sistem hitung **nilai akhir** otomatis

💡 Nilai bisa di-import dari Excel jika banyak!

---

## 4.3 ⚙️ PENGATURAN CBT

### Apa Fungsinya?
Setting untuk **Computer Based Test** (ujian di komputer).

### Pengaturan:

- Durasi ujian (misal: 90 menit)
- Jumlah soal
- Passing grade
- Randomize soal (acak)

---

## 4.4 🔗 INTEGRASI CBT EXTERNAL

### Apa Fungsinya?
Menghubungkan sistem PMBM dengan **sistem CBT eksternal** (jika pakai aplikasi ujian terpisah).

Contoh: Exambro, Moodle, Google Forms, dll.

---

# 5. 📣 MENU PASCA SELEKSI

Menu untuk kegiatan **setelah seleksi selesai**.

---

## 5.1 🎉 PENGUMUMAN

### Apa Fungsinya?
Membuat dan menampilkan **pengumuman hasil seleksi**.

### Fitur:

1. **Publish Pengumuman**
   - Klik tombol → Siswa bisa cek hasil di website

2. **Format Pengumuman:**
   - List nomor pendaftaran yang DITERIMA
   - Atau link download PDF

3. **Notifikasi Otomatis:**
   - Sistem bisa kirim WA/Email ke siswa (jika diaktifkan)

⚠️ **Pastikan data BENAR sebelum publish!** Tidak bisa di-undo!

---

## 5.2 ✍️ DAFTAR ULANG

### Apa Fungsinya?
Mencatat siswa yang sudah **daftar ulang** (confirm masuk).

### Proses:

1. Siswa yang diterima wajib daftar ulang
2. Admin centang siswa yang sudah datang
3. Catat pembayaran/dokumen yang diserahkan

💡 Ada **deadline daftar ulang** → Siswa yang telat, kursinya bisa diisi yang lain!

---

# 6. ⚙️ MENU SISTEM

Menu untuk **pengaturan sistem** dan **manajemen user**.

---

## 6.1 👥 USER MANAGEMENT

### Apa Fungsinya?
Mengelola **akun admin** yang bisa akses sistem.

### Hak Akses (Role):

1. **Admin**
   - Akses PENUH ke semua menu
   - Bisa tambah/hapus user

2. **Operator**
   - Sama seperti admin
   - Bisa kelola data siswa

3. **Panitia**
   - Hanya bisa akses menu "Kesiswaan"
   - Untuk tim verifikasi dokumen

---

### Fitur User Management:

#### ➕ Tambah User Baru

1. Klik "Tambah User"
2. Isi:
   - Username (untuk login)
   - Password
   - Nama Lengkap
   - Role (Admin/Operator/Panitia)
   - Email (opsional)

3. Klik "Simpan"

---

#### ✏️ Edit User

- Ubah password
- Ganti role
- Non-aktifkan akun

---

#### 🗑️ Hapus User

⚠️ **Hati-hati!** Akun terhapus tidak bisa login lagi.

---

## 6.2 🔐 MANAJEMEN AKSES

### Apa Fungsinya?
Mengatur **menu mana saja** yang bisa diakses setiap role.

### Contoh Pengaturan:

**Role: Panitia**
- ✅ Bisa akses: Kesiswaan
- ❌ Tidak bisa: Sekolah, Sistem, Ujian

**Role: Admin**
- ✅ Bisa akses: SEMUA menu

💡 **Kegunaan:** Membatasi hak akses supaya tidak sembarangan

---

## 6.3 📑 LAPORAN & EXPORT

### Apa Fungsinya?
Generate **laporan** dalam berbagai format.

### Jenis Laporan:

1. **Laporan Pendaftar**
   - Total pendaftar per jalur
   - Perbandingan tahun lalu

2. **Laporan Seleksi**
   - Jumlah diterima/ditolak
   - Persentase kelulusan

3. **Laporan Daftar Ulang**
   - Berapa yang sudah daftar ulang

### Format Export:

- **Excel** (.xlsx)
- **PDF** (siap print)
- **CSV** (untuk import ke aplikasi lain)

---

## 6.4 📱 WA GATEWAY SETTINGS

### Apa Fungsinya?
Mengatur **pengiriman WhatsApp otomatis** ke siswa.

### Pengaturan:

1. **API Key** dari penyedia layanan WA
2. **Nomor Pengirim**
3. **Template Pesan:**
   - Pesan saat daftar
   - Pesan saat diterima
   - Pesan saat ditolak

💡 **Contoh Pesan:**
```
Halo {nama},
Pendaftaran Anda dengan No. {no_daftar} berhasil!
Silakan cek email untuk info selanjutnya.
```

---

## 6.5 🔧 PENGATURAN SISTEM

### Apa Fungsinya?
Setting **informasi dasar** sistem yang tampil di website.

### Yang Bisa Diatur:

#### **1. Informasi Umum**
- Nama Sekolah
- Tahun Ajaran (contoh: 2026/2027)
- Nama Gelombang (contoh: Gelombang 1)

#### **2. Tampilan Beranda**
- Judul Utama (Hero Title)
  - Contoh: "PMBM MTsN 1 Kota Pekanbaru 2026"
- Deskripsi Singkat
  - Contoh: "Mari bergabung dengan kami..."

#### **3. Kontak Kami**
- No. Telepon / WhatsApp
- Alamat Email
- Alamat Sekolah

📌 **Tips:** Pastikan nomor WA aktif dan sering dicek!

---

### Cara Menggunakan:

1. Pilih tab (Informasi Umum/Tampilan Beranda/Kontak)
2. Isi form yang tersedia
3. Scroll ke bawah
4. Klik **"Simpan Semua Perubahan"** (tombol biru besar)

✅ **Perubahan langsung tampil di website!**

---

## 6.6 📋 LOG AKTIVITAS

### Apa Fungsinya?
Mencatat **semua aktivitas** yang dilakukan admin dalam sistem.

### Yang Dicatat:

- Siapa yang login
- Kapan login
- Apa yang diubah/dihapus
- Data apa yang di-export

📌 **Kegunaan:** Untuk **audit** dan **keamanan**. Jika ada masalah, bisa dilacak!

---

# 📌 TIPS & TRIK UMUM

## ✅ Do's (Yang Harus Dilakukan)

1. **Backup Data Rutin**
   - Export data Excel setiap minggu

2. **Cek Pending Harian**
   - Jangan biarkan siswa menunggu lama

3. **Verifikasi Dokumen Teliti**
   - Pastikan foto jelas
   - Cek tanggal lahir matches ijazah

4. **Koordinasi Tim**
   - Pakai grup WA untuk komunikasi cepat

5. **Test Sebelum Publish**
   - Cek pengumuman 2-3 kali sebelum publish

---

## ❌ Don'ts (Yang Jangan Dilakukan)

1. **Jangan Share Password**
   - Setiap admin punya akun sendiri

2. **Jangan Hapus Data Sembarangan**
   - Data terhapus tidak bisa dikembalikan

3. **Jangan Ganti Jalur saat Pendaftaran Aktif**
   - Bisa bikin bingung

4. **Jangan Lupa Logout**
   - Terutama di komputer umum

5. **Jangan Abaikan Notifikasi Error**
   - Segera laporkan ke IT

---

# 🆘 TROUBLESHOOTING UMUM

## Problem: Lupa Password

**Solusi:**
1. Hubungi admin utama
2. Minta reset password
3. Login dengan password baru
4. Ganti password via menu Profile

---

## Problem: Data Tidak Muncul

**Solusi:**
1. Refresh halaman (tekan F5)
2. Clear cache browser
3. Coba browser lain
4. Cek filter yang aktif

---

## Problem: Export Excel Error

**Solusi:**
1. Cek koneksi internet
2. Pastikan tidak ada special character di nama file
3. Coba export dengan filter lebih sedikit

---

## Problem: Upload Gagal

**Solusi:**
1. Cek ukuran file (max 2MB)
2. Pastikan format file benar (JPG/PNG/PDF)
3. Coba compress dulu file-nya

---

# 📞 KONTAK SUPPORT

Jika ada masalah teknis yang tidak bisa diselesaikan:

📧 **Email:** [email protected]  
📱 **WhatsApp:** 082xxxxxxxxx  
🕐 **Jam Kerja:** 08:00 - 16:00 WIB

---

**Terakhir Diupdate:** 14 Februari 2026  
**Versi Dokumentasi:** 1.0

---

**© 2026 MTsN 1 Kota Pekanbaru**  
*Semoga dokumentasi ini membantu! 🙏*

