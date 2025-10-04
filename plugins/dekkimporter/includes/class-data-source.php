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
     *
     * @var DekkImporter
     */
    private $plugin;

    /**
     * API endpoints
     *
     * @var array<string, string>
     */
    private $api_endpoints = [];

    /**
     * BK Image database cache
     *
     * @var array<string, string>
     */
    private $bk_image_db = [];

    /**
     * Constructor
     *
     * @param DekkImporter $plugin Plugin instance
     */
    public function __construct($plugin) {
        $this->plugin = $plugin;

        // Load API configuration
        $options = get_option('dekkimporter_options', []);
        $this->api_endpoints = [
            'BK' => $options['dekkimporter_bk_api_url'] ?? '',
            'BK_IMAGES' => 'https://bud.klettur.is/wp-content/themes/bud.klettur.is/json/myndir.php',
            'BM' => $options['dekkimporter_bm_api_url'] ?? '',
        ];
    }

    /**
     * Fetch products from all data sources
     *
     * @return array<int, array> Combined products from all suppliers
     */
    public function fetch_products(): array {
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
     *
     * @return void
     */
    private function fetch_bk_image_database(): void {
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

        // Validate data structure before accessing
        if (!is_array($data) || !isset($data['myndir'])) {
            $this->plugin->logger->log("Invalid BK image database structure", 'WARNING');
            return;
        }

        if (!is_array($data['myndir'])) {
            $this->plugin->logger->log("BK image database 'myndir' is not an array", 'WARNING');
            return;
        }

        // Create lookup by ItemId - store entire record for EU label data
        foreach ($data['myndir'] as $item) {
            if (isset($item['id'])) {
                $this->bk_image_db[$item['id']] = $item;  // Store entire record, not just photourl
            }
        }
        $this->plugin->logger->log("Loaded " . count($this->bk_image_db) . " BK product images");
    }

    /**
     * Fetch products from BK supplier (Klettur)
     *
     * @param string $api_url API endpoint URL
     * @return array<int, array> Array of normalized products
     */
    private function fetch_from_bk(string $api_url): array {
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
            // Validate required fields before accessing
            if (!isset($product['INVENTLOCATIONID']) || $product['INVENTLOCATIONID'] !== 'HJ-S') {
                continue;
            }

            if (!isset($product['ItemId'])) {
                $this->plugin->logger->log("BK product missing ItemId, skipping", 'WARNING');
                continue;
            }

            $item_id = $product['ItemId'];
            if (!isset($aggregated[$item_id])) {
                $aggregated[$item_id] = $product;
                // Map image and EU label data from database
                if (isset($this->bk_image_db[$item_id])) {
                    $image_data = $this->bk_image_db[$item_id];
                    // Set photourl
                    if (isset($image_data['photourl'])) {
                        $aggregated[$item_id]['photourl'] = $image_data['photourl'];
                    }
                    // Extract eprel ID from external_url for EU label
                    if (isset($image_data['external_url']) && !empty($image_data['external_url'])) {
                        // external_url format: https://eprel.ec.europa.eu/qr/529803
                        $url_parts = parse_url($image_data['external_url']);
                        if (isset($url_parts['path'])) {
                            $eprel_id = basename($url_parts['path']);
                            if (!empty($eprel_id) && is_numeric($eprel_id)) {
                                $aggregated[$item_id]['eprel'] = $eprel_id;
                            }
                        }
                    }
                }
                // Apply 24% VAT to price if it exists
                if (isset($aggregated[$item_id]['Price'])) {
                    $aggregated[$item_id]['Price'] *= 1.24;
                }
            } else {
                // Add quantities if both exist
                if (isset($product['QTY']) && isset($aggregated[$item_id]['QTY'])) {
                    $aggregated[$item_id]['QTY'] += $product['QTY'];
                }
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
     *
     * @param string $api_url API endpoint URL
     * @return array<int, array> Array of normalized products
     */
    private function fetch_from_bm(string $api_url): array {
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
     *
     * @param array $product Raw product data from BK API
     * @return array|null Normalized product data or null if invalid
     */
    private function normalize_bk_product(array $product): ?array {
        if (empty($product['ItemId']) || empty($product['ItemName'])) {
            return null;
        }

        $sku = $product['ItemId'] . '-BK';
        $price = isset($product['Price']) ? floatval($product['Price']) : 0;

        // EU Label URLs (based on dekkimporter-7.php lines 1562-1569)
        // Direct image URL pattern: https://eprel.ec.europa.eu/labels/tyres/Label_{ID}.png
        // Fallback to PDF if PNG doesn't exist (handled in upload function)
        $eu_label_image = '';
        $eu_label_page = '';
        if (isset($product['eprel']) && !empty($product['eprel'])) {
            $eprel_id = sanitize_text_field($product['eprel']);
            // Direct image URL - try PNG first (upload function will fallback to PDF)
            $eu_label_image = 'https://eprel.ec.europa.eu/labels/tyres/Label_' . $eprel_id . '.png';
            // Page URL for product description
            $eu_label_page = 'https://eprel.ec.europa.eu/screen/product/tyres/' . $eprel_id;
        }

        return [
            // Core fields
            'sku' => sanitize_text_field($sku),
            'ItemId' => sanitize_text_field($product['ItemId']),
            'ItemName' => sanitize_text_field($product['ItemName']),
            'Price' => $price,
            'QTY' => isset($product['QTY']) ? intval($product['QTY']) : 0,
            'INVENTLOCATIONID' => 'HJ-S',

            // Dimension fields (null coalescing for consistency)
            'Width' => $product['Width'] ?? '',
            'Height' => $product['Height'] ?? '',
            'RimSize' => isset($product['RimSize']) ? intval($product['RimSize']) : 0,

            // Image fields (null coalescing for consistency)
            'photourl' => isset($product['photourl']) ? esc_url_raw($product['photourl']) : '',
            'galleryPhotourl' => isset($product['galleryPhotourl']) ? esc_url_raw($product['galleryPhotourl']) : '',

            // EU Label (direct image URL for download)
            'EuSheeturl' => $eu_label_image,
            // EU Label page URL (for product description)
            'EuSheetPageUrl' => $eu_label_page,

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
     *
     * @param array $product Raw product data from BM API
     * @return array|null Normalized product data or null if invalid
     */
    private function normalize_bm_product(array $product): ?array {
        if (empty($product['product_number']) || empty($product['title'])) {
            return null;
        }

        // Special handling for VN0000375
        $title = $product['title'];
        $diameter = $product['diameter'] ?? '';

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

        // EU Label URLs (based on dekkimporter-7.php lines 1562-1569)
        // Direct image URL pattern: https://eprel.ec.europa.eu/labels/tyres/Label_{ID}.png
        // Fallback to PDF if PNG doesn't exist (handled in upload function)
        $eu_label_image = '';
        $eu_label_page = '';
        if (isset($product['eprel']) && !empty($product['eprel'])) {
            $eprel_id = sanitize_text_field($product['eprel']);
            // Direct image URL - try PNG first (upload function will fallback to PDF)
            $eu_label_image = 'https://eprel.ec.europa.eu/labels/tyres/Label_' . $eprel_id . '.png';
            // Page URL for product description
            $eu_label_page = 'https://eprel.ec.europa.eu/screen/product/tyres/' . $eprel_id;
        }

        return [
            // Core fields (using BK naming for consistency)
            'sku' => sanitize_text_field($sku),
            'ItemId' => sanitize_text_field($product['product_number']),
            'ItemName' => sanitize_text_field($title),
            'Price' => isset($product['price']) ? floatval($product['price']) : 0,
            'QTY' => isset($product['inventory']) ? intval($product['inventory']) : 0,
            'INVENTLOCATIONID' => 'Mitra',

            // Dimension fields (null coalescing for consistency)
            'Width' => $product['width'] ?? '',
            'Height' => $product['aspect_ratio'] ?? '',
            'RimSize' => !empty($diameter) ? intval($diameter) : 0,

            // Image fields
            'photourl' => $image_url,
            'galleryPhotourl' => $gallery_url,

            // EU Label (direct image URL for download)
            'EuSheeturl' => $eu_label_image,
            // EU Label page URL (for product description)
            'EuSheetPageUrl' => $eu_label_page,

            // Type from group (null coalescing for consistency)
            'type' => $product['group']['title'] ?? '',

            // Producer ID for brand mapping (null coalescing for consistency)
            'producer' => $product['producer'] ?? null,

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
     * @return array<int, string> Array of active SKUs
     */
    public function get_active_skus(): array {
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
