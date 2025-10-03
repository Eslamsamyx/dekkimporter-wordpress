<?php
/**
 * Plugin Name:       DekkImporter
 * Plugin URI:        mailto:zekonja993@gmail.com
 * Description:       Scraping and updating products.
 * Version:           2.2.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Miljan Zekovic
 * Author URI:        mailto:zekonja993@gmail.com
 * License:           Private
 * Text Domain:       dekkimporter
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('DEKKIMPORTER_VERSION', '2.2.0');
define('DEKKIMPORTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DEKKIMPORTER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DEKKIMPORTER_INCLUDES_DIR', DEKKIMPORTER_PLUGIN_DIR . 'includes/');

// Autoload classes
spl_autoload_register(function ($class) {
    // Only load our plugin classes
    if (strpos($class, 'DekkImporter_') !== 0) {
        return;
    }

    // Convert class name to file path
    $class_name = str_replace('DekkImporter_', '', $class);
    $class_name = strtolower($class_name);
    $class_file = DEKKIMPORTER_INCLUDES_DIR . 'class-' . str_replace('_', '-', $class_name) . '.php';

    if (file_exists($class_file)) {
        require_once $class_file;
    }
});

// Include required files
require_once DEKKIMPORTER_INCLUDES_DIR . 'class-helpers.php';

/**
 * Main plugin class
 */
class DekkImporter {
    /**
     * Plugin instance
     *
     * @var DekkImporter
     */
    private static $instance;

    /**
     * Logger instance
     *
     * @var DekkImporter_Logger
     */
    public $logger;

    /**
     * Admin instance
     *
     * @var DekkImporter_Admin
     */
    public $admin;

    /**
     * Cron instance
     *
     * @var DekkImporter_Cron
     */
    public $cron;

    /**
     * Data source instance
     *
     * @var DekkImporter_Data_Source
     */
    public $data_source;

    /**
     * Product creator instance
     *
     * @var DekkImporter_Product_Creator
     */
    public $product_creator;

    /**
     * Product updater instance
     *
     * @var DekkImporter_Product_Updater
     */
    public $product_updater;

    /**
     * Image handler instance
     *
     * @var DekkImporter_Image_Handler
     */
    public $image_handler;

    /**
     * Sync manager instance
     *
     * @var DekkImporter_Sync_Manager
     */
    public $sync_manager;

    /**
     * Get plugin instance
     *
     * @return DekkImporter
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Initialize components
        $this->init_components();

        // Register activation/deactivation hooks
        register_activation_hook(__FILE__, array($this->cron, 'activate'));
        register_deactivation_hook(__FILE__, array($this->cron, 'deactivate'));

        // Register WooCommerce hooks
        add_action('woocommerce_thankyou', array($this, 'process_order'));
        add_action('wp_mail_failed', array($this->logger, 'log_mailer_errors'), 10, 1);

        // Register dashboard widget
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
    }

    /**
     * Initialize components
     */
    private function init_components() {
        // Initialize logger first
        $this->logger = new DekkImporter_Logger();

        // Then initialize other components
        $this->admin = new DekkImporter_Admin($this);
        $this->cron = new DekkImporter_Cron($this);
        $this->data_source = new DekkImporter_Data_Source($this);
        $this->image_handler = new DekkImporter_Image_Handler($this);
        $this->product_creator = new DekkImporter_Product_Creator($this);
        $this->product_updater = new DekkImporter_Product_Updater($this);
        $this->sync_manager = new DekkImporter_Sync_Manager($this);
    }

    /**
     * Process order and notify suppliers
     *
     * @param int $order_id The order ID
     */
    public function process_order($order_id) {
        // Get order
        $order = wc_get_order($order_id);
        if (!$order) {
            $this->logger->log("Order ID $order_id not found.");
            return;
        }

        // Get order status
        $status = $order->get_status();
        if (!in_array($status, ['completed', 'processing'], true)) {
            $this->logger->log("Order $order_id has status '$status'. Notifications are only sent for 'completed' or 'processing' orders.");
            return;
        }

        // Get supplier email configurations
        $options = get_option('dekkimporter_options', []);
        $bk_email = isset($options['dekkimporter_bk_email']) ? sanitize_email($options['dekkimporter_bk_email']) : '';
        $bm_email = isset($options['dekkimporter_bm_email']) ? sanitize_email($options['dekkimporter_bm_email']) : '';
        $notification_email = isset($options['dekkimporter_field_notification_email']) ? sanitize_email($options['dekkimporter_field_notification_email']) : '';

        // If supplier emails are not configured, log warning and exit
        if (empty($bk_email) && empty($bm_email)) {
            $this->logger->log("Supplier emails not configured. Please set them in DekkImporter options.");
            return;
        }

        $this->notify_suppliers($order, $bk_email, $bm_email, $notification_email);
    }

    /**
     * Notify suppliers about the order
     *
     * @param WC_Order $order The order
     * @param string $bk_email Klettur supplier email
     * @param string $bm_email Mitra supplier email
     * @param string $notification_email Notification email
     */
    private function notify_suppliers($order, $bk_email, $bm_email, $notification_email) {
        $supplier_items = [
            'BK' => [],
            'BM' => [],
        ];

        // Extract items by supplier
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if (!$product) {
                continue;
            }

            $sku = $product->get_sku();
            if (empty($sku)) {
                continue;
            }

            if (strpos($sku, '-BK') !== false) {
                $supplier_items['BK'][] = $item;
            } elseif (strpos($sku, '-BM') !== false) {
                $supplier_items['BM'][] = $item;
            }
        }

        // Basic email headers
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
        ];

        // Add CC if notification email is set
        if (!empty($notification_email)) {
            $headers[] = 'Cc: ' . $notification_email;
        }

        // Get store info for better email formatting
        $store_name = get_bloginfo('name');
        $admin_email = get_option('admin_email');

        if (!empty($admin_email)) {
            $headers[] = 'From: ' . $store_name . ' <' . $admin_email . '>';
        }

        // Order info
        $order_number = $order->get_order_number();
        $order_date = $order->get_date_created()->date('Y-m-d H:i:s');
        $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

        // Send notifications to each supplier
        $this->send_supplier_notification('BK', $supplier_items['BK'], $bk_email, $headers, $store_name, $order_number, $order_date, $customer_name);
        $this->send_supplier_notification('BM', $supplier_items['BM'], $bm_email, $headers, $store_name, $order_number, $order_date, $customer_name);
    }

    /**
     * Send notification to a supplier
     *
     * @param string $supplier_code Supplier code (BK or BM)
     * @param array $items Order items for this supplier
     * @param string $email Supplier email
     * @param array $headers Email headers
     * @param string $store_name Store name
     * @param string $order_number Order number
     * @param string $order_date Order date
     * @param string $customer_name Customer name
     */
    private function send_supplier_notification($supplier_code, $items, $email, $headers, $store_name, $order_number, $order_date, $customer_name) {
        if (empty($items) || empty($email)) {
            return;
        }

        $supplier_name = $supplier_code === 'BK' ? 'Klettur' : 'Mitra';
        $to = $email;
        $subject = '[' . $store_name . '] New Order ' . $order_number . ' - ' . $supplier_name . ' Items';

        // Create email template
        $message = '
        <html>
        <head>
            <style>
                body { font-family: sans-serif; color: #333; }
                table { border-collapse: collapse; width: 100%; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <h2>New Order from ' . esc_html($store_name) . '</h2>
            <p>Order #: ' . esc_html($order_number) . '</p>
            <p>Date: ' . esc_html($order_date) . '</p>
            <p>Customer: ' . esc_html($customer_name) . '</p>

            <h3>Requested Items:</h3>
            <table>
                <tr>
                    <th>Quantity</th>
                    <th>Product</th>
                    <th>SKU</th>
                </tr>';

        foreach ($items as $item) {
            $product = $item->get_product();
            $message .= '<tr>
                <td>' . esc_html($item->get_quantity()) . '</td>
                <td>' . esc_html($item->get_name()) . '</td>
                <td>' . esc_html($product->get_sku()) . '</td>
            </tr>';
        }

        $message .= '
            </table>
            <p>Thank you,<br>' . esc_html($store_name) . '</p>
        </body>
        </html>';

        if (wp_mail($to, $subject, $message, $headers)) {
            $this->logger->log('Notification email sent to ' . $supplier_name . ' supplier: ' . $email);
        } else {
            $this->logger->log('Failed to send email to ' . $supplier_name . ' supplier: ' . $email);
        }
    }

    /**
     * Add dashboard widget
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'dekkimporter_status_widget',
            'DekkImporter Status',
            array($this, 'render_dashboard_widget')
        );
    }

    /**
     * Render dashboard widget
     */
    public function render_dashboard_widget() {
        $next_sync = wp_next_scheduled('dekkimporter_sync_products');
        $last_sync_stats = get_option('dekkimporter_last_sync_stats', array());
        $last_sync_time = get_option('dekkimporter_last_sync_time', 0);

        ?>
        <div class="dekkimporter-dashboard-widget">
            <div class="dekkimporter-widget-section">
                <h4><?php esc_html_e('Sync Status', 'dekkimporter'); ?></h4>
                <?php if ($last_sync_time > 0) : ?>
                    <p>
                        <strong><?php esc_html_e('Last Sync:', 'dekkimporter'); ?></strong>
                        <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $last_sync_time)); ?>
                        <?php if (!empty($last_sync_stats['errors']) && $last_sync_stats['errors'] > 0) : ?>
                            <span class="dashicons dashicons-warning" style="color: #dc3545;"></span>
                        <?php else : ?>
                            <span class="dashicons dashicons-yes-alt" style="color: #28a745;"></span>
                        <?php endif; ?>
                    </p>
                <?php else : ?>
                    <p><?php esc_html_e('No sync has been run yet.', 'dekkimporter'); ?></p>
                <?php endif; ?>

                <?php if ($next_sync) : ?>
                    <p>
                        <strong><?php esc_html_e('Next Sync:', 'dekkimporter'); ?></strong>
                        <span id="dekkimporter-widget-countdown" data-timestamp="<?php echo esc_attr($next_sync); ?>"></span>
                    </p>
                <?php endif; ?>
            </div>

            <?php if (!empty($last_sync_stats)) : ?>
            <div class="dekkimporter-widget-section">
                <h4><?php esc_html_e('Last Sync Results', 'dekkimporter'); ?></h4>
                <table class="widefat">
                    <tbody>
                        <tr>
                            <td><?php esc_html_e('Products Fetched', 'dekkimporter'); ?></td>
                            <td><strong><?php echo absint($last_sync_stats['products_fetched'] ?? 0); ?></strong></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Created', 'dekkimporter'); ?></td>
                            <td><strong style="color: #28a745;"><?php echo absint($last_sync_stats['products_created'] ?? 0); ?></strong></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Updated', 'dekkimporter'); ?></td>
                            <td><strong style="color: #007bff;"><?php echo absint($last_sync_stats['products_updated'] ?? 0); ?></strong></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Errors', 'dekkimporter'); ?></td>
                            <td><strong style="color: #dc3545;"><?php echo absint($last_sync_stats['errors'] ?? 0); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <div class="dekkimporter-widget-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=dekkimporter')); ?>" class="button">
                    <?php esc_html_e('Settings', 'dekkimporter'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dekkimporter-logs')); ?>" class="button">
                    <?php esc_html_e('View Logs', 'dekkimporter'); ?>
                </a>
            </div>
        </div>
        <?php
    }
}

// Initialize the plugin
function dekkimporter() {
    return DekkImporter::get_instance();
}

// Start the plugin
dekkimporter();
