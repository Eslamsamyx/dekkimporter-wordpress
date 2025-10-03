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
     */
    private $plugin;

    /**
     * Constructor
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
    public function create_product($item) {
        // Get markup setting
        $options = get_option('dekkimporter_options', []);
        $markup = isset($options['dekkimporter_field_markup']) ? (int)$options['dekkimporter_field_markup'] : 400;

        // Calculate target price (subtract markup)
        $target_price = $item['Price'] - $markup;

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

        // Upload and set images
        if (isset($item['photourl']) && !empty($item['photourl'])) {
            $image_id = DekkImporter_Product_Helpers::upload_image($item['photourl']);
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

        // Set product description with EU label
        $eu_label_url = isset($item['EuSheeturl']) ? $item['EuSheeturl'] : '';
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
        if (isset($item['EuSheeturl']) && !empty($item['EuSheeturl'])) {
            DekkImporter_Product_Helpers::add_eusheet_to_gallery($product_id, $item['EuSheeturl']);
            $this->plugin->logger->log("EU Sheet added to gallery: {$item['ItemId']}");
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
     */
    private function create_variations($parent_id, $item, $base_price) {
        $sku = $item['sku'];

        // Variation 1: Without studs
        $variation1 = new WC_Product_Variation();
        $variation1->set_attributes(['pa_negla' => 'nei']);
        $variation1->set_regular_price((string)$base_price);
        $variation1->set_sku($sku . '-0');
        $variation1->set_parent_id($parent_id);
        $variation1->set_manage_stock(false); // Parent manages stock
        $variation1_id = $variation1->save();

        $this->plugin->logger->log("Variation created (no studs): {$sku}-0 (ID: {$variation1_id})");

        // Variation 2: With studs (add 3000-4000 ISK)
        $stud_markup = $item['RimSize'] >= 18 ? 4000 : 3000;
        $studded_price = $base_price + $stud_markup;

        $variation2 = new WC_Product_Variation();
        $variation2->set_attributes(['pa_negla' => 'ja']);
        $variation2->set_regular_price((string)$studded_price);
        $variation2->set_sku($sku . '-1');
        $variation2->set_parent_id($parent_id);
        $variation2->set_manage_stock(false); // Parent manages stock
        $variation2_id = $variation2->save();

        $this->plugin->logger->log("Variation created (with studs): {$sku}-1 (ID: {$variation2_id}, +{$stud_markup} ISK)");
    }

    /**
     * Update existing product
     * Updates price, stock, and gallery images
     *
     * @param int $product_id Product ID
     * @param array $item Product data from API
     */
    public function update_product($product_id, $item) {
        $product = wc_get_product($product_id);
        if (!$product) {
            $this->plugin->logger->log("Product not found for update: ID {$product_id}", 'ERROR');
            return false;
        }

        // Get markup setting
        $options = get_option('dekkimporter_options', []);
        $markup = isset($options['dekkimporter_field_markup']) ? (int)$options['dekkimporter_field_markup'] : 400;
        $target_price = $item['Price'] - $markup;

        $updated = false;

        // Update price for simple products
        if ($product->is_type('simple')) {
            if ((string)$target_price !== $product->get_regular_price()) {
                $product->set_regular_price((string)$target_price);
                $updated = true;
                $this->plugin->logger->log("Price updated: {$item['sku']} -> {$target_price}");
            }
        }

        // Update price for variable products
        if ($product->is_type('variable')) {
            $children = array_map(function($id) {
                return wc_get_product($id);
            }, $product->get_children());

            if (isset($children[0]) && $children[0] !== null) {
                if ((string)$target_price !== $children[0]->get_regular_price()) {
                    $children[0]->set_regular_price((string)$target_price);
                    $children[0]->save();

                    // Update studded variation
                    $stud_markup = $item['RimSize'] >= 18 ? 4000 : 3000;
                    $studded_price = $target_price + $stud_markup;
                    $children[1]->set_regular_price((string)$studded_price);
                    $children[1]->save();

                    $updated = true;
                    $this->plugin->logger->log("Variation prices updated: {$item['sku']}");
                }
            }
        }

        // Update stock quantity
        if ($product->get_stock_quantity('edit') !== $item['QTY']) {
            $product->set_stock_quantity($item['QTY']);
            $updated = true;
            $this->plugin->logger->log("Stock updated: {$item['sku']} -> {$item['QTY']}");
        }

        // Update gallery images if EU label exists
        if (isset($item['EuSheeturl']) && !empty($item['EuSheeturl'])) {
            if (isset($item['galleryPhotourl']) && !empty($item['galleryPhotourl'])) {
                $gallery_image_id = DekkImporter_Product_Helpers::upload_image($item['galleryPhotourl']);
                if ($gallery_image_id !== null) {
                    $current_gallery_ids = $product->get_gallery_image_ids();

                    // Only add if not already in gallery
                    if (!in_array($gallery_image_id, $current_gallery_ids)) {
                        $current_gallery_ids[] = $gallery_image_id;
                        $product->set_gallery_image_ids($current_gallery_ids);
                        $updated = true;
                        $this->plugin->logger->log("Gallery image updated: {$item['sku']}");
                    }
                }
            }
        }

        if ($updated) {
            $product->save();
            return true;
        }

        return false;
    }
}
