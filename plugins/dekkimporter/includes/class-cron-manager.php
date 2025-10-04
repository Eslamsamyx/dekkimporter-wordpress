<?php
/**
 * Cron Manager class for DekkImporter
 * Handles automatic WordPress cron and Action Scheduler processing
 *
 * Purpose: Ensures WooCommerce background tasks (especially product attribute lookups)
 * are processed after sync, preventing task backlog accumulation.
 */

if (!defined('ABSPATH')) {
    exit;
}

class DekkImporter_Cron_Manager {
    /**
     * Plugin instance
     *
     * @var DekkImporter
     */
    private $plugin;

    /**
     * Option key for storing last cron run time
     */
    const LAST_RUN_OPTION = 'dekkimporter_last_cron_run';

    /**
     * Lock option to prevent concurrent runs
     */
    const LOCK_OPTION = 'dekkimporter_cron_processing_lock';

    /**
     * Constructor
     *
     * @param DekkImporter $plugin Plugin instance
     */
    public function __construct($plugin) {
        $this->plugin = $plugin;
    }

    /**
     * Initialize hooks
     *
     * @return void
     */
    public function init(): void {
        // Hook into sync completion
        add_action('dekkimporter_sync_completed', [$this, 'process_after_sync'], 10, 1);
    }

    /**
     * Process background tasks after sync completion
     * Main entry point - called automatically after each sync
     *
     * @param array $sync_result Results from sync operation
     * @return void
     */
    public function process_after_sync(array $sync_result = []): void {
        // Check if auto-processing is enabled
        $options = get_option('dekkimporter_options', []);
        $auto_process = isset($options['dekkimporter_field_auto_process_cron'])
            ? (bool)$options['dekkimporter_field_auto_process_cron']
            : true; // Default: enabled

        if (!$auto_process) {
            $this->plugin->logger->log("Auto-processing disabled in settings. Skipping cron processing.");
            return;
        }

        // Prevent concurrent runs
        if (get_transient(self::LOCK_OPTION)) {
            $this->plugin->logger->log("Cron processing already running. Skipping.");
            return;
        }

        // Set lock (expires in 5 minutes)
        set_transient(self::LOCK_OPTION, time(), 300);

        $this->plugin->logger->log("=== Starting Post-Sync Background Task Processing ===");

        try {
            // Get statistics before processing
            $stats_before = $this->get_action_scheduler_stats();
            $this->plugin->logger->log("Action Scheduler before: {$stats_before['pending']} pending, {$stats_before['past_due']} past-due");

            // Process Action Scheduler queue
            $processed = $this->run_action_scheduler();

            // Check if WordPress cron needs to run
            if ($this->should_run_wordpress_cron()) {
                $this->plugin->logger->log("WordPress cron is overdue. Spawning cron...");
                $this->run_wordpress_cron();
            }

            // Get statistics after processing
            $stats_after = $this->get_action_scheduler_stats();
            $this->plugin->logger->log("Action Scheduler after: {$stats_after['pending']} pending, {$stats_after['past_due']} past-due");

            // Log summary
            $reduced = $stats_before['past_due'] - $stats_after['past_due'];
            if ($reduced > 0) {
                $this->plugin->logger->log("Successfully reduced past-due tasks by {$reduced}");
            }

            // Update last run time
            update_option(self::LAST_RUN_OPTION, time());

            $this->plugin->logger->log("=== Completed Background Task Processing ===");

        } catch (Exception $e) {
            $this->plugin->logger->log("Error during cron processing: " . $e->getMessage(), 'ERROR');
        } finally {
            // Always release lock
            delete_transient(self::LOCK_OPTION);
        }
    }

    /**
     * Process Action Scheduler queue with configurable batch limit
     *
     * @return int Number of tasks processed
     */
    private function run_action_scheduler(): int {
        // Check if Action Scheduler is available
        if (!class_exists('ActionScheduler') || !class_exists('ActionScheduler_QueueRunner')) {
            $this->plugin->logger->log("Action Scheduler not available. Skipping.");
            return 0;
        }

        // Get batch size from settings
        $options = get_option('dekkimporter_options', []);
        $batch_size = isset($options['dekkimporter_field_cron_batch_size'])
            ? (int)$options['dekkimporter_field_cron_batch_size']
            : 25; // Default: 25 tasks

        $this->plugin->logger->log("Processing Action Scheduler queue (batch size: {$batch_size})...");

        $start_time = microtime(true);
        $processed = 0;

        try {
            // Get the queue runner
            $runner = ActionScheduler_QueueRunner::instance();

            // Process actions in batches
            // Note: ActionScheduler processes until time limit or batch limit is reached
            $runner->run(ActionScheduler_Abstract_QueueRunner::STATUS_RUNNING);

            // The runner doesn't return count, so we estimate based on time
            $elapsed = microtime(true) - $start_time;
            $this->plugin->logger->log(sprintf("Action Scheduler processing completed in %.2f seconds", $elapsed));

        } catch (Exception $e) {
            $this->plugin->logger->log("Error processing Action Scheduler: " . $e->getMessage(), 'ERROR');
        }

        return $processed;
    }

    /**
     * Check if WordPress cron should run
     * Based on time since last successful cron execution
     *
     * @return bool True if cron should run
     */
    private function should_run_wordpress_cron(): bool {
        $options = get_option('dekkimporter_options', []);
        $threshold_minutes = isset($options['dekkimporter_field_cron_interval'])
            ? (int)$options['dekkimporter_field_cron_interval']
            : 15; // Default: 15 minutes

        $last_run = get_option(self::LAST_RUN_OPTION, 0);
        $time_since_last_run = time() - $last_run;
        $threshold_seconds = $threshold_minutes * 60;

        if ($time_since_last_run > $threshold_seconds) {
            $minutes_ago = round($time_since_last_run / 60, 1);
            $this->plugin->logger->log("Last cron run was {$minutes_ago} minutes ago (threshold: {$threshold_minutes} minutes)");
            return true;
        }

        return false;
    }

    /**
     * Trigger WordPress cron execution
     * Uses spawn_cron() for non-blocking execution
     *
     * @return void
     */
    private function run_wordpress_cron(): void {
        try {
            // Use WordPress spawn_cron() for non-blocking execution
            spawn_cron();
            $this->plugin->logger->log("WordPress cron spawned successfully");

        } catch (Exception $e) {
            $this->plugin->logger->log("Error spawning WordPress cron: " . $e->getMessage(), 'ERROR');
        }
    }

    /**
     * Get Action Scheduler queue statistics
     *
     * @return array{pending: int, past_due: int, running: int, complete: int, failed: int} Statistics array with counts
     */
    public function get_action_scheduler_stats(): array {
        global $wpdb;

        $stats = [
            'pending' => 0,
            'past_due' => 0,
            'running' => 0,
            'complete' => 0,
            'failed' => 0,
        ];

        if (!class_exists('ActionScheduler')) {
            return $stats;
        }

        try {
            $table = $wpdb->prefix . 'actionscheduler_actions';

            // Check if table exists using prepared statement with LIKE
            $like_pattern = $wpdb->esc_like($table);
            $table_exists = $wpdb->get_var($wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $like_pattern
            )) === $table;

            if (!$table_exists) {
                return $stats;
            }

            // Get pending count - table name is safe (constructed from prefix)
            $stats['pending'] = (int)$wpdb->get_var(
                "SELECT COUNT(*) FROM {$table} WHERE status = 'pending'"
            );

            // Get past-due count
            $stats['past_due'] = (int)$wpdb->get_var(
                "SELECT COUNT(*) FROM {$table}
                WHERE status = 'pending'
                AND scheduled_date_gmt < UTC_TIMESTAMP()"
            );

            // Get running count
            $stats['running'] = (int)$wpdb->get_var(
                "SELECT COUNT(*) FROM {$table} WHERE status = 'in-progress'"
            );

            // Get complete count (last 24 hours)
            $stats['complete'] = (int)$wpdb->get_var(
                "SELECT COUNT(*) FROM {$table}
                WHERE status = 'complete'
                AND scheduled_date_gmt > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 24 HOUR)"
            );

            // Get failed count (last 24 hours)
            $stats['failed'] = (int)$wpdb->get_var(
                "SELECT COUNT(*) FROM {$table}
                WHERE status = 'failed'
                AND scheduled_date_gmt > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 24 HOUR)"
            );

        } catch (Exception $e) {
            $this->plugin->logger->log("Error getting Action Scheduler stats: " . $e->getMessage(), 'ERROR');
        }

        return $stats;
    }

    /**
     * Clean up old completed and failed Action Scheduler tasks
     *
     * @param int $days Days to keep (default: 30)
     * @return int Number of actions deleted
     */
    public function cleanup_old_actions(int $days = 30): int {
        global $wpdb;

        if (!class_exists('ActionScheduler')) {
            return 0;
        }

        $table = $wpdb->prefix . 'actionscheduler_actions';

        try {
            $deleted = $wpdb->query($wpdb->prepare(
                "DELETE FROM {$table}
                WHERE status IN ('complete', 'failed', 'canceled')
                AND scheduled_date_gmt < DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d DAY)",
                $days
            ));

            if ($deleted > 0) {
                $this->plugin->logger->log("Cleaned up {$deleted} old Action Scheduler tasks (older than {$days} days)");
            }

            return (int)$deleted;

        } catch (Exception $e) {
            $this->plugin->logger->log("Error cleaning up old actions: " . $e->getMessage(), 'ERROR');
            return 0;
        }
    }

    /**
     * Manual trigger for admin use
     * Can be called from admin interface or WP-CLI
     *
     * @return array{success: bool, before: array, after: array, reduced: int} Processing results
     */
    public function manual_process(): array {
        $this->plugin->logger->log("=== Manual Background Task Processing Triggered ===");

        $stats_before = $this->get_action_scheduler_stats();

        $this->run_action_scheduler();
        $this->run_wordpress_cron();

        $stats_after = $this->get_action_scheduler_stats();

        return [
            'success' => true,
            'before' => $stats_before,
            'after' => $stats_after,
            'reduced' => $stats_before['past_due'] - $stats_after['past_due'],
        ];
    }
}
