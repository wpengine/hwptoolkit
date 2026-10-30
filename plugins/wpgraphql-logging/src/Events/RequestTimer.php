<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Events;

/**
 * Tracks the elapsed time of the current WPGraphQL request lifecycle.
 *
 * The timer starts at the earliest WPGraphQL hook we can observe for a request
 * (`graphql_request_data`, falling back to `do_graphql_request`), is bound to the
 * request when `graphql_before_execute` fires, and is released on
 * `graphql_after_execute`.
 *
 * Timers are kept on a stack so that a `graphql()` call made while another
 * request is still executing (e.g. from inside a resolver) gets its own timer
 * and does not reset the outer request's timer. A batched HTTP request fires
 * `graphql_before_execute` / `graphql_after_execute` once, so all operations in
 * the batch share one timer that measures the whole HTTP request.
 *
 * @package WPGraphQL\Logging
 *
 * @since 1.1.0
 */
final class RequestTimer {
	/**
	 * Earliest hook priority, so the timer starts before other callbacks run.
	 *
	 * @var int
	 */
	public const START_PRIORITY = PHP_INT_MIN;

	/**
	 * Latest hook priority, so the timer is released after other callbacks run.
	 *
	 * @var int
	 */
	public const STOP_PRIORITY = PHP_INT_MAX;

	/**
	 * WPGraphQL action: graphql_after_execute.
	 *
	 * Fires once after a request (single or batch) has executed. Not a loggable
	 * event, so it is not part of the Events list.
	 *
	 * @var string
	 */
	public const AFTER_EXECUTE_HOOK = 'graphql_after_execute';

	/**
	 * Start time (hrtime, nanoseconds) of a request that has been seen but has not
	 * reached `graphql_before_execute` yet.
	 *
	 * @var int|null
	 */
	private static ?int $pending_start = null;

	/**
	 * Start times (hrtime, nanoseconds) of the requests currently executing.
	 *
	 * @var array<int>
	 */
	private static array $stack = [];

	/**
	 * Registers the hooks that drive the timer.
	 *
	 * @psalm-suppress HookNotFound
	 */
	public static function register_hooks(): void {
		add_filter( Events::REQUEST_DATA, [ self::class, 'on_request_data' ], self::START_PRIORITY, 1 );
		add_action( Events::PRE_REQUEST, [ self::class, 'on_pre_request' ], self::START_PRIORITY, 0 );
		add_action( Events::BEFORE_GRAPHQL_EXECUTION, [ self::class, 'on_before_execute' ], self::START_PRIORITY, 0 );
		add_action( self::AFTER_EXECUTE_HOOK, [ self::class, 'on_after_execute' ], self::STOP_PRIORITY, 0 );
	}

	/**
	 * Starts timing a new request when its data is parsed.
	 *
	 * Hooked to the `graphql_request_data` filter, which fires once per request
	 * before any operation is processed. Always restarts the pending timer, since
	 * this is the first hook of a new request.
	 *
	 * @param array<mixed> $data The request data.
	 *
	 * @return array<mixed> The request data, unchanged.
	 */
	public static function on_request_data( array $data ): array {
		self::$pending_start = hrtime( true );

		return $data;
	}

	/**
	 * Starts timing if `graphql_request_data` did not fire for this request.
	 *
	 * Hooked to `do_graphql_request`, which fires once per operation. For a batch
	 * this runs several times in a row, so it only starts the timer when one is
	 * not already pending.
	 */
	public static function on_pre_request(): void {
		if ( null !== self::$pending_start ) {
			return;
		}

		self::$pending_start = hrtime( true );
	}

	/**
	 * Binds the pending timer to the request that is about to execute.
	 *
	 * Hooked to `graphql_before_execute`.
	 */
	public static function on_before_execute(): void {
		self::$stack[]       = self::$pending_start ?? hrtime( true );
		self::$pending_start = null;
	}

	/**
	 * Releases the timer of the request that just finished.
	 *
	 * Hooked to `graphql_after_execute`.
	 */
	public static function on_after_execute(): void {
		array_pop( self::$stack );
	}

	/**
	 * Whether a request is currently being timed.
	 */
	public static function is_running(): bool {
		return null !== self::get_start();
	}

	/**
	 * Gets the elapsed time of the current request in milliseconds.
	 *
	 * @return float|null Milliseconds rounded to two decimals, or null if no request is being timed.
	 */
	public static function get_elapsed_ms(): ?float {
		$start = self::get_start();
		if ( null === $start ) {
			return null;
		}

		return round( ( hrtime( true ) - $start ) / 1000000, 2 );
	}

	/**
	 * Clears all timer state.
	 */
	public static function reset(): void {
		self::$pending_start = null;
		self::$stack         = [];
	}

	/**
	 * Gets the start time of the request currently being timed.
	 *
	 * A pending start belongs to a request that has been seen more recently than
	 * any executing one, so it takes precedence over the stack.
	 *
	 * @return int|null The hrtime start in nanoseconds, or null if none.
	 */
	private static function get_start(): ?int {
		if ( null !== self::$pending_start ) {
			return self::$pending_start;
		}

		if ( [] === self::$stack ) {
			return null;
		}

		return self::$stack[ count( self::$stack ) - 1 ];
	}
}
