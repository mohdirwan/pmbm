# 🔧 PANDUAN FIX ERROR DI HOSTING

## ❌ Error yang Muncul:
```
Fatal error: Uncaught Error: Call to undefined function...
/home/smanpeka/imsoftdev.my.id/pmbm/admin/dashboard.php on line 136
```

---

## 🔍 PENYEBAB UMUM:

### 1. **Database Credentials Salah**
File `includes/config.php` menggunakan kredensial localhost:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ppdb_mtsn1');
```

**❗ DI HOSTING HARUS DIGANTI!**

---

## ✅ LANGKAH PERBAIKAN:

### **STEP 1: Update Database Credentials**

Edit file: `includes/config.php`

Ganti baris 6-9 dengan kredensial hosting Anda:

```php
// Database Configuration - HOSTING
define('DB_HOST', 'localhost');  // biasanya 'localhost' atau IP server
define('DB_USER', 'smanpeka_pmbm');  // username database hosting
define('DB_PASS', 'PASSWORD_ANDA');  // password database hosting
define('DB_NAME', 'smanpeka_ppdb');  // nama database di hosting
```

**Cara cek kredensial:**
1. Login ke cPanel hosting
2. Buka **MySQL Databases**
3. Lihat nama database, username, dan password

---

### **STEP 2: Import Database**

**A. Export dari Localhost:**
```bash
1. Buka phpMyAdmin localhost
2. Pilih database 'ppdb_mtsn1'
3. Klik tab "Export"
4. Pilih "Quick" atau "Custom"
5. Klik "Go" untuk download file .sql
```

**B. Import ke Hosting:**
```bash
1. Login ke cPanel hosting
2. Buka phpMyAdmin
3. Pilih database Anda (misal: smanpeka_ppdb)
4. Klik tab "Import"
5. Choose file → Pilih file .sql dari localhost
6. Klik "Go"
7. Tunggu sampai selesai
```

**⚠️ PENTING:** Pastikan tabel `panduan_brosur` ada di database hosting!

---

### **STEP 3: Jalankan Migrasi (Jika Tabel Belum Ada)**

Akses file ini di browser hosting:
```
https://imsoftdev.my.id/pmbm/migration_panduan_brosur.php
```

Jika muncul pesan sukses, berarti tabel `panduan_brosur` sudah dibuat.

**Atau jalankan SQL manual di phpMyAdmin:**

```sql
CREATE TABLE IF NOT EXISTS panduan_brosur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    tipe ENUM('file', 'video') DEFAULT 'file',
    file_path VARCHAR(500),
    video_url VARCHAR(500),
    icon_class VARCHAR(100) DEFAULT 'fa-book-open',
    color_class VARCHAR(50) DEFAULT 'primary',
    urutan INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert data default
INSERT INTO panduan_brosur (judul, tipe, icon_class, color_class, urutan) VALUES
('Petunjuk Teknis PMBM', 'file', 'fa-book-open', 'primary', 1),
('Brosur Pendaftaran', 'file', 'fa-file-pdf', 'success', 2);
```

---

### **STEP 4: Cek Permission Folder**

Pastikan folder berikut memiliki permission 755 atau 777:

```bash
chmod 755 /home/smanpeka/imsoftdev.my.id/pmbm/uploads
chmod 755 /home/smanpeka/imsoftdev.my.id/pmbm/uploads/panduan
chmod 755 /home/smanpeka/imsoftdev.my.id/pmbm/includes
```

Di cPanel **File Manager**:
1. Klik kanan folder `uploads`
2. Pilih **Change Permissions**
3. Set ke `755` (rwxr-xr-x)
4. Centang **Recurse into subdirectories**
5. Apply

---

### **STEP 5: Cek PHP Version**

Sistem ini membutuhkan **PHP 7.4 atau lebih tinggi**.

**Cara cek di hosting:**
1. Login cPanel
2. Buka **Select PHP Version** atau **MultiPHP Manager**
3. Pilih PHP **7.4** atau **8.0** atau **8.1**
4. Save

**Cara cek via file:**
Buat file `phpinfo.php`:
```php
<?php phpinfo(); ?>
```
Akses: `https://imsoftdev.my.id/pmbm/phpinfo.php`

**⚠️ HAPUS FILE INI SETELAH CEK!**

---

### **STEP 6: Fix BASE_URL (Jika Perlu)**

Jika website tidak loading CSS/JS dengan benar, edit `config.php` line 29-32:

**Cara Otomatis (Recommended):**
Biarkan seperti ini (sudah ada di config.php):
```php
if (count($dir_parts) > 1 && $dir_parts[0] === 'pmbm') {
    define('BASE_URL', $protocol . "://" . $host . "/pmbm/");
} else {
    define('BASE_URL', $protocol . "://" . $host . $root_folder);
}
```

**Cara Manual (Jika Auto Gagal):**
Ganti dengan hardcode:
```php
// For hosting
define('BASE_URL', 'https://imsoftdev.my.id/pmbm/');
```

---

### **STEP 7: Test Error Reporting**

Edit `admin/dashboard.php` baris 2-4:

**Sementara aktifkan error:**
```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

Buka dashboard, lihat error detail.

**Setelah fix, MATIKAN error display:**
```php
ini_set('display_errors', 0);  // ubah jadi 0
error_reporting(0);  // ubah jadi 0
```

---

## 🎯 CHECKLIST DEBUGGING:

### ✅ Cek Satu-Satu:

- [ ] **Database credentials sudah benar?**
  - Username
  - Password  
  - Database name
  - Host (biasanya 'localhost')

- [ ] **Database sudah di-import?**
  - Semua tabel ada
  - Tabel `panduan_brosur` ada
  - Data default sudah ada

- [ ] **File config.php ter-upload?**
  - Path: `/home/smanpeka/imsoftdev.my.id/pmbm/includes/config.php`
  - File exists dan readable

- [ ] **Folder permission sudah benar?**
  - `uploads/` → 755
  - `uploads/panduan/` → 755
  - `includes/` → 755

- [ ] **PHP version cukup?**
  - Minimal PHP 7.4
  - Recommended PHP 8.0+

- [ ] **Extension PHP aktif?**
  - PDO
  - PDO_MySQL
  - mbstring
  - json

---

## 🔧 SOLUSI CEPAT:

### **Jika Error: "Call to undefined function"**

1. **Cek apakah file `config.php` ter-upload**
   ```bash
   File harus ada di:
   /home/smanpeka/imsoftdev.my.id/pmbm/includes/config.php
   ```

2. **Cek require path**
   ```php
   // Di dashboard.php harus:
   require_once '../includes/config.php';  // benar
   
   // BUKAN:
   require_once 'includes/config.php';  // salah jika di folder admin
   ```

3. **Cek case sensitivity**
   ```bash
   Linux hosting: CASE SENSITIVE
   Windows localhost: case INSENSITIVE
   
   Pastikan nama file PERSIS:
   - config.php (BUKAN Config.php)
   - auth_check.php (BUKAN Auth_Check.php)
   ```

---

### **Jika Error: "Table doesn't exist"**

Jalankan SQL ini di phpMyAdmin hosting:

```sql
-- Cek tabel apa saja yang ada
SHOW TABLES;

-- Jika tabel panduan_brosur tidak ada, buat:
CREATE TABLE IF NOT EXISTS panduan_brosur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    tipe ENUM('file', 'video') DEFAULT 'file',
    file_path VARCHAR(500),
    video_url VARCHAR(500),
    icon_class VARCHAR(100) DEFAULT 'fa-book-open',
    color_class VARCHAR(50) DEFAULT 'primary',
    urutan INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

### **Jika Error: "Access denied"**

Database credentials salah! Cek lagi:

1. Login cPanel
2. **MySQL Databases**
3. Scroll ke **Current Databases**
4. Lihat nama database
5. Scroll ke **Current Users**
6. Pastikan user sudah di-assign ke database
7. Klik **Add User To Database** jika belum

---

## 📞 BANTUAN LEBIH LANJUT:

### **Debug Mode:**

Tambahkan di `config.php` baris ke-4 (temporary):

```php
<?php
// Debug mode - HAPUS SETELAH FIX!
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set Timezone to Asia/Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');
```

### **Lihat Error Log:**

Di cPanel → **Error Log** atau file:
```
/home/smanpeka/logs/error_log
```

---

## ✅ SETELAH BERHASIL:

1. **Matikan error display:**
   ```php
   ini_set('display_errors', 0);
   error_reporting(0);
   ```

2. **Hapus file debug:**
   - `phpinfo.php` (jika ada)
   - `migration_panduan_brosur.php` (optional, bisa dihapus)

3. **Test website:**
   - Login admin
   - Cek dashboard
   - Test fitur panduan & brosur
   - Test pagination

4. **Backup database:**
   - Export database hosting
   - Simpan backup lokal

---

## 📝 CATATAN PENTING:

⚠️ **JANGAN LUPA:**
- Ganti password database yang kuat
- Hapus file migration setelah dijalankan
- Matikan display errors di production
- Backup database secara berkala
- Set permission folder dengan benar (jangan 777 kecuali benar-benar perlu)

🔒 **KEAMANAN:**
- Jangan expose kredensial database
- Gunakan .htaccess untuk protect folder includes
- Enable HTTPS jika tersedia
- Update PHP ke versi terbaru

---

**Good luck! 🚀**

Jika masih error, kirim screenshot error lengkap untuk analisa lebih detail.
