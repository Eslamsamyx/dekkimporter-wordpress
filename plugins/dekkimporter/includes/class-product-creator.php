<?php
/**
 * Product Creator class for DekkImporter
 * 100% Feature Parity with Original Plugin
 * Handles product creation with attributes, variations, categories, images
 */

if (!defined('ABSPATH')) {
    exit;
}

class DekkImporter_Product_Creator {
    /**
     * Plugin instance
     *
     * @var DekkImporter
     */
    private $plugin;

    /**
     * Constructor
     *
     * @param DekkImporter $plugin Plugin instance
     */
    public function __construct($plugin) {
        $this->plugin = $plugin;
    }

    /**
     * Create or update product with complete logic from original plugin
     *
     * @param array $item Product data from API (normalized format)
     * @return int|false Product ID on success, false on failure
     */
    public function create_product(array $item) {
        // Get markup setting (null coalescing for consistency)
        $options = get_option('dekkimporter_options', []);
        $markup = (int)($options['dekkimporter_field_markup'] ?? 400);

        // Calculate target price (subtract markup) - BUG FIX #3: Prevent negative prices
        $api_price = floatval($item['Price'] ?? 0);
        $target_price = max(0, $api_price - $markup);

        if ($target_price === 0) {
            $this->plugin->logger->log("Warning: Calculated price is 0 for {$item['sku']} (API price: {$api_price}, markup: {$markup})", 'WARNING');
        }

        // Extract all product attributes from name and data
        $attributes = DekkImporter_Product_Helpers::build_attributes($item);

        // Build standardized product name
        $product_name = DekkImporter_Product_Helpers::build_name($attributes);

        // Determine if this should be a variable product (studdable tires)
        $is_variable_product = isset($attributes['pa_negla']);

        // Create product object
        if ($is_variable_product) {
            $product = new WC_Product_Variable();
            $this->plugin->logger->log("Creating variable product: {$item['ItemId']}");
        } else {
            $product = new WC_Product_Simple();
            $this->plugin->logger->log("Creating simple product: {$item['ItemId']}");
        }

        // Set basic product data
        $product->set_status('publish'); // Publish immediately
        $product->set_sku($item['sku']);
        $product->set_regular_price((string)$target_price);
        $product->set_price((string)$target_price); // WooCommerce best practice: set both _price and _regular_price
        $product->set_name($product_name);

        // Set weight based on rim size
        if (isset($item['RimSize'])) {
            $weight = DekkImporter_Product_Helpers::get_weight($item['RimSize']);
            $product->set_weight($weight);
        }

        // Set stock management with offset (reserve 4 units)
        $product->set_manage_stock(true);
        $new_stock = max(0, (int)$item['QTY'] - 4);
        $product->set_stock_quantity($new_stock);
        $product->set_stock_status($new_stock > 0 ? 'instock' : 'outofstock');

        // Upload and set images (null coalescing for consistency)
        $photourl = $item['photourl'] ?? '';
        if (!empty($photourl)) {
            $image_id = DekkImporter_Product_Helpers::upload_image($photourl);
            if ($image_id !== null) {
                $product->set_image_id($image_id);
                $this->plugin->logger->log("Main image uploaded: {$item['ItemId']}");
            }
        }

        // Note: Gallery images will be set after product is saved
        // EU Sheet management handles gallery to keep ONLY EU label

        // Prepare and set WooCommerce attributes
        $wc_attributes = DekkImporter_Product_Helpers::wc_prepare_product_attributes($attributes);
        $product->set_attributes($wc_attributes);

        // Set categories based on tire type
        $categories = DekkImporter_Product_Helpers::get_product_categories($attributes);
        $product->set_category_ids($categories);

        // Determine tire type for product description
        $tire_type = 'summer'; // Default
        if (isset($attributes['pa_gerd']['term_names'])) {
            $types = $attributes['pa_gerd']['term_names'];

            if (in_array('Sumardekk', $types, true)) {
                $tire_type = 'summer';
            } elseif (in_array('Vetrardekk', $types, true)) {
                $tire_type = 'winter';
            } elseif (in_array('Jeppadekk', $types, true)) {
                $tire_type = 'allseason';
            }
        }

        // Set product description with EU label (null coalescing for consistency)
        $eu_label_url = $item['EuSheeturl'] ?? '';
        $description = DekkImporter_Product_Helpers::product_desc($tire_type, $eu_label_url);
        $product->set_description($description);

        // Save product
        $product_id = $product->save();

        if (!$product_id) {
            $this->plugin->logger->log("Failed to create product: {$item['ItemId']}", 'ERROR');
            return false;
        }

        $this->plugin->logger->log("Product saved: {$product_name} (ID: {$product_id})");

        // Set product_visibility taxonomy terms based on stock (v7 parity - lines 860-866)
        if ($new_stock > 0) {
            wp_remove_object_terms($product_id, 'outofstock', 'product_visibility');
            $this->plugin->logger->log("Removed 'outofstock' term for new product ID $product_id.");
        } else {
            wp_set_object_terms($product_id, 'outofstock', 'product_visibility', true);
            $this->plugin->logger->log("Assigned 'outofstock' term for new product ID $product_id.");
        }

        // Add EU Sheet to gallery (keeps ONLY EU sheet, removes other images)
        $eusheet_url = $item['EuSheeturl'] ?? '';
        if (!empty($eusheet_url)) {
            $result = DekkImporter_Product_Helpers::add_eusheet_to_gallery($product_id, $eusheet_url);
            if ($result) {
                $this->plugin->logger->log("EU Sheet processed for: {$item['ItemId']}");
            } else {
                $this->plugin->logger->log("EU Sheet processing failed for: {$item['ItemId']}", 'WARNING');
            }
        }

        // Create variations for variable products (studdable tires)
        if ($is_variable_product) {
            $this->create_variations($product_id, $item, $target_price);
        }

        return $product_id;
    }

    /**
     * Create product variations for studdable tires
     * Variation 1: Without studs (base price)
     * Variation 2: With studs (+3000 ISK for <18", +4000 ISK for >=18")
     *
     * @param int $parent_id Parent product ID
     * @param array $item Product data
     * @param float $base_price Base price (before stud markup)
     * @return void
     */
    private function create_variations(int $parent_id, array $item, float $base_price): void {
        $sku = $item['sku'];

        // Variation 1: Without studs
        $variation1 = new WC_Product_Variation();
        $variation1->set_attributes(['pa_negla' => 'nei']);
        $variation1->set_regular_price((string)$base_price);
        $variation1->set_price((string)$base_price);  // BUG FIX #4: Set both _price and _regular_price
        $variation1->set_sku($sku . '-0');
        $variation1->set_parent_id($parent_id);
        $variation1->set_manage_stock(false); // Parent manages stock
        $variation1_id = $variation1->save();

        $this->plugin->logger->log("Variation created (no studs): {$sku}-0 (ID: {$variation1_id})");

        // Variation 2: With studs (add 3000-4000 ISK) - null coalescing for RimSize
        $rim_size = (int)($item['RimSize'] ?? 0);
        $stud_markup = $rim_size >= 18 ? 4000 : 3000;
        $studded_price = $base_price + $stud_markup;

        $variation2 = new WC_Product_Variation();
        $variation2->set_attributes(['pa_negla' => 'ja']);
        $variation2->set_regular_price((string)$studded_price);
        $variation2->set_price((string)$studded_price);  // BUG FIX #4: Set both _price and _regular_price
        $variation2->set_sku($sku . '-1');
        $variation2->set_parent_id($parent_id);
        $variation2->set_manage_stock(false); // Parent manages stock
        $variation2_id = $variation2->save();

        $this->plugin->logger->log("Variation created (with studs): {$sku}-1 (ID: {$variation2_id}, +{$stud_markup} ISK)");
    }

    // BUG FIX #2: Removed dead code - update_product() method never called
    // Product updates are handled by class-product-updater.php
}
