<?php
namespace WPGraphQL\LogMonitor\Admin;

use WPGraphQL\LogMonitor\Database\QueryLogger;
use WPGraphQL\LogMonitor\Utilities;

class AjaxHandler {
    private $query_logger;
    
    public function __construct(QueryLogger $query_logger) {
        $this->query_logger = $query_logger;
    }
    
    public function ajax_get_query_stats() {
        check_ajax_referer('wpgraphql_monitor_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $stats = $this->query_logger->get_query_stats();
        
        $error_rate = $stats->total_queries > 0 ? 
            round(($stats->total_errors / $stats->total_queries) * 100, 2) : 0;
        
        wp_send_json_success([
            'total_queries' => (int)$stats->total_queries,
            'avg_execution_time' => round($stats->avg_execution_time * 1000, 2),
            'max_execution_time' => round($stats->max_execution_time * 1000, 2),
            'error_rate' => $error_rate,
            'avg_memory_usage' => Utilities::format_bytes($stats->avg_memory_usage),
            'avg_complexity' => round($stats->avg_complexity, 1)
        ]);
    }
    
    public function ajax_get_query_logs() {
        check_ajax_referer('wpgraphql_monitor_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $page = intval($_POST['page'] ?? 1);
        $logs = $this->query_logger->get_query_logs($page);
        
        wp_send_json_success($logs);
    }
    
    public function ajax_clear_logs() {
        check_ajax_referer('wpgraphql_monitor_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $this->query_logger->clear_logs();
        wp_send_json_success(['message' => 'All logs cleared successfully']);
    }
}