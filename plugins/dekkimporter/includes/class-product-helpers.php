<?php
/**
 * Product Helper Functions for DekkImporter
 * 100% Feature Parity with Original Plugin
 * Handles attribute extraction, name building, weight calculation, descriptions
 */

if (!defined('ABSPATH')) {
    exit;
}

class DekkImporter_Product_Helpers {
    /**
     * Extract all product attributes from API data
     * Ports complex regex logic from original plugin
     *
     * @param array $item Product data from API
     * @return array Extracted attributes
     */
    public static function build_attributes($item) {
        $attributes = [];
        $name = $item['ItemName'];

        // Helper function to add attribute (exact match to original)
        $add_attribute = function($key, $value, $visible = true, $variation = false) use (&$attributes) {
            $attr_key = 'pa_' . $key;
            if (isset($attributes[$attr_key])) {
                $attributes[$attr_key]['term_names'][] = $value;
            } else {
                $attributes[$attr_key] = [
                    'term_names' => [$value],
                    'is_visible' => $visible,
                    'for_variation' => $variation,
                ];
            }
        };

        // Extract dimensions
        $add_attribute('breidd', (string)$item['Width']);
        $add_attribute('haed', (string)$item['Height']);
        $add_attribute('tommur', 'R' . $item['RimSize']);

        // Cargo tire detection (MUST come first) - v7 regex with x and comma support
        if (preg_match('/^\d{2,3}[\/x]\d{2}(?:,\d{1,2})?R\d{2}(?:,\d)?C/', $name)) {
            $add_attribute('gerd', 'Cargo dekk(C)');
        }

        // All Terrain pattern detection
        if (preg_match('/\sAT(?!\/S)/', $name)) {
            $add_attribute('munstur', 'All Terrain(AT)');
        }

        // All Terrain All Seasons pattern detection
        if (strpos($name, 'AT/S') !== false) {
            $add_attribute('munstur', 'All Terrain All Seasons(AT/S)');
        }

        // OWL (Outline White Letters) detection
        if (strpos($name, 'OWL') !== false) {
            $add_attribute('gerd', 'Letur á dekki hvítt (OWL)');
        }

        // Detect brand and trim from name for subtype extraction
        if (isset($item['INVENTLOCATIONID']) && $item['INVENTLOCATIONID'] === 'Mitra' && isset($item['producer'])) {
            // BM Producer mapping (from dekkimporter-7)
            $producer_map = [
                1    => 'Sailun',
                14   => 'Renegade',
                15   => 'Bridgestone',
                16   => 'Firestone',
                null => 'Milestone',
            ];
            $producer_id = intval($item['producer']);
            $manufacturer = $producer_map[$producer_id] ?? 'Sailun';
            $add_attribute('dekkjaframleidandi', $manufacturer);
        } else {
            // BK brand detection - v7 logic: capture first word as brand
            if (preg_match('/^\s*([A-Za-z]+)\b/', $name, $matches)) {
                $brand = $matches[1];
                $add_attribute('dekkjaframleidandi', $brand);
                $name = str_replace($matches[0], '', $name);  // Remove brand from name
            }
        }

        // Detect studding status and trim from name
        if (preg_match('/(NEGLT|ón|\sneglanl)/iu', $name, $matches)) {
            $studded = ltrim($matches[0]);
            $pos = mb_strpos($name, $studded);
            $name = mb_substr($name, 0, $pos);  // Trim studding from name

            switch (mb_strtolower($studded)) {
                case 'neglt':
                    $add_attribute('negling', 'Nagladekk');
                    break;
                case 'ón':
                    $add_attribute('negling', 'Óneglanleg');
                    break;
                case 'neglanl':
                    $add_attribute('negling', 'Neglanleg');
                    $add_attribute('negla', 'Já', false, true);
                    $add_attribute('negla', 'Nei', false, true);
                    break;
            }
        }

        // Detect tire type (BM uses group title, BK uses regex)
        if (isset($item['INVENTLOCATIONID']) && $item['INVENTLOCATIONID'] === 'Mitra') {
            $add_attribute('gerd', $item['type']);
        } else {
            $type_regex = '/(vetr|jeppa|sumar|heil|vinnuvél|vagn|fram|aftur|drifd|burðar)/ui';
            if (preg_match_all($type_regex, $name, $matches)) {
                $pos = mb_strpos($name, $matches[0][0]);
                $name = mb_substr($name, 0, $pos);  // Trim type from name

                foreach ($matches[0] as $match) {
                    switch (mb_strtolower($match)) {
                        case 'vetr':
                            $add_attribute('gerd', 'Vetrardekk');
                            break;
                        case 'jeppa':
                            $add_attribute('gerd', 'Jeppadekk');
                            break;
                        case 'sumar':
                            $add_attribute('gerd', 'Sumardekk');
                            break;
                        case 'heil':
                            $add_attribute('gerd', 'Heilsársdekk');
                            break;
                        case 'vinnuvél':
                            $add_attribute('gerd', 'Vinnuvéladekk');
                            break;
                        case 'vagn':
                            $add_attribute('gerd', 'Vagnadekk');
                            break;
                        case 'fram':
                            $add_attribute('gerd', 'Framdekk');
                            break;
                        case 'aftur':
                            $add_attribute('gerd', 'Afturdekk');
                            break;
                        case 'drifd':
                            $add_attribute('gerd', 'Drifdekk');
                            break;
                        case 'burðar':
                            $add_attribute('gerd', 'Burðardekk(XL)');
                            break;
                    }
                }
            }
        }

        // Detect speed rating and load capacity, trim from name
        if (preg_match('/\s(\d{2,3}(?:\/\d{2,3})?)([H|L|S|T|Y|Q|R|V|W])/', $name, $matches)) {
            $pos = mb_strpos($name, $matches[0]);
            $name = mb_substr($name, 0, $pos);  // Trim speed/load from name

            // BM combines load+speed, BK separates them
            if (isset($item['INVENTLOCATIONID']) && $item['INVENTLOCATIONID'] === 'Mitra') {
                $add_attribute('burdargeta', $matches[1] . $matches[2]);  // e.g., "113W"
            } else {
                $add_attribute('burdargeta', $matches[1]);  // e.g., "113"
            }
            $add_attribute('hradi', $matches[2]);
        }

        // Remove "Tilboð" marker if present
        $extra = mb_strpos($name, 'Tilboð');
        if ($extra !== false) {
            $name = mb_substr($name, 0, $extra);
        }

        // Extract subtype from remaining name (progressive trimming approach)
        $name = trim($name);
        if ($name !== '') {
            // For BM products, remove first word
            if (isset($item['INVENTLOCATIONID']) && $item['INVENTLOCATIONID'] === 'Mitra') {
                $name = trim($name);
                $pos = mb_strpos($name, ' ');
                if ($pos !== false) {
                    $name = mb_substr($name, $pos + 1);
                }
            }

            $add_attribute('undirtegund', $name);
        }

        return $attributes;
    }

    /**
     * Build standardized product name from attributes
     * Format: {width}/{height}R{rim} - {brand} {subtype} - {studding} - {type}
     *
     * @param array $attributes Extracted attributes
     * @return string Standardized product name
     */
    public static function build_name($attributes) {
        $name_parts = [];

        // Part 1: Dimensions (225/45R17)
        $dimensions = '';
        if (isset($attributes['pa_breidd']['term_names'][0])) {
            $dimensions .= $attributes['pa_breidd']['term_names'][0];
        }
        if (isset($attributes['pa_haed']['term_names'][0])) {
            $dimensions .= '/' . $attributes['pa_haed']['term_names'][0];
        }
        if (isset($attributes['pa_tommur']['term_names'][0])) {
            $dimensions .= $attributes['pa_tommur']['term_names'][0];
        }

        if (!empty($dimensions)) {
            $name_parts[] = $dimensions;
        }

        // Part 2: Brand and Subtype (Nexen Winspike 3)
        $brand_section = '';
        if (isset($attributes['pa_dekkjaframleidandi']['term_names'][0])) {
            $brand_section .= $attributes['pa_dekkjaframleidandi']['term_names'][0];
        }
        if (isset($attributes['pa_undirtegund']['term_names'][0])) {
            $brand_section .= ' ' . $attributes['pa_undirtegund']['term_names'][0];
        }

        if (!empty($brand_section)) {
            $name_parts[] = $brand_section;
        }

        // Part 3: Studding status (Negld / Ónegld)
        if (isset($attributes['pa_negling']['term_names'][0])) {
            switch ($attributes['pa_negling']['term_names'][0]) {
                case 'Nagladekk':
                    $name_parts[] = 'Negld';
                    break;
                case 'Óneglanleg':
                    $name_parts[] = 'Ónegld';
                    break;
                case 'Neglanleg':
                    $name_parts[] = 'Neglanleg';
                    break;
            }
        }

        // Part 4: Tire type (Vetrardekk / Sumardekk)
        if (isset($attributes['pa_gerd']['term_names'][0])) {
            $name_parts[] = $attributes['pa_gerd']['term_names'][0];
        }

        return implode(' - ', $name_parts);
    }

    /**
     * Calculate product weight based on rim size
     * Extended mapping from dekkimporter-7 (supports 10-44")
     *
     * @param int $rim_size Rim size in inches
     * @return float Weight in kg
     */
    public static function get_weight($rim_size) {
        $weights = [
            '10' => 4.0,
            '11' => 5.0,
            '12' => 6.0,
            '13' => 6.5,
            '14' => 7.0,
            '15' => 8.0,
            '16' => 8.5,
            '17' => 9.0,
            '18' => 9.5,
            '19' => 10.0,
            '20' => 10.5,
            '21' => 11.0,
            '22' => 11.5,
            '23' => 12.0,
            '24' => 12.5,
            '25' => 13.0,
            '26' => 13.5,
            '27' => 14.0,
            '28' => 14.5,
            '29' => 15.0,
            '30' => 15.5,
            '31' => 16.0,
            '32' => 16.5,
            '33' => 17.0,
            '34' => 17.5,
            '35' => 18.0,
            '36' => 18.5,
            '37' => 19.0,
            '38' => 19.5,
            '39' => 20.0,
            '40' => 20.5,
            '41' => 21.0,
            '42' => 21.5,
            '43' => 22.0,
            '44' => 22.5,
        ];

        return isset($weights[$rim_size]) ? $weights[$rim_size] : 10.0;
    }

    /**
     * Generate product description with EU energy label link
     * Returns Icelandic descriptions
     *
     * @param string $type Tire type: 'summer', 'winter', or 'allseason'
     * @param string $eu_label_url EU energy label URL
     * @return string HTML description
     */
    public static function product_desc($type, $eu_label_url = '') {
        $label_link = '';
        if (!empty($eu_label_url)) {
            $label_link = '<a target="_blank" href="' . esc_url($eu_label_url) . '">Vöruupplýsingablað</a><br><br>';
        }

        $descriptions = [
            'summer' => $label_link . '<p><strong>Sumardekk</strong><br>
                Sumardekk eru hönnuð fyrir hámarks afköst í hlýju veðri.
                Þau veita frábæra veggrip á þurru og blautu undirlagi og eru bestur
                kosturinn fyrir sumarakstur á Íslandi.</p>',

            'winter' => $label_link . '<p><strong>Vetrardekk</strong><br>
                Vetrardekk eru sérstaklega hönnuð fyrir kalt veður, snjó og ís.
                Með mjúkara gúmmíblendu og sérhönnuðu mynstri veita þau öryggi
                og stjórnun í erfiðum vetraraðstæðum.</p>',

            'allseason' => $label_link . '<p><strong>Heilsársdekk</strong><br>
                Heilsársdekk eru fjölhæf dekk sem henta bæði sumar- og vetrarakstri.
                Þau bjóða upp á góða afkastagetu allt árið í hóflegum aðstæðum.</p>',
        ];

        return isset($descriptions[$type]) ? $descriptions[$type] : $label_link;
    }

    /**
     * Prepare WooCommerce product attributes
     * Creates taxonomy terms if they don't exist
     * Exact port from original plugin
     *
     * @param array $attributes Attributes from build_attributes()
     * @return array WooCommerce formatted attributes
     */
    public static function wc_prepare_product_attributes($attributes) {
        $data = [];
        $position = 0;

        foreach ($attributes as $taxonomy => $values) {
            if (!taxonomy_exists($taxonomy)) {
                continue;
            }

            // Get an instance of the WC_Product_Attribute Object
            $attribute = new WC_Product_Attribute();

            $term_ids = [];

            // Loop through the term names
            foreach ($values['term_names'] as $term_name) {
                if (term_exists($term_name, $taxonomy)) {
                    // Get and set the term ID in the array from the term name
                    $term_ids[] = get_term_by('name', $term_name, $taxonomy)->term_id;
                } else {
                    // Insert term if it does not exist
                    $inserted_term = wp_insert_term($term_name, $taxonomy);
                    if (!is_wp_error($inserted_term)) {
                        // Only add term_id if no error occurred
                        $term_ids[] = $inserted_term['term_id'];
                    } else {
                        // Handle the error if needed, e.g., log it
                        error_log('Error inserting term: ' . $inserted_term->get_error_message());
                    }
                }
            }

            $taxonomy_id = wc_attribute_taxonomy_id_by_name($taxonomy); // Get taxonomy ID

            $attribute->set_id($taxonomy_id);
            $attribute->set_name($taxonomy);
            $attribute->set_options($term_ids);
            $attribute->set_position($position);
            $attribute->set_visible($values['is_visible']);
            $attribute->set_variation($values['for_variation']);

            $data[$taxonomy] = $attribute;

            $position++;
        }

        return $data;
    }

    /**
     * Get product categories based on tire type attribute
     * Returns array of category term IDs
     *
     * @param array $attributes Product attributes with tire types
     * @return array Category term IDs
     */
    public static function get_product_categories($attributes) {
        $categories = [];

        // Main category: "dekk"
        $main_cat = get_term_by('slug', 'dekk', 'product_cat');
        if ($main_cat) {
            $categories[] = $main_cat->term_id;
        }

        // Get tire types from pa_gerd attribute
        $types = isset($attributes['pa_gerd']['term_names']) ? $attributes['pa_gerd']['term_names'] : [];

        // Type-specific categories
        if (in_array('Sumardekk', $types, true)) {
            $cat = get_term_by('slug', 'ny-sumardekk', 'product_cat');
            if ($cat) {
                $categories[] = $cat->term_id;
            }
        } elseif (in_array('Vetrardekk', $types, true)) {
            $cat = get_term_by('slug', 'ny-vetrardekk', 'product_cat');
            if ($cat) {
                $categories[] = $cat->term_id;
            }
        } elseif (in_array('Jeppadekk', $types, true)) {
            $cat = get_term_by('slug', 'ny-jeppadekk', 'product_cat');
            if ($cat) {
                $categories[] = $cat->term_id;
            }
        }

        return $categories;
    }

    /**
     * Upload image from URL to WordPress media library
     * From dekkimporter-7: Includes PDF→PNG conversion, sanitization, MIME validation, no-pic placeholder
     *
     * @param string $url Image URL
     * @param string $filename Optional custom filename
     * @return int|null Attachment ID or null on failure
     */
    public static function upload_image($url, $filename = '') {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        // Handle "no-pic" placeholder (from dekkimporter-7.php lines 626-636)
        $url_basename = basename(parse_url($url, PHP_URL_PATH));
        if (strpos($url_basename, 'no-pic') === 0) {
            $no_pic_url = 'https://dekk1.is/wp-content/uploads/2024/10/no-pic_width-584.jpg';
            $no_pic_id = self::get_attachment_id_by_url($no_pic_url);
            if ($no_pic_id) {
                return $no_pic_id;
            }
            // If placeholder doesn't exist, use the no-pic URL
            $url = $no_pic_url;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            error_log('DekkImporter: Invalid URL: ' . $url);
            return null;
        }

        // Download file
        $response = wp_remote_get($url, [
            'timeout' => 30,
            'headers' => ['User-Agent' => 'Mozilla/5.0'],
        ]);

        if (is_wp_error($response)) {
            error_log('DekkImporter: Error downloading file: ' . $response->get_error_message());
            return null;
        }

        if (wp_remote_retrieve_response_code($response) !== 200) {
            error_log('DekkImporter: Invalid URL or inaccessible: ' . $url);
            return null;
        }

        $mime_type = wp_remote_retrieve_header($response, 'content-type');

        $tmp = wp_tempnam($url);
        if (!$tmp) {
            error_log('DekkImporter: Failed to create temporary file');
            return null;
        }

        file_put_contents($tmp, wp_remote_retrieve_body($response));

        if (empty($filename)) {
            $filename = basename(parse_url($url, PHP_URL_PATH));
        }

        // Sanitize filename
        $path_info = pathinfo($filename);
        $name = str_replace('.', '_', $path_info['filename']);
        $extension = strtolower($path_info['extension']);
        $sanitized_filename = $name . '.' . $extension;

        // PDF to PNG conversion using Imagick
        if ($mime_type === 'application/pdf') {
            if (!extension_loaded('imagick')) {
                error_log('DekkImporter: Imagick extension not installed. Cannot convert PDF');
                @unlink($tmp);
                return null;
            }

            try {
                $imagick = new \Imagick();
                $imagick->setResolution(300, 300);
                $imagick->readImage($tmp . '[0]');
                $imagick->setImageFormat('png');
                $imagick->setImageCompressionQuality(90);

                $converted_tmp = $tmp . '.png';
                $imagick->writeImage($converted_tmp);

                $tmp = $converted_tmp;
                $sanitized_filename = $name . '.png';
                $mime_type = 'image/png';

                $imagick->clear();
                $imagick->destroy();
            } catch (\Exception $e) {
                error_log('DekkImporter: Error converting PDF to image: ' . $e->getMessage());
                @unlink($tmp);
                return null;
            }
        }

        // Validate MIME type
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($mime_type, $allowed_mime_types, true)) {
            error_log("DekkImporter: Disallowed MIME type ($mime_type) for URL $url");
            @unlink($tmp);
            return null;
        }

        $file_array = [
            'name'     => $sanitized_filename,
            'tmp_name' => $tmp,
        ];

        $id = media_handle_sideload($file_array, 0);

        if (is_wp_error($id)) {
            error_log('DekkImporter: Error uploading file: ' . $id->get_error_message());
            @unlink($tmp);
            return null;
        }

        return $id;
    }

    /**
     * Add EU Sheet image to product gallery
     * From dekkimporter-7.php lines 696-742
     * Keeps ONLY the EU sheet image in gallery (removes all others)
     *
     * @param int $product_id Product ID
     * @param string $eusheet_url EU label URL
     * @return void
     */
    public static function add_eusheet_to_gallery($product_id, $eusheet_url) {
        if (empty($eusheet_url)) {
            return;
        }

        // Check if we already have this EU sheet cached
        $cached_eusheet_id = get_post_meta($product_id, '_euSheet_image_id', true);

        if ($cached_eusheet_id) {
            // Verify attachment still exists
            if (get_post($cached_eusheet_id)) {
                // Set gallery to ONLY this image
                update_post_meta($product_id, '_product_image_gallery', $cached_eusheet_id);
                return;
            }
        }

        // Try to find existing attachment by filename
        $filename = basename(parse_url($eusheet_url, PHP_URL_PATH));
        $eusheet_id = self::get_attachment_id_by_filename($filename);

        if (!$eusheet_id) {
            // Upload new image
            $eusheet_id = self::upload_image($eusheet_url, $filename);
        }

        if ($eusheet_id) {
            // Cache the EU sheet image ID
            update_post_meta($product_id, '_euSheet_image_id', $eusheet_id);

            // Set gallery to ONLY the EU sheet image (removes all other images)
            update_post_meta($product_id, '_product_image_gallery', $eusheet_id);
        }
    }

    /**
     * Get attachment ID by filename
     * From dekkimporter-7.php lines 1621-1636
     *
     * @param string $filename Filename to search for
     * @return int|false Attachment ID or false
     */
    public static function get_attachment_id_by_filename($filename) {
        global $wpdb;

        // Remove extension for post_name search
        $post_name = pathinfo($filename, PATHINFO_FILENAME);

        $attachment_id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_name = %s LIMIT 1",
            $post_name
        ));

        return $attachment_id ? (int)$attachment_id : false;
    }

    /**
     * Get attachment ID by URL
     * From dekkimporter-7.php lines 758-776
     *
     * @param string $url Attachment URL
     * @return int|false Attachment ID or false
     */
    public static function get_attachment_id_by_url($url) {
        $filename = basename(parse_url($url, PHP_URL_PATH));
        return self::get_attachment_id_by_filename($filename);
    }
}
