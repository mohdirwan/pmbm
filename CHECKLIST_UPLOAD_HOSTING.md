# ✅ CHECKLIST UPLOAD KE HOSTING

## 📋 SEBELUM UPLOAD

### 1. Backup Database Localhost
- [ ] Buka phpMyAdmin localhost
- [ ] Pilih database `ppdb_mtsn1`
- [ ] Klik tab **Export**
- [ ] Pilih **Quick** export method
- [ ] Format: **SQL**
- [ ] Klik **Go** untuk download
- [ ] File tersimpan: `ppdb_mtsn1.sql`

### 2. Backup Files Localhost
- [ ] Copy seluruh folder `c:\xampp\htdocs\pmbm\`
- [ ] Zip file tersebut: `pmbm_backup.zip`
- [ ] Simpan di lokasi aman

---

## 🔧 KONFIGURASI

### 3. Edit File Config.php
- [ ] Buka file `config.hosting.php`
- [ ] Copy isinya
- [ ] Paste ke `includes/config.php`
- [ ] Ganti kredensial database:
  ```php
  define('DB_HOST', 'localhost');
  define('DB_USER', 'GANTI_INI');  // dari cPanel
  define('DB_PASS', 'GANTI_INI');  // dari cPanel
  define('DB_NAME', 'GANTI_INI');  // dari cPanel
  ```
- [ ] Save file

### 4. Cek BASE_URL
- [ ] Jika domain: `imsoftdev.my.id/pmbm/`
- [ ] Uncomment baris manual di config.php:
  ```php
  define('BASE_URL', 'https://imsoftdev.my.id/pmbm/');
  ```

---

## ☁️ UPLOAD KE HOSTING

### 5. Login cPanel
- [ ] Buka: `cpanel.hosting-anda.com`
- [ ] Login dengan username & password

### 6. Buat Database
#### A. Buat Database Baru
- [ ] Buka **MySQL Databases**
- [ ] Buat database baru:
  - Database name: `ppdb` (atau nama lain)
  - Klik **Create Database**
  - Catat nama lengkap: `smanpeka_ppdb`

#### B. Buat User Database
- [ ] Scroll ke **MySQL Users**
- [ ] Buat user baru:
  - Username: `pmbmuser`
  - Password: (generate strong password)
  - Klik **Create User**
  - Catat username lengkap: `smanpeka_pmbmuser`
  - **SIMPAN PASSWORD INI!**

#### C. Assign User ke Database
- [ ] Scroll ke **Add User To Database**
- [ ] Pilih User: `smanpeka_pmbmuser`
- [ ] Pilih Database: `smanpeka_ppdb`
- [ ] Klik **Add**
- [ ] Centang **ALL PRIVILEGES**
- [ ] Klik **Make Changes**

### 7. Import Database
- [ ] Buka **phpMyAdmin** dari cPanel
- [ ] Pilih database yang baru dibuat
- [ ] Klik tab **Import**
- [ ] Klik **Choose File**
- [ ] Pilih file `ppdb_mtsn1.sql`
- [ ] Scroll kebawah
- [ ] Klik **Go**
- [ ] Tunggu sampai selesai
- [ ] Lihat pesan sukses

### 8. Jalankan Migration SQL
- [ ] Di phpMyAdmin, tetap pilih database
- [ ] Klik tab **SQL**
- [ ] Copy isi file `hosting_migration.sql`
- [ ] Paste di SQL query box
- [ ] Klik **Go**
- [ ] Lihat hasil - harus sukses

### 9. Upload Files
#### A. Via File Manager (Recommended)
- [ ] Buka **File Manager** dari cPanel
- [ ] Navigate ke folder: `public_html/`
- [ ] Buat folder baru: `pmbm`
- [ ] Masuk ke folder `pmbm/`
- [ ] Klik **Upload**
- [ ] Drag & drop semua file/folder dari localhost
- [ ] Tunggu sampai upload selesai (100%)

#### B. Via FTP (Alternative)
- [ ] Download FileZilla
- [ ] Connect ke hosting (host, username, password dari cPanel)
- [ ] Navigate to: `/public_html/pmbm/`
- [ ] Upload semua files

### 10. Set Permissions
- [ ] Di File Manager, klik kanan folder `uploads/`
- [ ] Pilih **Change Permissions**
- [ ] Set ke: `755` (rwxr-xr-x)
- [ ] Centang **Recurse into subdirectories**
- [ ] Klik **Change Permissions**
- [ ] Ulangi untuk folder:
  - `uploads/panduan/`
  - `uploads/dokumen/`
  - `uploads/pakta/`
  - `includes/` (file config.php harus 644)

---

## ✅ TESTING

### 11. Test Website
- [ ] Buka browser
- [ ] Akses: `https://imsoftdev.my.id/pmbm/`
- [ ] Halaman homepage harus muncul
- [ ] CSS/JS harus load (tidak broken)
- [ ] Cek tombol Panduan & Brosur
- [ ] Semua link harus berfungsi

### 12. Test Admin Login
- [ ] Akses: `https://imsoftdev.my.id/pmbm/admin/`
- [ ] Login dengan username & password dari database
- [ ] Dashboard harus muncul
- [ ] **JIKA ERROR:** Lihat section Troubleshooting

### 13. Test Fitur Admin
- [ ] **Dashboard:**
  - [ ] Statistik muncul
  - [ ] Chart muncul
  - [ ] Tabel data muncul
  
- [ ] **Panduan & Brosur:**
  - [ ] List items muncul
  - [ ] Klik "Tambah Item" → Modal muncul
  - [ ] Upload file → Berhasil
  - [ ] Edit item → Berhasil
  - [ ] Hapus item → Muncul konfirmasi
  
- [ ] **Data Pendaftar:**
  - [ ] Tabel muncul
  - [ ] Pagination berfungsi (Previous/Next)
  - [ ] Filter berfungsi
  - [ ] Export Excel → Download file

### 14. Test Website Frontend
- [ ] Akses homepage
- [ ] Tombol "Panduan & Brosur" harus ada
- [ ] Klik tombol → File harus bisa di-download
- [ ] Form pendaftaran bisa diakses
- [ ] Login siswa berfungsi

---

## 🔍 TROUBLESHOOTING

### Error: "Call to undefined function"
**Solusi:**
1. Cek file `includes/config.php` sudah ter-upload
2. Cek path include di `admin/dashboard.php`:
   ```php
   require_once '../includes/config.php';  // harus ada ../
   ```
3. Cek permission file config.php → 644

### Error: "Access Denied"
**Solusi:**
1. Database credentials salah
2. Edit `includes/config.php`
3. Pastikan DB_USER, DB_PASS, DB_NAME sudah benar
4. Test koneksi database:
   ```php
   <?php
   require_once 'includes/config.php';
   echo "Koneksi berhasil!";
   ?>
   ```

### Error: "Table doesn't exist"
**Solusi:**
1. Database belum di-import
2. atau tabel `panduan_brosur` belum dibuat
3. Jalankan file `hosting_migration.sql` di phpMyAdmin

### Error: "File not found" / 404
**Solusi:**
1. BASE_URL salah
2. Edit `includes/config.php`
3. Set manual:
   ```php
   define('BASE_URL', 'https://imsoftdev.my.id/pmbm/');
   ```

### Error: "Permission denied" saat upload
**Solusi:**
1. Folder uploads tidak punya permission write
2. Di File Manager:
   - Klik kanan folder `uploads/`
   - Change Permissions → 755
   - Centang "Recurse into subdirectories"

### CSS/JS Tidak Load
**Solusi:**
1. BASE_URL salah
2. File assets tidak ter-upload
3. Cek di browser console (F12) untuk lihat error
4. Sesuaikan BASE_URL

---

## 🔒 KEAMANAN SETELAH LIVE

### 15. Matikan Error Display
- [ ] Edit `includes/config.php`
- [ ] Set:
  ```php
  ini_set('display_errors', 0);
  error_reporting(0);
  ```

### 16. Hapus File Debug
- [ ] Hapus: `phpinfo.php` (jika ada)
- [ ] Hapus: `migration_panduan_brosur.php`
- [ ] Hapus: `config.hosting.php`
- [ ] Hapus: `hosting_migration.sql`
- [ ] Hapus: `FIX_HOSTING.md`

### 17. Ganti Password Default
- [ ] Login admin
- [ ] Ganti password admin default
- [ ] Gunakan password yang kuat

### 18. Enable HTTPS
- [ ] Di cPanel → SSL/TLS Status
- [ ] Install SSL Certificate (biasanya auto via Let's Encrypt)
- [ ] Force HTTPS via .htaccess:
  ```apache
  RewriteEngine On
  RewriteCond %{HTTPS} off
  RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
  ```

### 19. Backup Hosting
- [ ] Backup database via phpMyAdmin → Export
- [ ] Backup files via File Manager → Compress → Download
- [ ] Simpan lokal
- [ ] Set reminder backup berkala (mingguan)

---

## 📝 CATATAN KREDENSIAL

**SIMPAN INI DI TEMPAT AMAN:**

```
=================================
HOSTING CREDENTIALS
=================================
Domain: imsoftdev.my.id
cPanel URL: cpanel.hosting.com
cPanel User: 
cPanel Pass: 

Database Host: localhost
Database Name: 
Database User: 
Database Pass: 

Admin URL: https://imsoftdev.my.id/pmbm/admin/
Admin Username: 
Admin Password: 

FTP Host: 
FTP User: 
FTP Pass: 
=================================
```

---

## ✅ CHECKLIST FINAL

### Semua Harus Centang:
- [ ] Database credentials sudah benar
- [ ] Database sudah di-import
- [ ] Tabel panduan_brosur sudah ada
- [ ] Files sudah ter-upload semua
- [ ] Permissions sudah di-set (755 untuk folder, 644 untuk file)
- [ ] Homepage bisa diakses
- [ ] Admin bisa login
- [ ] Dashboard tidak error
- [ ] Fitur panduan & brosur berfungsi
- [ ] Pagination berfungsi
- [ ] Upload file berfungsi
- [ ] Error display sudah dimatikan
- [ ] File debug sudah dihapus
- [ ] Password admin sudah diganti
- [ ] HTTPS sudah aktif
- [ ] Backup sudah dilakukan

---

## 🎉 JIKA SEMUA SUDAH CENTANG:

**SELAMAT! Website PMBM Anda sudah LIVE! 🚀**

Jangan lupa:
- Monitor secara berkala
- Backup rutin (1 minggu sekali)
- Update PHP jika ada versi baru
- Pantau error log

**Good luck! 💪**
