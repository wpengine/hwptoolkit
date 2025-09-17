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
	 *
	 * phpcs:disable Generic.Metrics.CyclomaticComplexity, SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
	 */
	protected function is_logging_enabled( array $config, ?string $query_string = null ): bool {
		if ( null === $query_string ) {
			return false;
		}

		$is_enabled = true;
		// Check the main "Enabled" checkbox.
		if ( ! (bool) ( $config[ Basic_Configuration_Tab::ENABLED ] ?? false ) ) {
			$is_enabled = false;
		}

		// Do not log the seedQuery for Faust.js.
		if ( $is_enabled && ( 'query GetSeedNode' === trim( $query_string ) ) ) {
			$is_enabled = false;
		}

		// Check if the current user is an admin if that option is enabled.
		if ( $is_enabled && ( (bool) ( $config[ Basic_Configuration_Tab::ADMIN_USER_LOGGING ] ?? false ) ) ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				$is_enabled = false;
			}
		}

		// Check for IP restrictions.
		$ip_restrictions = $config[ Basic_Configuration_Tab::IP_RESTRICTIONS ] ?? '';
		if ( $is_enabled && ! empty( $ip_restrictions ) ) {
			$allowed_ips = array_map( 'trim', explode( ',', $ip_restrictions ) );
			$remote_addr = $_SERVER['REMOTE_ADDR'] ?? ''; // @phpcs:ignore
			if ( ! in_array( $remote_addr, $allowed_ips, true ) ) {
				$is_enabled = false;
			}
		}

		// Check the data sampling rate.
		if ( $is_enabled ) {
			$sampling_rate = (int) ( $config[ Basic_Configuration_Tab::DATA_SAMPLING ] ?? 100 );
			if ( wp_rand( 0, 100 ) >= $sampling_rate ) {
				$is_enabled = false;
			}
		}

		// Check if the query is an introspection query and skip logging if it is.
		if ( $is_enabled && $this->is_introspection_query( $query_string ) ) {
			$is_enabled = false;
		}

		/**
		 * Filter the final decision on whether to log a request.
		 *
		 * @param bool                  $is_enabled True if logging is enabled, false otherwise.
		 * @param array<string, mixed>  $config     The current logging configuration.
		 */
		return apply_filters( 'wpgraphql_logging_is_enabled', $is_enabled, $config );
	}

	/**
	 * Checks if a query is an introspection query.
	 *
	 * @param string|null $query_string The GraphQL query string.
	 */
	protected function is_introspection_query( ?string $query_string ): bool {
		if ( null === $query_string ) {
			return false;
		}

		return strpos( $query_string, '__schema' ) !== false;
	}
}
