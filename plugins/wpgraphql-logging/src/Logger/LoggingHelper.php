<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger;

use WPGraphQL\Logging\Admin\Settings\Fields\Tab\Basic_Configuration_Tab;

/**
 * Trait for shared logging helper methods.
 */
trait LoggingHelper {
	/**
	 * Checks if logging is enabled based on user settings.
	 *
	 * @param array<string, mixed> $config The logging configuration.
	 */
	protected function is_logging_enabled( array $config ): bool {
		$is_enabled = true;

		// Check the main "Enabled" checkbox.
		if ( ! ( $config[ Basic_Configuration_Tab::ENABLED ] ?? false ) ) {
			$is_enabled = false;
		}

		// Check if the current user is an admin if that option is enabled.
		if ( $is_enabled && ( $config[ Basic_Configuration_Tab::ADMIN_USER_LOGGING ] ?? false ) ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				$is_enabled = false;
			}
		}

		// Check for IP restrictions.
		$ip_restrictions = $config[ Basic_Configuration_Tab::IP_RESTRICTIONS ] ?? '';
		if ( $is_enabled && ! empty( $ip_restrictions ) ) {
			$allowed_ips = array_map( 'trim', explode( ',', $ip_restrictions ) );
			if ( ! in_array( $_SERVER['REMOTE_ADDR'], $allowed_ips, true ) ) {
				$is_enabled = false;
			}
		}

		// Check the data sampling rate.
		if ( $is_enabled ) {
			$sampling_rate = (int) ( $config[ Basic_Configuration_Tab::DATA_SAMPLING ] ?? 100 );
			if ( mt_rand( 0, 100 ) >= $sampling_rate ) {
				$is_enabled = false;
			}
		}

		/**
		 * Filter the final decision on whether to log a request.
		 *
		 * @param bool                  $is_enabled True if logging is enabled, false otherwise.
		 * @param array<string, mixed>  $config     The current logging configuration.
		 */
		return apply_filters( 'wpgraphql_logging_is_enabled', $is_enabled, $config );
	}
}
