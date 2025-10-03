<?php
/**
 * Cron class for DekkImporter
 */

if (!defined('ABSPATH')) {
    exit;
}

class DekkImporter_Cron {
    /**
     * Plugin instance
     */
    private $plugin;

    /**
     * Constructor
     */
    public function __construct($plugin) {
        $this->plugin = $plugin;

        add_action('dekkimporter_sync_products', array($this, 'sync_products'));
        add_action('dekkimporter_cleanup_logs', array($this, 'cleanup_old_logs'));
        add_filter('cron_schedules', array($this, 'add_custom_cron_intervals'));
    }

    /**
     * Add custom cron intervals
     *
     * @param array $schedules Existing schedules
     * @return array Modified schedules
     */
    public function add_custom_cron_intervals($schedules) {
        $schedules['hourly'] = array(
            'interval' => HOUR_IN_SECONDS,
            'display'  => esc_html__('Hourly', 'dekkimporter'),
        );

        $schedules['twicedaily'] = array(
            'interval' => 12 * HOUR_IN_SECONDS,
            'display'  => esc_html__('Twice Daily', 'dekkimporter'),
        );

        $schedules['weekly'] = array(
            'interval' => WEEK_IN_SECONDS,
            'display'  => esc_html__('Weekly', 'dekkimporter'),
        );

        return $schedules;
    }

    /**
     * Activate cron
     */
    public function activate() {
        $interval = get_option('dekkimporter_sync_interval', 'daily');

        // Clear existing schedule
        $this->deactivate();

        // Schedule sync with selected interval
        if (!wp_next_scheduled('dekkimporter_sync_products')) {
            wp_schedule_event(time(), $interval, 'dekkimporter_sync_products');
        }

        // Schedule log cleanup (always daily)
        if (!wp_next_scheduled('dekkimporter_cleanup_logs')) {
            wp_schedule_event(time(), 'daily', 'dekkimporter_cleanup_logs');
        }
    }

    /**
     * Deactivate cron
     */
    public function deactivate() {
        $timestamp = wp_next_scheduled('dekkimporter_sync_products');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'dekkimporter_sync_products');
        }

        // Unschedule log cleanup
        $cleanup_timestamp = wp_next_scheduled('dekkimporter_cleanup_logs');
        if ($cleanup_timestamp) {
            wp_unschedule_event($cleanup_timestamp, 'dekkimporter_cleanup_logs');
        }
    }

    /**
     * Sync products
     * Called by WordPress cron
     */
    public function sync_products() {
        $this->plugin->logger->log('=== SCHEDULED SYNC STARTED ===');

        // Get sync options from settings
        $options = get_option('dekkimporter_options', []);

        $sync_options = [
            'handle_obsolete' => isset($options['handle_obsolete']) ? (bool) $options['handle_obsolete'] : true,
            'batch_size' => isset($options['sync_batch_size']) ? (int) $options['sync_batch_size'] : 50,
            'dry_run' => false,
        ];

        // Perform full sync
        $stats = $this->plugin->sync_manager->full_sync($sync_options);

        $this->plugin->logger->log('=== SCHEDULED SYNC COMPLETED ===');
        $this->plugin->logger->log('Results: ' . json_encode($stats));

        // Save sync stats for dashboard widget
        update_option('dekkimporter_last_sync_stats', $stats);
        update_option('dekkimporter_last_sync_time', time());

        // Send notification email if configured
        $this->send_sync_notification($stats);
    }

    /**
     * Send sync notification email
     *
     * @param array $stats Sync statistics
     */
    private function send_sync_notification($stats) {
        $options = get_option('dekkimporter_options', []);

        if (empty($options['sync_notification_email'])) {
            return;
        }

        $to = sanitize_email($options['sync_notification_email']);
        $subject = '[DekkImporter] Daily Sync Report - ' . date('Y-m-d');

        $message = $this->build_sync_notification_email($stats);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
        ];

        $sent = wp_mail($to, $subject, $message, $headers);

        if ($sent) {
            $this->plugin->logger->log("Sync notification sent to {$to}");
        } else {
            $this->plugin->logger->log("Failed to send sync notification to {$to}", 'ERROR');
        }
    }

    /**
     * Build sync notification email
     *
     * @param array $stats Sync statistics
     * @return string HTML email content
     */
    private function build_sync_notification_email($stats) {
        $site_name = get_bloginfo('name');

        $html = '
        <html>
        <head>
            <style>
                body { font-family: sans-serif; color: #333; }
                table { border-collapse: collapse; width: 100%; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .success { color: #28a745; }
                .warning { color: #ffc107; }
                .error { color: #dc3545; }
                .stats-value { font-weight: bold; font-size: 1.2em; }
            </style>
        </head>
        <body>
            <h2>Product Sync Report - ' . esc_html($site_name) . '</h2>
            <p>Date: ' . esc_html(current_time('Y-m-d H:i:s')) . '</p>

            <h3>Summary</h3>
            <table>
                <tr>
                    <th>Metric</th>
                    <th>Count</th>
                </tr>
                <tr>
                    <td>Products Fetched from API</td>
                    <td class="stats-value">' . esc_html($stats['products_fetched']) . '</td>
                </tr>
                <tr>
                    <td>Products Created</td>
                    <td class="stats-value success">' . esc_html($stats['products_created']) . '</td>
                </tr>
                <tr>
                    <td>Products Updated</td>
                    <td class="stats-value success">' . esc_html($stats['products_updated']) . '</td>
                </tr>
                <tr>
                    <td>Products Skipped (No Changes)</td>
                    <td class="stats-value">' . esc_html($stats['products_skipped']) . '</td>
                </tr>
                <tr>
                    <td>Obsolete Products Found</td>
                    <td class="stats-value warning">' . esc_html($stats['products_obsolete']) . '</td>
                </tr>
                <tr>
                    <td>Products Deleted/Drafted</td>
                    <td class="stats-value warning">' . esc_html($stats['products_deleted']) . '</td>
                </tr>
                <tr>
                    <td>Errors</td>
                    <td class="stats-value error">' . esc_html($stats['errors']) . '</td>
                </tr>
                <tr>
                    <td>Sync Duration</td>
                    <td class="stats-value">' . esc_html($stats['duration']) . ' seconds</td>
                </tr>
            </table>

            <p>
                <a href="' . admin_url('admin.php?page=dekkimporter') . '">View Full Details</a>
            </p>
        </body>
        </html>';

        return $html;
    }

    /**
     * Cleanup old log files
     * Called by WordPress cron daily
     */
    public function cleanup_old_logs() {
        $retention_days = get_option('dekkimporter_log_retention_days', 7);

        $this->plugin->logger->log("=== LOG CLEANUP STARTED (Retention: {$retention_days} days) ===");

        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/dekkimporter-logs';

        if (!is_dir($log_dir)) {
            $this->plugin->logger->log('Log directory does not exist: ' . $log_dir);
            return;
        }

        $files = glob($log_dir . '/dekkimporter-*.log');
        if (empty($files)) {
            $this->plugin->logger->log('No log files found to cleanup');
            return;
        }

        $cutoff_time = strtotime("-{$retention_days} days");
        $deleted_count = 0;

        foreach ($files as $file) {
            $file_time = filemtime($file);

            if ($file_time < $cutoff_time) {
                if (wp_delete_file($file)) {
                    $deleted_count++;
                    $this->plugin->logger->log('Deleted old log file: ' . basename($file));
                } else {
                    $this->plugin->logger->log('Failed to delete log file: ' . basename($file), 'ERROR');
                }
            }
        }

        $this->plugin->logger->log("=== LOG CLEANUP COMPLETED ({$deleted_count} files deleted) ===");
    }
}
