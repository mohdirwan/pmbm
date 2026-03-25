# 🚀 PPDB System - Production Deployment Guide

## 📋 Pre-Deployment Checklist

### 1. **Environment Configuration**
- [ ] Create `.env` file (copy from `.env.example`)
- [ ] Set database credentials
- [ ] Set production mode: `PRODUCTION=true`
- [ ] Configure mail settings
- [ ] Set BASE_URL to production domain

### 2. **Database Setup**
- [ ] Run all migrations
- [ ] Create database backups
- [ ] Set proper database user permissions (no DROP, CREATE rights)
- [ ] Enable database query logging
- [ ] Configure automated backups

### 3. **File Permissions**
```bash
# Set correct permissions
chmod 755 /path/to/pmbm
chmod 644 /path/to/pmbm/*.php
chmod 644 /path/to/pmbm/.htaccess
chmod 600 /path/to/pmbm/includes/config.php
chmod 755 /path/to/pmbm/uploads
chmod 644 /path/to/pmbm/uploads/* (files)
chmod 755 /path/to/pmbm/logs
chmod 644 /path/to/pmbm/logs/*.log
```

### 4. **Security Configuration**
- [ ] Enable HTTPS (SSL Certificate)
- [ ] Update `.htaccess` - uncomment HTTPS redirect
- [ ] Update `initialize_security(true)` in process_register.php
- [ ] Set secure session cookies
- [ ] Configure security headers
- [ ] Enable rate limiting
- [ ] Set up firewall rules

### 5. **Create Required Directories**
```bash
mkdir -p logs
mkdir -p uploads
mkdir -p backups
```

### 6. **Security Headers Verification**
Test dengan: https://securityheaders.com
Expected headers:
- X-Frame-Options: SAMEORIGIN
- X-Content-Type-Options: nosniff
- X-XSS-Protection: 1; mode=block
- Strict-Transport-Security (HTTPS)
- Content-Security-Policy

### 7. **PHP Configuration (php.ini)**
```ini
; Display Errors
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /path/to/logs/php_errors.log

; File Uploads
file_uploads = On
upload_max_filesize = 2M
post_max_size = 8M
max_file_uploads = 20

; Session Security
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_only_cookies = 1
session.cookie_samesite = Strict

; Disable dangerous functions
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source

; Other Security
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off
```

### 8. **Testing Before Launch**
- [ ] Test registration flow
- [ ] Test file uploads
- [ ] Test CSRF protection
- [ ] Test rate limiting
- [ ] Test SQL injection (safe testing)
- [ ] Test XSS protection
- [ ] Test error handling
- [ ] Test mobile responsiveness
- [ ] Test different browsers

### 9. **Monitoring Setup**
- [ ] Set up error monitoring (email alerts)
- [ ] Configure server monitoring
- [ ] Set up uptime monitoring
- [ ] Enable security event logging
- [ ] Configure backup alerts

### 10. **Backup Strategy**
- [ ] Automated daily database backups
- [ ] Weekly full system backups
- [ ] Off-site backup storage
- [ ] Test backup restoration process

---

## 🔧 Deployment Steps

### Step 1: Upload Files
```bash
# Using FTP/SFTP or git
rsync -avz --exclude='.git' . user@server:/path/to/pmbm/
```

### Step 2: Set Permissions
```bash
ssh user@server
cd /path/to/pmbm
chmod -R 755 .
chmod 644 *.php
chmod 600 includes/config.php
chmod -R 755 uploads logs
```

### Step 3: Database Migration
```bash
php run_migration_add_documents.php
# Verify tables created successfully
```

### Step 4: Test Security
```bash
# Test HTTPS redirect
curl -I http://yourdomain.com/pmbm/
# Should return 301 redirect to https://

# Test security headers
curl -I https://yourdomain.com/pmbm/
```

### Step 5: Final Checks
- [ ] Access register.php
- [ ] Test complete registration
- [ ] Check logs directory
- [ ] Verify file uploads work
- [ ] Test admin login

### Step 6: Go Live!
- [ ] Update DNS if needed
- [ ] Monitor logs for first few hours
- [ ] Be ready for quick rollback if issues occur

---

## 🔒 Security Hardening

### Enable All Security Features

**1. Update process_register.php:**
```php
// Change from:
initialize_security(false);

// To:
initialize_security(true); // Enables HTTPS enforcement
```

**2. Update .htaccess:**
Uncomment these lines:
```apache
# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# HSTS Header
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
```

**3. Set Production Mode in config.php:**
```php
define('PRODUCTION', true);
```

### IP Whitelisting for Admin (Optional)

Edit `.htaccess`:
```apache
<Directory "admin">
    Order deny,allow
    Deny from all
    Allow from 127.0.0.1
    Allow from YOUR_OFFICE_IP
</Directory>
```

---

## 📊 Monitoring & Maintenance

### Daily Checks
- [ ] Check error logs: `logs/error.log`
- [ ] Check security logs: `logs/security_*.log`
- [ ] Check disk space (uploads folder)
- [ ] Monitor server load

### Weekly Tasks
- [ ] Review security event logs
- [ ] Check for failed login attempts
- [ ] Verify backups are working
- [ ] Update dependencies if any

### Monthly Tasks
- [ ] Security audit
- [ ] Performance optimization
- [ ] Database cleanup (if needed)
- [ ] Review and rotate logs

---

## 🆘 Troubleshooting

### Issue: HTTPS Redirect Loop
**Solution:**
```apache
# In .htaccess, change to:
RewriteCond %{HTTPS} !=on
RewriteCond %{HTTP:X-Forwarded-Proto} !https
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### Issue: File Upload Fails
**Check:**
1. Directory permissions (755 for uploads/)
2. PHP upload_max_filesize setting
3. Disk space available
4. Security logs for validation errors

### Issue: Session Not Working
**Check:**
1. Session directory writable
2. HTTPS enabled if using secure cookies
3. Session timeout settings
4. Browser cookie settings

### Issue: Rate Limit Too Strict
**Adjust in security.php:**
```php
check_rate_limit_enhanced(10, 600); // 10 attempts in 10 minutes
```

---

## 📞 Support Contacts

**Technical Issues:**
- Server Admin: [email/phone]
- Database Admin: [email/phone]
- Security Team: [email/phone]

**Emergency Rollback:**
```bash
# Keep backup of previous version
cd /path/to/
mv pmbm pmbm_backup
mv pmbm_previous pmbm
# Restart web server
systemctl restart apache2
```

---

## ✅ Post-Launch Checklist

### First 24 Hours
- [ ] Monitor error logs continuously
- [ ] Check server performance
- [ ] Verify no security alerts
- [ ] Track registration success rate
- [ ] Be ready to assist users

### First Week
- [ ] Collect user feedback
- [ ] Fix any minor issues
- [ ] Optimize performance if needed
- [ ] Review security logs daily

### First Month
- [ ] Conduct security audit
- [ ] Review backup procedures
- [ ] Analyze usage patterns
- [ ] Plan improvements

---

## 🎯 Success Metrics

Track these KPIs:
- [ ] Registration completion rate
- [ ] Average registration time
- [ ] File upload success rate
- [ ] Server uptime (target: 99.9%)
- [ ] Security incidents (target: 0)

---

**Deployment Date:** _____________

**Deployed By:** _____________

**Verified By:** _____________

**Status:** ⏳ READY FOR PRODUCTION

---

*Document Version: 1.0*  
*Last Updated: 07 Februari 2026*
