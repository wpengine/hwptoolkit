<?php
/**
 * Test file for Smart Cache webhook integration
 * 
 * This file demonstrates how to test the Smart Cache events with webhooks.
 * Place this in your WordPress installation and run it to see the integration in action.
 */

// Add this to your theme's functions.php or a custom plugin to test

add_action( 'init', function() {
    // Only run this test if explicitly requested via URL parameter
    if ( ! isset( $_GET['test_smart_cache_webhooks'] ) ) {
        return;
    }

    // Security check
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized' );
    }

    echo '<h1>Smart Cache Webhook Integration Test</h1>';
    echo '<pre>';

    // Test 1: Simulate a graphql_purge event for post creation
    echo "Test 1: Simulating post CREATE event\n";
    echo "=====================================\n";
    
    do_action( 'graphql_purge', 'list:post', 'post_CREATE', 'mysite.local/graphql' );
    echo "✓ Triggered: graphql_purge with list:post key\n\n";

    // Test 2: Simulate a graphql_purge event for post update with Relay ID
    echo "Test 2: Simulating post UPDATE event with Relay ID\n";
    echo "==================================================\n";
    
    // Create a test post first
    $post_id = wp_insert_post([
        'post_title' => 'Test Post for Smart Cache',
        'post_content' => 'This is a test post',
        'post_status' => 'publish',
        'post_type' => 'post'
    ]);
    
    // Generate the Relay global ID (base64 encoded "post:ID")
    $relay_id = base64_encode( 'post:' . $post_id );
    
    do_action( 'graphql_purge', $relay_id, 'post_UPDATE', 'mysite.local/graphql' );
    echo "✓ Triggered: graphql_purge with Relay ID: $relay_id\n";
    echo "  Decodes to: post:$post_id\n\n";

    // Test 3: Simulate a graphql_purge event for post deletion
    echo "Test 3: Simulating post DELETE event\n";
    echo "====================================\n";
    
    do_action( 'graphql_purge', $relay_id, 'post_DELETE', 'mysite.local/graphql' );
    echo "✓ Triggered: graphql_purge for deletion\n\n";

    // Test 4: Simulate cache purge nodes event
    echo "Test 4: Simulating cache purge nodes event\n";
    echo "==========================================\n";
    
    $test_nodes = [
        ['id' => $relay_id, 'type' => 'post'],
        ['id' => 'dGVybTox', 'type' => 'term'], // Example term
    ];
    
    do_action( 'wpgraphql_cache_purge_nodes', 'list:post', $test_nodes );
    echo "✓ Triggered: wpgraphql_cache_purge_nodes with " . count($test_nodes) . " nodes\n\n";

    // Clean up test post
    wp_delete_post( $post_id, true );
    
    echo "Test completed!\n\n";
    echo "Check your webhook logs to see if the events were captured.\n";
    echo "Expected webhook events:\n";
    echo "- smart_cache_created (from Test 1)\n";
    echo "- smart_cache_updated (from Test 2)\n";
    echo "- smart_cache_deleted (from Test 3)\n";
    echo "- smart_cache_nodes_purged (from Test 4)\n";
    
    echo '</pre>';
    
    // Add a button to view webhooks
    echo '<p><a href="' . admin_url('options-general.php?page=webhooks') . '" class="button button-primary">View Webhooks Admin</a></p>';
    
    die(); // Stop WordPress execution
});

// Add logging to see when Smart Cache events are triggered
add_action( 'graphql_webhooks_before_trigger', function( $event, $payload ) {
    if ( strpos( $event, 'smart_cache' ) === 0 ) {
        error_log( '[Smart Cache Webhook] Event: ' . $event );
        error_log( '[Smart Cache Webhook] Payload: ' . print_r( $payload, true ) );
    }
}, 10, 2 );
