<?php

// Disable updates to prevent WP from going into maintenance mode while tests run
add_filter( 'enable_maintenance_mode', '__return_false' );
add_filter( 'wp_auto_update_core', '__return_false' );
add_filter( 'auto_update_plugin', '__return_false' );
add_filter( 'auto_update_theme', '__return_false' );

// Activating the plugin created a real documents table. Drop it, so each test
// creates its own temporary table and table checks see only that one.
HWP\Documents\Store\Document_Table::uninstall();
