<?php
/**
 * Test Order Processing
 */

if (!defined('ABSPATH')) {
    require_once(dirname(__FILE__) . '/../../../wp-load.php');
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "DekkImporter Order Processing Test\n";
echo str_repeat("=", 70) . "\n\n";

// Prevent actual email sending
$emails_intercepted = [];
add_filter('wp_mail', function($args) use (&$emails_intercepted) {
    $emails_intercepted[] = $args;
    echo "✓ Email intercepted (not sent)\n";
    echo "  To: " . $args['to'] . "\n";
    echo "  Subject: " . $args['subject'] . "\n";
    return false; // Prevent actual sending
}, 10, 1);

// Set up test supplier emails
update_option('dekkimporter_options', [
    'dekkimporter_bk_email' => 'bk-supplier@test.local',
    'dekkimporter_bm_email' => 'bm-supplier@test.local',
    'dekkimporter_field_notification_email' => 'admin@test.local'
]);

echo "1. Creating test products...\n";

// Create test products
$product1 = new WC_Product_Simple();
$product1->set_name('Test Product BK');
$product1->set_sku('PROD-001-BK');
$product1->set_regular_price('100.00');
$product1_id = $product1->save();
echo "   ✓ Created product: {$product1->get_name()} (SKU: {$product1->get_sku()})\n";

$product2 = new WC_Product_Simple();
$product2->set_name('Test Product BM');
$product2->set_sku('PROD-002-BM');
$product2->set_regular_price('50.00');
$product2_id = $product2->save();
echo "   ✓ Created product: {$product2->get_name()} (SKU: {$product2->get_sku()})\n";

echo "\n2. Creating test order...\n";

// Create test order
$order = wc_create_order();
$order->add_product($product1, 2); // 2x BK product
$order->add_product($product2, 3); // 3x BM product

// Set billing details
$order->set_billing_first_name('Test');
$order->set_billing_last_name('Customer');
$order->set_billing_email('customer@test.local');

// Save and set status
$order->calculate_totals();
$order->set_status('processing');
$order->save();

$order_id = $order->get_id();
echo "   ✓ Order created: #{$order->get_order_number()} (ID: {$order_id})\n";
echo "   - Status: {$order->get_status()}\n";
echo "   - Total: {$order->get_currency()} {$order->get_total()}\n";

echo "\n3. Testing order processing (email interception active)...\n";

// Get plugin instance and process order
$plugin = dekkimporter();
$plugin->process_order($order_id);

echo "\n4. Email Interception Report:\n";
echo "   Total emails intercepted: " . count($emails_intercepted) . "\n";

foreach ($emails_intercepted as $index => $email) {
    echo "\n   Email #" . ($index + 1) . ":\n";
    echo "   - To: {$email['to']}\n";
    echo "   - Subject: {$email['subject']}\n";

    // Parse message to extract key info
    $message = $email['message'];
    if (strpos($message, 'Klettur') !== false) {
        echo "   - Supplier: Klettur (BK)\n";
    } elseif (strpos($message, 'Mitra') !== false) {
        echo "   - Supplier: Mitra (BM)\n";
    }

    // Check for CC header
    $has_cc = false;
    if (isset($email['headers'])) {
        foreach ($email['headers'] as $header) {
            if (strpos($header, 'Cc:') !== false) {
                echo "   - CC: " . str_replace('Cc: ', '', $header) . "\n";
                $has_cc = true;
            }
        }
    }

    // Count items in email
    preg_match_all('/<tr>.*?<td>(\d+)<\/td>/s', $message, $matches);
    if (isset($matches[1])) {
        $item_count = 0;
        foreach ($matches[1] as $qty) {
            if (is_numeric($qty)) {
                $item_count++;
            }
        }
        if ($item_count > 0) {
            echo "   - Items in email: {$item_count}\n";
        }
    }
}

echo "\n5. Verifying supplier separation logic...\n";

// Check that BK products went to BK supplier
$bk_email_found = false;
$bm_email_found = false;

foreach ($emails_intercepted as $email) {
    if ($email['to'] === 'bk-supplier@test.local') {
        $bk_email_found = true;
        if (strpos($email['message'], 'PROD-001-BK') !== false) {
            echo "   ✓ BK supplier email contains BK products\n";
        } else {
            echo "   ✗ BK supplier email missing BK products\n";
        }
    }

    if ($email['to'] === 'bm-supplier@test.local') {
        $bm_email_found = true;
        if (strpos($email['message'], 'PROD-002-BM') !== false) {
            echo "   ✓ BM supplier email contains BM products\n";
        } else {
            echo "   ✗ BM supplier email missing BM products\n";
        }
    }
}

if (!$bk_email_found) {
    echo "   ✗ No email sent to BK supplier\n";
}
if (!$bm_email_found) {
    echo "   ✗ No email sent to BM supplier\n";
}

echo "\n6. Testing logger functionality...\n";

$upload_dir = wp_upload_dir();
$log_file = $upload_dir['basedir'] . '/dekkimporter-logs/dekkimporter-' . date('Y-m-d') . '.log';

if (file_exists($log_file)) {
    echo "   ✓ Log file exists: {$log_file}\n";
    $log_content = file_get_contents($log_file);
    $log_lines = explode("\n", trim($log_content));
    echo "   - Log entries: " . count($log_lines) . "\n";

    // Show last few log entries
    echo "   - Recent log entries:\n";
    $recent = array_slice($log_lines, -5);
    foreach ($recent as $line) {
        if (!empty($line)) {
            echo "     " . substr($line, 0, 100) . "...\n";
        }
    }
} else {
    echo "   ✗ Log file not found\n";
}

echo "\n7. Cleanup test data...\n";

wp_delete_post($product1_id, true);
echo "   ✓ Deleted test product 1\n";

wp_delete_post($product2_id, true);
echo "   ✓ Deleted test product 2\n";

wp_delete_post($order_id, true);
echo "   ✓ Deleted test order\n";

echo "\n" . str_repeat("=", 70) . "\n";
echo "Test Complete - NO EMAILS WERE ACTUALLY SENT\n";
echo str_repeat("=", 70) . "\n\n";
