<?php
/**
 * Autoloader for HWP Previews Plugin.
 *
 * Prioritizes Composer's autoloader if available,
 * otherwise implements a PSR-4 compatible autoloader.
 *
 * @package HWP\Previews
 */

declare(strict_types=1);

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';

	return;
}

spl_autoload_register( static function ( $hwp_class ) {
	// Define namespace prefix for your plugin.
	$prefix   = 'HWP\\Previews\\';
	$base_dir = __DIR__ . '/src/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $hwp_class, $len ) !== 0 ) {
		return;
	}

	$hwp_file = $base_dir . str_replace( '\\', '/', substr( $hwp_class, $len ) ) . '.php';

	if ( file_exists( $hwp_file ) ) {
		// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		require $hwp_file;

		return;
	}

	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		return;
	}

	_doing_it_wrong(
		__FUNCTION__,
		sprintf(
			/* translators: 1: Class name, 2: File path */
			esc_html( __( 'Class %1$s could not be loaded. File %2$s not found.', 'hwp-previews' ) ),
			'<code>' . esc_html( $hwp_class ) . '</code>',
			'<code>' . esc_html( $hwp_file ) . '</code>'
		),
		'1.0.0'
	);
} );
