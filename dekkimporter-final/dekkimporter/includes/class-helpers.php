<?php
/**
 * Helper functions for DekkImporter
 */

if (!defined('ABSPATH')) {
    exit;
}

class DekkImporter_Helpers {
    /**
     * Sanitize array recursively
     */
    public static function sanitize_array($array) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::sanitize_array($value);
            } else {
                $array[$key] = sanitize_text_field($value);
            }
        }
        return $array;
    }

    /**
     * Format price
     */
    public static function format_price($price) {
        return number_format((float)$price, 2, '.', '');
    }
}
