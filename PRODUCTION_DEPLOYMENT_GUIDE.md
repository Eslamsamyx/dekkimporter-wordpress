# DekkImporter - Production Deployment Guide

**Plugin Version:** 1.3
**Package:** dekkimporter-v1.3-production.zip
**Size:** ~15 KB (compressed) / ~46 KB (uncompressed)
**Date:** October 2, 2025

---

## 📦 What You Have

### Production-Ready Package Location
```
/Users/eslamsamy/projects/wordpress-local/dekkimporter-v1.3-production.zip
```

### Folder Structure
```
dekkimporter/
├── dekkimporter.php                    # Main plugin file
└── includes/                            # Core plugin classes
    ├── class-admin.php                  # Admin settings (2.8 KB)
    ├── class-cron.php                   # Daily sync + email reports (5.4 KB)
    ├── class-data-source.php            # API integration (6.5 KB)
    ├── class-helpers.php                # Utility functions (651 bytes)
    ├── class-image-handler.php          # Image uploads (902 bytes)
    ├── class-logger.php                 # Logging system (968 bytes)
    ├── class-product-creator.php        # Product creation (1.4 KB)
    ├── class-product-updater.php        # Product updates (1.4 KB)
    └── class-sync-manager.php           # Sync orchestration (15 KB)
```

**Total Files:** 10 files (no test files included)
**Total Size:** 46 KB uncompressed

---

## 🚀 Deployment Methods

### Method 1: Upload via WordPress Admin (Easiest)

1. **Download the zip file to your local machine:**
   ```bash
   # The file is at:
   /Users/eslamsamy/projects/wordpress-local/dekkimporter-v1.3-production.zip
   ```

2. **In WordPress Admin:**
   - Go to **Plugins → Add New**
   - Click **Upload Plugin**
   - Choose `dekkimporter-v1.3-production.zip`
   - Click **Install Now**
   - Click **Activate Plugin**

3. **Done!** ✅

---

### Method 2: FTP/SFTP Upload

1. **Extract the zip file locally**

2. **Upload the `dekkimporter-production` folder to:**
   ```
   /wp-content/plugins/
   ```

3. **Final path should be:**
   ```
   /wp-content/plugins/dekkimporter-production/dekkimporter.php
   ```

4. **In WordPress Admin:**
   - Go to **Plugins**
   - Find **DekkImporter**
   - Click **Activate**

---

### Method 3: SSH/Command Line

```bash
# SSH into your production server
ssh user@yourserver.com

# Navigate to WordPress plugins directory
cd /path/to/wordpress/wp-content/plugins/

# Upload the zip file (using scp from your machine)
# From your local machine:
scp /Users/eslamsamy/projects/wordpress-local/dekkimporter-v1.3-production.zip user@yourserver.com:/tmp/

# Back on server, extract it
unzip /tmp/dekkimporter-v1.3-production.zip
mv dekkimporter-production dekkimporter

# Set proper permissions
chown -R www-data:www-data dekkimporter
chmod -R 755 dekkimporter

# Activate via WP-CLI (if available)
wp plugin activate dekkimporter
```

---

## ⚙️ Configuration Steps (After Installation)

### 1. Navigate to Settings
WordPress Admin → **DekkImporter**

### 2. Configure Email Settings (Required)
```
BK Supplier Email:     supplier-bk@example.com
BM Supplier Email:     supplier-bm@example.com
CC Notification Email: admin@yoursite.com (optional)
```

### 3. Configure API Endpoints (Required for Sync)
You'll need to add these via WordPress options or code:

**Option A: Add to `wp-config.php` (recommended):**
```php
define('DEKKIMPORTER_BK_API_URL', 'https://api.bk-supplier.com/products');
define('DEKKIMPORTER_BM_API_URL', 'https://api.bm-supplier.com/products');
```

**Option B: Add to database:**
```sql
INSERT INTO wp_options (option_name, option_value, autoload) VALUES
('dekkimporter_options', 'a:5:{s:20:"dekkimporter_bk_email";s:25:"supplier-bk@example.com";s:20:"dekkimporter_bm_email";s:25:"supplier-bm@example.com";s:39:"dekkimporter_field_notification_email";s:17:"admin@example.com";s:26:"dekkimporter_bk_api_url";s:38:"https://api.bk-supplier.com/products";s:26:"dekkimporter_bm_api_url";s:38:"https://api.bm-supplier.com/products";}', 'yes');
```

**Option C: Add via PHP in theme functions.php (temporary):**
```php
update_option('dekkimporter_options', [
    'dekkimporter_bk_email' => 'supplier-bk@example.com',
    'dekkimporter_bm_email' => 'supplier-bm@example.com',
    'dekkimporter_field_notification_email' => 'admin@example.com',
    'dekkimporter_bk_api_url' => 'https://api.bk-supplier.com/products',
    'dekkimporter_bm_api_url' => 'https://api.bm-supplier.com/products',
    'handle_obsolete' => true,
    'sync_batch_size' => 50,
    'obsolete_action' => 'draft', // or 'delete' or 'out_of_stock'
    'sync_notification_email' => 'admin@example.com',
]);
```

### 4. Configure SMTP (Required for Emails)
Install an SMTP plugin like:
- **WP Mail SMTP** (recommended)
- **Easy WP SMTP**
- **Post SMTP**

Without SMTP, emails won't send!

### 5. Verify Cron is Working
```bash
# Via WP-CLI
wp cron event list | grep dekkimporter

# Expected output:
# dekkimporter_sync_products    <next-run-time>    daily
```

---

## ✅ Post-Deployment Checklist

### Immediate (First Hour)
- [ ] Plugin activated successfully
- [ ] No PHP errors in WordPress debug log
- [ ] Settings page accessible (DekkImporter menu appears)
- [ ] Email settings configured
- [ ] SMTP plugin installed and configured

### First Day
- [ ] API endpoints configured
- [ ] Test order processed successfully
- [ ] Supplier emails received (check spam folder)
- [ ] Logs directory created: `/wp-content/uploads/dekkimporter-logs/`
- [ ] First log file created

### First Week
- [ ] Daily sync running (check cron)
- [ ] Sync email reports received
- [ ] Check logs for errors
- [ ] Verify products syncing correctly
- [ ] Monitor obsolete product detection

---

## 🔍 Verification Commands

### Check Plugin Files
```bash
ls -la /wp-content/plugins/dekkimporter/
# Should show: dekkimporter.php and includes/ folder
```

### Check Logs
```bash
tail -f /wp-content/uploads/dekkimporter-logs/dekkimporter-$(date +%Y-%m-%d).log
```

### Check Cron Schedule
```bash
wp cron event list --allow-root | grep dekkimporter
```

### Test Sync Manually
```bash
wp cron event run dekkimporter_sync_products --allow-root
```

---

## 📧 Email Configuration Examples

### Using WP Mail SMTP with Gmail

1. Install **WP Mail SMTP** plugin
2. Configure:
   - **From Email:** noreply@yoursite.com
   - **From Name:** Your Site Name
   - **Mailer:** Gmail
   - **Client ID & Secret:** (from Google Cloud Console)

### Using SendGrid

1. Install **WP Mail SMTP** plugin
2. Configure:
   - **Mailer:** SendGrid
   - **API Key:** Your SendGrid API key

### Using SMTP Credentials

1. Install **WP Mail SMTP** plugin
2. Configure:
   - **Mailer:** Other SMTP
   - **SMTP Host:** smtp.yourhost.com
   - **SMTP Port:** 587 (or 465 for SSL)
   - **Encryption:** TLS (or SSL)
   - **Username:** Your SMTP username
   - **Password:** Your SMTP password

---

## 🛡️ Security Recommendations

### 1. File Permissions
```bash
# Set proper permissions
chown -R www-data:www-data /wp-content/plugins/dekkimporter
chmod 755 /wp-content/plugins/dekkimporter
chmod 644 /wp-content/plugins/dekkimporter/*.php
chmod 755 /wp-content/plugins/dekkimporter/includes
chmod 644 /wp-content/plugins/dekkimporter/includes/*.php
```

### 2. Log Directory
```bash
# Logs will be created at:
/wp-content/uploads/dekkimporter-logs/

# Ensure directory is writable
chmod 755 /wp-content/uploads/dekkimporter-logs/
```

### 3. API Keys
- Never commit API keys to version control
- Use environment variables or wp-config.php
- Rotate API keys regularly

---

## 📊 Monitoring & Maintenance

### Daily
- Check sync email reports
- Verify no errors in logs

### Weekly
- Review obsolete products (if set to 'draft')
- Check sync performance metrics
- Verify product data accuracy

### Monthly
- Review log file sizes (consider rotation)
- Check database growth
- Update API endpoints if changed

---

## 🔧 Troubleshooting

### Emails Not Sending

**Check:**
1. SMTP plugin installed and configured?
2. Test email from WP Mail SMTP settings
3. Check spam folder
4. Verify supplier emails in settings

**Fix:**
```bash
# Check WP mail errors
tail -f /wp-content/uploads/dekkimporter-logs/dekkimporter-*.log | grep "Mail Error"
```

### Cron Not Running

**Check:**
```bash
wp cron event list | grep dekkimporter
```

**Fix:**
```bash
# Manually trigger
wp cron event run dekkimporter_sync_products --allow-root

# Or deactivate/reactivate plugin to reset
wp plugin deactivate dekkimporter --allow-root
wp plugin activate dekkimporter --allow-root
```

### API Connection Issues

**Check logs:**
```bash
grep "API Error" /wp-content/uploads/dekkimporter-logs/dekkimporter-*.log
```

**Common issues:**
- Invalid API URL
- API credentials missing
- Firewall blocking outgoing requests
- API rate limiting

### Products Not Syncing

**Check:**
1. API endpoints configured correctly?
2. API returning valid JSON?
3. Products have required fields (SKU, name)?
4. Check logs for specific errors

**Debug:**
```bash
# Enable WordPress debug mode in wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

# Check debug.log
tail -f /wp-content/debug.log
```

---

## 🔄 Updating the Plugin

### When You Need to Update

1. **Download new version** (if provided)
2. **Deactivate plugin** in WordPress Admin
3. **Delete old plugin folder** (backup first!)
4. **Upload new version** (via any method above)
5. **Activate plugin**

### Backup Before Updating
```bash
# Backup plugin folder
cd /wp-content/plugins/
tar -czf dekkimporter-backup-$(date +%Y%m%d).tar.gz dekkimporter/

# Backup database options
wp option get dekkimporter_options > dekkimporter-options-backup.json
```

---

## 📋 Required Dependencies

### WordPress
- **Minimum Version:** 5.2
- **Recommended:** 6.0+

### PHP
- **Minimum Version:** 7.2
- **Recommended:** 8.0+
- **Required Extensions:**
  - curl (for API requests)
  - json (for parsing)
  - mbstring (for strings)

### WooCommerce
- **Required:** Yes
- **Minimum Version:** 4.0+
- **Recommended:** 8.0+

### Server Requirements
- PHP memory_limit: 256M+ recommended
- max_execution_time: 300+ recommended (for large syncs)
- Outgoing HTTP/HTTPS allowed (for API requests)

---

## 📝 Configuration Summary

### Minimum Required Settings
```php
[
    'dekkimporter_bk_email' => 'supplier-bk@example.com',
    'dekkimporter_bm_email' => 'supplier-bm@example.com',
]
```

### Recommended Settings
```php
[
    // Email Settings
    'dekkimporter_bk_email' => 'supplier-bk@example.com',
    'dekkimporter_bm_email' => 'supplier-bm@example.com',
    'dekkimporter_field_notification_email' => 'admin@example.com',

    // API Settings
    'dekkimporter_bk_api_url' => 'https://api.bk-supplier.com/products',
    'dekkimporter_bm_api_url' => 'https://api.bm-supplier.com/products',

    // Sync Settings
    'handle_obsolete' => true,
    'sync_batch_size' => 50,
    'obsolete_action' => 'draft', // draft|delete|out_of_stock
    'sync_notification_email' => 'admin@example.com',
]
```

---

## 🎯 Quick Start Deployment

**5-Minute Setup:**

1. Upload `dekkimporter-v1.3-production.zip` via WordPress Admin
2. Activate plugin
3. Go to **DekkImporter → Settings**
4. Add BK and BM supplier emails
5. Install SMTP plugin
6. Done! Plugin will sync daily automatically

**Full Setup (with API sync):**

1. Follow 5-minute setup above
2. Configure API endpoints (wp-config.php or options)
3. Test manual sync: `wp cron event run dekkimporter_sync_products`
4. Monitor first sync email report
5. Review logs for any issues

---

## 📞 Support & Documentation

**Documentation Files:**
- `DEKKIMPORTER_TEST_REPORT.md` - Original test report
- `SYNC_STALENESS_REPORT.md` - Sync system documentation
- `SYNC_FEATURES_SUMMARY.txt` - Quick feature reference
- `SYNC_ARCHITECTURE.txt` - Technical architecture
- `QUICK_REFERENCE.md` - Day-to-day usage guide

**Logs Location:**
- `/wp-content/uploads/dekkimporter-logs/`

**Plugin Homepage:**
- Check main plugin file header for author contact

---

## ✅ Production Readiness Checklist

Before going live:

- [ ] All files uploaded correctly
- [ ] Plugin activated without errors
- [ ] WooCommerce installed and active
- [ ] SMTP configured and tested
- [ ] Supplier emails configured
- [ ] API endpoints configured (if using sync)
- [ ] Test order completed successfully
- [ ] Supplier emails received
- [ ] Logs directory created and writable
- [ ] Cron job scheduled (check with WP-CLI)
- [ ] Backup taken
- [ ] Monitoring set up

---

**Your plugin is production-ready! 🚀**

Package: `dekkimporter-v1.3-production.zip` (15 KB)
Location: `/Users/eslamsamy/projects/wordpress-local/`

