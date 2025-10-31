<?php
/**
 * Plugin Name: Reset WPGraphQL Logging settings
 * Description: This plugin resets WPGraphQL Logging settings on activation. It's only intended to be used for e2e testing purposes.
 */

add_action('init', function () {
  if ($_SERVER['REQUEST_URI'] === '/wp-admin/options-general.php?page=wpgraphql-logging&reset=true') {
    global $wpdb;

    // Reset settings
    update_option('wpgraphql_logging_settings', array());

    // Clear logs table
    $table_name = $wpdb->prefix . 'wpgraphql_logging';
    $wpdb->query("TRUNCATE TABLE {$table_name}");
  }
});