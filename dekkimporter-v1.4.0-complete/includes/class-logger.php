<?php
/**
 * Logger class for DekkImporter
 */

if (!defined('ABSPATH')) {
    exit;
}

class DekkImporter_Logger {
    /**
     * Log file path
     */
    private $log_file;

    /**
     * Constructor
     */
    public function __construct() {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/dekkimporter-logs';

        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }

        $this->log_file = $log_dir . '/dekkimporter-' . date('Y-m-d') . '.log';
    }

    /**
     * Log a message
     */
    public function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;

        error_log($log_message, 3, $this->log_file);
    }

    /**
     * Log mailer errors
     */
    public function log_mailer_errors($wp_error) {
        $this->log('Mail Error: ' . $wp_error->get_error_message(), 'ERROR');
    }
}
