<?php
/**
 * Product Creator class for DekkImporter
 */

if (!defined('ABSPATH')) {
    exit;
}

class DekkImporter_Product_Creator {
    /**
     * Plugin instance
     */
    private $plugin;

    /**
     * Constructor
     */
    public function __construct($plugin) {
        $this->plugin = $plugin;
    }

    /**
     * Create product
     */
    public function create_product($product_data) {
        $product = new WC_Product_Simple();

        if (isset($product_data['name'])) {
            $product->set_name($product_data['name']);
        }

        if (isset($product_data['sku'])) {
            $product->set_sku($product_data['sku']);
        }

        if (isset($product_data['price'])) {
            $product->set_regular_price($product_data['price']);
        }

        if (isset($product_data['description'])) {
            $product->set_description($product_data['description']);
        }

        if (isset($product_data['short_description'])) {
            $product->set_short_description($product_data['short_description']);
        }

        $product_id = $product->save();

        if ($product_id) {
            $this->plugin->logger->log("Product created: {$product_data['name']} (ID: {$product_id})");
        } else {
            $this->plugin->logger->log("Failed to create product: {$product_data['name']}", 'ERROR');
        }

        return $product_id;
    }
}
