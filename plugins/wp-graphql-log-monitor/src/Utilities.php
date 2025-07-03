<?php
namespace WPGraphQL\LogMonitor;

class Utilities {
    
    public static function get_client_ip() {
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
    
    public static function format_bytes($bytes) {
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
    
    public static function calculate_query_complexity($query) {
        // Simple complexity calculation based on field count and nesting
        $field_count = substr_count($query, '{') + substr_count($query, '}');
        $fragment_count = substr_count($query, 'fragment');
        return $field_count + ($fragment_count * 2);
    }
    
    public static function calculate_query_depth($query) {
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
}