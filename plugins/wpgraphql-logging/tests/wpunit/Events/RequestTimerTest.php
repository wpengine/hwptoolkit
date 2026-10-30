<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Events;

use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Events\Events;
use WPGraphQL\Logging\Events\RequestTimer;

/**
 * Tests for the RequestTimer class.
 *
 * @package WPGraphQL\Logging\Tests\Events
 *
 * @since 1.1.0
 */
class RequestTimerTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();
		RequestTimer::reset();
	}

	public function tearDown(): void {
		RequestTimer::reset();
		parent::tearDown();
	}

	public function test_not_running_by_default(): void {
		$this->assertFalse( RequestTimer::is_running() );
		$this->assertNull( RequestTimer::get_elapsed_ms() );
	}

	public function test_register_hooks(): void {
		RequestTimer::register_hooks();

		$this->assertSame( RequestTimer::START_PRIORITY, has_action( RequestTimer::HTTP_REQUEST_START_HOOK, [ RequestTimer::class, 'on_http_request' ] ) );
		$this->assertSame( RequestTimer::START_PRIORITY, has_filter( Events::REQUEST_DATA, [ RequestTimer::class, 'on_request_data' ] ) );
		$this->assertSame( RequestTimer::START_PRIORITY, has_action( Events::PRE_REQUEST, [ RequestTimer::class, 'start' ] ) );
		$this->assertSame( RequestTimer::START_PRIORITY, has_action( Events::BEFORE_GRAPHQL_EXECUTION, [ RequestTimer::class, 'on_before_execute' ] ) );
		$this->assertSame( RequestTimer::STOP_PRIORITY, has_action( RequestTimer::AFTER_EXECUTE_HOOK, [ RequestTimer::class, 'on_after_execute' ] ) );
		$this->assertSame( RequestTimer::STOP_PRIORITY, has_action( RequestTimer::HTTP_REQUEST_END_HOOK, [ RequestTimer::class, 'reset' ] ) );
	}

	public function test_http_request_lifecycle(): void {
		RequestTimer::on_http_request();
		usleep( 5000 );

		// Later hooks of the same request keep the HTTP start time.
		RequestTimer::on_request_data( [] );
		RequestTimer::start();
		RequestTimer::on_before_execute();
		$this->assertGreaterThanOrEqual( 5, RequestTimer::get_elapsed_ms() );

		RequestTimer::on_after_execute();
		$this->assertFalse( RequestTimer::is_running() );
	}

	public function test_http_request_clears_state_left_by_a_failed_request(): void {
		// A request that threw after graphql_before_execute never reaches graphql_after_execute.
		RequestTimer::on_request_data( [] );
		RequestTimer::on_before_execute();
		usleep( 20000 );

		RequestTimer::on_http_request();

		$this->assertLessThan( 20, RequestTimer::get_elapsed_ms() );
		RequestTimer::on_before_execute();
		RequestTimer::on_after_execute();
		$this->assertFalse( RequestTimer::is_running() );
	}

	public function test_request_data_starts_timer_and_returns_data_unchanged(): void {
		$data = [ 'query' => '{ posts { nodes { id } } }' ];

		$this->assertSame( $data, RequestTimer::on_request_data( $data ) );
		$this->assertTrue( RequestTimer::is_running() );

		$elapsed = RequestTimer::get_elapsed_ms();
		$this->assertIsFloat( $elapsed );
		$this->assertGreaterThanOrEqual( 0, $elapsed );
	}

	public function test_elapsed_time_increases(): void {
		RequestTimer::on_request_data( [] );
		usleep( 5000 );

		$this->assertGreaterThanOrEqual( 5, RequestTimer::get_elapsed_ms() );
	}

	public function test_start_starts_timer_when_not_pending(): void {
		RequestTimer::start();
		$this->assertTrue( RequestTimer::is_running() );
	}

	public function test_start_does_not_restart_pending_timer_for_batch_operations(): void {
		RequestTimer::on_request_data( [] );
		usleep( 5000 );

		// A batch fires do_graphql_request once per operation.
		RequestTimer::start();
		RequestTimer::start();

		$this->assertGreaterThanOrEqual( 5, RequestTimer::get_elapsed_ms() );
	}

	public function test_request_data_keeps_earlier_pending_start(): void {
		RequestTimer::start();
		usleep( 5000 );
		RequestTimer::on_request_data( [] );

		$this->assertGreaterThanOrEqual( 5, RequestTimer::get_elapsed_ms() );
	}

	public function test_request_data_returns_non_array_data_unchanged(): void {
		$this->assertNull( RequestTimer::on_request_data( null ) );
	}

	public function test_timer_spans_execution_and_stops_after_execute(): void {
		RequestTimer::on_request_data( [] );
		RequestTimer::start();
		usleep( 5000 );
		RequestTimer::on_before_execute();

		$this->assertTrue( RequestTimer::is_running() );
		$this->assertGreaterThanOrEqual( 5, RequestTimer::get_elapsed_ms() );

		RequestTimer::on_after_execute();

		$this->assertFalse( RequestTimer::is_running() );
		$this->assertNull( RequestTimer::get_elapsed_ms() );
	}

	public function test_before_execute_starts_timer_without_earlier_hooks(): void {
		RequestTimer::on_before_execute();
		$this->assertTrue( RequestTimer::is_running() );

		RequestTimer::on_after_execute();
		$this->assertFalse( RequestTimer::is_running() );
	}

	public function test_after_execute_without_running_timer_is_safe(): void {
		RequestTimer::on_after_execute();
		$this->assertFalse( RequestTimer::is_running() );
	}

	public function test_nested_request_does_not_reset_outer_request(): void {
		// Outer request.
		RequestTimer::on_request_data( [] );
		RequestTimer::on_before_execute();
		usleep( 20000 );

		// Inner request, e.g. graphql() called from a resolver.
		RequestTimer::on_request_data( [] );
		$this->assertLessThan( 20, RequestTimer::get_elapsed_ms() );
		RequestTimer::on_before_execute();
		$this->assertLessThan( 20, RequestTimer::get_elapsed_ms() );
		RequestTimer::on_after_execute();

		// Back on the outer request.
		$this->assertTrue( RequestTimer::is_running() );
		$this->assertGreaterThanOrEqual( 20, RequestTimer::get_elapsed_ms() );

		RequestTimer::on_after_execute();
		$this->assertFalse( RequestTimer::is_running() );
	}

	public function test_reset_clears_state(): void {
		RequestTimer::on_request_data( [] );
		RequestTimer::on_before_execute();
		RequestTimer::on_request_data( [] );

		RequestTimer::reset();

		$this->assertFalse( RequestTimer::is_running() );
	}

	public function test_graphql_request_is_timed_and_each_request_starts_fresh(): void {
		RequestTimer::register_hooks();

		$captured  = [];
		$capture   = static function () use ( &$captured ): void {
			$captured[] = RequestTimer::get_elapsed_ms();
		};
		$slow_down = static function (): void {
			usleep( 20000 );
		};

		add_action( Events::BEFORE_RESPONSE_RETURNED, $capture );
		add_action( Events::BEFORE_GRAPHQL_EXECUTION, $slow_down );
		graphql( [ 'query' => '{ __typename }' ] );
		remove_action( Events::BEFORE_GRAPHQL_EXECUTION, $slow_down );
		remove_action( Events::BEFORE_RESPONSE_RETURNED, $capture );

		$this->assertCount( 1, $captured );
		$this->assertGreaterThanOrEqual( 20, $captured[0] );
		$this->assertFalse( RequestTimer::is_running() );

		// The second request must not include the first request's time.
		add_action( Events::PRE_REQUEST, $capture );
		graphql( [ 'query' => '{ __typename }' ] );
		remove_action( Events::PRE_REQUEST, $capture );

		$this->assertCount( 2, $captured );
		$this->assertLessThan( 20, $captured[1] );
		$this->assertFalse( RequestTimer::is_running() );
	}

	public function test_batched_graphql_request_times_each_operation(): void {
		RequestTimer::register_hooks();

		$captured = [];
		$capture  = static function () use ( &$captured ): void {
			$captured[] = RequestTimer::get_elapsed_ms();
		};

		add_action( Events::BEFORE_RESPONSE_RETURNED, $capture );
		graphql(
			[
				[ 'query' => '{ __typename }' ],
				[ 'query' => '{ __typename }' ],
			]
		);
		remove_action( Events::BEFORE_RESPONSE_RETURNED, $capture );

		$this->assertCount( 2, $captured );
		$this->assertIsFloat( $captured[0] );
		$this->assertIsFloat( $captured[1] );
		$this->assertFalse( RequestTimer::is_running() );
	}
}
