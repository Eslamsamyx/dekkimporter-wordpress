#!/bin/bash

# DekkImporter v1.4.0 Deployment Script
# This script deploys the updated plugin to WordPress Docker container

set -e

echo "🚀 Deploying DekkImporter v1.4.0 to Docker WordPress..."

# Variables
PLUGIN_DIR="/Users/eslamsamy/projects/wordpress-local/plugins/dekkimporter-v1.4.0"
DOCKER_PLUGIN_DIR="/var/www/html/wp-content/plugins/dekkimporter"

# Step 1: Create assets directories
echo "📁 Creating assets directories..."
mkdir -p "$PLUGIN_DIR/assets/js"
mkdir -p "$PLUGIN_DIR/assets/css"

# Step 2: Update version in main plugin file
echo "📝 Updating plugin version to 1.4.0..."
sed -i '' 's/Version:           1.3/Version:           1.4.0/g' "$PLUGIN_DIR/dekkimporter.php"
sed -i '' "s/define('DEKKIMPORTER_VERSION', '1.3');/define('DEKKIMPORTER_VERSION', '1.4.0');/g" "$PLUGIN_DIR/dekkimporter.php"

# Step 3: Deactivate current plugin in Docker
echo "⏸️  Deactivating current plugin..."
docker exec wordpress-site wp plugin deactivate dekkimporter --path=/var/www/html --allow-root 2>/dev/null || true

# Step 4: Backup current plugin in Docker
echo "💾 Backing up current plugin..."
docker exec wordpress-site cp -r $DOCKER_PLUGIN_DIR ${DOCKER_PLUGIN_DIR}-backup-$(date +%Y%m%d-%H%M%S) 2>/dev/null || true

# Step 5: Copy updated plugin to Docker
echo "📦 Copying updated plugin to Docker..."
docker cp "$PLUGIN_DIR/." wordpress-site:$DOCKER_PLUGIN_DIR/

# Step 6: Set proper permissions
echo "🔐 Setting permissions..."
docker exec wordpress-site chown -R www-data:www-data $DOCKER_PLUGIN_DIR
docker exec wordpress-site chmod -R 755 $DOCKER_PLUGIN_DIR

# Step 7: Activate plugin
echo "✅ Activating plugin..."
docker exec wordpress-site wp plugin activate dekkimporter --path=/var/www/html --allow-root

# Step 8: Verify installation
echo "🔍 Verifying installation..."
docker exec wordpress-site wp plugin list --path=/var/www/html --allow-root | grep dekkimporter

echo ""
echo "✨ Deployment complete!"
echo "🌐 Visit http://localhost:8080/wp-admin to see the changes"
echo ""
echo "New features added:"
echo "  ✅ Countdown timer to next import"
echo "  ✅ Real-time logs viewer"
echo "  ✅ Automatic log cleanup"
echo "  ✅ Manual sync control with AJAX"
echo "  ✅ Dashboard status widget"
echo "  ✅ Flexible schedule management"
