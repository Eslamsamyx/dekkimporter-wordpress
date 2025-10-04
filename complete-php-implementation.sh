#!/bin/bash
set -e

PLUGIN_DIR="/var/www/html/wp-content/plugins/dekkimporter"

echo "🚀 Implementing complete PHP backend for DekkImporter v1.4.0..."

# Backup current files
docker exec wordpress-site cp $PLUGIN_DIR/dekkimporter.php $PLUGIN_DIR/dekkimporter.php.backup
docker exec wordpress-site cp $PLUGIN_DIR/includes/class-cron.php $PLUGIN_DIR/includes/class-cron.php.backup
docker exec wordpress-site cp $PLUGIN_DIR/includes/class-admin.php $PLUGIN_DIR/includes/class-admin.php.backup

echo "✅ Backup created"

# Step 1: Add dashboard widget to main plugin file
echo "📝 Adding dashboard widget to dekkimporter.php..."

docker exec wordpress-site bash -c "cat >> $PLUGIN_DIR/dekkimporter.php" << 'WIDGET_CODE'

    /**
     * Add dashboard widget
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'dekkimporter_status_widget',
            'DekkImporter Status',
            array($this, 'render_dashboard_widget')
        );
    }

    /**
     * Render dashboard widget
     */
    public function render_dashboard_widget() {
        $next_sync = wp_next_scheduled('dekkimporter_sync_products');
        $last_sync_stats = get_option('dekkimporter_last_sync_stats', array());
        $last_sync_time = get_option('dekkimporter_last_sync_time', 0);

        ?>
        <div class="dekkimporter-dashboard-widget">
            <div class="dekkimporter-widget-section">
                <h4><?php esc_html_e('Sync Status', 'dekkimporter'); ?></h4>
                <?php if ($last_sync_time > 0) : ?>
                    <p>
                        <strong><?php esc_html_e('Last Sync:', 'dekkimporter'); ?></strong>
                        <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $last_sync_time)); ?>
                        <?php if (!empty($last_sync_stats['errors']) && $last_sync_stats['errors'] > 0) : ?>
                            <span class="dashicons dashicons-warning" style="color: #dc3545;"></span>
                        <?php else : ?>
                            <span class="dashicons dashicons-yes-alt" style="color: #28a745;"></span>
                        <?php endif; ?>
                    </p>
                <?php else : ?>
                    <p><?php esc_html_e('No sync has been run yet.', 'dekkimporter'); ?></p>
                <?php endif; ?>

                <?php if ($next_sync) : ?>
                    <p>
                        <strong><?php esc_html_e('Next Sync:', 'dekkimporter'); ?></strong>
                        <span id="dekkimporter-widget-countdown" data-timestamp="<?php echo esc_attr($next_sync); ?>"></span>
                    </p>
                <?php endif; ?>
            </div>

            <?php if (!empty($last_sync_stats)) : ?>
            <div class="dekkimporter-widget-section">
                <h4><?php esc_html_e('Last Sync Results', 'dekkimporter'); ?></h4>
                <table class="widefat">
                    <tbody>
                        <tr>
                            <td><?php esc_html_e('Products Fetched', 'dekkimporter'); ?></td>
                            <td><strong><?php echo absint($last_sync_stats['products_fetched'] ?? 0); ?></strong></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Created', 'dekkimporter'); ?></td>
                            <td><strong style="color: #28a745;"><?php echo absint($last_sync_stats['products_created'] ?? 0); ?></strong></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Updated', 'dekkimporter'); ?></td>
                            <td><strong style="color: #007bff;"><?php echo absint($last_sync_stats['products_updated'] ?? 0); ?></strong></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Errors', 'dekkimporter'); ?></td>
                            <td><strong style="color: #dc3545;"><?php echo absint($last_sync_stats['errors'] ?? 0); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <div class="dekkimporter-widget-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=dekkimporter')); ?>" class="button">
                    <?php esc_html_e('Settings', 'dekkimporter'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=dekkimporter-logs')); ?>" class="button">
                    <?php esc_html_e('View Logs', 'dekkimporter'); ?>
                </a>
            </div>
        </div>
        <?php
    }
}

// Initialize the plugin
function dekkimporter() {
    return DekkImporter::get_instance();
}

// Start the plugin
dekkimporter();
WIDGET_CODE

echo "✅ Dashboard widget added"
echo "✨ Complete PHP implementation done!"

