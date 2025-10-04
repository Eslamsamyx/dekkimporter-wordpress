<?php
/**
 * Data Source class for DekkImporter
 */

if (!defined('ABSPATH')) {
    exit;
}

class DekkImporter_Data_Source {
    /**
     * Plugin instance
     */
    private $plugin;

    /**
     * API endpoints
     */
    private $api_endpoints = [];

    /**
     * Constructor
     */
    public function __construct($plugin) {
        $this->plugin = $plugin;

        // Load API configuration
        $options = get_option('dekkimporter_options', []);
        $this->api_endpoints = [
            'BK' => isset($options['dekkimporter_bk_api_url']) ? $options['dekkimporter_bk_api_url'] : '',
            'BM' => isset($options['dekkimporter_bm_api_url']) ? $options['dekkimporter_bm_api_url'] : '',
        ];
    }

    /**
     * Fetch products from all data sources
     *
     * @return array Combined products from all suppliers
     */
    public function fetch_products() {
        $this->plugin->logger->log('Fetching products from all data sources');

        $all_products = [];

        // Fetch from BK supplier
        if (!empty($this->api_endpoints['BK'])) {
            $bk_products = $this->fetch_from_supplier('BK', $this->api_endpoints['BK']);
            $all_products = array_merge($all_products, $bk_products);
        }

        // Fetch from BM supplier
        if (!empty($this->api_endpoints['BM'])) {
            $bm_products = $this->fetch_from_supplier('BM', $this->api_endpoints['BM']);
            $all_products = array_merge($all_products, $bm_products);
        }

        $this->plugin->logger->log('Fetched ' . count($all_products) . ' products from data sources');

        return $all_products;
    }

    /**
     * Fetch products from a specific supplier
     *
     * @param string $supplier Supplier code (BK or BM)
     * @param string $api_url API endpoint URL
     * @return array Products from supplier
     */
    private function fetch_from_supplier($supplier, $api_url) {
        $this->plugin->logger->log("Fetching products from {$supplier} supplier: {$api_url}");

        $response = wp_remote_get($api_url, [
            'timeout' => 30,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            $this->plugin->logger->log("API Error ({$supplier}): " . $response->get_error_message(), 'ERROR');
            return [];
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            $this->plugin->logger->log("API returned status {$status_code} for {$supplier}", 'ERROR');
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->plugin->logger->log("JSON decode error for {$supplier}: " . json_last_error_msg(), 'ERROR');
            return [];
        }

        if (!is_array($data)) {
            $this->plugin->logger->log("Invalid data format from {$supplier}", 'ERROR');
            return [];
        }

        // Normalize product data and add supplier info
        $products = [];
        foreach ($data as $product) {
            $normalized = $this->normalize_product_data($product, $supplier);
            if ($normalized) {
                $products[] = $normalized;
            }
        }

        $this->plugin->logger->log("Fetched " . count($products) . " products from {$supplier}");

        return $products;
    }

    /**
     * Normalize product data from API
     *
     * @param array $product Raw product data from API
     * @param string $supplier Supplier code
     * @return array|null Normalized product data or null if invalid
     */
    private function normalize_product_data($product, $supplier) {
        // Required fields
        if (empty($product['sku']) || empty($product['name'])) {
            $this->plugin->logger->log("Skipping product: missing SKU or name", 'WARNING');
            return null;
        }

        // Ensure SKU has supplier suffix
        $sku = $product['sku'];
        if (strpos($sku, "-{$supplier}") === false) {
            $sku = $sku . "-{$supplier}";
        }

        return [
            'sku' => sanitize_text_field($sku),
            'name' => sanitize_text_field($product['name']),
            'price' => isset($product['price']) ? floatval($product['price']) : 0,
            'description' => isset($product['description']) ? wp_kses_post($product['description']) : '',
            'short_description' => isset($product['short_description']) ? wp_kses_post($product['short_description']) : '',
            'stock_quantity' => isset($product['stock']) ? intval($product['stock']) : 0,
            'image_url' => isset($product['image']) ? esc_url_raw($product['image']) : '',
            'supplier' => $supplier,
            'api_id' => isset($product['id']) ? sanitize_text_field($product['id']) : $sku,
            'last_modified' => isset($product['updated_at']) ? sanitize_text_field($product['updated_at']) : current_time('mysql'),
        ];
    }

    /**
     * Get list of active product SKUs from API
     * Used to identify obsolete products
     *
     * @return array Array of active SKUs
     */
    public function get_active_skus() {
        $products = $this->fetch_products();
        $skus = [];

        foreach ($products as $product) {
            if (!empty($product['sku'])) {
                $skus[] = $product['sku'];
            }
        }

        return $skus;
    }

    /**
     * Mock API data for testing
     * This simulates API responses for testing purposes
     *
     * @param string $supplier Supplier code
     * @param int $count Number of products to generate
     * @return array Mock product data
     */
    public function generate_mock_data($supplier, $count = 10) {
        $products = [];

        for ($i = 1; $i <= $count; $i++) {
            $products[] = [
                'id' => "API-{$supplier}-{$i}",
                'sku' => "PRODUCT-" . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => "Test Product {$supplier} #{$i}",
                'price' => rand(10, 500) + (rand(0, 99) / 100),
                'description' => "This is a test product from {$supplier} supplier. Product ID: {$i}",
                'short_description' => "Test product #{$i}",
                'stock' => rand(0, 100),
                'image' => "https://via.placeholder.com/300x300?text=Product+{$i}",
                'updated_at' => current_time('mysql'),
            ];
        }

        return $products;
    }
}
