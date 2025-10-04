<?php
/**
 * Data Source class for DekkImporter
 * Handles fetching products from Klettur (BK) and Mitra (BM) APIs
 * 100% Feature Parity with Original Plugin
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
     * BK Image database cache
     */
    private $bk_image_db = [];

    /**
     * Constructor
     */
    public function __construct($plugin) {
        $this->plugin = $plugin;

        // Load API configuration
        $options = get_option('dekkimporter_options', []);
        $this->api_endpoints = [
            'BK' => isset($options['dekkimporter_bk_api_url']) ? $options['dekkimporter_bk_api_url'] : '',
            'BK_IMAGES' => 'https://bud.klettur.is/wp-content/themes/bud.klettur.is/json/myndir.php',
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

        // Fetch from BK supplier (Klettur)
        if (!empty($this->api_endpoints['BK'])) {
            $bk_products = $this->fetch_from_bk($this->api_endpoints['BK']);
            $all_products = array_merge($all_products, $bk_products);
        }

        // Fetch from BM supplier (Mitra) - includes 3 API calls
        if (!empty($this->api_endpoints['BM'])) {
            $bm_products = $this->fetch_from_bm($this->api_endpoints['BM']);
            $all_products = array_merge($all_products, $bm_products);
        }

        $this->plugin->logger->log('Fetched ' . count($all_products) . ' products from data sources');

        return $all_products;
    }

    /**
     * Fetch BK image database
     */
    private function fetch_bk_image_database() {
        if (!empty($this->bk_image_db)) {
            return; // Already cached
        }

        $this->plugin->logger->log("Fetching BK image database");

        $response = wp_remote_get($this->api_endpoints['BK_IMAGES'], [
            'timeout' => 30,
            'headers' => ['Accept' => 'application/json'],
        ]);

        if (is_wp_error($response)) {
            $this->plugin->logger->log("Error fetching BK images: " . $response->get_error_message(), 'ERROR');
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['myndir']) && is_array($data['myndir'])) {
            // Create lookup by ItemId
            foreach ($data['myndir'] as $item) {
                if (isset($item['id']) && isset($item['photourl'])) {
                    $this->bk_image_db[$item['id']] = $item['photourl'];
                }
            }
            $this->plugin->logger->log("Loaded " . count($this->bk_image_db) . " BK product images");
        }
    }

    /**
     * Fetch products from BK supplier (Klettur)
     */
    private function fetch_from_bk($api_url) {
        $this->plugin->logger->log("Fetching products from BK supplier: {$api_url}");

        // First fetch image database
        $this->fetch_bk_image_database();

        $response = wp_remote_get($api_url, [
            'timeout' => 30,
            'headers' => ['Accept' => 'application/json'],
        ]);

        if (is_wp_error($response)) {
            $this->plugin->logger->log("API Error (BK): " . $response->get_error_message(), 'ERROR');
            return [];
        }

        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            $this->plugin->logger->log("API returned status {$status_code} for BK", 'ERROR');
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->plugin->logger->log("JSON decode error for BK: " . json_last_error_msg(), 'ERROR');
            return [];
        }

        if (!is_array($data)) {
            $this->plugin->logger->log("Invalid data format from BK", 'ERROR');
            return [];
        }

        // Aggregate products by ItemId (sum quantities from same location)
        $aggregated = [];
        foreach ($data as $product) {
            if (!isset($product['INVENTLOCATIONID']) || $product['INVENTLOCATIONID'] !== 'HJ-S') {
                continue;
            }

            $item_id = $product['ItemId'];
            if (!isset($aggregated[$item_id])) {
                $aggregated[$item_id] = $product;
                // Map image from database
                if (isset($this->bk_image_db[$item_id])) {
                    $aggregated[$item_id]['photourl'] = $this->bk_image_db[$item_id];
                }
                // Apply 24% VAT
                $aggregated[$item_id]['Price'] *= 1.24;
            } else {
                // Add quantities
                $aggregated[$item_id]['QTY'] += $product['QTY'];
            }
        }

        // Filter and normalize
        $products = [];
        foreach ($aggregated as $product) {
            // Filter: QTY >= 4, RimSize >= 13, valid tire format
            if (
                isset($product['QTY']) && $product['QTY'] >= 4 &&
                isset($product['RimSize']) && $product['RimSize'] >= 13 &&
                isset($product['ItemName']) && preg_match("/^\d{2,3}[\/x]\d{2}(?:,\d{1,2})?R/", $product['ItemName']) === 1
            ) {
                $normalized = $this->normalize_bk_product($product);
                if ($normalized) {
                    $products[] = $normalized;
                }
            }
        }

        $this->plugin->logger->log("Fetched " . count($products) . " products from BK");
        return $products;
    }

    /**
     * Fetch products from BM supplier (Mitra) - includes 4 API endpoints
     */
    private function fetch_from_bm($api_url) {
        $this->plugin->logger->log("Fetching products from BM supplier: {$api_url}");

        // Mitra has 4 endpoints
        $endpoints = [
            $api_url,
            $api_url . '?g=1',
            $api_url . '?g=2',
            $api_url . '?g=3',
        ];

        $all_data = [];
        foreach ($endpoints as $endpoint) {
            $response = wp_remote_get($endpoint, [
                'timeout' => 30,
                'headers' => ['Accept' => 'application/json'],
            ]);

            if (is_wp_error($response)) {
                $this->plugin->logger->log("API Error (BM - {$endpoint}): " . $response->get_error_message(), 'ERROR');
                continue;
            }

            $status_code = wp_remote_retrieve_response_code($response);
            if ($status_code !== 200) {
                $this->plugin->logger->log("API returned status {$status_code} for BM endpoint {$endpoint}", 'ERROR');
                continue;
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->plugin->logger->log("JSON decode error for BM endpoint {$endpoint}: " . json_last_error_msg(), 'ERROR');
                continue;
            }

            if (is_array($data)) {
                $all_data = array_merge($all_data, $data);
            }
        }

        // Process BM products
        $products = [];
        foreach ($all_data as $product) {
            $normalized = $this->normalize_bm_product($product);
            if ($normalized) {
                $products[] = $normalized;
            }
        }

        $this->plugin->logger->log("Fetched " . count($products) . " products from BM");
        return $products;
    }

    /**
     * Normalize BK product data
     * Maps ALL fields needed for attribute extraction and product creation
     */
    private function normalize_bk_product($product) {
        if (empty($product['ItemId']) || empty($product['ItemName'])) {
            return null;
        }

        $sku = $product['ItemId'] . '-BK';
        $price = isset($product['Price']) ? floatval($product['Price']) : 0;

        return [
            // Core fields
            'sku' => sanitize_text_field($sku),
            'ItemId' => sanitize_text_field($product['ItemId']),
            'ItemName' => sanitize_text_field($product['ItemName']),
            'Price' => $price,
            'QTY' => isset($product['QTY']) ? intval($product['QTY']) : 0,
            'INVENTLOCATIONID' => 'HJ-S',

            // Dimension fields
            'Width' => isset($product['Width']) ? $product['Width'] : '',
            'Height' => isset($product['Height']) ? $product['Height'] : '',
            'RimSize' => isset($product['RimSize']) ? intval($product['RimSize']) : 0,

            // Image fields
            'photourl' => isset($product['photourl']) ? esc_url_raw($product['photourl']) : '',
            'galleryPhotourl' => isset($product['galleryPhotourl']) ? esc_url_raw($product['galleryPhotourl']) : '',

            // EU Label
            'EuSheeturl' => isset($product['EuSheeturl']) ? esc_url_raw($product['EuSheeturl']) : '',

            // Tracking fields (required by sync manager)
            'api_id' => sanitize_text_field($product['ItemId']),
            'last_modified' => current_time('mysql'),  // API doesn't provide this, use current time

            // Meta
            'supplier' => 'BK',
            'UnitId' => 'stk',
        ];
    }

    /**
     * Normalize BM product data
     * Maps ALL fields needed for attribute extraction and product creation
     */
    private function normalize_bm_product($product) {
        if (empty($product['product_number']) || empty($product['title'])) {
            return null;
        }

        // Special handling for VN0000375
        $title = $product['title'];
        $diameter = isset($product['diameter']) ? $product['diameter'] : '';

        if ($product['product_number'] === 'VN0000375') {
            if (strlen($title) >= 8) {
                $title = substr($title, 0, 6) . ' ' . substr($title, 6);
            }
            $diameter = "16";
        }

        $sku = $product['product_number'] . '-BM';

        // Build image URLs
        $image_url = '';
        if (isset($product['card_image']) && !empty($product['card_image'])) {
            $image_url = 'https:' . $product['card_image'];
        }

        $gallery_url = '';
        if (isset($product['extra_images']) && is_array($product['extra_images']) && count($product['extra_images']) > 0) {
            $total_images = count($product['extra_images']);
            $gallery_url = 'https:' . $product['extra_images'][$total_images - 1];
        }

        // EU Label URL
        $eu_label = '';
        if (isset($product['eprel']) && !empty($product['eprel'])) {
            $eu_label = 'https://eprel.ec.europa.eu/screen/product/tyres/' . $product['eprel'];
        }

        return [
            // Core fields (using BK naming for consistency)
            'sku' => sanitize_text_field($sku),
            'ItemId' => sanitize_text_field($product['product_number']),
            'ItemName' => sanitize_text_field($title),
            'Price' => isset($product['price']) ? floatval($product['price']) : 0,
            'QTY' => isset($product['inventory']) ? intval($product['inventory']) : 0,
            'INVENTLOCATIONID' => 'Mitra',

            // Dimension fields
            'Width' => isset($product['width']) ? $product['width'] : '',
            'Height' => isset($product['aspect_ratio']) ? $product['aspect_ratio'] : '',
            'RimSize' => !empty($diameter) ? intval($diameter) : 0,

            // Image fields
            'photourl' => $image_url,
            'galleryPhotourl' => $gallery_url,

            // EU Label
            'EuSheeturl' => $eu_label,

            // Type from group
            'type' => isset($product['group']['title']) ? $product['group']['title'] : '',

            // Producer ID for brand mapping
            'producer' => isset($product['producer']) ? $product['producer'] : null,

            // Tracking fields (required by sync manager)
            'api_id' => sanitize_text_field($product['product_number']),
            'last_modified' => current_time('mysql'),  // API doesn't provide this, use current time

            // Meta
            'supplier' => 'BM',
            'UnitId' => 'stk',
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
}
