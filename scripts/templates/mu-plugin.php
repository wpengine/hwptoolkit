<?php
/**
 * Plugin Name: Toolbar Demo CORS & Frontend Links
 * Description: Enable CORS for dev server and add frontend preview links
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

// Add "View on Frontend" link in admin bar
add_action('admin_bar_menu', function($wp_admin_bar) {
    if (!defined('HEADLESS_FRONTEND_URL')) {
        return;
    }

    // Only show for singular posts/pages
    if (!is_singular()) {
        return;
    }

    global $post;
    $frontend_url = HEADLESS_FRONTEND_URL . '/' . $post->post_name;

    $wp_admin_bar->add_node([
        'id' => 'view-on-frontend',
        'title' => 'View on Frontend',
        'href' => $frontend_url,
        'meta' => [
            'target' => '_blank'
        ]
    ]);
}, 100);
