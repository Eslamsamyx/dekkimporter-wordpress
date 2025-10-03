# DekkImporter - Quick Reference Guide

## Access Information

**WordPress Admin:**
- URL: http://localhost:8080/wp-admin
- Username: `admin`
- Password: `admin123`

**WordPress Site:**
- URL: http://localhost:8080

## Plugin Status

✅ **DekkImporter** - Version 1.3 - **ACTIVE**
✅ **WooCommerce** - Version 10.2.2 - **ACTIVE**

## Plugin Location

```
/Users/eslamsamy/projects/wordpress-local/plugins/dekkimporter/
├── dekkimporter.php                    # Main plugin file
└── includes/                            # Plugin classes
    ├── class-admin.php                  # Admin settings page
    ├── class-cron.php                   # Cron job management
    ├── class-data-source.php            # Data fetching
    ├── class-helpers.php                # Helper functions
    ├── class-image-handler.php          # Image uploads
    ├── class-logger.php                 # Logging system
    ├── class-product-creator.php        # Product creation
    └── class-product-updater.php        # Product updates
```

## Configuration

Navigate to: **WordPress Admin → DekkImporter**

Required Settings:
- **BK Supplier Email** - Email for Klettur supplier
- **BM Supplier Email** - Email for Mitra supplier
- **CC Notification Email** - Admin notification email (optional)

## How It Works

1. **Customer places order** with products containing BK/BM SKUs
2. **Order status** changes to "processing" or "completed"
3. **Plugin processes order:**
   - Separates products by SKU suffix (-BK or -BM)
   - Generates HTML emails for each supplier
   - Includes: order number, date, customer, product list
   - CC's notification email if configured
4. **Emails sent** to respective suppliers

## SKU Format

Products must have SKUs in this format:
- `PRODUCT-123-BK` → Sends to BK Supplier (Klettur)
- `PRODUCT-456-BM` → Sends to BM Supplier (Mitra)

## Logs

**Location:** `/wp-content/uploads/dekkimporter-logs/`
**Format:** `dekkimporter-YYYY-MM-DD.log`

View logs via:
```bash
docker exec wordpress-site cat /var/www/html/wp-content/uploads/dekkimporter-logs/dekkimporter-$(date +%Y-%m-%d).log
```

## Cron Jobs

**Event:** `dekkimporter_sync_products`
**Frequency:** Daily
**Purpose:** Product synchronization (currently placeholder)

View scheduled cron:
```bash
docker exec wordpress-site wp cron event list --allow-root | grep dekkimporter
```

## Docker Commands

**Start WordPress:**
```bash
cd /Users/eslamsamy/projects/wordpress-local
docker-compose up -d
```

**Stop WordPress:**
```bash
docker-compose down
```

**View logs:**
```bash
docker-compose logs -f
```

**Restart:**
```bash
docker-compose restart
```

**Access WordPress container:**
```bash
docker exec -it wordpress-site bash
```

## Testing Commands

**Run all tests:**
```bash
docker exec wordpress-site php /var/www/html/wp-content/plugins/dekkimporter/test-plugin.php
```

**Test order processing:**
```bash
docker exec wordpress-site php /var/www/html/wp-content/plugins/dekkimporter/test-order-processing.php
```

**Test cron:**
```bash
docker exec wordpress-site php /var/www/html/wp-content/plugins/dekkimporter/test-cron.php
```

## WP-CLI Commands

**Plugin status:**
```bash
docker exec wordpress-site wp plugin list --allow-root
```

**Activate plugin:**
```bash
docker exec wordpress-site wp plugin activate dekkimporter --allow-root
```

**Deactivate plugin:**
```bash
docker exec wordpress-site wp plugin deactivate dekkimporter --allow-root
```

**Check options:**
```bash
docker exec wordpress-site wp option get dekkimporter_options --allow-root
```

## Troubleshooting

### Plugin not appearing
```bash
# Check plugin directory exists
docker exec wordpress-site ls -la /var/www/html/wp-content/plugins/dekkimporter/

# Reactivate
docker exec wordpress-site wp plugin deactivate dekkimporter --allow-root
docker exec wordpress-site wp plugin activate dekkimporter --allow-root
```

### Emails not sending
1. Check logs: `/wp-content/uploads/dekkimporter-logs/`
2. Verify supplier emails configured in admin
3. Check order status is "processing" or "completed"
4. Verify products have -BK or -BM in SKU

### View WordPress logs
```bash
docker exec wordpress-site wp cli debug --allow-root
docker-compose logs wordpress-site
```

## Test Results Summary

✅ **95% Test Pass Rate** (23/24 tests)
- ✅ All core functionality working
- ✅ Email routing correct (BK/BM separation)
- ✅ Logger functional
- ✅ Cron jobs working
- ✅ Product creation/updates working
- ✅ Security best practices implemented

## Production Checklist

Before deploying to production:

- [ ] Configure SMTP/sendmail in WordPress
- [ ] Set actual supplier email addresses
- [ ] Test email delivery in staging
- [ ] Remove test files (`test-*.php`)
- [ ] Set up log rotation
- [ ] Configure backup for logs
- [ ] Test with real orders
- [ ] Monitor logs after deployment

## Support & Documentation

**Test Reports:**
- Full Report: `/Users/eslamsamy/projects/wordpress-local/DEKKIMPORTER_TEST_REPORT.md`
- Summary: `/Users/eslamsamy/projects/wordpress-local/TEST_SUMMARY.txt`

**Plugin Version:** 1.3
**WordPress Version:** Latest
**WooCommerce Version:** 10.2.2
**PHP Version:** 7.4+

---

**Last Updated:** October 2, 2025
