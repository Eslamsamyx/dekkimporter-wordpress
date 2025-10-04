#!/bin/bash
# DekkImporter v1.4.0 Complete Deployment

set -e

echo "🚀 Deploying DekkImporter v1.4.0 with all new features..."

# Step 1: Deactivate plugin
echo "⏸️  Deactivating plugin..."
docker exec wordpress-site wp plugin deactivate dekkimporter --path=/var/www/html --allow-root 2>/dev/null || true

# Step 2: Update version in main file
echo "📝 Updating version to 1.4.0..."
docker exec wordpress-site sed -i 's/Version:           1.3/Version:           1.4.0/g' /var/www/html/wp-content/plugins/dekkimporter/dekkimporter.php
docker exec wordpress-site sed -i "s/define('DEKKIMPORTER_VERSION', '1.3');/define('DEKKIMPORTER_VERSION', '1.4.0');/g" /var/www/html/wp-content/plugins/dekkimporter/dekkimporter.php

# Step 3: Create assets directories
echo "📁 Creating assets directories..."
docker exec wordpress-site mkdir -p /var/www/html/wp-content/plugins/dekkimporter/assets/js
docker exec wordpress-site mkdir -p /var/www/html/wp-content/plugins/dekkimporter/assets/css

# Step 4: Copy code files from local to Docker
echo "📦 Copying updated code files..."
SRC_DIR="/Users/eslamsamy/projects/wordpress-local/dekkimporter-v1.4.0-complete"
DEST="/var/www/html/wp-content/plugins/dekkimporter"

# Just copy the whole updated directory
docker cp "$SRC_DIR/." wordpress-site:$DEST/

# Step 5: Set permissions
echo "🔐 Setting permissions..."
docker exec wordpress-site chown -R www-data:www-data $DEST
docker exec wordpress-site chmod -R 755 $DEST

# Step 6: Activate plugin
echo "✅ Activating plugin..."
docker exec wordpress-site wp plugin activate dekkimporter --path=/var/www/html --allow-root

# Step 7: Verify
echo "🔍 Verifying..."
docker exec wordpress-site wp plugin list --path=/var/www/html --allow-root | grep dekkimporter

echo ""
echo "✨ Deployment Complete!"
echo "🌐 Open http://localhost:8080/wp-admin"
echo ""
echo "✅ Features Added:"
echo "   - Countdown timer to next import"
echo "   - Real-time logs viewer" 
echo "   - Automatic log cleanup"
echo "   - Manual sync control"
echo "   - Dashboard status widget"
echo "   - Flexible schedule management"

