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
     * Update product with 100% PARITY to dekkimporter-7.php (lines 535-675)
     *
     * Features from v7:
     * - Direct $wpdb updates for performance
     * - Title regeneration and comparison
     * - Description updates based on tire type
     * - Stock management with product_visibility terms
     * - Variable products: parent stock mgmt, variations no stock
     * - Updates BOTH _price AND _regular_price
     * - Featured image comparison by sanitized filename
     * - EU Sheet gallery update
     * - Sets post_modified timestamps
     *
     * @param int $product_id Product ID
     * @param array $item Product data from API (normalized format)
     * @return bool Success
     */
    public function update_product(int $product_id, array $item): bool {
        global $wpdb;

        $this->plugin->logger->log("Starting update for product ID: $product_id");

        // Track if product needs to be saved
        $needs_save = false;

        // Get post data
        $post = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE ID = %d", $product_id));
        if (!$post) {
            $this->plugin->logger->log("Product with ID $product_id not found.");
            return false;
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            return false;
        }

        $sku = $product->get_sku();
        $source = (strpos($sku, '-BK') !== false) ? 'Klettur' : ((strpos($sku, '-BM') !== false) ? 'Mitra' : 'Unknown');

        // Get markup setting
        $options = get_option('dekkimporter_options', []);
        $markup = isset($options['dekkimporter_field_markup']) ? (int)$options['dekkimporter_field_markup'] : 400;

        // BUG FIX #3: Prevent negative prices
        $api_price = isset($item['Price']) ? floatval($item['Price']) : 0;
        $target_price = max(0, $api_price - $markup);

        if ($target_price === 0) {
            $this->plugin->logger->log("Warning: Calculated price is 0 for product ID {$product_id} (API price: {$api_price}, markup: {$markup})", 'WARNING');
        }

        // Regenerate expected title
        $attributes = DekkImporter_Product_Helpers::build_attributes($item);
        $expected_title = DekkImporter_Product_Helpers::build_name($attributes);

        // Sanitize for database security
        $expected_title = sanitize_text_field($expected_title);

        // Update title if changed - WooCommerce CRUD
        if ($post->post_title !== $expected_title) {
            $product->set_name($expected_title);
            $this->plugin->logger->log("Updated title for product ID $product_id from '{$post->post_title}' to '$expected_title'.");
            $needs_save = true;
        } else {
            $this->plugin->logger->log("Title unchanged for product ID $product_id: '$expected_title'");
        }

        // Determine description type based on tire type
        $tire_type = 'default';
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

        // Generate expected description with EU label page URL (not image URL)
        $eu_label_page_url = isset($item['EuSheetPageUrl']) ? $item['EuSheetPageUrl'] : '';
        $expected_description = DekkImporter_Product_Helpers::product_desc($tire_type, $eu_label_page_url);

        // Sanitize for database security (allow safe HTML)
        $expected_description = wp_kses_post($expected_description);

        // Update description if changed - WooCommerce CRUD
        if ($post->post_content !== $expected_description) {
            $product->set_description($expected_description);
            $this->plugin->logger->log("Updated description for product ID $product_id.");
            $needs_save = true;
        }

        // Stock and Price management - WooCommerce CRUD Best Practice
        $new_stock = max(0, (int)$item['QTY'] - 4);
        $current_stock = $product->get_stock_quantity();
        $current_price = $product->get_price();

        $this->plugin->logger->log("Stock check for product ID $product_id: current=$current_stock, new=$new_stock, api_qty={$item['QTY']}");

        // Update stock using CRUD
        if ($current_stock !== $new_stock) {
            $product->set_manage_stock(true);
            $product->set_stock_quantity($new_stock);
            $product->set_stock_status($new_stock > 0 ? 'instock' : 'outofstock');
            $needs_save = true;
            $this->plugin->logger->log("Updated stock for product ID $product_id from $current_stock to $new_stock.");

            // WooCommerce 3.0+ handles product_visibility automatically, but we maintain compatibility
            if ($new_stock > 0) {
                wp_remove_object_terms($product_id, 'outofstock', 'product_visibility');
            } else {
                wp_set_object_terms($product_id, 'outofstock', 'product_visibility', true);
            }
        }

        // Update price using CRUD
        if ($current_price !== $target_price) {
            $product->set_regular_price((string)$target_price);
            $product->set_price((string)$target_price);
            $needs_save = true;
            $this->plugin->logger->log("Updated price for product ID $product_id from $current_price to $target_price.");
        } else {
            $this->plugin->logger->log("Price unchanged for product ID $product_id: $target_price");
        }

        // Handle variable products
        if ($product->is_type('variable')) {
            $variations = $product->get_children();

            // Update variation prices and clear their stock management
            foreach ($variations as $variation_id) {
                $variation = wc_get_product($variation_id);
                if (!$variation) {
                    continue;
                }

                // Clear variation stock management (parent handles it)
                $variation->set_manage_stock(false);
                $variation->set_stock_status('');

                // Update variation prices
                $attributes = $variation->get_attributes();

                // Check if this is "with studs" variation
                if (isset($attributes['pa_negla']) && $attributes['pa_negla'] === 'ja') {
                    // With studs: add stud markup based on rim size
                    $rim_size = isset($item['RimSize']) ? (int)$item['RimSize'] : 0;
                    $stud_markup = $rim_size >= 18 ? 4000 : 3000;
                    $variation_price = $target_price + $stud_markup;
                    $this->plugin->logger->log("Variation {$variation_id} with studs: price={$variation_price} (base {$target_price} + markup {$stud_markup})");
                } else {
                    // Without studs: base price
                    $variation_price = $target_price;
                    $this->plugin->logger->log("Variation {$variation_id} without studs: price={$variation_price}");
                }

                // Update variation prices using CRUD
                $variation->set_regular_price((string)$variation_price);
                $variation->set_price((string)$variation_price);
                $variation->save();

                // Clear variation cache
                wc_delete_product_transients($variation_id);
            }

            $this->plugin->logger->log("Updated prices for all variations of product ID $product_id.");
        }

        // Featured image update with filename comparison
        if (!empty($item['photourl'])) {
            $photourl = $item['photourl'];

            // Handle no-pic placeholder
            if (strpos(basename($photourl), 'no-pic') === 0) {
                $no_pic_url = 'https://dekk1.is/wp-content/uploads/2024/10/no-pic_width-584.jpg';
                $no_pic_id = DekkImporter_Product_Helpers::get_attachment_id_by_url($no_pic_url);

                if ($no_pic_id) {
                    set_post_thumbnail($product_id, $no_pic_id);
                    $this->plugin->logger->log("Set 'no-pic' image for product ID $product_id.");
                } else {
                    $this->plugin->logger->log("Failed to find 'no-pic' image for product ID $product_id.");
                }
            } else {
                // Compare current and new image filenames
                $current_featured_image_id = get_post_thumbnail_id($product_id);
                $current_image_url = $current_featured_image_id ? wp_get_attachment_url($current_featured_image_id) : '';
                $current_image_filename = basename(parse_url($current_image_url, PHP_URL_PATH));
                $new_image_filename = basename(parse_url($photourl, PHP_URL_PATH));

                // Sanitize filenames for comparison
                $sanitized_current = $this->sanitize_filename($current_image_filename);
                $sanitized_new = $this->sanitize_filename($new_image_filename);

                if ($sanitized_current !== $sanitized_new) {
                    $uploaded_image_id = DekkImporter_Product_Helpers::upload_image($photourl);
                    if ($uploaded_image_id) {
                        set_post_thumbnail($product_id, $uploaded_image_id);
                        $this->plugin->logger->log("Updated featured image for product ID $product_id with new image.");
                    } else {
                        $this->plugin->logger->log("Failed to upload new image for product ID $product_id.");
                    }
                } else {
                    $this->plugin->logger->log("Featured image for product ID $product_id is already up-to-date.");
                }
            }
        }

        // Update EU Sheet gallery
        if (!empty($item['EuSheeturl'])) {
            $result = DekkImporter_Product_Helpers::add_eusheet_to_gallery($product_id, $item['EuSheeturl']);
            if ($result) {
                $this->plugin->logger->log("EU Sheet processed for product ID $product_id");
            } else {
                $this->plugin->logger->log("EU Sheet processing failed for product ID $product_id", 'WARNING');
            }
        }

        // Save product if changes were made
        if ($needs_save) {
            $product->save(); // WordPress handles post_modified automatically
            $this->plugin->logger->log("Saved all updates for product ID $product_id.");

            // WooCommerce action hooks - notify other plugins of changes
            if ($current_stock !== $new_stock) {
                do_action('woocommerce_product_set_stock', $product);
                do_action('woocommerce_product_stock_status_changed', $product_id, $product->get_stock_status());
            }

            // Clear product cache - WooCommerce best practice
            $this->clear_product_cache($product_id);
        }

        $this->plugin->logger->log("Finished update for product ID $product_id");
        return true;
    }

    /**
     * Sanitize filename for comparison (from v7 line 683)
     *
     * @param string $filename The filename to sanitize
     * @return string The sanitized filename
     */
    private function sanitize_filename(string $filename): string {
        if (empty($filename)) {
            return '';
        }

        $path_info = pathinfo($filename);
        $name = str_replace('.', '_', $path_info['filename']);
        $extension = isset($path_info['extension']) ? strtolower($path_info['extension']) : '';

        return $name . '.' . $extension;
    }

    /**
     * Clear WooCommerce product cache
     * WooCommerce Best Practice: Always clear caches after product updates
     *
     * @param int $product_id Product ID
     * @return void
     */
    private function clear_product_cache(int $product_id): void {
        // Clear WooCommerce transients
        wc_delete_product_transients($product_id);

        // Clear object cache
        wp_cache_delete('product-' . $product_id, 'products');
        wp_cache_delete('woocommerce_product_' . $product_id, 'products');

        // Clear post cache
        clean_post_cache($product_id);

        // Clear variable product cache if applicable
        $product = wc_get_product($product_id);
        if ($product && $product->is_type('variable')) {
            foreach ($product->get_children() as $variation_id) {
                wc_delete_product_transients($variation_id);
                wp_cache_delete('product-' . $variation_id, 'products');
                clean_post_cache($variation_id);
            }
        }

        $this->plugin->logger->log("Cleared product cache for ID $product_id");
    }
}
