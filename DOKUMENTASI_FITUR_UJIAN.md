# 📚 Dokumentasi Fitur: Data Test Akademik (PMBM)

Dokumentasi ini menjelaskan cara penggunaan fitur manajemen ujian akademik yang terletak pada menu **Data Test Akademik** di panel Admin.

## 1. Pengaturan & Penjadwalan Otomatis (Auto-Scheduling)
Fitur ini dirancang untuk mendistribusikan siswa yang telah lolos verifikasi berkas ke dalam slot waktu ujian yang tersedia secara otomatis.

### Cara Penggunaan:
1. **Input Daftar Hari**: Masukkan hari pelaksanaan tes, dipisahkan dengan koma. 
   * *Contoh: Senin, Selasa, Rabu*
2. **Input Jumlah Sesi**: Tentukan berapa sesi yang akan dilaksanakan dalam satu hari.
   * *Contoh: 3* (Maka dalam sehari akan ada Sesi 1, Sesi 2, dan Sesi 3).
3. **Input Kapasitas**: Tentukan jumlah maksimal siswa yang dapat ditampung dalam satu sesi (berdasarkan kapasitas Lab Komputer).
   * *Contoh: 40* (Maka sistem akan mengisi Sesi 1 dengan 40 orang, lalu pindah ke Sesi 2, dst).
4. **Simpan Pengaturan**: Klik tombol ini untuk mengunci konfigurasi.
5. **Generate Jadwal Otomatis**: Klik tombol ini untuk menjalankan mesin penjadwalan. Sistem akan memproses seluruh siswa berstatus 'Terverifikasi' dan memberikan mereka jadwal secara berurutan.

> [!TIP]
> Gunakan tombol **Reset Semua Jadwal** jika ingin mengulang pembagian jadwal dari awal (misal jika ada perubahan hari atau kapasitas mendadak).

---

## 2. Export Data ke Excel
Fitur ini memungkinkan Admin mengunduh data peserta beserta jadwalnya ke dalam format file Excel.

### Detail Output:
* **Nama File**: `Data_Jadwal_Ujian_[Tanggal].xls`
* **Kolom Data**:
    1. No Pendaftaran
    2. Nama Lengkap
    3. NISN (Sebagai Username)
    4. Hari Ujian
    5. Sesi Ujian
* **Kegunaan**: Data ini biasanya digunakan untuk diimpor ke aplikasi pihak ketiga seperti **Aplikasi CBT**, pembuatan daftar hadir fisik (presensi), atau laporan rekapitulasi panitia.

---

## 3. Cetak Kartu Ujian Massal
Fitur ini digunakan untuk mencetak kartu peserta dalam jumlah banyak sekaligus dalam satu halaman (print-ready).

### Komponen Kartu:
* Header Instansi (Kemenag & Logo Sekolah).
* Identitas Murid (Nama, No Pendaftaran, NISN, Jalur).
* **Akun Login Ujian**: Menampilkan Username dan Password khusus untuk aplikasi CBT.
* **Jadwal Ujian**: Menampilkan Hari dan Sesi yang telah didapat dari hasil *Generate* otomatis.
* Instruksi Penting mengenai tata tertib ujian.

### Cara Mencetak:
1. Klik tombol **Print Semua Kartu Ujian**.
2. Tab baru akan terbuka menampilkan daftar kartu.
3. Tekan `Ctrl + P` pada keyboard.
4. Pastikan setelan printer adalah **Potrait** dan margin **Default/None** agar kartu tercetak rapi.

---

## 🛠 Informasi Teknis (Untuk Developer)
* **File Utama**: `4dmMtsn1/ujian/test_akademik.php`
* **Logika Generate**: Menggunakan perulangan bersarang (*nested loop*) melalui elemen Hari -> Sesi -> Kapasitas.
* **Filter Data**: Hanya memproses siswa dengan `status = 'Terverifikasi'`.
* **Database**: Kolom yang digunakan adalah `test_hari` dan `test_sesi` pada tabel `pendaftar`.
