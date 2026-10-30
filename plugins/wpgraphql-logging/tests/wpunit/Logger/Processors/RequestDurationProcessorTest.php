<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Logger\Processors;

use DateTimeImmutable;
use lucatume\WPBrowser\TestCase\WPTestCase;
use Monolog\Level;
use Monolog\LogRecord;
use WPGraphQL\Logging\Events\RequestTimer;
use WPGraphQL\Logging\Logger\LoggerService;
use WPGraphQL\Logging\Logger\Processors\RequestDurationProcessor;

/**
 * Test cases for the RequestDurationProcessor.
 *
 * @package WPGraphQL\Logging
 *
 * @since 1.1.0
 */
class RequestDurationProcessorTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();
		RequestTimer::reset();
	}

	public function tearDown(): void {
		RequestTimer::reset();
		parent::tearDown();
	}

	private function create_record(): LogRecord {
		return new LogRecord(
			new DateTimeImmutable( 'now' ),
			'wpgraphql_logging',
			Level::Info,
			'Test log message',
			[],
			[]
		);
	}

	public function test_record_unchanged_outside_of_a_request(): void {
		$processor = new RequestDurationProcessor();
		$record    = $processor( $this->create_record() );

		$this->assertArrayNotHasKey( RequestDurationProcessor::EXTRA_KEY, $record->extra );
	}

	public function test_adds_elapsed_time_during_a_request(): void {
		RequestTimer::on_request_data( [] );
		usleep( 5000 );

		$processor = new RequestDurationProcessor();
		$record    = $processor( $this->create_record() );

		$this->assertArrayHasKey( RequestDurationProcessor::EXTRA_KEY, $record->extra );
		$this->assertIsFloat( $record->extra[ RequestDurationProcessor::EXTRA_KEY ] );
		$this->assertGreaterThanOrEqual( 5, $record->extra[ RequestDurationProcessor::EXTRA_KEY ] );
	}

	public function test_elapsed_time_grows_across_records_in_the_same_request(): void {
		RequestTimer::on_request_data( [] );
		$processor = new RequestDurationProcessor();

		$first = $processor( $this->create_record() );
		usleep( 5000 );
		$second = $processor( $this->create_record() );

		$this->assertGreaterThan(
			$first->extra[ RequestDurationProcessor::EXTRA_KEY ],
			$second->extra[ RequestDurationProcessor::EXTRA_KEY ]
		);
	}

	public function test_is_a_default_processor(): void {
		$classes = array_map( 'get_class', LoggerService::get_default_processors() );

		$this->assertContains( RequestDurationProcessor::class, $classes );
	}
}
