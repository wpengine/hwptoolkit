<?php
/**
 * Plugin Name: HWP Frontend Links
 * Plugin URI: https://github.com/wpengine/hwptoolkit
 * Description: Adds "View on Frontend" links to WordPress admin for headless WordPress sites. Configure via HEADLESS_FRONTEND_URL constant.
 * Version: 1.0.0
 * Author: WP Engine
 * Author URI: https://wpengine.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package HWP\FrontendLinks
 */

namespace HWP\FrontendLinks;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the frontend URL
 *
 * @return string|null The frontend URL or null if not configured
 */
function get_frontend_url() {
	if ( defined( 'HEADLESS_FRONTEND_URL' ) ) {
		return rtrim( HEADLESS_FRONTEND_URL, '/' );
	}

	return null;
}

/**
 * Build frontend URL for a post
 *
 * @param \WP_Post $post The post object
 * @return string|null The frontend URL for the post or null if not configured
 */
function build_frontend_url( $post ) {
	$frontend_url = get_frontend_url();

	if ( ! $frontend_url || ! $post ) {
		return null;
	}

	// Allow filtering of the path construction
	$path = apply_filters( 'hwp_frontend_links_post_path', '/' . $post->post_name, $post );

	return $frontend_url . $path;
}

/**
 * Add "View on Frontend" link to admin bar
 *
 * @param \WP_Admin_Bar $wp_admin_bar The admin bar instance
 */
function add_admin_bar_link( $wp_admin_bar ) {
	if ( ! get_frontend_url() ) {
		return;
	}

	// Only show for singular posts/pages
	if ( ! is_singular() ) {
		return;
	}

	global $post;

	$frontend_url = build_frontend_url( $post );

	if ( ! $frontend_url ) {
		return;
	}

	$wp_admin_bar->add_node( [
		'id'     => 'hwp-view-on-frontend',
		'parent' => null,
		'group'  => null,
		'title'  => __( 'View on Frontend', 'hwp-frontend-links' ),
		'href'   => $frontend_url,
		'meta'   => [
			'target' => '_blank',
			'rel'    => 'noopener noreferrer',
			'title'  => __( 'View this content on the headless frontend', 'hwp-frontend-links' ),
		],
	] );
}

/**
 * Add frontend link to post row actions
 *
 * @param array    $actions An array of row action links
 * @param \WP_Post $post    The post object
 * @return array Modified actions array
 */
function add_post_row_action( $actions, $post ) {
	$frontend_url = build_frontend_url( $post );

	if ( ! $frontend_url ) {
		return $actions;
	}

	$actions['hwp_view_frontend'] = sprintf(
		'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
		esc_url( $frontend_url ),
		__( 'View on Frontend', 'hwp-frontend-links' )
	);

	return $actions;
}

/**
 * Initialize the plugin
 */
function init() {
	// Only add frontend links if configured
	if ( ! get_frontend_url() ) {
		return;
	}

	// Add admin bar link
	add_action( 'admin_bar_menu', __NAMESPACE__ . '\\add_admin_bar_link', 100 );

	// Add row actions for posts
	add_filter( 'post_row_actions', __NAMESPACE__ . '\\add_post_row_action', 10, 2 );
	add_filter( 'page_row_actions', __NAMESPACE__ . '\\add_post_row_action', 10, 2 );
}

init();
