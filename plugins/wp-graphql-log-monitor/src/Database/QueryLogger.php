<?php
namespace WPGraphQL\LogMonitor\Database;

use WPGraphQL\LogMonitor\Utilities;

class QueryLogger {
    private $table_name;
    private $start_time;
    private $query_data = [];
    
    public function __construct(Schema $schema) {
        $this->table_name = $schema->get_table_name();
    }
    
    public function start_monitoring($query, $variables, $operation_name) {
        $this->start_time = microtime(true);
        $this->query_data = [
            'query' => $query,
            'variables' => $variables,
            'operation_name' => $operation_name,
            'memory_start' => memory_get_usage(true),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip_address' => Utilities::get_client_ip(),
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
        $complexity = Utilities::calculate_query_complexity($query);
        $depth = Utilities::calculate_query_depth($query);
        
        // Check for errors
        $errors = [];
        $error_count = 0;
        if (isset($result_array['errors']) && is_array($result_array['errors'])) {
            $errors = $result_array['errors'];
            $error_count = count($errors);
        }
        
        $log_data = [
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
        ];
        
        $this->save_log_entry($log_data);
        
        // Reset for next query
        $this->start_time = null;
        $this->query_data = [];
    }
    
    public function save_log_entry($data) {
        global $wpdb;
        return $wpdb->insert($this->table_name, $data);
    }
    
    public function get_query_stats() {
        global $wpdb;
        
        return $wpdb->get_row("
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
    }
    
    public function get_query_logs($page = 1, $per_page = 20) {
        global $wpdb;
        
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
        
        return [
            'logs' => $logs,
            'total' => (int)$total,
            'page' => $page,
            'per_page' => $per_page
        ];
    }
    
    public function clear_logs() {
        global $wpdb;
        return $wpdb->query("TRUNCATE TABLE {$this->table_name}");
    }
    
    public function cleanup_old_logs() {
        global $wpdb;
        
        return $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE timestamp < %s",
            date('Y-m-d H:i:s', strtotime('-24 hours'))
        ));
    }
}