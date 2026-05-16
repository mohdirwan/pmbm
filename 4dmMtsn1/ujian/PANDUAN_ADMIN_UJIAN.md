# 📘 Panduan Administrasi Ujian - PMBM

Dokumen ini menjelaskan cara pengelolaan infrastruktur ujian dan penjadwalan otomatis bagi Admin sistem PMBM.

---

## 1. Modul Pengaturan Ruangan & Sesi
Halaman ini digunakan untuk menyiapkan "Wadah" ujian.

### A. Manajemen Laboratorium (Labor)
* **Tambah Labor:** Masukkan nama labor dan **Kapasitas PC** (Jumlah unit komputer yang tersedia).
* **Kapasitas Spesifik:** Setiap labor bisa memiliki kapasitas yang berbeda-beda. Sistem akan menghitung total kapasitas gabungan secara otomatis.
* **Edit/Update:** Gunakan tombol ikon pensil untuk memperbarui nama atau kapasitas labor tanpa perlu menghapusnya.

### B. Manajemen Sesi Ujian
* **Tambah Sesi:** Tentukan nama sesi (misal: Sesi 1) dan rentang waktunya.
* **Waktu Real-time:** Jam yang Anda masukkan di sini akan langsung tercetak di kartu ujian siswa saat jadwal dibuat.

### C. Ringkasan Kapasitas
Di bagian atas halaman terdapat statistik otomatis:
- **Total Kapasitas Per Sesi:** Jumlah seluruh PC di semua labor.
- **Total Kapasitas Harian:** Daya tampung maksimal siswa dalam satu hari (Total PC × Jumlah Sesi).

---

## 2. Modul Data Test Akademik
Halaman ini digunakan untuk mengeksekusi pembagian jadwal siswa.

### A. Slot Hari Ujian
* Masukkan daftar hari pelaksanaan ujian pada kolom **Daftar Hari Ujian** (contoh: `Senin, Selasa, Rabu`).
* Klik **Simpan Daftar Hari** untuk menyimpan konfigurasi hari.

### B. Informasi Slot (Sinkronisasi)
Sistem menampilkan informasi otomatis yang ditarik dari Pengaturan Ruangan:
- **Jumlah Sesi** yang aktif.
- **Kapasitas Per Sesi** (Total PC Gabungan).
*Catatan: Jika angka ini tidak sesuai, silakan kembali ke menu "Atur Ruangan".*

### C. Penjadwalan Otomatis (Generate)
1. Klik tombol **"Generate Jadwal Otomatis"**.
2. Sistem akan memproses seluruh siswa yang berstatus **"Terverifikasi"** namun belum memiliki jadwal.
3. Algoritma akan mengisi Labor 1 sesuai kapasitasnya, lalu lanjut ke Labor 2, dan seterusnya untuk setiap sesi dan hari yang tersedia.
4. Hasil pembagian labor, sesi, dan jam akan langsung muncul di tabel daftar peserta.

---

## ⚠️ Hal Penting untuk Diperhatikan
1. **Urutan Kerja:** Selalu atur **Ruangan & Sesi** terlebih dahulu sebelum melakukan **Generate Jadwal**.
2. **Reset Jadwal:** Tombol **"Reset Semua Jadwal"** akan menghapus seluruh jadwal yang sudah ada. Gunakan hanya jika Anda ingin mengulang pembagian dari awal.
3. **Cetak Kartu:** Setelah jadwal digenerate, Admin dapat menggunakan menu "Cetak Kartu" untuk mencetak kartu ujian siswa yang sudah berisi informasi Hari, Sesi, Jam, dan Lokasi Laboratorium.

---
*Dokumentasi Sistem Penjadwalan PMBM v2.0*
