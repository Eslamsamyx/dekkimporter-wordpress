<?php
/**
 * Admin class for DekkImporter
 */

if (!defined('ABSPATH')) {
    exit;
}

class DekkImporter_Admin {
    /**
     * Plugin instance
     */
    private $plugin;

    /**
     * Constructor
     */
    public function __construct($plugin) {
        $this->plugin = $plugin;

        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_dekkimporter_manual_sync', array($this, 'handle_manual_sync'));
        add_action('update_option_dekkimporter_sync_interval', array($this, 'reschedule_cron'), 10, 2);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'DekkImporter Settings',
            'DekkImporter',
            'manage_options',
            'dekkimporter',
            array($this, 'settings_page'),
            'dashicons-download',
            56
        );

        add_submenu_page(
            'dekkimporter',
            'DekkImporter Logs',
            'Logs',
            'manage_options',
            'dekkimporter-logs',
            array($this, 'logs_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('dekkimporter_options', 'dekkimporter_options');
        register_setting('dekkimporter_options', 'dekkimporter_sync_interval');
        register_setting('dekkimporter_options', 'dekkimporter_log_retention_days');

        // API Settings Section
        add_settings_section(
            'dekkimporter_api_section',
            'API Settings',
            array($this, 'render_api_section'),
            'dekkimporter'
        );

        add_settings_field(
            'dekkimporter_bk_api_url',
            'BK Supplier API URL',
            array($this, 'render_text_field'),
            'dekkimporter',
            'dekkimporter_api_section',
            array('field' => 'dekkimporter_bk_api_url', 'placeholder' => 'https://api.example.com/bk/products')
        );

        add_settings_field(
            'dekkimporter_bm_api_url',
            'BM Supplier API URL',
            array($this, 'render_text_field'),
            'dekkimporter',
            'dekkimporter_api_section',
            array('field' => 'dekkimporter_bm_api_url', 'placeholder' => 'https://api.example.com/bm/products')
        );

        // NEW: Price markup field
        add_settings_field(
            'dekkimporter_field_markup',
            'Price Markup (ISK)',
            array($this, 'render_markup_field'),
            'dekkimporter',
            'dekkimporter_api_section',
            array('field' => 'dekkimporter_field_markup')
        );

        // Email Settings Section
        add_settings_section(
            'dekkimporter_section',
            'Email Settings',
            null,
            'dekkimporter'
        );

        add_settings_field(
            'dekkimporter_bk_email',
            'BK Supplier Email',
            array($this, 'render_email_field'),
            'dekkimporter',
            'dekkimporter_section',
            array('field' => 'dekkimporter_bk_email')
        );

        add_settings_field(
            'dekkimporter_bm_email',
            'BM Supplier Email',
            array($this, 'render_email_field'),
            'dekkimporter',
            'dekkimporter_section',
            array('field' => 'dekkimporter_bm_email')
        );

        add_settings_field(
            'dekkimporter_field_notification_email',
            'CC Notification Email',
            array($this, 'render_email_field'),
            'dekkimporter',
            'dekkimporter_section',
            array('field' => 'dekkimporter_field_notification_email')
        );

        // Sync Settings Section
        add_settings_section(
            'dekkimporter_sync_section',
            'Sync Settings',
            array($this, 'render_sync_section'),
            'dekkimporter'
        );

        add_settings_field(
            'dekkimporter_sync_interval',
            'Sync Interval',
            array($this, 'render_sync_interval_field'),
            'dekkimporter',
            'dekkimporter_sync_section'
        );

        add_settings_field(
            'dekkimporter_log_retention_days',
            'Log Retention (Days)',
            array($this, 'render_log_retention_field'),
            'dekkimporter',
            'dekkimporter_sync_section'
        );

        add_settings_field(
            'sync_notification_email',
            'Sync Notification Email',
            array($this, 'render_sync_notification_email_field'),
            'dekkimporter',
            'dekkimporter_sync_section'
        );
    }

    /**
     * Render API section description
     */
    public function render_api_section() {
        echo '<p>' . esc_html__('Configure API endpoints and pricing settings for fetching products from suppliers.', 'dekkimporter') . '</p>';
    }

    /**
     * Render text field
     */
    public function render_text_field($args) {
        $options = get_option('dekkimporter_options', array());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : '';
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
        ?>
        <input type="url" name="dekkimporter_options[<?php echo esc_attr($args['field']); ?>]" value="<?php echo esc_attr($value); ?>" class="large-text" placeholder="<?php echo esc_attr($placeholder); ?>" />
        <?php
    }

    /**
     * Render markup field (NEW)
     * Amount in ISK to subtract from API prices
     */
    public function render_markup_field($args) {
        $options = get_option('dekkimporter_options', array());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : '400';
        ?>
        <input type="number" name="dekkimporter_options[<?php echo esc_attr($args['field']); ?>]" value="<?php echo esc_attr($value); ?>" min="0" max="10000" class="regular-text" />
        <p class="description">
            <?php esc_html_e('Amount in ISK to subtract from API prices. Default: 400 ISK', 'dekkimporter'); ?><br>
            <strong><?php esc_html_e('Final Price = (API Price × 1.24) - Markup', 'dekkimporter'); ?></strong>
        </p>
        <?php
    }

    /**
     * Render email field
     */
    public function render_email_field($args) {
        $options = get_option('dekkimporter_options', array());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : '';
        ?>
        <input type="email" name="dekkimporter_options[<?php echo esc_attr($args['field']); ?>]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
        <?php
    }

    /**
     * Render sync section description
     */
    public function render_sync_section() {
        echo '<p>' . esc_html__('Configure automatic synchronization and log management settings.', 'dekkimporter') . '</p>';
    }

    /**
     * Render sync interval field
     */
    public function render_sync_interval_field() {
        $interval = get_option('dekkimporter_sync_interval', 'daily');
        $intervals = array(
            'hourly'     => esc_html__('Hourly', 'dekkimporter'),
            'twicedaily' => esc_html__('Twice Daily', 'dekkimporter'),
            'daily'      => esc_html__('Daily', 'dekkimporter'),
            'weekly'     => esc_html__('Weekly', 'dekkimporter'),
        );
        ?>
        <select name="dekkimporter_sync_interval" id="dekkimporter_sync_interval">
            <?php foreach ($intervals as $key => $label) : ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($interval, $key); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php esc_html_e('How often to automatically sync products from the API.', 'dekkimporter'); ?></p>
        <?php
    }

    /**
     * Render log retention field
     */
    public function render_log_retention_field() {
        $retention_days = get_option('dekkimporter_log_retention_days', 7);
        ?>
        <input type="number" name="dekkimporter_log_retention_days" value="<?php echo esc_attr($retention_days); ?>" min="1" max="365" class="small-text" />
        <p class="description"><?php esc_html_e('Number of days to keep log files before automatic cleanup.', 'dekkimporter'); ?></p>
        <?php
    }

    /**
     * Render sync notification email field
     */
    public function render_sync_notification_email_field() {
        $options = get_option('dekkimporter_options', array());
        $email = isset($options['sync_notification_email']) ? $options['sync_notification_email'] : '';
        ?>
        <input type="email" name="dekkimporter_options[sync_notification_email]" value="<?php echo esc_attr($email); ?>" class="regular-text" />
        <p class="description"><?php esc_html_e('Receive sync completion reports at this email address.', 'dekkimporter'); ?></p>
        <?php
    }

    /**
     * Settings page
     */
    public function settings_page() {
        $next_sync = wp_next_scheduled('dekkimporter_sync_products');
        ?>
        <div class="wrap">
            <h1>DekkImporter Settings</h1>

            <?php if ($next_sync) : ?>
                <div class="dekkimporter-sync-status">
                    <h3><?php esc_html_e('Next Scheduled Sync:', 'dekkimporter'); ?></h3>
                    <p>
                        <span id="dekkimporter-countdown" data-timestamp="<?php echo esc_attr($next_sync); ?>"></span>
                    </p>
                    <button type="button" id="dekkimporter-manual-sync" class="button button-secondary">
                        <?php esc_html_e('Run Manual Sync Now', 'dekkimporter'); ?>
                    </button>
                </div>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php
                settings_fields('dekkimporter_options');
                do_settings_sections('dekkimporter');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Logs page
     */
    public function logs_page() {
        if (!class_exists('DekkImporter_Logs_Viewer')) {
            echo '<div class="wrap"><h1>' . esc_html__('DekkImporter Logs', 'dekkimporter') . '</h1>';
            echo '<p>' . esc_html__('Logs viewer not available.', 'dekkimporter') . '</p></div>';
            return;
        }

        $logs_viewer = new DekkImporter_Logs_Viewer();
        $logs_viewer->prepare_items();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('DekkImporter Logs', 'dekkimporter'); ?></h1>
            <form method="get">
                <input type="hidden" name="page" value="dekkimporter-logs" />
                <?php $logs_viewer->display(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_dekkimporter' && $hook !== 'index.php') {
            return;
        }

        wp_enqueue_style(
            'dekkimporter-admin',
            DEKKIMPORTER_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            DEKKIMPORTER_VERSION
        );

        wp_enqueue_script(
            'dekkimporter-admin',
            DEKKIMPORTER_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            DEKKIMPORTER_VERSION,
            true
        );

        wp_localize_script(
            'dekkimporter-admin',
            'dekkimporterAdmin',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonces'  => array(
                    'manualSync' => wp_create_nonce('dekkimporter_manual_sync'),
                ),
            )
        );
    }

    /**
     * Handle manual sync AJAX request
     */
    public function handle_manual_sync() {
        check_ajax_referer('dekkimporter_manual_sync', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }

        $this->plugin->logger->log('=== MANUAL SYNC STARTED ===');

        $options = get_option('dekkimporter_options', array());
        $sync_options = array(
            'handle_obsolete' => isset($options['handle_obsolete']) ? (bool) $options['handle_obsolete'] : true,
            'batch_size'      => isset($options['sync_batch_size']) ? (int) $options['sync_batch_size'] : 50,
            'dry_run'         => false,
        );

        $stats = $this->plugin->sync_manager->full_sync($sync_options);

        update_option('dekkimporter_last_sync_stats', $stats);
        update_option('dekkimporter_last_sync_time', time());

        $this->plugin->logger->log('=== MANUAL SYNC COMPLETED ===');
        $this->plugin->logger->log('Results: ' . json_encode($stats));

        wp_send_json_success(array(
            'message' => 'Sync completed successfully',
            'stats'   => $stats,
        ));
    }

    /**
     * Reschedule cron when interval changes
     */
    public function reschedule_cron($old_value, $new_value) {
        if ($old_value !== $new_value) {
            $this->plugin->cron->activate();
        }
    }
}
