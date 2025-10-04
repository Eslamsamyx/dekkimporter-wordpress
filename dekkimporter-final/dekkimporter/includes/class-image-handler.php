<?php
/**
 * Image Handler class for DekkImporter
 */

if (!defined('ABSPATH')) {
    exit;
}

class DekkImporter_Image_Handler {
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
     * Upload image from URL
     */
    public function upload_image($image_url, $product_id) {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $image_id = media_sideload_image($image_url, $product_id, null, 'id');

        if (is_wp_error($image_id)) {
            $this->plugin->logger->log('Failed to upload image: ' . $image_id->get_error_message(), 'ERROR');
            return false;
        }

        return $image_id;
    }
}
