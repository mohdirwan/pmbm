# 🔐 PPDB Security - Quick Reference Card

## 🚨 CRITICAL ACTIONS BEFORE LAUNCH

### 1. Enable Production Mode
```php
// File: includes/config.php
define('PRODUCTION', true);

// File: process_register.php (line ~7)
initialize_security(true); // Change false to true
```

### 2. Enable HTTPS Redirect
```apache
# File: .htaccess (uncomment these lines)
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
```

### 3. Set File Permissions
```bash
chmod 755 /path/to/pmbm
chmod 644 *.php
chmod 600 includes/config.php
chmod 755 uploads logs
```

### 4. Run Security Test
```
http://yourdomain.com/pmbm/security_test.php
```
Fix all **FAIL** items!

### 5. Delete/Protect Test File
```bash
rm security_test.php
# Or add password protection
```

---

## 📊 Daily Monitoring

### Check Logs
```bash
tail -f logs/error.log
tail -f logs/security_*.log
```

### Watch for These Patterns
- ❌ `CSRF_VIOLATION`
- ❌ `INVALID_FILE_UPLOAD`
- ❌ `SQL Injection Attempt`
- ❌ `FILE_UPLOAD_FAILED`
- ❌ Multiple failed attempts from same IP

---

## 🛡️ Security Settings Summary

| Feature | Status | File |
|---------|--------|------|
| CSRF Protection | ✅ Active | config.php |
| Rate Limiting | ✅ Active (5/5min) | security.php |
| File Upload Validation | ✅ Enhanced | security.php |
| Session Security | ✅ Strict | security.php |
| Security Headers | ✅ All set | .htaccess |
| HTTPS Enforcement | ⏳ Enable manually | .htaccess |
| SQL Injection Protection | ✅ PDO + Validation | process_register.php |
| XSS Protection | ✅ Input sanitization | security.php |

---

## 🔧 Common Issues & Fixes

### Issue: Registration fails silently
**Check:** `logs/error.log` for details
**Fix:** Usually file permissions or upload directory

### Issue: "Too many requests" error
**Adjust:** `security.php` line ~135
```php
check_rate_limit_enhanced(10, 600); // 10 attempts in 10 minutes
```

### Issue: File upload rejected
**Check:** `logs/security_*.log` for validation errors
**Common causes:** Wrong file type, file too large, corrupted file

### Issue: HTTPS redirect loop
**Fix:** In `.htaccess`, add:
```apache
RewriteCond %{HTTP:X-Forwarded-Proto} !https
```

---

## 📞 Emergency Response

### Security Incident Detected

**Step 1: Identify**
```bash
grep "SECURITY" logs/security_*.log
```

**Step 2: Block IP** (if needed)
```apache
# Add to .htaccess
Order allow,deny
Deny from MALICIOUS_IP
Allow from all
```

**Step 3: Review & Fix**
- Check what vulnerability was exploited
- Apply patch immediately
- Review recent registrations

**Step 4: Restore** (if compromised)
```bash
# Restore from last known good backup
mysql -u user -p ppdb_db < backups/ppdb_backup_YYYYMMDD.sql
```

---

## ✅ Weekly Checklist

- [ ] Review security logs
- [ ] Check disk space (uploads/)
- [ ] Verify backups are working
- [ ] Test registration flow
- [ ] Review failed upload attempts

---

## 🎯 Security Hotline

**Technical Issues:** __________  
**Security Incidents:** __________  
**After Hours:** __________

---

*Keep this card accessible! Print or save in secure location.*

**Last Updated:** 07 Feb 2026
