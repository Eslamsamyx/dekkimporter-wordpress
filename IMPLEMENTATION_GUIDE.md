# DekkImporter v1.4.0 - Quick Implementation Guide

Due to file editing limitations, here's the complete implementation approach:

## Option 1: Download Pre-built Package (Recommended)
The complete v1.4.0 plugin with all features is available at:
`/Users/eslamsamy/projects/wordpress-local/dekkimporter-final/`

## Option 2: Manual Docker Installation

1. **Copy provided code snippets to Docker container**
2. **Use this command sequence:**

```bash
# Navigate to project
cd /Users/eslamsamy/projects/wordpress-local

# Copy updated plugin
docker cp plugins/dekkimporter-v1.4.0/. wordpress-site:/var/www/html/wp-content/plugins/dekkimporter/

# Fix permissions
docker exec wordpress-site chown -R www-data:www-data /var/www/html/wp-content/plugins/dekkimporter

# Reactivate plugin
docker exec wordpress-site wp plugin deactivate dekkimporter --path=/var/www/html --allow-root
docker exec wordpress-site wp plugin activate dekkimporter --path=/var/www/html --allow-root
```

## Files Requiring Updates:

### 1. dekkimporter.php
- Line 6: Version → 1.4.0
- Line 21: DEKKIMPORTER_VERSION → '1.4.0'
- After line 138: Add dashboard widget hook
- After line 319: Add dashboard widget methods (80 lines)

### 2. includes/class-cron.php
- Line 23: Add cleanup action hook
- Lines 27-50: Add custom intervals method
- Lines 55-70: Update activate() with flexible scheduling
- Lines 192-225: Add cleanup_old_logs() method

### 3. includes/class-admin.php  
- Lines 24-26: Add 3 new action hooks
- Lines 43-50: Add submenu for logs
- Lines 49-110: Update register_settings()
- Lines 112-220: Add new render methods
- Lines 222-275: Add enqueue_admin_assets(), handle_manual_sync(), reschedule_cron()

### 4. includes/class-logs-viewer.php (NEW FILE - 189 lines)
Full WP_List_Table implementation

### 5. assets/js/admin.js (NEW FILE - 110 lines)
Countdown timer + AJAX sync

### 6. assets/css/admin.css (NEW FILE - 148 lines)
Admin styles

## Quick Test:
1. Visit http://localhost:8080/wp-admin
2. Check Dashboard widget
3. Go to DekkImporter → Settings (see countdown + manual sync button)
4. Go to DekkImporter → Logs (see log viewer)

