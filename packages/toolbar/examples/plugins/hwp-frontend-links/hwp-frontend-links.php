<?php
/**
 * Plugin Name: HWP Frontend Links
 * Plugin URI: https://github.com/wpengine/hwptoolkit
 * Description: Adds "View on Frontend" links to WordPress admin for headless sites. Supports single or multiple frontends.
 * Version: 0.1.0
 * Author: WP Engine
 * Author URI: https://wpengine.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * Configuration:
 *
 * Single frontend:
 * define( 'HEADLESS_FRONTEND_URL', 'https://example.com' );
 *
 * Multiple frontends:
 * define( 'HWP_FRONTEND_LINKS', [
 *   [ 'label' => 'Production', 'url' => 'https://example.com' ],
 *   [ 'label' => 'Staging', 'url' => 'https://staging.example.com' ]
 * ] );
 *
 * @package HWP\FrontendLinks
 */

namespace HWP\FrontendLinks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get configured frontend URLs with labels
 *
 * Supports both single frontend (HEADLESS_FRONTEND_URL) and multiple frontends
 * (HWP_FRONTEND_LINKS) configurations. Returns normalized array of configs.
 *
 * @return array Array of frontend configurations with 'label' and 'url' keys
 */
function get_frontend_configs() {
	$configs = [];

	if ( defined( 'HWP_FRONTEND_LINKS' ) && is_array( HWP_FRONTEND_LINKS ) ) {
		foreach ( HWP_FRONTEND_LINKS as $config ) {
			if ( isset( $config['label'] ) && isset( $config['url'] ) ) {
				$configs[] = [
					'label' => $config['label'],
					'url'   => rtrim( $config['url'], '/' ),
				];
			}
		}
	}

	if ( empty( $configs ) && defined( 'HEADLESS_FRONTEND_URL' ) ) {
		$configs[] = [
			'label' => 'Frontend',
			'url'   => rtrim( HEADLESS_FRONTEND_URL, '/' ),
		];
	}

	return $configs;
}

/**
 * Build frontend URL for a post
 *
 * Constructs full frontend URL by combining base URL with post slug.
 * Path construction is filterable via 'hwp_frontend_links_post_path'.
 *
 * @param \WP_Post $post          The post object
 * @param string   $frontend_url  The frontend base URL
 * @return string|null Frontend URL or null if invalid
 */
function build_frontend_url( $post, $frontend_url ) {
	if ( ! $frontend_url || ! $post ) {
		return null;
	}

	$path = apply_filters( 'hwp_frontend_links_post_path', '/' . $post->post_name, $post );

	return $frontend_url . $path;
}

/**
 * Add frontend links to admin bar
 *
 * Adds "View in [Label]" link(s) to admin bar for each configured frontend.
 * Only displays on singular post/page views.
 *
 * @param \WP_Admin_Bar $wp_admin_bar Admin bar instance
 */
function add_admin_bar_link( $wp_admin_bar ) {
	$frontend_configs = get_frontend_configs();

	if ( empty( $frontend_configs ) || ! is_singular() ) {
		return;
	}

	global $post;

	foreach ( $frontend_configs as $index => $config ) {
		$frontend_url = build_frontend_url( $post, $config['url'] );

		if ( ! $frontend_url ) {
			continue;
		}

		$node_id = 'hwp-view-on-frontend' . ( $index > 0 ? '-' . $index : '' );
		$title   = sprintf( __( 'View in %s', 'hwp-frontend-links' ), $config['label'] );

		$wp_admin_bar->add_node( [
			'id'     => $node_id,
			'parent' => null,
			'group'  => null,
			'title'  => $title,
			'href'   => $frontend_url,
			'meta'   => [
				'target' => '_blank',
				'rel'    => 'noopener noreferrer',
				'title'  => $title,
			],
		] );
	}
}

/**
 * Add frontend links to post row actions
 *
 * Adds "View in [Label]" link(s) to post/page row actions in admin lists
 * for each configured frontend.
 *
 * @param array    $actions Row action links
 * @param \WP_Post $post    Post object
 * @return array Modified actions array
 */
function add_post_row_action( $actions, $post ) {
	$frontend_configs = get_frontend_configs();

	if ( empty( $frontend_configs ) ) {
		return $actions;
	}

	foreach ( $frontend_configs as $index => $config ) {
		$frontend_url = build_frontend_url( $post, $config['url'] );

		if ( ! $frontend_url ) {
			continue;
		}

		$action_key = 'hwp_view_frontend' . ( $index > 0 ? '_' . $index : '' );
		$label      = sprintf( __( 'View in %s', 'hwp-frontend-links' ), $config['label'] );

		$actions[ $action_key ] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( $frontend_url ),
			esc_html( $label )
		);
	}

	return $actions;
}

/**
 * Initialize the plugin
 */
function init() {
	// Only add frontend links if configured
	if ( empty( get_frontend_configs() ) ) {
		return;
	}

	// Add admin bar link
	add_action( 'admin_bar_menu', __NAMESPACE__ . '\\add_admin_bar_link', 100 );

	// Add row actions for posts
	add_filter( 'post_row_actions', __NAMESPACE__ . '\\add_post_row_action', 10, 2 );
	add_filter( 'page_row_actions', __NAMESPACE__ . '\\add_post_row_action', 10, 2 );
}

init();
