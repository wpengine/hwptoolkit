<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Events;

/**
 * Simple pub/sub Event Manager for WPGraphQL Logging
 *
 * Provides a lightweight event bus with optional WordPress bridge.
 *
 * Users can:
 * - subscribe to events using subscribe()
 * - publish events using publish()
 * - also listen via WordPress hooks: `wpgraphql_logging_event_{event_name}`
 */
final class EventManager {
	/**
	 * In-memory map of event name to priority to listeners.
	 *
	 * @var array<string, array<int, array<int, callable>>>
	 */
	private static array $events = [];

	/**
	 * Transform listeners that can modify a payload.
	 *
	 * @var array<string, array<int, array<int, callable>>>
	 */
	private static array $transforms = [];

	/**
	 * Subscribe a listener to an event.
	 *
	 * @param string   $event_name Event name (see Events constants).
	 * @param callable $listener  Listener callable: function(array $payload): void {}.
	 * @param int      $priority  Lower runs earlier.
	 */
	public static function subscribe(string $event_name, callable $listener, int $priority = 10): void {
		if ( ! isset( self::$events[ $event_name ] ) ) {
			self::$events[ $event_name ] = [];
		}
		if ( ! isset( self::$events[ $event_name ][ $priority ] ) ) {
			self::$events[ $event_name ][ $priority ] = [];
		}

		self::$events[ $event_name ][ $priority ][] = $listener;
	}

	/**
	 * Publish an event to all subscribers and a WordPress action bridge.
	 *
	 * @param string               $event_name Event name (see Events constants).
	 * @param array<string, mixed> $payload   Arbitrary payload for listeners.
	 */
	public static function publish(string $event_name, array $payload = []): void {

		$ordered_listeners = self::get_ordered_listeners( $event_name );

		if ( [] === $ordered_listeners ) {
			/** @psalm-suppress HookNotFound */
			do_action( 'wpgraphql_logging_event_' . $event_name, $payload );
			return;
		}

		foreach ( $ordered_listeners as $listener ) {
			self::invoke_listener( $listener, $payload );
		}

		/** @psalm-suppress HookNotFound */
		do_action( 'wpgraphql_logging_event_' . $event_name, $payload );
	}

	/**
	 * Subscribe a transformer to modify the payload before it is used by core code.
	 *
	 * @param string   $event_name Event name.
	 * @param callable $transform function(array $payload): array {}.
	 * @param int      $priority  Lower runs earlier.
	 */
	public static function subscribe_to_transform(string $event_name, callable $transform, int $priority = 10): void {
		if ( ! isset( self::$transforms[ $event_name ] ) ) {
			self::$transforms[ $event_name ] = [];
		}
		if ( ! isset( self::$transforms[ $event_name ][ $priority ] ) ) {
			self::$transforms[ $event_name ][ $priority ] = [];
		}

		self::$transforms[ $event_name ][ $priority ][] = $transform;
	}

	/**
	 * Transform a payload by running transform subscribers and a WordPress filter bridge.
	 *
	 * @param string               $event_name Event name.
	 * @param array<string, mixed> $payload   Initial payload.
	 *
	 * @return array<string, mixed> Modified payload.
	 */
	public static function transform(string $event_name, array $payload): array {

		$ordered_transforms = self::get_ordered_transforms( $event_name );
		if ( [] === $ordered_transforms ) {
			/** @psalm-suppress HookNotFound */
			return apply_filters( 'wpgraphql_logging_filter_' . $event_name, $payload );
		}

		foreach ( $ordered_transforms as $transform ) {
			$payload = self::invoke_transform( $transform, $payload );
		}

		/** @psalm-suppress HookNotFound */
		return apply_filters( 'wpgraphql_logging_filter_' . $event_name, $payload );
	}

	/**
	 * Return listeners for an event flattened and ordered by priority (ascending).
	 *
	 * @param string $event_name Event name.
	 *
	 * @return array<int, callable>
	 */
	private static function get_ordered_listeners(string $event_name): array {
		if ( ! isset( self::$events[ $event_name ] ) ) {
			return [];
		}

		$priority_to_listeners = self::$events[ $event_name ];
		ksort( $priority_to_listeners );

		$ordered = [];
		foreach ( $priority_to_listeners as $listeners_at_priority ) {
			foreach ( $listeners_at_priority as $listener ) {
				$ordered[] = $listener;
			}
		}

		return $ordered;
	}

	/**
	 * Return transforms for an event flattened and ordered by priority (ascending).
	 *
	 * @param string $event_name Event name.
	 *
	 * @return array<int, callable>
	 */
	private static function get_ordered_transforms(string $event_name): array {
		if ( ! isset( self::$transforms[ $event_name ] ) ) {
			return [];
		}

		$priority_to_transforms = self::$transforms[ $event_name ];
		ksort( $priority_to_transforms );

		$ordered = [];
		foreach ( $priority_to_transforms as $transforms_at_priority ) {
			foreach ( $transforms_at_priority as $transform ) {
				$ordered[] = $transform;
			}
		}

		return $ordered;
	}

	/**
	 * Invoke a listener safely; errors are logged and do not break the pipeline.
	 *
	 * @param callable             $listener Listener.
	 * @param array<string, mixed> $payload  Payload for listener.
	 */
	private static function invoke_listener(callable $listener, array $payload): void {
		try {
			$listener( $payload );
		} catch ( \Throwable $e ) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'WPGraphQL Logging EventManager listener error: ' . $e->getMessage() );
		}
	}

	/**
	 * Invoke a transform safely; returns the updated payload if valid, otherwise the original.
	 *
	 * @param callable             $transform Transform callable.
	 * @param array<string, mixed> $payload   Current payload.
	 *
	 * @return array<string, mixed> Updated payload.
	 */
	private static function invoke_transform(callable $transform, array $payload): array {
		try {
			$result = $transform( $payload );
			if ( is_array( $result ) ) {
				return $result;
			}
		} catch ( \Throwable $e ) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'WPGraphQL Logging EventManager transform error: ' . $e->getMessage() );
		}

		return $payload;
	}
}
