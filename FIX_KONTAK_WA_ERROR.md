# FIX: Unknown column 'kontak_wa' Error

## Problem:
```
Error: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'kontak_wa' in 'field list'
```

Kolom `kontak_wa` dan `nama_kontak_wa` belum ada di tabel `pendaftar`.

## Solution:
Jalankan migration script untuk menambahkan kolom yang diperlukan.

## CARA 1: Via Browser (RECOMMENDED)

```
http://localhost/pmbm/run_migration_kontak_wa.php
```

**Expected Output:**
```
=== Migration: Add Kontak WA Fields ===

Executing migration...
  ✓ Executed: ALTER TABLE pendaftar ADD COLUMN kontak_wa...
  ✓ Executed: ALTER TABLE pendaftar ADD COLUMN nama_kontak_wa...

✅ Verified columns:
  - kontak_wa: varchar(15) (Nomor WhatsApp yang bisa dihubungi)
  - nama_kontak_wa: varchar(100) (Nama pemilik nomor WhatsApp)

✅ Migration completed successfully!

=== Migration Complete ===
```

## CARA 2: Via Command Line

```bash
cd C:\xampp\htdocs\pmbm
php run_migration_kontak_wa.php
```

## CARA 3: Manual SQL (Jika cara 1 & 2 gagal)

Buka phpMyAdmin:
```
http://localhost/phpmyadmin
```

Pilih database: `ppdb` (atau database PPDB Anda)

Klik tab "SQL" dan jalankan query ini:

```sql
-- Add kontak_wa column
ALTER TABLE pendaftar 
ADD COLUMN kontak_wa VARCHAR(15) DEFAULT NULL 
COMMENT 'Nomor WhatsApp yang bisa dihubungi'
AFTER no_hp;

-- Add nama_kontak_wa column
ALTER TABLE pendaftar 
ADD COLUMN nama_kontak_wa VARCHAR(100) DEFAULT NULL 
COMMENT 'Nama pemilik nomor WhatsApp'
AFTER kontak_wa;

-- Verify columns
SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_COMMENT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'pendaftar' 
  AND COLUMN_NAME IN ('kontak_wa', 'nama_kontak_wa');
```

## Testing After Migration:

1. Refresh halaman register.php
2. Isi form sampai selesai
3. Submit form
4. Seharusnya tidak ada error lagi

## What These Columns Do:

**kontak_wa** (VARCHAR 15)
- Menyimpan nomor WhatsApp yang bisa dihubungi
- Format: 081234567890
- Digunakan untuk kontak darurat/alternatif

**nama_kontak_wa** (VARCHAR 100)
- Menyimpan nama pemilik nomor WA
- Ex: "Bapak Ahmad (Ayah)", "Ibu Siti (Ibu)"
- Digunakan untuk identifikasi kontak

## Troubleshooting:

**Q: Migration error "Duplicate column"?**
A: Kolom sudah ada. Tidak perlu migration.

**Q: Migration error "Table doesn't exist"?**
A: Pastikan database name benar di config.php

**Q: Masih error setelah migration?**
A: Clear browser cache (CTRL+SHIFT+R) dan coba lagi

---

**ACTION REQUIRED:**
👉 Jalankan: http://localhost/pmbm/run_migration_kontak_wa.php
👉 Tunggu sampai muncul "Migration completed successfully!"
👉 Test form register lagi
