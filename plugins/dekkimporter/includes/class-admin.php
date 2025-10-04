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
     *
     * @var DekkImporter
     */
    private $plugin;

    /**
     * Constructor
     *
     * @param DekkImporter $plugin Plugin instance
     */
    public function __construct($plugin) {
        $this->plugin = $plugin;

        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_dekkimporter_manual_sync', array($this, 'handle_manual_sync'));
        add_action('wp_ajax_dekkimporter_sync_progress', array($this, 'handle_sync_progress'));
        add_action('wp_ajax_dekkimporter_stop_sync', array($this, 'handle_stop_sync'));
        add_action('update_option_dekkimporter_sync_interval', array($this, 'reschedule_cron'), 10, 2);
    }

    /**
     * Add admin menu
     *
     * @return void
     */
    public function add_admin_menu(): void {
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
     *
     * @return void
     */
    public function register_settings(): void {
        register_setting('dekkimporter_options', 'dekkimporter_options', array(
            'sanitize_callback' => array($this, 'sanitize_options'),
        ));
        register_setting('dekkimporter_options', 'dekkimporter_sync_interval', array(
            'sanitize_callback' => array($this, 'sanitize_sync_interval'),
        ));
        register_setting('dekkimporter_options', 'dekkimporter_log_retention_days', array(
            'sanitize_callback' => array($this, 'sanitize_log_retention'),
        ));

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
            array($this, 'render_email_section'),
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

        // Cron Manager Settings
        add_settings_field(
            'dekkimporter_field_auto_process_cron',
            'Auto-Process Background Tasks',
            array($this, 'render_auto_process_cron_field'),
            'dekkimporter',
            'dekkimporter_sync_section'
        );

        add_settings_field(
            'dekkimporter_field_cron_batch_size',
            'Task Batch Size',
            array($this, 'render_cron_batch_size_field'),
            'dekkimporter',
            'dekkimporter_sync_section'
        );

        add_settings_field(
            'dekkimporter_field_cron_interval',
            'Cron Check Interval (Minutes)',
            array($this, 'render_cron_interval_field'),
            'dekkimporter',
            'dekkimporter_sync_section'
        );
    }

    /**
     * Sanitize options array
     *
     * @param mixed $input Input options
     * @return array<string, mixed> Sanitized options
     */
    public function sanitize_options($input): array {
        if (!is_array($input)) {
            return [];
        }

        $sanitized = [];

        // Sanitize API URLs
        if (isset($input['dekkimporter_bk_api_url'])) {
            $url = esc_url_raw($input['dekkimporter_bk_api_url']);
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $sanitized['dekkimporter_bk_api_url'] = $url;
            } else {
                add_settings_error('dekkimporter_options', 'invalid_bk_url', 'BK API URL is invalid.');
            }
        }

        if (isset($input['dekkimporter_bm_api_url'])) {
            $url = esc_url_raw($input['dekkimporter_bm_api_url']);
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $sanitized['dekkimporter_bm_api_url'] = $url;
            } else {
                add_settings_error('dekkimporter_options', 'invalid_bm_url', 'BM API URL is invalid.');
            }
        }

        // Sanitize markup (0-10000 range)
        if (isset($input['dekkimporter_field_markup'])) {
            $markup = absint($input['dekkimporter_field_markup']);
            if ($markup >= 0 && $markup <= 10000) {
                $sanitized['dekkimporter_field_markup'] = $markup;
            } else {
                add_settings_error('dekkimporter_options', 'invalid_markup', 'Price markup must be between 0 and 10000.');
                $sanitized['dekkimporter_field_markup'] = 400; // Default
            }
        }

        // Sanitize email fields (modern array syntax)
        $email_fields = [
            'dekkimporter_bk_email',
            'dekkimporter_bm_email',
            'dekkimporter_field_notification_email',
            'sync_notification_email',
        ];

        // Field name mapping for better error messages
        $field_labels = [
            'dekkimporter_bk_email' => __('BK Supplier Email', 'dekkimporter'),
            'dekkimporter_bm_email' => __('BM Supplier Email', 'dekkimporter'),
            'dekkimporter_field_notification_email' => __('CC Notification Email', 'dekkimporter'),
            'sync_notification_email' => __('Sync Notification Email', 'dekkimporter'),
        ];

        foreach ($email_fields as $field) {
            if (isset($input[$field]) && !empty($input[$field])) {
                $email = sanitize_email($input[$field]);
                if (is_email($email)) {
                    $sanitized[$field] = $email;
                } else {
                    $field_label = $field_labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
                    add_settings_error('dekkimporter_options', 'invalid_' . $field,
                        sprintf(__('%s is invalid. Please enter a valid email address.', 'dekkimporter'), $field_label)
                    );
                }
            }
        }

        // Sanitize cron manager settings
        if (isset($input['dekkimporter_field_auto_process_cron'])) {
            $sanitized['dekkimporter_field_auto_process_cron'] = (bool) $input['dekkimporter_field_auto_process_cron'];
        }

        if (isset($input['dekkimporter_field_cron_batch_size'])) {
            $batch_size = absint($input['dekkimporter_field_cron_batch_size']);
            if ($batch_size >= 1 && $batch_size <= 100) {
                $sanitized['dekkimporter_field_cron_batch_size'] = $batch_size;
            } else {
                add_settings_error('dekkimporter_options', 'invalid_batch_size', 'Task batch size must be between 1 and 100.');
                $sanitized['dekkimporter_field_cron_batch_size'] = 25; // Default
            }
        }

        if (isset($input['dekkimporter_field_cron_interval'])) {
            $interval = absint($input['dekkimporter_field_cron_interval']);
            if ($interval >= 1 && $interval <= 60) {
                $sanitized['dekkimporter_field_cron_interval'] = $interval;
            } else {
                add_settings_error('dekkimporter_options', 'invalid_cron_interval', 'Cron check interval must be between 1 and 60 minutes.');
                $sanitized['dekkimporter_field_cron_interval'] = 15; // Default
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize sync interval
     *
     * @param mixed $input Sync interval
     * @return string Sanitized interval
     */
    public function sanitize_sync_interval($input): string {
        $valid_intervals = ['every15minutes', 'hourly', 'twicedaily', 'daily', 'weekly'];

        if (in_array($input, $valid_intervals, true)) {
            return sanitize_text_field($input);
        }

        add_settings_error('dekkimporter_sync_interval', 'invalid_interval', 'Invalid sync interval selected.');
        return 'daily'; // Default
    }

    /**
     * Sanitize log retention days
     *
     * @param mixed $input Retention days
     * @return int Sanitized days
     */
    public function sanitize_log_retention($input): int {
        $days = absint($input);

        if ($days >= 1 && $days <= 365) {
            return $days;
        }

        add_settings_error('dekkimporter_log_retention_days', 'invalid_retention', 'Log retention must be between 1 and 365 days.');
        return 7; // Default
    }

    /**
     * Render API section description
     *
     * @return void
     */
    public function render_api_section(): void {
        ?>
        <div class="dekkimporter-section-header">
            <span class="dashicons dashicons-admin-site-alt3"></span>
            <div class="section-description">
                <p><?php esc_html_e('Configure API endpoints and pricing settings for fetching products from suppliers.', 'dekkimporter'); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Render Email section description
     *
     * @return void
     */
    public function render_email_section(): void {
        ?>
        <div class="dekkimporter-section-header">
            <span class="dashicons dashicons-email"></span>
            <div class="section-description">
                <p><?php esc_html_e('Configure email addresses for supplier notifications and order processing.', 'dekkimporter'); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Render text field
     *
     * @param array $args Field arguments
     * @return void
     */
    public function render_text_field(array $args): void {
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
     *
     * @param array $args Field arguments
     * @return void
     */
    public function render_markup_field(array $args): void {
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
     *
     * @param array $args Field arguments
     * @return void
     */
    public function render_email_field(array $args): void {
        $options = get_option('dekkimporter_options', array());
        $value = isset($options[$args['field']]) ? $options[$args['field']] : '';
        ?>
        <input type="email" name="dekkimporter_options[<?php echo esc_attr($args['field']); ?>]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
        <?php
    }

    /**
     * Render sync section description
     *
     * @return void
     */
    public function render_sync_section(): void {
        ?>
        <div class="dekkimporter-section-header">
            <span class="dashicons dashicons-update"></span>
            <div class="section-description">
                <p><?php esc_html_e('Configure automatic synchronization and log management settings.', 'dekkimporter'); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Render sync interval field
     *
     * @return void
     */
    public function render_sync_interval_field(): void {
        $interval = get_option('dekkimporter_sync_interval', 'daily');
        $intervals = array(
            'every15minutes' => esc_html__('Every 15 Minutes', 'dekkimporter'),
            'hourly'         => esc_html__('Hourly', 'dekkimporter'),
            'twicedaily'     => esc_html__('Twice Daily', 'dekkimporter'),
            'daily'          => esc_html__('Daily', 'dekkimporter'),
            'weekly'         => esc_html__('Weekly', 'dekkimporter'),
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
     *
     * @return void
     */
    public function render_log_retention_field(): void {
        $retention_days = get_option('dekkimporter_log_retention_days', 7);
        ?>
        <input type="number" name="dekkimporter_log_retention_days" value="<?php echo esc_attr($retention_days); ?>" min="1" max="365" class="small-text" />
        <p class="description"><?php esc_html_e('Number of days to keep log files before automatic cleanup.', 'dekkimporter'); ?></p>
        <?php
    }

    /**
     * Render sync notification email field
     *
     * @return void
     */
    public function render_sync_notification_email_field(): void {
        $options = get_option('dekkimporter_options', array());
        $email = isset($options['sync_notification_email']) ? $options['sync_notification_email'] : '';
        ?>
        <input type="email" name="dekkimporter_options[sync_notification_email]" value="<?php echo esc_attr($email); ?>" class="regular-text" />
        <p class="description"><?php esc_html_e('Receive sync completion reports at this email address.', 'dekkimporter'); ?></p>
        <?php
    }

    /**
     * Settings page
     *
     * @return void
     */
    public function settings_page(): void {
        $next_sync = wp_next_scheduled('dekkimporter_sync_products');
        $last_sync_stats = get_option('dekkimporter_last_sync_stats', array());
        $last_sync_time = get_option('dekkimporter_last_sync_time', 0);
        ?>
        <div class="wrap dekkimporter-wrap">
            <h1 class="wp-heading-inline">
                <span class="dashicons dashicons-update"></span>
                <?php esc_html_e('DekkImporter', 'dekkimporter'); ?>
            </h1>
            <hr class="wp-header-end">

            <?php if ($next_sync) : ?>
                <!-- Sync Control Box -->
                <div class="dekk-sync-control postbox">
                    <div class="postbox-header">
                        <h2 class="hndle">
                            <span class="dashicons dashicons-clock"></span>
                            <?php esc_html_e('Sync Control', 'dekkimporter'); ?>
                        </h2>
                    </div>
                    <div class="inside">
                        <div class="dekk-sync-info">
                            <div class="dekk-countdown-section">
                                <label><?php esc_html_e('Next Scheduled Sync', 'dekkimporter'); ?></label>
                                <div class="dekk-countdown" id="dekkimporter-countdown" data-timestamp="<?php echo esc_attr($next_sync); ?>"></div>
                            </div>
                            <div class="dekk-action-section">
                                <button type="button" id="dekkimporter-manual-sync" class="button button-primary button-hero">
                                    <span class="dashicons dashicons-update"></span>
                                    <?php esc_html_e('Run Manual Sync Now', 'dekkimporter'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress Container -->
                <div id="dekkimporter-progress-container" class="dekk-progress-box postbox" style="display: none;">
                    <div class="postbox-header">
                        <h2 class="hndle">
                            <span class="dashicons dashicons-update-alt"></span>
                            <span id="progress-message"><?php esc_html_e('Sync in Progress', 'dekkimporter'); ?></span>
                        </h2>
                        <div class="dekk-progress-percent">
                            <span id="progress-percentage">0%</span>
                        </div>
                    </div>
                    <div class="inside">
                        <!-- Progress Bar -->
                        <div class="dekk-progress-bar-wrapper">
                            <div class="dekk-progress-bar">
                                <div id="progress-fill" class="dekk-progress-fill"></div>
                            </div>
                        </div>

                        <!-- Stats Grid -->
                        <div class="dekk-stats-grid">
                            <div class="dekk-stat-item">
                                <span class="dashicons dashicons-archive"></span>
                                <div class="dekk-stat-content">
                                    <span class="dekk-stat-label"><?php esc_html_e('Processed', 'dekkimporter'); ?></span>
                                    <span class="dekk-stat-value" id="stat-processed">0 / 0</span>
                                </div>
                            </div>
                            <div class="dekk-stat-item stat-created">
                                <span class="dashicons dashicons-plus-alt"></span>
                                <div class="dekk-stat-content">
                                    <span class="dekk-stat-label"><?php esc_html_e('Created', 'dekkimporter'); ?></span>
                                    <span class="dekk-stat-value" id="stat-created">0</span>
                                </div>
                            </div>
                            <div class="dekk-stat-item stat-updated">
                                <span class="dashicons dashicons-update"></span>
                                <div class="dekk-stat-content">
                                    <span class="dekk-stat-label"><?php esc_html_e('Updated', 'dekkimporter'); ?></span>
                                    <span class="dekk-stat-value" id="stat-updated">0</span>
                                </div>
                            </div>
                            <div class="dekk-stat-item stat-skipped">
                                <span class="dashicons dashicons-controls-forward"></span>
                                <div class="dekk-stat-content">
                                    <span class="dekk-stat-label"><?php esc_html_e('Skipped', 'dekkimporter'); ?></span>
                                    <span class="dekk-stat-value" id="stat-skipped">0</span>
                                </div>
                            </div>
                            <div class="dekk-stat-item stat-time">
                                <span class="dashicons dashicons-clock"></span>
                                <div class="dekk-stat-content">
                                    <span class="dekk-stat-label"><?php esc_html_e('Time Remaining', 'dekkimporter'); ?></span>
                                    <span class="dekk-stat-value" id="stat-time-remaining">--</span>
                                </div>
                            </div>
                        </div>

                        <!-- Stop Button -->
                        <div class="dekk-actions">
                            <button type="button" id="dekkimporter-stop-sync" class="button button-secondary dekk-stop-btn">
                                <span class="dashicons dashicons-no"></span>
                                <?php esc_html_e('Stop Sync', 'dekkimporter'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($last_sync_stats)) : ?>
                <!-- Stats Dashboard -->
                <div class="dekk-stats-dashboard">
                    <div class="dekk-stat-card dekk-stat-created">
                        <div class="dekk-stat-icon">
                            <span class="dashicons dashicons-plus-alt"></span>
                        </div>
                        <div class="dekk-stat-details">
                            <span class="dekk-stat-number"><?php echo absint($last_sync_stats['products_created'] ?? 0); ?></span>
                            <span class="dekk-stat-title"><?php esc_html_e('Products Created', 'dekkimporter'); ?></span>
                        </div>
                    </div>
                    <div class="dekk-stat-card dekk-stat-updated">
                        <div class="dekk-stat-icon">
                            <span class="dashicons dashicons-update"></span>
                        </div>
                        <div class="dekk-stat-details">
                            <span class="dekk-stat-number"><?php echo absint($last_sync_stats['products_updated'] ?? 0); ?></span>
                            <span class="dekk-stat-title"><?php esc_html_e('Products Updated', 'dekkimporter'); ?></span>
                        </div>
                    </div>
                    <div class="dekk-stat-card dekk-stat-fetched">
                        <div class="dekk-stat-icon">
                            <span class="dashicons dashicons-download"></span>
                        </div>
                        <div class="dekk-stat-details">
                            <span class="dekk-stat-number"><?php echo absint($last_sync_stats['products_fetched'] ?? 0); ?></span>
                            <span class="dekk-stat-title"><?php esc_html_e('Products Fetched', 'dekkimporter'); ?></span>
                        </div>
                    </div>
                    <div class="dekk-stat-card dekk-stat-errors <?php echo (isset($last_sync_stats['errors']) && $last_sync_stats['errors'] > 0) ? 'has-errors' : ''; ?>">
                        <div class="dekk-stat-icon">
                            <span class="dashicons dashicons-warning"></span>
                        </div>
                        <div class="dekk-stat-details">
                            <span class="dekk-stat-number"><?php echo absint($last_sync_stats['errors'] ?? 0); ?></span>
                            <span class="dekk-stat-title"><?php esc_html_e('Errors', 'dekkimporter'); ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($last_sync_time > 0) : ?>
                    <p class="dekk-last-sync">
                        <span class="dashicons dashicons-clock"></span>
                        <?php
                        printf(
                            esc_html__('Last sync: %s', 'dekkimporter'),
                            esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $last_sync_time))
                        );
                        ?>
                    </p>
                <?php endif; ?>

                <!-- Error Log Display -->
                <?php if (isset($last_sync_stats['errors']) && $last_sync_stats['errors'] > 0 && !empty($last_sync_stats['error_log'])) : ?>
                    <div class="dekk-error-box postbox">
                        <div class="postbox-header">
                            <h2 class="hndle">
                                <span class="dashicons dashicons-warning"></span>
                                <?php esc_html_e('Sync Errors', 'dekkimporter'); ?>
                                <span class="dekk-error-count">(<?php echo absint($last_sync_stats['errors']); ?>)</span>
                            </h2>
                        </div>
                        <div class="inside">
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th width="15%"><?php esc_html_e('SKU', 'dekkimporter'); ?></th>
                                        <th width="30%"><?php esc_html_e('Product Name', 'dekkimporter'); ?></th>
                                        <th width="40%"><?php esc_html_e('Error Message', 'dekkimporter'); ?></th>
                                        <th width="15%"><?php esc_html_e('Time', 'dekkimporter'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($last_sync_stats['error_log'] as $error) : ?>
                                        <tr>
                                            <td><code><?php echo esc_html($error['sku']); ?></code></td>
                                            <td><strong><?php echo esc_html($error['name']); ?></strong></td>
                                            <td><?php echo esc_html($error['message']); ?></td>
                                            <td><?php echo esc_html(date_i18n(get_option('time_format'), strtotime($error['timestamp']))); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Settings Form -->
            <div class="dekk-settings-box postbox">
                <div class="postbox-header">
                    <h2 class="hndle">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <?php esc_html_e('Configuration', 'dekkimporter'); ?>
                    </h2>
                </div>
                <div class="inside">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('dekkimporter_options');
                        do_settings_sections('dekkimporter');
                        submit_button();
                        ?>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Logs page
     *
     * @return void
     */
    public function logs_page(): void {
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
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_admin_assets(string $hook): void {
        if ($hook !== 'toplevel_page_dekkimporter' && $hook !== 'index.php') {
            return;
        }

        // Enqueue Bootstrap CSS (Local)
        wp_enqueue_style(
            'bootstrap',
            DEKKIMPORTER_PLUGIN_URL . 'assets/bootstrap/bootstrap.min.css',
            array(),
            '5.3.2'
        );

        // Enqueue Bootstrap Icons (Local)
        wp_enqueue_style(
            'bootstrap-icons',
            DEKKIMPORTER_PLUGIN_URL . 'assets/bootstrap-icons/bootstrap-icons.min.css',
            array(),
            '1.11.3'
        );

        wp_enqueue_style(
            'dekkimporter-admin',
            DEKKIMPORTER_PLUGIN_URL . 'assets/css/admin.css',
            array('bootstrap', 'bootstrap-icons'),
            DEKKIMPORTER_VERSION
        );

        // Enqueue Bootstrap JS (Local)
        wp_enqueue_script(
            'bootstrap-bundle',
            DEKKIMPORTER_PLUGIN_URL . 'assets/bootstrap/bootstrap.bundle.min.js',
            array(),
            '5.3.2',
            true
        );

        wp_enqueue_script(
            'dekkimporter-admin',
            DEKKIMPORTER_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'bootstrap-bundle'),
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
                    'stopSync'   => wp_create_nonce('dekkimporter_stop_sync'),
                ),
            )
        );
    }

    /**
     * Handle manual sync AJAX request
     *
     * @return void
     */
    public function handle_manual_sync(): void {
        check_ajax_referer('dekkimporter_manual_sync', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }

        // Check if sync is already running
        $lock_key = 'dekkimporter_sync_lock';
        if (get_transient($lock_key)) {
            wp_send_json_error(array('message' => 'Sync already in progress'));
            return;
        }

        $this->plugin->logger->log('=== MANUAL SYNC STARTED (FASTCGI) ===');

        // Increase PHP limits for long-running sync
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);
        ignore_user_abort(true);

        // Prepare success response
        $response = array(
            'success' => true,
            'data' => array(
                'message' => 'Sync started in background',
                'background' => true,
            )
        );

        // Send response and close connection
        if (function_exists('fastcgi_finish_request')) {
            // For PHP-FPM
            wp_send_json($response);
            fastcgi_finish_request();
        } else {
            // For other environments - manually close connection
            // Clear any existing output buffers
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            // Start new buffer
            ob_start();

            // Send headers
            header('Content-Type: application/json; charset=utf-8');
            header('Connection: close');

            // Send JSON response
            echo json_encode($response);

            // Get the size and close
            $size = ob_get_length();
            header('Content-Length: ' . $size);

            // Flush everything
            ob_end_flush();
            flush();

            // Close session if exists
            if (session_id()) {
                session_write_close();
            }
        }

        // Now run the sync in this same process (but connection is closed)
        $this->run_sync_process();
    }

    /**
     * Handle sync progress AJAX request
     *
     * @return void
     */
    public function handle_sync_progress(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }

        $progress = get_transient('dekkimporter_sync_progress');

        if ($progress === false) {
            // No sync in progress
            wp_send_json_success(array(
                'in_progress' => false,
                'message' => 'No sync in progress',
            ));
        } else {
            wp_send_json_success(array(
                'in_progress' => true,
                'progress' => $progress,
            ));
        }
    }

    /**
     * Handle stop sync AJAX request
     *
     * @return void
     */
    public function handle_stop_sync(): void {
        check_ajax_referer('dekkimporter_stop_sync', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }

        $this->plugin->logger->log('=== SYNC STOP REQUESTED BY USER ===');

        // Set cancellation flag
        set_transient('dekkimporter_sync_cancelled', true, 300); // 5 minutes

        // Clear locks and progress
        delete_transient('dekkimporter_sync_lock');
        delete_transient('dekkimporter_sync_progress');

        $this->plugin->logger->log('Sync locks cleared, cancellation flag set');

        wp_send_json_success(array(
            'message' => 'Sync stopped successfully',
        ));
    }

    /**
     * Run the actual sync process (connection already closed to client)
     *
     * @return void
     */
    private function run_sync_process(): void {
        // Re-verify capabilities for security
        if (!current_user_can('manage_options')) {
            $this->plugin->logger->log('Unauthorized sync attempt in background process', 'ERROR');
            return;
        }

        $this->plugin->logger->log('=== SYNC PROCESS EXECUTING (CONNECTION CLOSED) ===');

        $options = get_option('dekkimporter_options', []);

        // Validate options is an array
        if (!is_array($options)) {
            $this->plugin->logger->log('Invalid options format, using defaults', 'WARNING');
            $options = [];
        }

        $sync_options = [
            'handle_obsolete' => isset($options['handle_obsolete']) ? (bool) $options['handle_obsolete'] : true,
            'batch_size'      => isset($options['sync_batch_size']) ? absint($options['sync_batch_size']) : 50,
            'dry_run'         => false,
        ];

        // Validate batch_size range
        if ($sync_options['batch_size'] < 1 || $sync_options['batch_size'] > 1000) {
            $sync_options['batch_size'] = 50;
            $this->plugin->logger->log('Batch size out of range, using default: 50', 'WARNING');
        }

        $stats = $this->plugin->sync_manager->full_sync($sync_options);

        update_option('dekkimporter_last_sync_stats', $stats);
        update_option('dekkimporter_last_sync_time', time());

        $this->plugin->logger->log('=== SYNC PROCESS COMPLETED ===');
        $this->plugin->logger->log('Results: ' . json_encode($stats));
    }

    /**
     * Reschedule cron when interval changes
     *
     * @param mixed $old_value Old interval value
     * @param mixed $new_value New interval value
     * @return void
     */
    public function reschedule_cron($old_value, $new_value): void {
        if ($old_value !== $new_value) {
            $this->plugin->cron->activate();
        }
    }

    /**
     * Render auto-process cron field (checkbox)
     *
     * @return void
     */
    public function render_auto_process_cron_field(): void {
        $options = get_option('dekkimporter_options', array());
        $enabled = isset($options['dekkimporter_field_auto_process_cron']) ? (bool)$options['dekkimporter_field_auto_process_cron'] : true;
        ?>
        <label>
            <input type="checkbox" name="dekkimporter_options[dekkimporter_field_auto_process_cron]" value="1" <?php checked($enabled, true); ?> />
            <?php esc_html_e('Automatically process Action Scheduler and WordPress cron tasks after each sync', 'dekkimporter'); ?>
        </label>
        <p class="description">
            <?php esc_html_e('When enabled, processes WooCommerce background tasks (like product attribute lookups) automatically after sync. This prevents task backlog and keeps your store performant. Recommended: Enabled', 'dekkimporter'); ?>
        </p>
        <?php
    }

    /**
     * Render cron batch size field (number input)
     *
     * @return void
     */
    public function render_cron_batch_size_field(): void {
        $options = get_option('dekkimporter_options', array());
        $batch_size = isset($options['dekkimporter_field_cron_batch_size']) ? (int)$options['dekkimporter_field_cron_batch_size'] : 25;
        ?>
        <input type="number" name="dekkimporter_options[dekkimporter_field_cron_batch_size]" value="<?php echo esc_attr($batch_size); ?>" min="1" max="100" class="small-text" />
        <p class="description">
            <?php esc_html_e('Maximum number of Action Scheduler tasks to process per sync. Lower values reduce server load. Higher values process backlogs faster. Default: 25', 'dekkimporter'); ?>
        </p>
        <?php
    }

    /**
     * Render cron interval field (number input)
     *
     * @return void
     */
    public function render_cron_interval_field(): void {
        $options = get_option('dekkimporter_options', array());
        $interval = isset($options['dekkimporter_field_cron_interval']) ? (int)$options['dekkimporter_field_cron_interval'] : 15;
        ?>
        <input type="number" name="dekkimporter_options[dekkimporter_field_cron_interval]" value="<?php echo esc_attr($interval); ?>" min="1" max="60" class="small-text" /> <?php esc_html_e('minutes', 'dekkimporter'); ?>
        <p class="description">
            <?php esc_html_e('If WordPress cron hasn\'t run for this many minutes, trigger it automatically after sync. Ensures cron tasks don\'t get stuck. Default: 15 minutes', 'dekkimporter'); ?>
        </p>
        <?php
    }
}
