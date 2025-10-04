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
     */
    public function __construct($plugin) {
        $this->plugin = $plugin;
    }

    /**
     * Perform full sync
     *
     * @param array $options Sync options
     * @return array Sync results
     */
    public function full_sync($options = []) {
        $start_time = microtime(true);

        $defaults = [
            'handle_obsolete' => true,
            'batch_size' => 50,
            'dry_run' => false,
        ];

        $options = array_merge($defaults, $options);

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

        try {
            // Fetch products from API
            $api_products = $this->plugin->data_source->fetch_products();
            $stats['products_fetched'] = count($api_products);

            $this->plugin->logger->log("Fetched {$stats['products_fetched']} products from API");

            // Process products in batches
            $batches = array_chunk($api_products, $options['batch_size']);

            foreach ($batches as $batch_index => $batch) {
                $this->plugin->logger->log("Processing batch " . ($batch_index + 1) . " of " . count($batches));

                foreach ($batch as $api_product) {
                    $result = $this->sync_product($api_product, $options['dry_run']);

                    switch ($result['action']) {
                        case 'created':
                            $stats['products_created']++;
                            break;
                        case 'updated':
                            $stats['products_updated']++;
                            break;
                        case 'skipped':
                            $stats['products_skipped']++;
                            break;
                        case 'error':
                            $stats['errors']++;
                            break;
                    }
                }

                // Small delay between batches to avoid overwhelming the server
                if ($batch_index < count($batches) - 1) {
                    usleep(100000); // 0.1 second
                }
            }

            // Handle obsolete products
            if ($options['handle_obsolete']) {
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

            // Save stats
            $this->save_sync_stats($stats);

        } catch (Exception $e) {
            $this->plugin->logger->log('SYNC ERROR: ' . $e->getMessage(), 'ERROR');
            $this->update_sync_status('failed', 'Sync failed: ' . $e->getMessage());
            $stats['errors']++;
        }

        return $stats;
    }

    /**
     * Sync a single product
     *
     * @param array $api_product Product data from API
     * @param bool $dry_run If true, don't make changes
     * @return array Result with action taken
     */
    private function sync_product($api_product, $dry_run = false) {
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
     * @return array Result
     */
    private function create_new_product($api_product, $dry_run = false) {
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
     * @return array Result
     */
    private function update_existing_product($product_id, $api_product, $dry_run = false) {
        // Check if update is needed
        $last_sync = get_post_meta($product_id, self::META_LAST_SYNC, true);
        $api_last_modified = $api_product['last_modified'];

        // If API product hasn't changed since last sync, skip
        if (!empty($last_sync) && !empty($api_last_modified)) {
            if (strtotime($api_last_modified) <= strtotime($last_sync)) {
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
     * Handle obsolete products
     * Products that no longer exist in API
     *
     * @param array $api_products Current products from API
     * @param bool $dry_run If true, don't delete
     * @return array Results
     */
    private function handle_obsolete_products($api_products, $dry_run = false) {
        $this->plugin->logger->log('Checking for obsolete products...');

        // Get all active API SKUs
        $api_skus = [];
        foreach ($api_products as $product) {
            $api_skus[] = $product['sku'];
        }

        // Get all products with our supplier suffix
        $args = [
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => ['publish', 'draft'],
            'meta_query' => [
                [
                    'key' => self::META_SUPPLIER,
                    'compare' => 'EXISTS',
                ],
            ],
        ];

        $query = new WP_Query($args);
        $our_products = $query->posts;

        $obsolete_products = [];

        foreach ($our_products as $post) {
            $product = wc_get_product($post->ID);
            if (!$product) {
                continue;
            }

            $sku = $product->get_sku();

            // If product SKU is not in API results, it's obsolete
            if (!in_array($sku, $api_skus, true)) {
                $obsolete_products[] = [
                    'id' => $post->ID,
                    'sku' => $sku,
                    'name' => $product->get_name(),
                ];
            }
        }

        $this->plugin->logger->log('Found ' . count($obsolete_products) . ' obsolete products');

        $deleted_count = 0;

        foreach ($obsolete_products as $obsolete) {
            if ($dry_run) {
                $this->plugin->logger->log("[DRY RUN] Would delete obsolete product: {$obsolete['name']} (SKU: {$obsolete['sku']})");
            } else {
                // Mark as obsolete first
                update_post_meta($obsolete['id'], self::META_OBSOLETE_CHECK, current_time('mysql'));

                // Check if it's been obsolete for more than the threshold
                $obsolete_since = get_post_meta($obsolete['id'], self::META_OBSOLETE_CHECK, true);

                if (!empty($obsolete_since)) {
                    $days_obsolete = (time() - strtotime($obsolete_since)) / DAY_IN_SECONDS;

                    if ($days_obsolete >= self::STALENESS_THRESHOLD) {
                        // Delete or draft the product
                        $this->handle_stale_product($obsolete['id'], $obsolete);
                        $deleted_count++;
                    } else {
                        $this->plugin->logger->log("Product {$obsolete['sku']} obsolete for {$days_obsolete} days (threshold: " . self::STALENESS_THRESHOLD . " days)");
                    }
                }
            }
        }

        return [
            'found' => count($obsolete_products),
            'deleted' => $deleted_count,
        ];
    }

    /**
     * Handle stale product
     *
     * @param int $product_id Product ID
     * @param array $product_info Product information
     */
    private function handle_stale_product($product_id, $product_info) {
        $options = get_option('dekkimporter_options', []);
        $action = isset($options['obsolete_action']) ? $options['obsolete_action'] : 'draft';

        switch ($action) {
            case 'delete':
                wp_delete_post($product_id, true);
                $this->plugin->logger->log("Deleted obsolete product: {$product_info['name']} (ID: {$product_id})");
                break;

            case 'draft':
                wp_update_post([
                    'ID' => $product_id,
                    'post_status' => 'draft',
                ]);
                $this->plugin->logger->log("Drafted obsolete product: {$product_info['name']} (ID: {$product_id})");
                break;

            case 'out_of_stock':
                $product = wc_get_product($product_id);
                if ($product) {
                    $product->set_stock_status('outofstock');
                    $product->set_stock_quantity(0);
                    $product->save();
                }
                $this->plugin->logger->log("Marked as out of stock: {$product_info['name']} (ID: {$product_id})");
                break;
        }
    }

    /**
     * Get stale products
     * Products that haven't been synced in X days
     *
     * @param int $days Number of days
     * @return array Stale products
     */
    public function get_stale_products($days = null) {
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
     */
    private function update_sync_status($status, $message, $stats = []) {
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
     */
    private function save_sync_stats($stats) {
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
     * @return array Current sync status
     */
    public function get_sync_status() {
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
     * @return array Sync stats
     */
    public function get_sync_stats($limit = 10) {
        $stats = get_option(self::OPTION_SYNC_STATS, []);
        return array_slice($stats, -$limit);
    }
}
