<?php

declare(strict_types=1);

namespace WPGraphQL\Velocity;

/**
 * Includes the composer Autoloader used for packages and classes in the src/ directory.
 *
 * @package WPGraphQL\Velocity
 *
 * @since 0.0.1
 */
class Autoloader {
	/**
	 * Whether the autoloader has been loaded.
	 *
	 * @var bool
	 */
	protected static bool $is_loaded = false;

	/**
	 * Attempts to autoload the Composer dependencies.
	 */
	public static function autoload(): bool {
		if ( self::get_is_loaded() ) {
			return self::get_is_loaded();
		}

		$autoloader      = self::get_composer_autoloader_path();
		self::$is_loaded = self::require_autoloader( $autoloader );

		return self::get_is_loaded();
	}

	/**
	 * If the autoloader has been loaded.
	 */
	public static function get_is_loaded(): bool {
		return self::$is_loaded;
	}

	/**
	 * Returns the path to the Composer autoloader.
	 */
	public static function get_composer_autoloader_path(): string {
		return dirname( __DIR__ ) . '/vendor/autoload.php';
	}

	/**
	 * Attempts to load the autoloader file, if it exists.
	 *
	 * @param string $autoloader_file The path to the autoloader file.
	 */
	protected static function require_autoloader( string $autoloader_file ): bool {
		if ( ! is_readable( $autoloader_file ) ) {
				self::missing_autoloader_notice();
				return false;
		}

		return (bool) require_once $autoloader_file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable -- Autoloader is a Composer file.
	}

	/**
	 * Displays a notice if the autoloader is missing.
	 */
	protected static function missing_autoloader_notice(): void {
		$error_message = 'WPGraphQL Velocity: The Composer autoloader was not found. If you installed the plugin from the GitHub source, make sure to run `composer install`.';

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( esc_html( $error_message ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- This is a development notice.
		}

		$hooks = [
			'admin_notices',
			'network_admin_notices',
		];

		foreach ( $hooks as $hook ) {
			/** @psalm-suppress HookNotFound */
			add_action(
				$hook,
				static function () use ( $error_message ): void {
					?>
					<div class="error notice">
						<p>
							<?php echo esc_html( $error_message ); ?>
						</p>
					</div>
					<?php
				}
			);
		}
	}
}
