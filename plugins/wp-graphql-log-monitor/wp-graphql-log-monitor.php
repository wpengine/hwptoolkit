<?php
/**
 * Plugin Name: WpGraphQL Query Monitor
 * Plugin URI: https://github.com/yourname/wpgraphql-query-monitor
 * Description: Monitor and analyze WpGraphQL queries with detailed logging and performance insights
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPGRAPHQL_MONITOR_VERSION', '1.0.0');
define('WPGRAPHQL_MONITOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPGRAPHQL_MONITOR_PLUGIN_URL', plugin_dir_url(__FILE__));

class WpGraphQLQueryMonitor {
    
    private $table_name;
    private $start_time;
    private $query_data = [];
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wpgraphql_query_logs';
        
        add_action('init', [$this, 'init']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('wp_ajax_get_query_stats', [$this, 'ajax_get_query_stats']);
        add_action('wp_ajax_get_query_logs', [$this, 'ajax_get_query_logs']);
        add_action('wp_ajax_clear_logs', [$this, 'ajax_clear_logs']);
        
        // Hook into WpGraphQL
        add_action('do_graphql_request', [$this, 'start_query_monitoring'], 10, 3);
        add_action('graphql_process_http_request_response', [$this, 'log_query_results'], 10, 5);
        
        // Cleanup old logs daily
        add_action('wp_scheduled_delete', [$this, 'cleanup_old_logs']);
        
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }
    
    public function init() {
        // Check if WpGraphQL is active
        if (!class_exists('WPGraphQL')) {
            add_action('admin_notices', [$this, 'wpgraphql_missing_notice']);
            return;
        }
    }
    
    public function wpgraphql_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('WpGraphQL Query Monitor requires WpGraphQL plugin to be installed and activated.', 'wpgraphql-monitor'); ?></p>
        </div>
        <?php
    }
    
    public function activate() {
        $this->create_table();
        
        // Schedule cleanup if not already scheduled
        if (!wp_next_scheduled('wp_scheduled_delete')) {
            wp_schedule_event(time(), 'daily', 'wp_scheduled_delete');
        }
    }
    
    public function deactivate() {
        wp_clear_scheduled_hook('wp_scheduled_delete');
    }
    
    private function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            query_hash varchar(32) NOT NULL,
            query_text longtext NOT NULL,
            query_variables longtext,
            operation_name varchar(255),
            execution_time float NOT NULL,
            memory_usage bigint(20) unsigned NOT NULL,
            query_size bigint(20) unsigned NOT NULL,
            response_size bigint(20) unsigned NOT NULL,
            query_complexity int(11),
            query_depth int(11),
            error_count int(11) DEFAULT 0,
            errors longtext,
            user_agent text,
            ip_address varchar(45),
            user_id bigint(20) unsigned,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY query_hash (query_hash),
            KEY timestamp (timestamp),
            KEY execution_time (execution_time),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function start_query_monitoring($query, $variables, $operation_name) {
        $this->start_time = microtime(true);
        $this->query_data = [
            'query' => $query,
            'variables' => $variables,
            'operation_name' => $operation_name,
            'memory_start' => memory_get_usage(true),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip_address' => $this->get_client_ip(),
            'user_id' => get_current_user_id()
        ];
    }
    
    public function log_query_results($response, $result, $operation_name, $query, $variables) {
        if (!$this->start_time) {
            return;
        }
        
        // Convert ExecutionResult objects to arrays
        $response_array = is_object($response) ? $response->toArray() : $response;
        $result_array = is_object($result) ? $result->toArray() : $result;
        
        $execution_time = microtime(true) - $this->start_time;
        $memory_usage = memory_get_usage(true) - $this->query_data['memory_start'];
        $query_size = strlen($query);
        $response_size = strlen(json_encode($response_array));
        
        // Calculate query complexity and depth
        $complexity = $this->calculate_query_complexity($query);
        $depth = $this->calculate_query_depth($query);
        
        // Check for errors
        $errors = [];
        $error_count = 0;
        if (isset($result_array['errors']) && is_array($result_array['errors'])) {
            $errors = $result_array['errors'];
            $error_count = count($errors);
        }
        
        $this->save_log_entry([
            'query_hash' => md5($query),
            'query_text' => $query,
            'query_variables' => json_encode($variables),
            'operation_name' => $operation_name,
            'execution_time' => $execution_time,
            'memory_usage' => $memory_usage,
            'query_size' => $query_size,
            'response_size' => $response_size,
            'query_complexity' => $complexity,
            'query_depth' => $depth,
            'error_count' => $error_count,
            'errors' => json_encode($errors),
            'user_agent' => $this->query_data['user_agent'],
            'ip_address' => $this->query_data['ip_address'],
            'user_id' => $this->query_data['user_id']
        ]);
        
        // Reset for next query
        $this->start_time = null;
        $this->query_data = [];
    }

    private function save_log_entry($data) {
        global $wpdb;
        
        $wpdb->insert($this->table_name, $data);
    }
    
    private function calculate_query_complexity($query) {
        // Simple complexity calculation based on field count and nesting
        $field_count = substr_count($query, '{') + substr_count($query, '}');
        $fragment_count = substr_count($query, 'fragment');
        return $field_count + ($fragment_count * 2);
    }
    
    private function calculate_query_depth($query) {
        // Calculate maximum nesting depth
        $max_depth = 0;
        $current_depth = 0;
        $chars = str_split($query);
        
        foreach ($chars as $char) {
            if ($char === '{') {
                $current_depth++;
                $max_depth = max($max_depth, $current_depth);
            } elseif ($char === '}') {
                $current_depth--;
            }
        }
        
        return $max_depth;
    }
    
    private function get_client_ip() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    public function cleanup_old_logs() {
        global $wpdb;
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE timestamp < %s",
            date('Y-m-d H:i:s', strtotime('-24 hours'))
        ));
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'tools.php',
            'WpGraphQL Query Monitor',
            'GraphQL Monitor',
            'manage_options',
            'wpgraphql-monitor',
            [$this, 'admin_page']
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'tools_page_wpgraphql-monitor') {
            return;
        }
        
        wp_enqueue_script('chart-js', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js', [], '3.9.1', true);
        wp_enqueue_script('wpgraphql-monitor-admin', WPGRAPHQL_MONITOR_PLUGIN_URL . 'assets/admin.js', ['jquery', 'chart-js'], WPGRAPHQL_MONITOR_VERSION, true);
        wp_enqueue_style('wpgraphql-monitor-admin', WPGRAPHQL_MONITOR_PLUGIN_URL . 'assets/admin.css', [], WPGRAPHQL_MONITOR_VERSION);
        
        wp_localize_script('wpgraphql-monitor-admin', 'wpgraphqlMonitor', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wpgraphql_monitor_nonce')
        ]);
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>WpGraphQL Query Monitor</h1>
            
            <div class="wpgraphql-monitor-dashboard">
                <div class="stats-row">
                    <div class="stat-card">
                        <h3>Total Queries (24h)</h3>
                        <div class="stat-value" id="total-queries">-</div>
                    </div>
                    <div class="stat-card">
                        <h3>Avg Response Time</h3>
                        <div class="stat-value" id="avg-response-time">-</div>
                    </div>
                    <div class="stat-card">
                        <h3>Error Rate</h3>
                        <div class="stat-value" id="error-rate">-</div>
                    </div>
                    <div class="stat-card">
                        <h3>Slowest Query</h3>
                        <div class="stat-value" id="slowest-query">-</div>
                    </div>
                </div>
                
                <div class="charts-row">
                    <div class="chart-container">
                        <h3>Query Performance Over Time</h3>
                        <canvas id="performance-chart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h3>Query Complexity Distribution</h3>
                        <canvas id="complexity-chart"></canvas>
                    </div>
                </div>
                
                <div class="charts-row">
                    <div class="chart-container">
                        <h3>Memory Usage</h3>
                        <canvas id="memory-chart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h3>Response Size Distribution</h3>
                        <canvas id="response-size-chart"></canvas>
                    </div>
                </div>
                
                <div class="table-container">
                    <div class="table-header">
                        <h3>Recent Queries</h3>
                        <div class="table-actions">
                            <button id="refresh-logs" class="button">Refresh</button>
                            <button id="clear-logs" class="button button-secondary">Clear All Logs</button>
                        </div>
                    </div>
                    <table id="query-logs-table" class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Operation</th>
                                <th>Execution Time</th>
                                <th>Memory Usage</th>
                                <th>Complexity</th>
                                <th>Errors</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Query Detail Modal -->
            <div id="query-detail-modal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h3>Query Details</h3>
                    <div id="query-detail-content"></div>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function ajax_get_query_stats() {
        check_ajax_referer('wpgraphql_monitor_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        global $wpdb;
        
        $stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_queries,
                AVG(execution_time) as avg_execution_time,
                MAX(execution_time) as max_execution_time,
                SUM(error_count) as total_errors,
                AVG(memory_usage) as avg_memory_usage,
                AVG(query_complexity) as avg_complexity
            FROM {$this->table_name} 
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        
        $error_rate = $stats->total_queries > 0 ? 
            round(($stats->total_errors / $stats->total_queries) * 100, 2) : 0;
        
        wp_send_json_success([
            'total_queries' => (int)$stats->total_queries,
            'avg_execution_time' => round($stats->avg_execution_time * 1000, 2),
            'max_execution_time' => round($stats->max_execution_time * 1000, 2),
            'error_rate' => $error_rate,
            'avg_memory_usage' => $this->format_bytes($stats->avg_memory_usage),
            'avg_complexity' => round($stats->avg_complexity, 1)
        ]);
    }
    
    public function ajax_get_query_logs() {
        check_ajax_referer('wpgraphql_monitor_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        global $wpdb;
        
        $page = intval($_POST['page'] ?? 1);
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        
        $logs = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$this->table_name} 
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ORDER BY timestamp DESC 
            LIMIT %d OFFSET %d
        ", $per_page, $offset));
        
        $total = $wpdb->get_var("
            SELECT COUNT(*) FROM {$this->table_name} 
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        
        wp_send_json_success([
            'logs' => $logs,
            'total' => (int)$total,
            'page' => $page,
            'per_page' => $per_page
        ]);
    }
    
    public function ajax_clear_logs() {
        check_ajax_referer('wpgraphql_monitor_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$this->table_name}");
        
        wp_send_json_success(['message' => 'All logs cleared successfully']);
    }
    
    private function format_bytes($bytes) {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}

// Initialize the plugin
new WpGraphQLQueryMonitor();