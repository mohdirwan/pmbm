# 🔧 FIX CSS TIDAK LOAD DI HOSTING

## ❌ **MASALAH:**
- Di localhost: Tombol Panduan & Brosur terlihat **glassmorphism/transparan yang bagus**
- Di hosting: Tombol **plain/broken** tanpa style

## 🎯 **PENYEBAB:**
CSS file tidak ter-load karena **BASE_URL salah** atau **file CSS tidak ter-upload**

---

## ✅ **SOLUSI CEPAT:**

### **STEP 1: Cek File CSS di Hosting**

1. Login cPanel
2. Buka **File Manager**
3. Navigate ke: `/public_html/pmbm/assets/css/`
4. **Pastikan file `style.css` ADA**
   - Jika TIDAK ada → Upload file `style.css` dari localhost

---

### **STEP 2: Fix BASE_URL di config.php**

Edit file `includes/config.php` di hosting:

**Cara 1: Manual (Paling Aman)**

Ganti baris 29-32 dengan:

```php
// BASE_URL - HARDCODED (untuk hosting)
define('BASE_URL', 'https://imsoftdev.my.id/pmbm/');
```

**Cara 2: Auto-detect (Jika sudah benar)**

Pastikan baris 29-32 seperti ini:

```php
// Auto-detect (default)
if (count($dir_parts) > 1 && $dir_parts[0] === 'pmbm') {
    define('BASE_URL', $protocol . "://" . $host . "/pmbm/");
} else {
    define('BASE_URL', $protocol . "://" . $host . $root_folder);
}
```

---

### **STEP 3: Test BASE_URL**

Buat file `test_base_url.php` di root folder `pmbm/`:

```php
<?php
require_once 'includes/config.php';

echo "<h2>Testing BASE_URL</h2>";
echo "<p><strong>BASE_URL:</strong> " . BASE_URL . "</p>";
echo "<p><strong>Expected:</strong> https://imsoftdev.my.id/pmbm/</p>";

echo "<h3>Test CSS Load:</h3>";
echo "<link href='" . BASE_URL . "assets/css/style.css' rel='stylesheet'>";
echo "<p>File CSS: <a href='" . BASE_URL . "assets/css/style.css' target='_blank'>Klik untuk test</a></p>";

echo "<style>
.test-btn {
    background: rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 100px;
    padding: 14px 28px;
    color: #ffffff !important;
    background-color: #0f5132;
}
</style>";

echo "<button class='test-btn'>Test Button Glassmorphism</button>";
?>
```

Upload file ini ke hosting, lalu akses:
```
https://imsoftdev.my.id/pmbm/test_base_url.php
```

**Hasilnya harus:**
- BASE_URL: `https://imsoftdev.my.id/pmbm/`
- Link CSS harus bisa diklik dan terbuka
- Test button harus terlihat dengan style

**❗ HAPUS FILE INI SETELAH TESTING!**

---

### **STEP 4: Cek di Browser Console**

1. Buka website: `https://imsoftdev.my.id/pmbm/`
2. Tekan **F12** (Developer Tools)
3. Klik tab **Console**
4. Lihat apakah ada error merah seperti:
   ```
   Failed to load resource: https://imsoftdev.my.id/pmbm/assets/css/style.css
   ```

Jika ada error ini → **File CSS tidak ketemu**

**Solusi:**
- Cek path file apakah benar
- Re-upload file `style.css`
- Cek permission file (harus 644)

---

### **STEP 5: Cek di Network Tab**

1. Masih di Developer Tools (F12)
2. Klik tab **Network**
3. Refresh halaman (Ctrl+F5)
4. Cari file `style.css`
5. Klik file tersebut
6. Lihat **Status Code**:
   - ✅ **200 OK** → File berhasil load
   - ❌ **404 Not Found** → File tidak ada
   - ❌ **500 Error** → Server error

---

## 🔧 **SOLUSI ALTERNATIF:**

### **Option 1: Inline di index.php (Quick Fix)**

Edit `index.php` di hosting, setelah line 120 <link rel="stylesheet" href="assets/css/style.css">:

```php
<!-- Custom CSS -->
<link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">

<!-- FALLBACK: Inline CSS jika file tidak load -->
<style>
.btn-glass {
    background: rgba(255, 255, 255, 0.12) !important;
    backdrop-filter: blur(12px) !important;
    -webkit-backdrop-filter: blur(12px) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    border-radius: 100px !important;
    padding: 14px 28px !important;
    font-weight: 600 !important;
    font-size: 0.95rem !important;
    color: #ffffff !important;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1) !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 12px !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
    text-decoration: none !important;
}

.btn-glass:hover {
    background: rgba(255, 255, 255, 0.2) !important;
    border-color: rgba(255, 255, 255, 0.4) !important;
    color: #ffffff !important;
    transform: translateY(-5px) !important;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2) !important;
}

.btn-glass i {
    font-size: 1.2rem !important;
    transition: transform 0.3s ease !important;
}

.btn-glass:hover i {
    transform: scale(1.2) !important;
}

.btn-glass-warning {
    border-left: 4px solid #ffc107 !important;
}

.btn-glass-warning i {
    color: #ffc107 !important;
}

.btn-glass-info {
    border-left: 4px solid #0dcaf0 !important;
}

.btn-glass-info i {
    color: #0dcaf0 !important;
}

.btn-glass-success {
    border-left: 4px solid #20c997 !important;
}

.btn-glass-success i {
    color: #20c997 !important;
}
</style>
```

**Ini akan fix tombol meskipun file CSS tidak load**

---

### **Option 2: Use CDN (If file upload issue)**

Jika masalah terus berlanjut, upload file `style.css` ke layanan CDN gratis seperti:
- GitHub Pages
- jsDelivr
- atau hosting lain

Lalu ganti link CSS di `index.php`:
```html
<link href="URL_CDN_ANDA/style.css" rel="stylesheet">
```

---

## 🎯 **CHECKLIST DEBUGGING:**

- [ ] File `style.css` sudah di-upload ke `/public_html/pmbm/assets/css/`
- [ ] Permission file `style.css` = 644
- [ ] BASE_URL di `config.php` sudah benar
- [ ] Test `test_base_url.php` → BASE_URL sesuai
- [ ] Browser console (F12) → Tidak ada error loading CSS
- [ ] Network tab → style.css status 200 OK
- [ ] File CSS bisa diakses langsung: `https://imsoftdev.my.id/pmbm/assets/css/style.css`

---

## 📸 **CARA TEST LANGSUNG:**

**Test 1: Akses CSS Langsung**
```
https://imsoftdev.my.id/pmbm/assets/css/style.css
```
- Jika terbuka dan muncul kode CSS → ✅ File ada
- Jika 404 Not Found → ❌ File tidak ada

**Test 2: View Page Source**
1. Buka `https://imsoftdev.my.id/pmbm/`
2. Klik kanan → View Page Source
3. Cari baris: `<link href="...assets/css/style.css"`
4. Copy URL-nya
5. Paste di browser baru
6. Harus terbuka file CSS

---

## 🚀 **SETELAH FIX:**

Tombol harus terlihat seperti **Gambar 1** (localhost):
- Transparan glassmorphism
- Border putih tipis
- Blur effect di background
- Icon berwarna (kuning/biru/hijau)
- Hover effect: naik ke atas + shadow lebih besar

---

**Coba langkah-langkah di atas, lalu screenshot hasilnya!** 😊

Jika masih bermasalah, kirim:
1. Screenshot Developer Tools → Console (F12)
2. Screenshot Developer Tools → Network tab
3. Screenshot hasil akses langsung: `https://imsoftdev.my.id/pmbm/assets/css/style.css`
