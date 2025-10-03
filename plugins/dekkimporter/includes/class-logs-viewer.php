<?php
/**
 * Logs Viewer class for DekkImporter
 * Extends WP_List_Table to display log entries
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class DekkImporter_Logs_Viewer extends WP_List_Table {
    /**
     * Log entries
     */
    private $log_entries = array();

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(array(
            'singular' => 'log',
            'plural'   => 'logs',
            'ajax'     => false,
        ));
    }

    /**
     * Get columns
     */
    public function get_columns() {
        return array(
            'timestamp' => esc_html__('Timestamp', 'dekkimporter'),
            'level'     => esc_html__('Level', 'dekkimporter'),
            'message'   => esc_html__('Message', 'dekkimporter'),
        );
    }

    /**
     * Get sortable columns
     */
    public function get_sortable_columns() {
        return array(
            'timestamp' => array('timestamp', true),
            'level'     => array('level', false),
        );
    }

    /**
     * Prepare items for display
     */
    public function prepare_items() {
        $this->_column_headers = array(
            $this->get_columns(),
            array(),
            $this->get_sortable_columns(),
        );

        $this->log_entries = $this->get_log_entries();

        // Pagination
        $per_page = 50;
        $current_page = $this->get_pagenum();
        $total_items = count($this->log_entries);

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ));

        $this->items = array_slice($this->log_entries, (($current_page - 1) * $per_page), $per_page);
    }

    /**
     * Get log entries from files
     */
    private function get_log_entries() {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/dekkimporter-logs';

        if (!is_dir($log_dir)) {
            return array();
        }

        // Get date filter from request
        $date_filter = isset($_GET['date_filter']) ? sanitize_text_field($_GET['date_filter']) : '';

        // Get all log files
        $log_files = glob($log_dir . '/dekkimporter-*.log');
        if (empty($log_files)) {
            return array();
        }

        // Sort by modification time (newest first)
        usort($log_files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // If date filter is set, only use that file
        if (!empty($date_filter)) {
            $filtered_file = $log_dir . '/dekkimporter-' . $date_filter . '.log';
            if (file_exists($filtered_file)) {
                $log_files = array($filtered_file);
            } else {
                return array();
            }
        }

        $entries = array();
        $max_files = empty($date_filter) ? 5 : 1; // Show last 5 files or filtered file

        foreach (array_slice($log_files, 0, $max_files) as $log_file) {
            $content = file_get_contents($log_file);
            if (empty($content)) {
                continue;
            }

            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                if (empty($line)) {
                    continue;
                }

                $parsed = $this->parse_log_line($line);
                if ($parsed) {
                    $entries[] = $parsed;
                }
            }
        }

        // Sort by timestamp (newest first)
        usort($entries, function($a, $b) {
            return $b['timestamp_raw'] - $a['timestamp_raw'];
        });

        return $entries;
    }

    /**
     * Parse a log line
     */
    private function parse_log_line($line) {
        // Format: [2024-01-15 10:30:45] LEVEL: Message
        if (!preg_match('/^\[(.+?)\]\s*(\w+):\s*(.+)$/', $line, $matches)) {
            // Try simpler format: [2024-01-15 10:30:45] Message
            if (!preg_match('/^\[(.+?)\]\s*(.+)$/', $line, $matches)) {
                return null;
            }
            return array(
                'timestamp'     => $matches[1],
                'timestamp_raw' => strtotime($matches[1]),
                'level'         => 'INFO',
                'message'       => $matches[2],
            );
        }

        return array(
            'timestamp'     => $matches[1],
            'timestamp_raw' => strtotime($matches[1]),
            'level'         => strtoupper($matches[2]),
            'message'       => $matches[3],
        );
    }

    /**
     * Column timestamp
     */
    protected function column_timestamp($item) {
        return esc_html($item['timestamp']);
    }

    /**
     * Column level
     */
    protected function column_level($item) {
        $level = $item['level'];
        $class = 'dekkimporter-log-level-' . strtolower($level);

        $color = '#6c757d'; // default
        if ($level === 'ERROR') {
            $color = '#dc3545';
        } elseif ($level === 'WARNING') {
            $color = '#ffc107';
        } elseif ($level === 'SUCCESS') {
            $color = '#28a745';
        } elseif ($level === 'INFO') {
            $color = '#17a2b8';
        }

        return '<span class="' . esc_attr($class) . '" style="color: ' . esc_attr($color) . '; font-weight: bold;">' . esc_html($level) . '</span>';
    }

    /**
     * Column message
     */
    protected function column_message($item) {
        return esc_html($item['message']);
    }

    /**
     * Display extra table navigation
     */
    protected function extra_tablenav($which) {
        if ($which !== 'top') {
            return;
        }

        $date_filter = isset($_GET['date_filter']) ? sanitize_text_field($_GET['date_filter']) : '';

        // Get available dates from log files
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/dekkimporter-logs';
        $log_files = glob($log_dir . '/dekkimporter-*.log');

        $available_dates = array();
        foreach ($log_files as $file) {
            if (preg_match('/dekkimporter-(\d{4}-\d{2}-\d{2})\.log$/', basename($file), $matches)) {
                $available_dates[] = $matches[1];
            }
        }

        rsort($available_dates); // Newest first
        ?>
        <div class="alignleft actions">
            <label for="date_filter" class="screen-reader-text"><?php esc_html_e('Filter by date', 'dekkimporter'); ?></label>
            <select name="date_filter" id="date_filter">
                <option value=""><?php esc_html_e('All dates', 'dekkimporter'); ?></option>
                <?php foreach ($available_dates as $date) : ?>
                    <option value="<?php echo esc_attr($date); ?>" <?php selected($date_filter, $date); ?>>
                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($date))); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="submit" class="button" value="<?php esc_attr_e('Filter', 'dekkimporter'); ?>" />
        </div>
        <?php
    }
}
