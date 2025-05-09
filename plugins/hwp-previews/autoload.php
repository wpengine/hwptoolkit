<?php

/**
 * Autoloader for HWP Previews Plugin.
 *
 * Prioritizes Composer's autoloader if available,
 * otherwise implements a PSR-4 compatible autoloader.
 *
 * @package HWP\Previews
 */

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';

	return;
}

spl_autoload_register( function ( $class ) {
	// Define namespace prefix for your plugin
	$prefix   = 'HWP\\Previews\\';
	$base_dir = __DIR__ . '/src/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$file = $base_dir . str_replace( '\\', '/', substr( $class, $len ) ) . '.php';

	if ( file_exists( $file ) ) {
		require $file;

		return;
	}

	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
		_doing_it_wrong(
			__FUNCTION__,
			sprintf(
				/* translators: 1: Class name, 2: File path */
				__( 'Class %1$s could not be loaded. File %2$s not found.', 'hwp-previews' ),
				'<code>' . esc_html( $class ) . '</code>',
				'<code>' . esc_html( $file ) . '</code>'
			),
			'1.0.0'
		);

		error_log( sprintf( 'HWP Previews: Failed to load class %s, file %s not found', $class, $file ) );
	}
} );