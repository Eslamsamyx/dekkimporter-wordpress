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
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('dekkimporter_options', 'dekkimporter_options');

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
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>DekkImporter Settings</h1>
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
}
