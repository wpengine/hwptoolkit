<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Logger\Database;

use DateTimeImmutable;
use DateTimeZone;
use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseLogService;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseLogStats;

/**
 * Tests for the dashboard aggregation queries.
 *
 * @package WPGraphQL\Logging
 *
 * @since 1.1.0
 */
class WordPressDatabaseLogStatsTest extends WPTestCase {

	private WordPressDatabaseLogService $log_service;

	private WordPressDatabaseLogStats $stats;

	private DateTimeImmutable $start;

	protected function setUp(): void {
		parent::setUp();
		$this->log_service = new WordPressDatabaseLogService();
		$this->log_service->activate();
		$this->log_service->delete_all_entities();
		$this->stats = new WordPressDatabaseLogStats();
		$this->start = new DateTimeImmutable( '2026-01-01 00:00:00', new DateTimeZone( 'UTC' ) );
	}

	protected function tearDown(): void {
		$this->log_service->delete_all_entities();
		parent::tearDown();
	}

	/**
	 * Inserts a log row with an explicit datetime.
	 *
	 * @param string      $datetime  UTC datetime.
	 * @param int         $level     Monolog level.
	 * @param string      $level_name Level name.
	 * @param string|null $operation Operation name stored in context.operation_name.
	 */
	private function insert_row( string $datetime, int $level = 200, string $level_name = 'INFO', ?string $operation = 'GetPosts' ): void {
		global $wpdb;
		$wpdb->insert(
			WordPressDatabaseEntity::get_table_name(),
			[
				'channel'    => 'wpgraphql_logging',
				'level'      => $level,
				'level_name' => $level_name,
				'message'    => 'WPGraphQL Response',
				'context'    => wp_json_encode( [ 'operation_name' => $operation ] ),
				'extra'      => wp_json_encode( [] ),
				'datetime'   => $datetime,
			]
		);
	}

	private function end( int $hours ): DateTimeImmutable {
		return $this->start->modify( sprintf( '+%d hours', $hours ) );
	}

	public function test_summary_is_zero_for_empty_table(): void {
		$summary = $this->stats->get_summary( $this->start, $this->end( 24 ) );
		$this->assertSame( [ 'total' => 0, 'errors' => 0, 'operations' => 0 ], $summary );
	}

	public function test_summary_counts_entries_errors_and_distinct_operations(): void {
		$this->insert_row( '2026-01-01 01:00:00' );
		$this->insert_row( '2026-01-01 02:00:00', 400, 'ERROR' );
		$this->insert_row( '2026-01-01 03:00:00', 500, 'CRITICAL', 'GetPage' );
		$this->insert_row( '2026-01-01 04:00:00', 300, 'WARNING', null );
		// Outside the window.
		$this->insert_row( '2025-12-31 23:59:59', 400, 'ERROR', 'Old' );
		$this->insert_row( '2026-01-02 00:00:00', 400, 'ERROR', 'Later' );

		$summary = $this->stats->get_summary( $this->start, $this->end( 24 ) );

		$this->assertSame( 4, $summary['total'] );
		$this->assertSame( 2, $summary['errors'], 'ERROR and above count as errors; WARNING does not.' );
		$this->assertSame( 2, $summary['operations'], 'Unnamed operations are not counted as distinct operations.' );
	}

	public function test_time_series_fills_empty_buckets_and_groups_by_bucket(): void {
		$this->insert_row( '2026-01-01 00:00:00' );
		$this->insert_row( '2026-01-01 00:59:59', 400, 'ERROR' );
		$this->insert_row( '2026-01-01 05:30:00' );
		$this->insert_row( '2026-01-01 23:59:59', 500, 'CRITICAL' );
		// Outside the window.
		$this->insert_row( '2026-01-02 00:00:00' );

		$series = $this->stats->get_time_series( $this->start, HOUR_IN_SECONDS, 24 );

		$this->assertCount( 24, $series );
		$this->assertSame( [ 'total' => 2, 'errors' => 1 ], $series[0] );
		$this->assertSame( [ 'total' => 0, 'errors' => 0 ], $series[1] );
		$this->assertSame( [ 'total' => 1, 'errors' => 0 ], $series[5] );
		$this->assertSame( [ 'total' => 1, 'errors' => 1 ], $series[23] );
		$this->assertSame( 4, array_sum( array_column( $series, 'total' ) ) );
	}

	public function test_time_series_daily_buckets(): void {
		$this->insert_row( '2026-01-01 12:00:00' );
		$this->insert_row( '2026-01-03 00:00:00' );
		$this->insert_row( '2026-01-03 23:00:00', 400, 'ERROR' );

		$series = $this->stats->get_time_series( $this->start, DAY_IN_SECONDS, 7 );

		$this->assertCount( 7, $series );
		$this->assertSame( 1, $series[0]['total'] );
		$this->assertSame( 0, $series[1]['total'] );
		$this->assertSame( [ 'total' => 2, 'errors' => 1 ], $series[2] );
	}

	public function test_level_breakdown_orders_by_level_descending(): void {
		$this->insert_row( '2026-01-01 01:00:00' );
		$this->insert_row( '2026-01-01 02:00:00' );
		$this->insert_row( '2026-01-01 03:00:00', 400, 'ERROR' );

		$levels = $this->stats->get_level_breakdown( $this->start, $this->end( 24 ) );

		$this->assertSame(
			[
				[ 'level' => 400, 'level_name' => 'ERROR', 'total' => 1 ],
				[ 'level' => 200, 'level_name' => 'INFO', 'total' => 2 ],
			],
			$levels
		);
	}

	public function test_top_operations_groups_unnamed_and_respects_limit(): void {
		$this->insert_row( '2026-01-01 01:00:00', 200, 'INFO', 'GetPosts' );
		$this->insert_row( '2026-01-01 02:00:00', 400, 'ERROR', 'GetPosts' );
		$this->insert_row( '2026-01-01 03:00:00', 200, 'INFO', 'GetPosts' );
		$this->insert_row( '2026-01-01 04:00:00', 200, 'INFO', null );
		$this->insert_row( '2026-01-01 05:00:00', 200, 'INFO', null );
		$this->insert_row( '2026-01-01 06:00:00', 200, 'INFO', 'GetMenu' );

		$operations = $this->stats->get_top_operations( $this->start, $this->end( 24 ), 2 );

		$this->assertSame(
			[
				[ 'operation' => 'GetPosts', 'total' => 3, 'errors' => 1 ],
				[ 'operation' => '', 'total' => 2, 'errors' => 0 ],
			],
			$operations
		);
	}

	public function test_top_operations_treats_missing_operation_key_as_unnamed(): void {
		global $wpdb;
		$wpdb->insert(
			WordPressDatabaseEntity::get_table_name(),
			[
				'channel'    => 'wpgraphql_logging',
				'level'      => 200,
				'level_name' => 'INFO',
				'message'    => 'WPGraphQL Pre Request',
				'context'    => wp_json_encode( [ 'query' => '{ posts { nodes { id } } }' ] ),
				'extra'      => wp_json_encode( [] ),
				'datetime'   => '2026-01-01 01:00:00',
			]
		);

		$operations = $this->stats->get_top_operations( $this->start, $this->end( 24 ) );

		$this->assertSame( [ [ 'operation' => '', 'total' => 1, 'errors' => 0 ] ], $operations );
	}
}
