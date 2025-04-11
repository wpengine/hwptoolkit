<?php
/*
Plugin Name: Custom Sitemap URL Adjustment (MU)
Description: Adjusts the sitemap URL to ensure it's not redirected when HOME_URL is set to the headless site.
Version: 1.0
*/

// Define the namespace
namespace ProxiedSitemap;

// Add filters to modify the sitemap entries
add_filter( 'wp_sitemaps_posts_entry', __NAMESPACE__ . '\\adjust_sitemap_urls' );
add_filter( 'wp_sitemaps_pages_entry', __NAMESPACE__ . '\\adjust_sitemap_urls' );
add_filter( 'wp_sitemaps_taxonomies_entry', __NAMESPACE__ . '\\adjust_sitemap_urls' );
add_filter( 'wp_sitemaps_users_entry', __NAMESPACE__ . '\\adjust_sitemap_urls' );
add_filter( 'wp_sitemaps_index_entry', __NAMESPACE__ . '\\adjust_sitemap_urls' );

// For Yoast SEO if present
add_filter( 'wpseo_xml_sitemap_post_url', __NAMESPACE__ . '\\adjust_yoast_sitemap_url' );
add_filter( 'wpseo_xml_sitemap_term_url', __NAMESPACE__ . '\\adjust_yoast_sitemap_url' );
add_filter( 'wpseo_xml_sitemap_author_url', __NAMESPACE__ . '\\adjust_yoast_sitemap_url' );

// Adjusts the URL in sitemap entries
function adjust_sitemap_urls( $entry ) {
    $entry['loc'] = equivalent_frontend_url( $entry['loc'] );
    return $entry;
}

// Adjusts the URL for Yoast SEO
function adjust_yoast_sitemap_url( $url ) {
    return equivalent_frontend_url( $url );
}

// Replaces the WordPress domain with the frontend domain in a URL
function equivalent_frontend_url( $url ) {
    return normalize_url( $url, true );
}

// Replaces the frontend domain with the WordPress domain (optional use)
function equivalent_wp_url( $url ) {
    return normalize_url( $url, false );
}

// Normalizes between frontend and backend domains
function normalize_url( $url, $frontend = true ) {
    $frontend_uri = defined( 'FRONTEND_URL' ) ? FRONTEND_URL : 'http://localhost:3000';

    if ( ! $frontend_uri ) {
        return $url;
    }

    $frontend_uri = trailingslashit( $frontend_uri );
    $home_url     = trailingslashit( get_home_url() );

    return $frontend
        ? str_replace( $home_url, $frontend_uri, $url )
        : str_replace( $frontend_uri, $home_url, $url );
}