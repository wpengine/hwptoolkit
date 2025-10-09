<?php
/**
 * Plugin Name: Toolbar Demo CORS
 * Description: Enable CORS for dev server
 */

add_action('rest_api_init', function() {
    remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
    add_filter('rest_pre_serve_request', function($value) {
        header('Access-Control-Allow-Origin: http://localhost:{{FRONTEND_PORT}}');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            status_header(200);
            exit();
        }

        return $value;
    });
});

// Force REST API to use ?rest_route= format to avoid .htaccess issues in wp-env
add_filter('rest_url', function($url) {
    $url = remove_query_arg('rest_route', $url);
    return add_query_arg('rest_route', trim(parse_url($url, PHP_URL_PATH), '/'), home_url('/'));
}, 10, 1);
