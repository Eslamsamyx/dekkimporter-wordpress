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
    public function update_product($product_id, $item) {
        global $wpdb;

        $this->plugin->logger->log("Starting update for product ID: $product_id");

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
        $target_price = $item['Price'] - $markup;

        // Regenerate expected title
        $attributes = DekkImporter_Product_Helpers::build_attributes($item);
        $expected_title = DekkImporter_Product_Helpers::build_name($attributes);

        // Sanitize for database security
        $expected_title = sanitize_text_field($expected_title);

        // Update title if changed (direct $wpdb for performance)
        if ($post->post_title !== $expected_title) {
            $wpdb->update(
                $wpdb->posts,
                ['post_title' => $expected_title],
                ['ID' => $product_id],
                ['%s'],  // Data format
                ['%d']   // Where format
            );
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

        // Generate expected description
        $eu_label_url = isset($item['EuSheeturl']) ? $item['EuSheeturl'] : '';
        $expected_description = DekkImporter_Product_Helpers::product_desc($tire_type, $eu_label_url);

        // Sanitize for database security (allow safe HTML)
        $expected_description = wp_kses_post($expected_description);

        // Update description if changed (direct $wpdb)
        if ($post->post_content !== $expected_description) {
            $wpdb->update(
                $wpdb->posts,
                ['post_content' => $expected_description],
                ['ID' => $product_id],
                ['%s'],  // Data format
                ['%d']   // Where format
            );
            $this->plugin->logger->log("Updated description for product ID $product_id.");
        }

        // Stock management with product_visibility terms
        $needs_save = false;
        $new_stock = max(0, (int)$item['QTY'] - 4);
        $current_stock = $product->get_stock_quantity();

        $this->plugin->logger->log("Stock check for product ID $product_id: current=$current_stock, new=$new_stock, api_qty={$item['QTY']}");

        if ($product->is_type('variable')) {
            // Variable product: parent manages stock, variations don't
            update_post_meta($product_id, '_manage_stock', 'yes');
            update_post_meta($product_id, '_stock', $new_stock);
            update_post_meta($product_id, '_stock_status', ($new_stock > 0) ? 'instock' : 'outofstock');

            // Set/remove product_visibility terms
            if ($new_stock > 0) {
                wp_remove_object_terms($product_id, 'outofstock', 'product_visibility');
            } else {
                wp_set_object_terms($product_id, 'outofstock', 'product_visibility', true);
            }

            // Clear variation stock management (parent handles it)
            $variations = $product->get_children();
            foreach ($variations as $variation_id) {
                update_post_meta($variation_id, '_manage_stock', 'no');
                update_post_meta($variation_id, '_stock_status', '');
            }

            $needs_save = true;
            $this->plugin->logger->log("Updated stock for variable product ID $product_id and its variations.");
        } else {
            // Simple product: direct stock update
            if (get_post_meta($product_id, '_stock', true) != $new_stock) {
                update_post_meta($product_id, '_stock', $new_stock);
                update_post_meta($product_id, '_stock_status', ($new_stock > 0) ? 'instock' : 'outofstock');
                update_post_meta($product_id, '_manage_stock', 'yes');

                // Set/remove product_visibility terms
                if ($new_stock > 0) {
                    wp_remove_object_terms($product_id, 'outofstock', 'product_visibility');
                    $this->plugin->logger->log("Removed 'outofstock' term for simple product ID $product_id.");
                } else {
                    wp_set_object_terms($product_id, 'outofstock', 'product_visibility', true);
                    $this->plugin->logger->log("Assigned 'outofstock' term for simple product ID $product_id.");
                }

                $needs_save = true;
                $this->plugin->logger->log("Updated stock for simple product ID $product_id to $new_stock.");
            }
        }

        // Update price (set BOTH _price and _regular_price like v7)
        $current_price = get_post_meta($product_id, '_price', true);
        if ($current_price != $target_price) {
            update_post_meta($product_id, '_regular_price', $target_price);
            update_post_meta($product_id, '_price', $target_price);
            $needs_save = true;
            $this->plugin->logger->log("Updated price for product ID $product_id from $current_price to $target_price.");
        } else {
            $this->plugin->logger->log("Price unchanged for product ID $product_id: $target_price");
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
            DekkImporter_Product_Helpers::add_eusheet_to_gallery($product_id, $item['EuSheeturl']);
            $this->plugin->logger->log("Checked and handled EuSheet image for product ID $product_id.");
        }

        // Update post_modified timestamps (like v7)
        if ($needs_save) {
            $wpdb->update(
                $wpdb->posts,
                [
                    'post_modified' => current_time('mysql'),
                    'post_modified_gmt' => current_time('mysql', 1)
                ],
                ['ID' => $product_id],
                ['%s', '%s'],  // Data format
                ['%d']         // Where format
            );
            $this->plugin->logger->log("Saved all updates for product ID $product_id.");
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
    private function sanitize_filename($filename) {
        if (empty($filename)) {
            return '';
        }

        $path_info = pathinfo($filename);
        $name = str_replace('.', '_', $path_info['filename']);
        $extension = isset($path_info['extension']) ? strtolower($path_info['extension']) : '';

        return $name . '.' . $extension;
    }
}
