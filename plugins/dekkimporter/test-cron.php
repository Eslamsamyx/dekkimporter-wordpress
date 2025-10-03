<?php
/**
 * Test Cron Functionality
 */

if (!defined('ABSPATH')) {
    require_once(dirname(__FILE__) . '/../../../wp-load.php');
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "DekkImporter Cron Test\n";
echo str_repeat("=", 70) . "\n\n";

$plugin = dekkimporter();

echo "1. Testing cron activation...\n";
$plugin->cron->activate();
$scheduled = wp_next_scheduled('dekkimporter_sync_products');
if ($scheduled) {
    echo "   ✓ Cron scheduled for: " . date('Y-m-d H:i:s', $scheduled) . "\n";
    echo "   - Next run in: " . human_time_diff($scheduled, time()) . "\n";
} else {
    echo "   ✗ Cron not scheduled\n";
}

echo "\n2. Testing cron deactivation...\n";
$plugin->cron->deactivate();
$scheduled_after = wp_next_scheduled('dekkimporter_sync_products');
if (!$scheduled_after) {
    echo "   ✓ Cron unscheduled successfully\n";
} else {
    echo "   ✗ Cron still scheduled\n";
}

echo "\n3. Testing sync_products method...\n";
echo "   Running sync (check logs for output)...\n";
$plugin->cron->sync_products();
echo "   ✓ Sync method executed without errors\n";

echo "\n4. Checking log file for sync entries...\n";
$upload_dir = wp_upload_dir();
$log_file = $upload_dir['basedir'] . '/dekkimporter-logs/dekkimporter-' . date('Y-m-d') . '.log';

if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    if (strpos($log_content, 'Product sync started') !== false) {
        echo "   ✓ Found 'Product sync started' in logs\n";
    }
    if (strpos($log_content, 'Product sync completed') !== false) {
        echo "   ✓ Found 'Product sync completed' in logs\n";
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "Cron Test Complete\n";
echo str_repeat("=", 70) . "\n\n";
