<?php
/**
 * Sync Manager class for DekkImporter
 * Handles product synchronization with staleness detection
 */

if (!defined('ABSPATH')) {
    exit;
}

class DekkImporter_Sync_Manager {
    /**
     * Plugin instance
     *
     * @var DekkImporter
     */
    private $plugin;

    /**
     * Meta keys for tracking
     */
    const META_LAST_SYNC = '_dekkimporter_last_sync';
    const META_API_ID = '_dekkimporter_api_id';
    const META_SUPPLIER = '_dekkimporter_supplier';
    const META_SYNC_COUNT = '_dekkimporter_sync_count';
    const META_OBSOLETE_CHECK = '_dekkimporter_obsolete_check';

    /**
     * Options keys
     */
    const OPTION_LAST_FULL_SYNC = 'dekkimporter_last_full_sync';
    const OPTION_SYNC_STATUS = 'dekkimporter_sync_status';
    const OPTION_SYNC_STATS = 'dekkimporter_sync_stats';

    /**
     * Staleness threshold (days)
     */
    const STALENESS_THRESHOLD = 7;

    /**
     * Constructor
     *
     * @param DekkImporter $plugin Plugin instance
     */
    public function __construct($plugin) {
        $this->plugin = $plugin;
    }

    /**
     * Perform full sync
     *
     * @param array $options Sync options
     * @return array<string, mixed> Sync results
     */
    public function full_sync(array $options = []): array {
        $start_time = microtime(true);

        $defaults = [
            'handle_obsolete' => true,
            'batch_size' => 50,
            'dry_run' => false,
        ];

        $options = array_merge($defaults, $options);

        // Prevent concurrent syncs using atomic lock mechanism
        $lock_key = 'dekkimporter_sync_lock';
        $lock_timeout = 3600; // 1 hour max
        $current_time = time();

        // Check for stale locks (older than 1 hour)
        $existing_lock = get_transient($lock_key);
        if ($existing_lock !== false) {
            $lock_age = $current_time - (int)$existing_lock;
            if ($lock_age > $lock_timeout) {
                // Stale lock detected, clean it up
                $this->plugin->logger->log('Stale sync lock detected (age: ' . $lock_age . 's), cleaning up', 'WARNING');
                delete_transient($lock_key);
            } else {
                // Active lock, abort
                $this->plugin->logger->log('Another sync is already running. Aborting.', 'WARNING');
                return [
                    'products_fetched' => 0,
                    'products_created' => 0,
                    'products_updated' => 0,
                    'products_skipped' => 0,
                    'products_obsolete' => 0,
                    'products_deleted' => 0,
                    'errors' => 0,
                    'message' => 'Another sync is already running',
                    'status' => 'aborted',
                ];
            }
        }

        // Set atomic lock with current timestamp
        set_transient($lock_key, $current_time, $lock_timeout);

        // Register shutdown function to ensure lock is released
        register_shutdown_function(function() use ($lock_key) {
            if (get_transient($lock_key)) {
                delete_transient($lock_key);
            }
        });

        $this->plugin->logger->log('=== FULL SYNC STARTED ===');
        $this->plugin->logger->log('Options: ' . json_encode($options));

        // Update sync status
        $this->update_sync_status('running', 'Full sync in progress');

        $stats = [
            'products_fetched' => 0,
            'products_created' => 0,
            'products_updated' => 0,
            'products_skipped' => 0,
            'products_obsolete' => 0,
            'products_deleted' => 0,
            'errors' => 0,
        ];

        // Initialize progress tracking
        $progress_key = 'dekkimporter_sync_progress';
        $progress = [
            'total' => 0,
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'start_time' => time(),
            'status' => 'initializing',
            'message' => 'Preparing sync...',
        ];
        set_transient($progress_key, $progress, 3600);

        // Initialize error tracking
        $error_log = [];

        $new_product_links = [];  // Track new product links for notifications (v7 parity)

        try {
            // Fetch products from API
            $api_products = $this->plugin->data_source->fetch_products();
            $stats['products_fetched'] = count($api_products);

            $this->plugin->logger->log("Fetched {$stats['products_fetched']} products from API");

            // Update progress with total count
            $progress['total'] = $stats['products_fetched'];
            $progress['status'] = 'processing';
            $progress['message'] = 'Processing products...';
            set_transient($progress_key, $progress, 3600);

            // Process products in batches
            $batches = array_chunk($api_products, $options['batch_size']);

            foreach ($batches as $batch_index => $batch) {
                $this->plugin->logger->log("Processing batch " . ($batch_index + 1) . " of " . count($batches));

                foreach ($batch as $api_product) {
                    $result = $this->sync_product($api_product, $options['dry_run']);

                    switch ($result['action']) {
                        case 'created':
                            $stats['products_created']++;
                            $progress['created']++;
                            // Collect new product links for notification
                            if (isset($result['product_id'])) {
                                $permalink = get_permalink($result['product_id']);
                                if ($permalink) {
                                    $new_product_links[] = $permalink;
                                }
                            }
                            break;
                        case 'updated':
                            $stats['products_updated']++;
                            $progress['updated']++;
                            break;
                        case 'skipped':
                            $stats['products_skipped']++;
                            $progress['skipped']++;
                            break;
                        case 'error':
                            $stats['errors']++;
                            $progress['errors']++;
                            // Track error details
                            $error_log[] = [
                                'sku' => $api_product['sku'] ?? 'Unknown',
                                'name' => $api_product['name'] ?? 'Unknown Product',
                                'message' => $result['message'] ?? 'Unknown error',
                                'timestamp' => current_time('mysql'),
                            ];
                            break;
                    }

                    // Update progress after each product
                    $progress['processed']++;
                    $elapsed = time() - $progress['start_time'];
                    $rate = $progress['processed'] / max($elapsed, 1);
                    $remaining = $progress['total'] - $progress['processed'];
                    $progress['estimated_time'] = $remaining > 0 ? round($remaining / max($rate, 0.1)) : 0;
                    $progress['percentage'] = $progress['total'] > 0 ? round(($progress['processed'] / $progress['total']) * 100, 1) : 0;
                    $progress['message'] = sprintf('Processing %d of %d products...', $progress['processed'], $progress['total']);
                    set_transient($progress_key, $progress, 3600);

                    // Check for cancellation request
                    if (get_transient('dekkimporter_sync_cancelled')) {
                        $this->plugin->logger->log('SYNC CANCELLED BY USER at product ' . $progress['processed']);
                        delete_transient('dekkimporter_sync_cancelled');
                        delete_transient($lock_key);
                        delete_transient($progress_key);
                        throw new Exception('Sync cancelled by user');
                    }
                }

                // Small delay between batches to avoid overwhelming the server
                if ($batch_index < count($batches) - 1) {
                    usleep(100000); // 0.1 second
                }
            }

            // Update progress to show finalizing
            $progress['percentage'] = 95;
            $progress['message'] = 'Finalizing sync...';
            set_transient($progress_key, $progress, 3600);

            // Handle obsolete products
            if ($options['handle_obsolete']) {
                $progress['message'] = 'Handling obsolete products...';
                set_transient($progress_key, $progress, 3600);

                $obsolete_results = $this->handle_obsolete_products($api_products, $options['dry_run']);
                $stats['products_obsolete'] = $obsolete_results['found'];
                $stats['products_deleted'] = $obsolete_results['deleted'];
            }

            // Update last sync timestamp
            if (!$options['dry_run']) {
                update_option(self::OPTION_LAST_FULL_SYNC, current_time('mysql'));
            }

            $elapsed_time = round(microtime(true) - $start_time, 2);
            $stats['duration'] = $elapsed_time;

            $this->plugin->logger->log('=== FULL SYNC COMPLETED ===');
            $this->plugin->logger->log('Stats: ' . json_encode($stats));
            $this->plugin->logger->log("Duration: {$elapsed_time} seconds");

            // Update sync status
            $this->update_sync_status('completed', 'Full sync completed successfully', $stats);

            // Save stats with error log
            $stats['error_log'] = $error_log;
            $this->save_sync_stats($stats);

            // Send new product notification email (v7 parity - lines 505-506, 1676-1694)
            if (!empty($new_product_links) && !$options['dry_run']) {
                $progress['message'] = 'Sending notifications...';
                set_transient($progress_key, $progress, 3600);
                $this->send_new_product_notification($new_product_links);
            }

        } catch (Exception $e) {
            $this->plugin->logger->log('SYNC ERROR: ' . $e->getMessage(), 'ERROR');
            $this->update_sync_status('failed', 'Sync failed: ' . $e->getMessage());
            $stats['errors']++;
        }

        // Set final progress state with complete stats
        $progress['status'] = 'completed';
        $progress['message'] = 'Sync completed!';
        $progress['percentage'] = 100;
        $progress['processed'] = $stats['products_fetched'];
        $progress['total'] = $stats['products_fetched'];
        $progress['created'] = $stats['products_created'];
        $progress['updated'] = $stats['products_updated'];
        $progress['skipped'] = $stats['products_skipped'];
        $progress['errors'] = $stats['errors'];
        $progress['estimated_time'] = 0;
        set_transient($progress_key, $progress, 10); // Keep for 10 seconds so UI can show final state

        // Note: Transient will auto-expire in 10 seconds, or can be cleared by next sync

        // BUG FIX #5: Release sync lock
        delete_transient($lock_key);
        $this->plugin->logger->log('Sync lock released');

        // Fire completion hook for cron manager and other extensions
        do_action('dekkimporter_sync_completed', $stats);

        return $stats;
    }

    /**
     * Sync a single product
     *
     * @param array $api_product Product data from API
     * @param bool $dry_run If true, don't make changes
     * @return array<string, mixed> Result with action taken
     */
    private function sync_product(array $api_product, bool $dry_run = false): array {
        $sku = $api_product['sku'];

        // Check if product exists
        $product_id = wc_get_product_id_by_sku($sku);

        if ($product_id) {
            // Product exists - update it
            return $this->update_existing_product($product_id, $api_product, $dry_run);
        } else {
            // Product doesn't exist - create it
            return $this->create_new_product($api_product, $dry_run);
        }
    }

    /**
     * Create new product
     *
     * @param array $api_product Product data
     * @param bool $dry_run If true, don't create
     * @return array<string, mixed> Result
     */
    private function create_new_product(array $api_product, bool $dry_run = false): array {
        if ($dry_run) {
            $this->plugin->logger->log("[DRY RUN] Would create product: {$api_product['name']} ({$api_product['sku']})");
            return ['action' => 'created', 'dry_run' => true];
        }

        try {
            $product_id = $this->plugin->product_creator->create_product($api_product);

            if ($product_id) {
                // Add metadata
                update_post_meta($product_id, self::META_LAST_SYNC, current_time('mysql'));
                update_post_meta($product_id, self::META_API_ID, $api_product['api_id']);
                update_post_meta($product_id, self::META_SUPPLIER, $api_product['supplier']);
                update_post_meta($product_id, self::META_SYNC_COUNT, 1);

                return ['action' => 'created', 'product_id' => $product_id];
            } else {
                return ['action' => 'error', 'message' => 'Failed to create product'];
            }
        } catch (Exception $e) {
            $this->plugin->logger->log("Error creating product {$api_product['sku']}: " . $e->getMessage(), 'ERROR');
            return ['action' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Update existing product
     *
     * @param int $product_id Product ID
     * @param array $api_product Product data from API
     * @param bool $dry_run If true, don't update
     * @return array<string, mixed> Result
     */
    private function update_existing_product(int $product_id, array $api_product, bool $dry_run = false): array {
        // Check if update is needed
        $last_sync = get_post_meta($product_id, self::META_LAST_SYNC, true);
        $api_last_modified = isset($api_product['last_modified']) ? $api_product['last_modified'] : '';

        // Debug logging for tracking
        $this->plugin->logger->log("Checking product {$product_id} ({$api_product['sku']}): last_sync={$last_sync}, api_modified={$api_last_modified}");

        // If API product hasn't changed since last sync, skip
        if (!empty($last_sync) && !empty($api_last_modified)) {
            if (strtotime($api_last_modified) <= strtotime($last_sync)) {
                $this->plugin->logger->log("Skipping product {$product_id}: API data not modified since last sync");
                return ['action' => 'skipped', 'reason' => 'not_modified'];
            }
        }

        if ($dry_run) {
            $this->plugin->logger->log("[DRY RUN] Would update product: {$api_product['name']} (ID: {$product_id})");
            return ['action' => 'updated', 'dry_run' => true];
        }

        try {
            $this->plugin->product_updater->update_product($product_id, $api_product);

            // Update metadata
            update_post_meta($product_id, self::META_LAST_SYNC, current_time('mysql'));
            update_post_meta($product_id, self::META_API_ID, $api_product['api_id']);

            $sync_count = (int) get_post_meta($product_id, self::META_SYNC_COUNT, true);
            update_post_meta($product_id, self::META_SYNC_COUNT, $sync_count + 1);

            return ['action' => 'updated', 'product_id' => $product_id];
        } catch (Exception $e) {
            $this->plugin->logger->log("Error updating product {$product_id}: " . $e->getMessage(), 'ERROR');
            return ['action' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Handle obsolete products (removed from API)
     * 100% PARITY with dekkimporter-7.php (lines 462-503)
     *
     * Features from v7:
     * - Immediate deactivation (no grace period)
     * - Batch processing in chunks of 10
     * - Different logic for variable vs simple products
     * - Sets product_visibility taxonomy terms
     * - Variable: parent manages stock, variations don't
     *
     * @param array $api_products Current products from API
     * @param bool $dry_run If true, don't deactivate
     * @return array{found: int, deactivated: int} Results
     */
    private function handle_obsolete_products(array $api_products, bool $dry_run = false): array {
        $this->plugin->logger->log('Checking for obsolete products (v7 parity mode)...');

        // Get all active API SKUs
        $api_skus = [];
        foreach ($api_products as $product) {
            $api_skus[] = $product['sku'];
        }

        // Get all products with our supplier suffix
        $args = [
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'publish',  // Only published products (like v7)
            'meta_query' => [
                [
                    'key' => self::META_SUPPLIER,
                    'compare' => 'EXISTS',
                ],
            ],
        ];

        $query = new WP_Query($args);
        $our_products = $query->posts;

        $to_deactivate = [];

        foreach ($our_products as $post) {
            $product = wc_get_product($post->ID);
            if (!$product) {
                continue;
            }

            $sku = $product->get_sku();
            $stock_quantity = $product->get_stock_quantity();
            $stock_status = $product->get_stock_status();

            // If product SKU is not in API results and currently has stock, deactivate it
            if (!in_array($sku, $api_skus, true)) {
                if ($stock_quantity > 0 && $stock_status !== 'outofstock') {
                    $to_deactivate[] = $post->ID;
                }
            }
        }

        $this->plugin->logger->log('Found ' . count($to_deactivate) . ' products to deactivate');

        $deactivated_count = 0;

        if (!$dry_run && !empty($to_deactivate)) {
            // Process in chunks of 10 (exactly like v7)
            $chunks = array_chunk($to_deactivate, 10);

            foreach ($chunks as $chunk_index => $chunk) {
                $this->plugin->logger->log("Processing deactivation chunk " . ($chunk_index + 1) . " of " . count($chunks));

                foreach ($chunk as $product_id) {
                    $product = wc_get_product($product_id);
                    if (!$product) {
                        continue;
                    }

                    if ($product->is_type('variable')) {
                        // Variable product: parent manages stock, variations don't
                        update_post_meta($product_id, '_manage_stock', 'yes');
                        update_post_meta($product_id, '_stock', 0);
                        update_post_meta($product_id, '_stock_status', 'outofstock');
                        wp_set_object_terms($product_id, 'outofstock', 'product_visibility', false);

                        // Clear variation stock management
                        foreach ($product->get_children() as $variation_id) {
                            update_post_meta($variation_id, '_manage_stock', 'no');
                            update_post_meta($variation_id, '_stock_status', '');
                        }

                        $this->plugin->logger->log("Deactivated variable product ID $product_id (SKU: {$product->get_sku()})");
                    } else {
                        // Simple product: direct stock update
                        update_post_meta($product_id, '_stock', 0);
                        update_post_meta($product_id, '_stock_status', 'outofstock');
                        update_post_meta($product_id, '_manage_stock', 'yes');
                        wp_set_object_terms($product_id, 'outofstock', 'product_visibility', true);

                        $this->plugin->logger->log("Deactivated simple product ID $product_id (SKU: {$product->get_sku()})");
                    }

                    $deactivated_count++;
                }
            }
        } elseif ($dry_run) {
            foreach ($to_deactivate as $product_id) {
                $product = wc_get_product($product_id);
                $this->plugin->logger->log("[DRY RUN] Would deactivate product: {$product->get_name()} (ID: $product_id, SKU: {$product->get_sku()})");
            }
        }

        return [
            'found' => count($to_deactivate),
            'deactivated' => $deactivated_count,
        ];
    }

    /**
     * Get stale products
     * Products that haven't been synced in X days
     *
     * @param int|null $days Number of days
     * @return array<int, WP_Post> Stale products
     */
    public function get_stale_products(?int $days = null): array {
        if ($days === null) {
            $days = self::STALENESS_THRESHOLD;
        }

        $threshold_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $args = [
            'post_type' => 'product',
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => self::META_LAST_SYNC,
                    'value' => $threshold_date,
                    'compare' => '<',
                    'type' => 'DATETIME',
                ],
                [
                    'key' => self::META_LAST_SYNC,
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ];

        $query = new WP_Query($args);
        return $query->posts;
    }

    /**
     * Update sync status
     *
     * @param string $status Status (running, completed, failed)
     * @param string $message Status message
     * @param array $stats Optional stats
     * @return void
     */
    private function update_sync_status(string $status, string $message, array $stats = []): void {
        $status_data = [
            'status' => $status,
            'message' => $message,
            'timestamp' => current_time('mysql'),
            'stats' => $stats,
        ];

        update_option(self::OPTION_SYNC_STATUS, $status_data);
    }

    /**
     * Save sync stats
     *
     * @param array $stats Sync statistics
     * @return void
     */
    private function save_sync_stats(array $stats): void {
        $all_stats = get_option(self::OPTION_SYNC_STATS, []);

        $all_stats[] = array_merge($stats, [
            'timestamp' => current_time('mysql'),
        ]);

        // Keep only last 30 sync stats
        if (count($all_stats) > 30) {
            $all_stats = array_slice($all_stats, -30);
        }

        update_option(self::OPTION_SYNC_STATS, $all_stats);
    }

    /**
     * Get sync status
     *
     * @return array<string, mixed> Current sync status
     */
    public function get_sync_status(): array {
        return get_option(self::OPTION_SYNC_STATUS, [
            'status' => 'idle',
            'message' => 'No sync has been run',
            'timestamp' => null,
        ]);
    }

    /**
     * Get sync stats history
     *
     * @param int $limit Number of records to return
     * @return array<int, array> Sync stats
     */
    public function get_sync_stats(int $limit = 10): array {
        $stats = get_option(self::OPTION_SYNC_STATS, []);
        return array_slice($stats, -$limit);
    }

    /**
     * Send new product notification email
     * 100% PARITY with dekkimporter-7.php (lines 1676-1694)
     *
     * @param array<int, string> $links Array of product permalinks
     * @return bool Success status
     */
    private function send_new_product_notification(array $links): bool {
        $options = get_option('dekkimporter_options', []);
        $notification_email = isset($options['dekkimporter_field_notification_email']) ? sanitize_email($options['dekkimporter_field_notification_email']) : '';

        if (empty($notification_email)) {
            $this->plugin->logger->log('Notification email not set. Skipping new product notification.');
            return false;
        }

        $subject = 'DekkImporter Update Report';
        $message = empty($links)
            ? 'No new products added during the latest import.'
            : "New products have been added:\n" . implode("\n", $links);

        $headers = ['Content-Type: text/plain; charset=UTF-8'];

        if (wp_mail($notification_email, $subject, $message, $headers)) {
            $this->plugin->logger->log('New product notification email sent successfully to: ' . $notification_email);
            return true;
        } else {
            $this->plugin->logger->log('Failed to send new product notification email to: ' . $notification_email);
            return false;
        }
    }
}
