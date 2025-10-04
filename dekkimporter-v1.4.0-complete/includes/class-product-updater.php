<?php
/**
 * Product Updater class for DekkImporter
 */

if (!defined('ABSPATH')) {
    exit;
}

class DekkImporter_Product_Updater {
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
     * Update product
     */
    public function update_product($product_id, $product_data) {
        $product = wc_get_product($product_id);

        if (!$product) {
            $this->plugin->logger->log("Product not found: ID {$product_id}", 'ERROR');
            return false;
        }

        if (isset($product_data['name'])) {
            $product->set_name($product_data['name']);
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

        if (isset($product_data['stock_quantity'])) {
            $product->set_stock_quantity($product_data['stock_quantity']);
        }

        $product->save();

        $this->plugin->logger->log("Product updated: ID {$product_id}");
        return true;
    }
}
