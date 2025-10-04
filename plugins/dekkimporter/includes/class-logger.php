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
     *
     * @var string
     */
    private $log_file;

    /**
     * Log directory writable status
     *
     * @var bool
     */
    private $is_writable = false;

    /**
     * Constructor
     *
     * Initializes the logger and sets up log directory
     */
    public function __construct() {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/dekkimporter-logs';

        // Create log directory if it doesn't exist
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }

        // Check if directory is writable
        if (is_dir($log_dir) && is_writable($log_dir)) {
            $this->is_writable = true;
            $this->log_file = $log_dir . '/dekkimporter-' . date('Y-m-d') . '.log';
        } else {
            // Fallback: log to system error log
            error_log('DekkImporter: Log directory not writable: ' . $log_dir);
        }
    }

    /**
     * Log a message
     *
     * @param string $message Message to log
     * @param string $level Log level (INFO, WARNING, ERROR)
     * @return bool Success status
     */
    public function log(string $message, string $level = 'INFO'): bool {
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;

        if ($this->is_writable) {
            // Write to plugin log file
            $result = error_log($log_message, 3, $this->log_file);
            return $result !== false;
        } else {
            // Fallback to system error log
            error_log('DekkImporter: ' . $log_message);
            return false;
        }
    }

    /**
     * Log mailer errors
     *
     * @param WP_Error $wp_error WordPress error object
     * @return void
     */
    public function log_mailer_errors($wp_error): void {
        $this->log('Mail Error: ' . $wp_error->get_error_message(), 'ERROR');
    }
}
