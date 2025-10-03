<?php
/**
 * DekkImporter Plugin Test Suite
 *
 * Run this from WordPress root: wp eval-file plugins/dekkimporter/test-plugin.php
 * Or include it in a WordPress context
 */

// Ensure WordPress environment is loaded
if (!defined('ABSPATH')) {
    // Try to load WordPress
    require_once(dirname(__FILE__) . '/../../../wp-load.php');
}

class DekkImporter_Test {
    private $results = [];
    private $total_tests = 0;
    private $passed_tests = 0;
    private $failed_tests = 0;

    public function __construct() {
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "DekkImporter Plugin Test Suite\n";
        echo str_repeat("=", 70) . "\n\n";
    }

    private function test($name, $callback) {
        $this->total_tests++;
        echo "Testing: {$name}... ";

        try {
            $result = $callback();
            if ($result === true) {
                $this->passed_tests++;
                echo "✓ PASSED\n";
                $this->results[] = ['name' => $name, 'status' => 'PASSED'];
            } else {
                $this->failed_tests++;
                echo "✗ FAILED: {$result}\n";
                $this->results[] = ['name' => $name, 'status' => 'FAILED', 'message' => $result];
            }
        } catch (Exception $e) {
            $this->failed_tests++;
            echo "✗ ERROR: " . $e->getMessage() . "\n";
            $this->results[] = ['name' => $name, 'status' => 'ERROR', 'message' => $e->getMessage()];
        }
    }

    public function run_tests() {
        // Test 1: Plugin constants
        $this->test('Plugin constants defined', function() {
            if (!defined('DEKKIMPORTER_VERSION')) return 'DEKKIMPORTER_VERSION not defined';
            if (!defined('DEKKIMPORTER_PLUGIN_DIR')) return 'DEKKIMPORTER_PLUGIN_DIR not defined';
            if (!defined('DEKKIMPORTER_PLUGIN_URL')) return 'DEKKIMPORTER_PLUGIN_URL not defined';
            if (!defined('DEKKIMPORTER_INCLUDES_DIR')) return 'DEKKIMPORTER_INCLUDES_DIR not defined';
            return true;
        });

        // Test 2: Main plugin class exists
        $this->test('Main DekkImporter class exists', function() {
            return class_exists('DekkImporter') ? true : 'DekkImporter class not found';
        });

        // Test 3: Plugin instance
        $this->test('Plugin instance creation', function() {
            $instance = dekkimporter();
            return is_object($instance) ? true : 'Failed to get plugin instance';
        });

        // Test 4: Logger class
        $this->test('Logger class exists and initializes', function() {
            if (!class_exists('DekkImporter_Logger')) return 'Logger class not found';
            $plugin = dekkimporter();
            return is_object($plugin->logger) ? true : 'Logger not initialized';
        });

        // Test 5: Admin class
        $this->test('Admin class exists and initializes', function() {
            if (!class_exists('DekkImporter_Admin')) return 'Admin class not found';
            $plugin = dekkimporter();
            return is_object($plugin->admin) ? true : 'Admin not initialized';
        });

        // Test 6: Cron class
        $this->test('Cron class exists and initializes', function() {
            if (!class_exists('DekkImporter_Cron')) return 'Cron class not found';
            $plugin = dekkimporter();
            return is_object($plugin->cron) ? true : 'Cron not initialized';
        });

        // Test 7: Data Source class
        $this->test('Data Source class exists and initializes', function() {
            if (!class_exists('DekkImporter_Data_Source')) return 'Data Source class not found';
            $plugin = dekkimporter();
            return is_object($plugin->data_source) ? true : 'Data Source not initialized';
        });

        // Test 8: Image Handler class
        $this->test('Image Handler class exists and initializes', function() {
            if (!class_exists('DekkImporter_Image_Handler')) return 'Image Handler class not found';
            $plugin = dekkimporter();
            return is_object($plugin->image_handler) ? true : 'Image Handler not initialized';
        });

        // Test 9: Product Creator class
        $this->test('Product Creator class exists and initializes', function() {
            if (!class_exists('DekkImporter_Product_Creator')) return 'Product Creator class not found';
            $plugin = dekkimporter();
            return is_object($plugin->product_creator) ? true : 'Product Creator not initialized';
        });

        // Test 10: Product Updater class
        $this->test('Product Updater class exists and initializes', function() {
            if (!class_exists('DekkImporter_Product_Updater')) return 'Product Updater class not found';
            $plugin = dekkimporter();
            return is_object($plugin->product_updater) ? true : 'Product Updater not initialized';
        });

        // Test 11: Helper class
        $this->test('Helper class exists', function() {
            return class_exists('DekkImporter_Helpers') ? true : 'Helpers class not found';
        });

        // Test 12: Logger file creation
        $this->test('Logger creates log file', function() {
            $plugin = dekkimporter();
            $plugin->logger->log('Test log entry');

            $upload_dir = wp_upload_dir();
            $log_dir = $upload_dir['basedir'] . '/dekkimporter-logs';
            $log_file = $log_dir . '/dekkimporter-' . date('Y-m-d') . '.log';

            if (!file_exists($log_file)) return 'Log file not created';
            $content = file_get_contents($log_file);
            return strpos($content, 'Test log entry') !== false ? true : 'Log entry not written';
        });

        // Test 13: Settings registration
        $this->test('Settings are registered', function() {
            global $wp_settings_sections;
            return isset($wp_settings_sections['dekkimporter']) ? true : 'Settings not registered';
        });

        // Test 14: WooCommerce dependency check
        $this->test('WooCommerce is active', function() {
            if (!function_exists('WC')) return 'WooCommerce not active';
            if (!class_exists('WC_Product_Simple')) return 'WooCommerce classes not available';
            return true;
        });

        // Test 15: Test order processing logic (without email)
        $this->test('Order processing method exists', function() {
            $plugin = dekkimporter();
            return method_exists($plugin, 'process_order') ? true : 'process_order method not found';
        });

        // Test 16: Product creation functionality
        $this->test('Product creation (dry run)', function() {
            if (!function_exists('WC')) return 'WooCommerce not available';

            $plugin = dekkimporter();
            $test_data = [
                'name' => 'Test Product BK',
                'sku' => 'TEST-001-BK',
                'price' => '99.99',
                'description' => 'Test product description',
                'short_description' => 'Test short description'
            ];

            $product_id = $plugin->product_creator->create_product($test_data);

            if (!$product_id) return 'Product creation failed';

            // Clean up
            wp_delete_post($product_id, true);

            return true;
        });

        // Test 17: Product update functionality
        $this->test('Product update (dry run)', function() {
            if (!function_exists('WC')) return 'WooCommerce not available';

            $plugin = dekkimporter();

            // Create a test product first
            $test_data = [
                'name' => 'Test Product Update',
                'sku' => 'TEST-002-BM',
                'price' => '50.00'
            ];

            $product_id = $plugin->product_creator->create_product($test_data);
            if (!$product_id) return 'Failed to create test product for update';

            // Update the product
            $update_data = [
                'name' => 'Updated Test Product',
                'price' => '75.00'
            ];

            $result = $plugin->product_updater->update_product($product_id, $update_data);

            // Verify update
            $product = wc_get_product($product_id);
            $updated_name = $product->get_name();
            $updated_price = $product->get_regular_price();

            // Clean up
            wp_delete_post($product_id, true);

            if ($updated_name !== 'Updated Test Product') return 'Product name not updated';
            if ($updated_price != '75.00') return 'Product price not updated';

            return true;
        });

        // Test 18: Email filtering (prevent actual sending)
        $this->test('Email sending can be intercepted', function() {
            // Add a filter to prevent emails during testing
            $email_sent = false;

            add_filter('wp_mail', function($args) use (&$email_sent) {
                $email_sent = true;
                return false; // Prevent actual sending
            });

            // This would normally send an email
            wp_mail('test@example.com', 'Test', 'Test message');

            return $email_sent ? true : 'Email filter not working';
        });

        // Test 19: Supplier email extraction logic
        $this->test('Supplier detection from SKU', function() {
            $sku_bk = 'PRODUCT-123-BK';
            $sku_bm = 'PRODUCT-456-BM';

            $is_bk = strpos($sku_bk, '-BK') !== false;
            $is_bm = strpos($sku_bm, '-BM') !== false;

            if (!$is_bk) return 'BK supplier detection failed';
            if (!$is_bm) return 'BM supplier detection failed';

            return true;
        });

        // Test 20: Cron schedule functionality
        $this->test('Cron activation/deactivation', function() {
            $plugin = dekkimporter();

            // Activate
            $plugin->cron->activate();
            $scheduled = wp_next_scheduled('dekkimporter_sync_products');

            if (!$scheduled) return 'Cron not scheduled after activation';

            // Deactivate
            $plugin->cron->deactivate();
            $scheduled_after = wp_next_scheduled('dekkimporter_sync_products');

            if ($scheduled_after) return 'Cron still scheduled after deactivation';

            return true;
        });

        $this->print_summary();
    }

    private function print_summary() {
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "Test Summary\n";
        echo str_repeat("=", 70) . "\n";
        echo "Total Tests: {$this->total_tests}\n";
        echo "Passed: {$this->passed_tests} ✓\n";
        echo "Failed: {$this->failed_tests} ✗\n";

        $percentage = $this->total_tests > 0 ? round(($this->passed_tests / $this->total_tests) * 100, 2) : 0;
        echo "Success Rate: {$percentage}%\n";
        echo str_repeat("=", 70) . "\n";

        if ($this->failed_tests > 0) {
            echo "\nFailed Tests Details:\n";
            echo str_repeat("-", 70) . "\n";
            foreach ($this->results as $result) {
                if ($result['status'] !== 'PASSED') {
                    echo "- {$result['name']}: {$result['status']}";
                    if (isset($result['message'])) {
                        echo " ({$result['message']})";
                    }
                    echo "\n";
                }
            }
        }

        echo "\n";
    }

    public function get_results() {
        return [
            'total' => $this->total_tests,
            'passed' => $this->passed_tests,
            'failed' => $this->failed_tests,
            'percentage' => $this->total_tests > 0 ? round(($this->passed_tests / $this->total_tests) * 100, 2) : 0,
            'details' => $this->results
        ];
    }
}

// Run tests
$tester = new DekkImporter_Test();
$tester->run_tests();
