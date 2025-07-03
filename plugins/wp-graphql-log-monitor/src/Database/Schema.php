<?php
namespace WPGraphQL\LogMonitor\Database;

class Schema {
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wpgraphql_query_logs';
    }
    
    public function create_table() {
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
    
    public function get_table_name() {
        return $this->table_name;
    }
}