<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Events;

/**
 * Tracks the elapsed time of the current WPGraphQL request lifecycle.
 *
 * The timer starts at the earliest WPGraphQL hook we can observe for a request:
 * `graphql_process_http_request` for HTTP requests, otherwise
 * `graphql_request_data` or `do_graphql_request` for `graphql()` calls. It is
 * bound to the request when `graphql_before_execute` fires and released on
 * `graphql_after_execute`.
 *
 * Timers are kept on a stack so that a `graphql()` call made while another
 * request is still executing (e.g. from inside a resolver) gets its own timer
 * and does not reset the outer request's timer. A batched HTTP request fires
 * `graphql_before_execute` / `graphql_after_execute` once, so all operations in
 * the batch share one timer that measures the whole HTTP request.
 *
 * All state is cleared when an HTTP request starts and when its response is
 * ready (`graphql_process_http_request_response`), so a request that throws
 * before `graphql_after_execute` cannot leak its timer into the next one.
 *
 * @package WPGraphQL\Logging
 *
 * @since 1.1.0
 */
final class RequestTimer {
	/**
	 * The log data key for the elapsed time, used in both `extra` and `context`.
	 *
	 * @var string
	 */
	public const DURATION_KEY = 'request_duration_ms';

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
	 * WPGraphQL action: graphql_process_http_request.
	 *
	 * Fires once per HTTP request, before the WPGraphQL request is created.
	 *
	 * @var string
	 */
	public const HTTP_REQUEST_START_HOOK = 'graphql_process_http_request';

	/**
	 * WPGraphQL action: graphql_process_http_request_response.
	 *
	 * Fires once per HTTP request when the response is ready to be sent, including
	 * when execution threw an error.
	 *
	 * @var string
	 */
	public const HTTP_REQUEST_END_HOOK = 'graphql_process_http_request_response';

	/**
	 * WPGraphQL action: graphql_after_execute.
	 *
	 * Fires once after a request (single or batch) has executed.
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
	 * The HTTP and after-execute hooks are not logging events, so their names are
	 * defined here rather than in Events.
	 *
	 * @psalm-suppress HookNotFound
	 */
	public static function register_hooks(): void {
		add_action( self::HTTP_REQUEST_START_HOOK, [ self::class, 'on_http_request' ], self::START_PRIORITY, 0 );
		// Literal name (Events::REQUEST_DATA): psalm's WordPress plugin records hooks named by
		// constants under one placeholder, so a filter here clashes with actions registered elsewhere.
		add_filter( 'graphql_request_data', [ self::class, 'on_request_data' ], self::START_PRIORITY, 1 );
		add_action( Events::PRE_REQUEST, [ self::class, 'start' ], self::START_PRIORITY, 0 );
		add_action( Events::BEFORE_GRAPHQL_EXECUTION, [ self::class, 'on_before_execute' ], self::START_PRIORITY, 0 );
		add_action( self::AFTER_EXECUTE_HOOK, [ self::class, 'on_after_execute' ], self::STOP_PRIORITY, 0 );
		add_action( self::HTTP_REQUEST_END_HOOK, [ self::class, 'reset' ], self::STOP_PRIORITY, 0 );
	}

	/**
	 * Starts timing a new HTTP request.
	 *
	 * Hooked to `graphql_process_http_request`. An HTTP request is always the
	 * outermost request, so any state left over from earlier requests is cleared.
	 */
	public static function on_http_request(): void {
		self::reset();
		self::start();
	}

	/**
	 * Starts timing when the request data is parsed, if not already started.
	 *
	 * Hooked to the `graphql_request_data` filter.
	 *
	 * @param mixed $data The request data. Returned unchanged.
	 */
	public static function on_request_data( mixed $data ): mixed {
		self::start();

		return $data;
	}

	/**
	 * Starts timing a request that has been seen but not executed yet.
	 *
	 * Does nothing if a pending timer already exists, so the earliest hook wins
	 * and the repeated `do_graphql_request` calls of a batch do not restart it.
	 */
	public static function start(): void {
		if ( null !== self::$pending_start ) {
			return;
		}

		self::$pending_start = self::now();
	}

	/**
	 * Binds the pending timer to the request that is about to execute.
	 *
	 * Hooked to `graphql_before_execute`.
	 */
	public static function on_before_execute(): void {
		self::$stack[]       = self::$pending_start ?? self::now();
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
	 * Gets the elapsed time of the current request in milliseconds.
	 *
	 * @return float|null Milliseconds rounded to two decimals, or null if no request is being timed.
	 */
	public static function get_elapsed_ms(): ?float {
		$start = self::get_start();
		if ( null === $start ) {
			return null;
		}

		return round( ( self::now() - $start ) / 1000000, 2 );
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
	 * @return int|null The start time in nanoseconds, or null if none.
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

	/**
	 * Gets the current monotonic time in nanoseconds.
	 *
	 * Falls back to the wall clock if hrtime() is unavailable, so the timer can
	 * never break a GraphQL request.
	 */
	private static function now(): int {
		$time = hrtime( true );
		if ( is_int( $time ) ) {
			return $time;
		}

		return (int) ( microtime( true ) * 1e9 );
	}
}
