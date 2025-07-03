<?php
namespace WPGraphQL\LogMonitor;

use WPGraphQL\LogMonitor\Admin\AdminPage;
use WPGraphQL\LogMonitor\Admin\AjaxHandler;
use WPGraphQL\LogMonitor\Database\Schema;
use WPGraphQL\LogMonitor\Database\QueryLogger;

class Plugin {
    private $schema;
    private $query_logger;
    private $admin_page;
    private $ajax_handler;
    
    public function __construct() {
        $this->init_dependencies();
        $this->register_hooks();
        $this->register_lifecycle_hooks();
    }
    
    private function init_dependencies() {
        $this->schema = new Schema();
        $this->query_logger = new QueryLogger($this->schema);
        $this->admin_page = new AdminPage();
        $this->ajax_handler = new AjaxHandler($this->query_logger);
    }
    
    private function register_hooks() {
        // Core plugin hooks
        add_action('init', [$this, 'init']);
        
        // Admin hooks
        $this->admin_page->register_hooks();
        
        // AJAX hooks
        add_action('wp_ajax_get_query_stats', [$this->ajax_handler, 'ajax_get_query_stats']);
        add_action('wp_ajax_get_query_logs', [$this->ajax_handler, 'ajax_get_query_logs']);
        add_action('wp_ajax_clear_logs', [$this->ajax_handler, 'ajax_clear_logs']);
        
        // GraphQL monitoring hooks
        add_action('do_graphql_request', [$this->query_logger, 'start_monitoring'], 10, 3);
        add_action('graphql_process_http_request_response', [$this->query_logger, 'log_query_results'], 10, 5);
        
        // Cleanup hook
        add_action('wp_scheduled_delete', [$this->query_logger, 'cleanup_old_logs']);
    }
    
    private function register_lifecycle_hooks() {
        register_activation_hook(WPGRAPHQL_MONITOR_PLUGIN_DIR . 'wp-graphql-log-monitor.php', [$this, 'activate']);
        register_deactivation_hook(WPGRAPHQL_MONITOR_PLUGIN_DIR . 'wp-graphql-log-monitor.php', [$this, 'deactivate']);
    }
    
    public function init() {
        if (!class_exists('WPGraphQL')) {
            add_action('admin_notices', [$this, 'wpgraphql_missing_notice']);
            return;
        }
    }
    
    public function wpgraphql_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('WpGraphQL Query Monitor requires WpGraphQL plugin to be installed and activated.', 'graphql-log-monitor'); ?></p>
        </div>
        <?php
    }
    
    public function activate() {
        $this->schema->create_table();
        
        // Schedule cleanup if not already scheduled
        if (!wp_next_scheduled('wp_scheduled_delete')) {
            wp_schedule_event(time(), 'daily', 'wp_scheduled_delete');
        }
    }
    
    public function deactivate() {
        wp_clear_scheduled_hook('wp_scheduled_delete');
    }
    

}