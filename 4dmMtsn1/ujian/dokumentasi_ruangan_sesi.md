# Panduan Pengaturan Ruangan & Sesi Ujian

Dokumentasi ini menjelaskan cara mengelola laboratorium dan sesi ujian pada sistem PMBM untuk memastikan distribusi pendaftar berjalan dengan efisien.

## 1. Ikhtisar (Overview)
Fitur **Pengaturan Ruangan & Sesi** memungkinkan Panitia PMBM untuk:
*   Mendata laboratorium komputer yang tersedia beserta kapasitas fisiknya.
*   Membagi waktu ujian menjadi beberapa sesi.
*   Mengatur kapasitas target per sesi untuk menghitung daya tampung harian secara otomatis.

---

## 2. Mengelola Laboratorium (Ruangan)
Laboratorium adalah lokasi fisik tempat ujian dilaksanakan.
1.  Buka menu **Manajemen Ujian > Pengaturan Ruangan & Sesi**.
2.  Klik tombol **[+ Tambah Labor]**.
3.  Masukkan **Nama Labor** (contoh: *Labor Komputer 1*) dan **Kapasitas PC** (jumlah komputer yang berfungsi di ruangan tersebut).
4.  Klik **Simpan Ruangan**.

> [!TIP]
> Anda dapat menambahkan beberapa laboratorium jika ujian dilaksanakan di lebih dari satu ruangan secara bersamaan.

---

## 3. Mengelola Sesi Ujian
Sesi digunakan untuk membagi waktu pelaksanaan dalam satu hari.
1.  Pada kolom **Pembagian Sesi Ujian**, klik **[+ Tambah Sesi]**.
2.  Masukkan **Nama Sesi** (contoh: *Sesi 1*, *Sesi Pagi*).
3.  Atur **Waktu Mulai** dan **Waktu Selesai**.
4.  Klik **Simpan Sesi**.

---

## 4. Memahami Perhitungan Kapasitas
Sistem menggunakan logika dinamis untuk menghitung berapa banyak siswa yang dapat ditampung dalam satu hari.

### A. Konfigurasi Kapasitas Global
Pada bagian atas halaman, terdapat input **Target Kapasitas Per Sesi**. 
*   Ini adalah jumlah siswa yang Anda rencanakan untuk hadir dalam satu sesi per labor.
*   **Contoh:** Jika Anda ingin setiap sesi diisi oleh 25 orang, masukkan angka `25`.

### B. Rumus Kapasitas Harian
Kapasitas harian dihitung dengan rumus:
`Kapasitas Per Sesi` × `Jumlah Sesi yang Dibuat`

### C. Batasan Fisik (Safety Check)
Sistem secara cerdas akan membatasi kapasitas harian berdasarkan jumlah PC yang tersedia. 
*   Jika **PC tersedia = 20**, namun Anda mengatur **Kapasitas Per Sesi = 25**, maka sistem akan tetap menggunakan angka **20** sebagai pengali agar tidak terjadi kekurangan komputer saat ujian.

---

## 5. Contoh Skenario
| Nama Labor | Kapasitas PC | Jumlah Sesi | Target Per Sesi | Kapasitas Harian |
| :--- | :---: | :---: | :---: | :---: |
| Labor 1 | 30 | 3 | 25 | **75** (3x25) |
| Labor 2 | 20 | 3 | 25 | **60** (3x20 - dibatasi PC) |

---

## 6. Pertanyaan Umum (FAQ)
**P: Bagaimana jika saya ingin menambah jumlah sesi di tengah jalan?**
J: Anda cukup menambah sesi baru pada menu Sesi, maka kapasitas harian di tabel labor akan otomatis bertambah.

**P: Apakah pendaftar akan otomatis masuk ke ruangan ini?**
J: Data ini akan menjadi dasar bagi sistem untuk pembagian kartu ujian dan jadwal masing-masing siswa (pada fitur manajemen jadwal).

---
*Dokumentasi Sistem PMBM MTsN 1 Kota Pekanbaru*
