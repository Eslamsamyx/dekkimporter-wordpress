<?php
/**
 * Order Notifications Handler
 * Sends order notifications to suppliers based on SKU suffix
 * Port from dekkimporter-7.php lines 1713-1787
 *
 * @package DekkImporter
 */

if (!defined('ABSPATH')) {
    exit;
}

class DekkImporter_Order_Notifications {
    /**
     * Plugin instance
     */
    private $plugin;

    /**
     * Constructor
     */
    public function __construct($plugin) {
        $this->plugin = $plugin;

        // Hook into WooCommerce order completion
        add_action('woocommerce_thankyou', array($this, 'process_order'), 10, 1);
    }

    /**
     * Process order and send notifications to suppliers
     * Separates items by SKU suffix and sends to appropriate supplier
     *
     * @param int $order_id Order ID
     */
    public function process_order($order_id) {
        if (!$order_id) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        // Separate items by supplier
        $bk_items = array();
        $bm_items = array();

        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            if (!$product) {
                continue;
            }

            $sku = $product->get_sku();
            $quantity = $item->get_quantity();
            $product_name = $item->get_name();

            $item_data = array(
                'name' => $product_name,
                'sku' => $sku,
                'quantity' => $quantity,
            );

            // Route by SKU suffix
            if (strpos($sku, '-BK') !== false) {
                $bk_items[] = $item_data;
            } elseif (strpos($sku, '-BM') !== false) {
                $bm_items[] = $item_data;
            }
        }

        // Send to BK supplier (Klettur)
        if (!empty($bk_items)) {
            $this->send_supplier_notification('BK', $bk_items, $order);
        }

        // Send to BM supplier (Mitra)
        if (!empty($bm_items)) {
            $this->send_supplier_notification('BM', $bm_items, $order);
        }

        $this->plugin->logger->log("Order #{$order_id} notifications sent to suppliers");
    }

    /**
     * Send notification email to supplier
     *
     * @param string $supplier Supplier code (BK or BM)
     * @param array $items Array of order items
     * @param WC_Order $order Order object
     */
    private function send_supplier_notification($supplier, $items, $order) {
        // Supplier email addresses
        $supplier_emails = array(
            'BK' => 'bud@klettur.is',
            'BM' => 'mitra@mitra.is',
        );

        $supplier_names = array(
            'BK' => 'Klettur',
            'BM' => 'Mitra',
        );

        if (!isset($supplier_emails[$supplier])) {
            return;
        }

        $to = $supplier_emails[$supplier];
        $cc = 'fyrirspurnir@dekk1.is';
        $supplier_name = $supplier_names[$supplier];
        $order_id = $order->get_id();

        // Build email content
        $subject = "Ný pöntun frá dekk1.is - Pöntun #{$order_id}";

        $message = "<html><body>";
        $message .= "<h2>Góðan daginn,</h2>";
        $message .= "<p>Ný pöntun hefur verið gerð á dekk1.is sem inniheldur vörur frá {$supplier_name}.</p>";
        $message .= "<p><strong>Pöntunarnúmer:</strong> #{$order_id}</p>";
        $message .= "<h3>Vörur:</h3>";
        $message .= "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse;'>";
        $message .= "<tr><th>Vara</th><th>SKU</th><th>Magn</th></tr>";

        foreach ($items as $item) {
            $message .= "<tr>";
            $message .= "<td>" . esc_html($item['name']) . "</td>";
            $message .= "<td>" . esc_html($item['sku']) . "</td>";
            $message .= "<td>" . esc_html($item['quantity']) . "</td>";
            $message .= "</tr>";
        }

        $message .= "</table>";
        $message .= "<p>Kær kveðja,<br>dekk1.is</p>";
        $message .= "</body></html>";

        // Email headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'Cc: ' . $cc,
        );

        // Send email
        $sent = wp_mail($to, $subject, $message, $headers);

        if ($sent) {
            $this->plugin->logger->log("Order notification sent to {$supplier} ({$to})");
        } else {
            $this->plugin->logger->log("ERROR: Failed to send order notification to {$supplier} ({$to})");
        }
    }
}
