<?php

/*
 * Plugin Name: HWPT WPGraphQL Sitemap
 * Description: An example plugin to expose the WordPress sitemap via WPGraphQL.
 * Author: hwptoolkit
 * Version: 1.0.0
*/


/*
This plugin does three things:
	1. Adds image URL to the sitemap entry using the 'wp_sitemaps_posts_entry' filter.
	2. Registers a new GraphQL object type for the sitemap types.
	3. Registers a new GraphQL field for the sitemap entries.
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Unauthorized access!' );
}

// Insert image URL into the sitemap entry using the 'wp_sitemaps_posts_entry' filter
add_filter( 'wp_sitemaps_posts_entry', function( $entry, $post ) {
	// Get the featured image URL (full size)
	$featured_image_id = get_post_thumbnail_id( $post->ID );

	if ( $featured_image_id ) {
		$featured_image_url = wp_get_attachment_image_url( $featured_image_id, 'full' );

		if ( $featured_image_url ) {
			// Add image to the entry array
			$entry['imageLoc'] = $featured_image_url;
		}
	}

	return $entry;
}, 10, 2 );

// Custom WPGraphQL fields
require_once __DIR__ . '/sitemap-types-resolver.php';
require_once __DIR__ . '/sitemap-entries-resolver.php';
