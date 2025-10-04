#!/bin/bash
set -e

PLUGIN_DIR="/Users/eslamsamy/projects/wordpress-local/plugins/dekkimporter-v1.4.0"

# Create assets directories
mkdir -p "$PLUGIN_DIR/assets/js"
mkdir -p "$PLUGIN_DIR/assets/css"

# Update version
sed -i '' 's/Version:           1.3/Version:           1.4.0/g' "$PLUGIN_DIR/dekkimporter.php"
sed -i '' "s/define('DEKKIMPORTER_VERSION', '1.3');/define('DEKKIMPORTER_VERSION', '1.4.0');/g" "$PLUGIN_DIR/dekkimporter.php"

echo "✅ Version updated to 1.4.0"
echo "✅ Assets directories created"
echo "📝 Now copy the PHP, JS, and CSS code manually or run the full deployment"

