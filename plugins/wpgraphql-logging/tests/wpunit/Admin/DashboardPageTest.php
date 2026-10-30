<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Admin;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use lucatume\WPBrowser\TestCase\WPTestCase;
use ReflectionClass;
use WPGraphQL\Logging\Admin\DashboardPage;
use WPGraphQL\Logging\Logger\Api\LogStatsInterface;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseLogService;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseLogStats;

/**
 * Tests for the dashboard page.
 *
 * @package WPGraphQL\Logging
 *
 * @since 1.1.0
 */
class DashboardPageTest extends WPTestCase {

	private WordPressDatabaseLogService $log_service;

	protected function setUp(): void {
		parent::setUp();
		$this->reset_instance();
		$this->log_service = new WordPressDatabaseLogService();
		$this->log_service->activate();
		$this->log_service->delete_all_entities();
		$this->delete_cache();
		update_option( 'timezone_string', 'UTC' );
	}

	protected function tearDown(): void {
		$this->log_service->delete_all_entities();
		$this->delete_cache();
		$this->reset_instance();
		unset( $_GET['range'] );
		remove_all_filters( 'wpgraphql_logging_dashboard_ranges' );
		remove_all_filters( 'wpgraphql_logging_dashboard_stats_service' );
		remove_all_filters( 'wpgraphql_logging_dashboard_cache_ttl' );
		parent::tearDown();
	}

	private function reset_instance(): void {
		$reflection = new ReflectionClass( DashboardPage::class );
		$instance   = $reflection->getProperty( 'instance' );
		$instance->setAccessible( true );
		$instance->setValue( null, null );
	}

	private function delete_cache(): void {
		foreach ( [ '24h', '7d', '30d' ] as $range ) {
			delete_transient( DashboardPage::CACHE_KEY_PREFIX . $range );
		}
	}

	private function set_as_admin(): void {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
	}

	private function insert_row( int $timestamp, int $level = 200, string $level_name = 'INFO', ?string $operation = 'GetPosts' ): void {
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
				'datetime'   => gmdate( 'Y-m-d H:i:s', $timestamp ),
			]
		);
	}

	public function test_init_returns_null_without_manage_options(): void {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'editor' ] ) );
		$this->assertNull( DashboardPage::init() );
	}

	public function test_init_returns_singleton_and_registers_hooks(): void {
		$this->set_as_admin();
		$instance = DashboardPage::init();

		$this->assertInstanceOf( DashboardPage::class, $instance );
		$this->assertSame( $instance, DashboardPage::init() );
		$this->assertSame( 10, has_action( 'admin_menu', [ $instance, 'register_page' ] ) );
		$this->assertSame( 10, has_action( 'admin_enqueue_scripts', [ $instance, 'enqueue_admin_styles' ] ) );
	}

	public function test_register_page_adds_submenu_under_logs_menu(): void {
		global $submenu;
		$this->set_as_admin();
		set_current_screen( 'dashboard' );

		$instance = DashboardPage::init();
		$instance->register_page();

		$slugs = array_column( $submenu['wpgraphql-logging-view'] ?? [], 2 );
		$this->assertContains( DashboardPage::ADMIN_PAGE_SLUG, $slugs );
	}

	public function test_styles_are_only_enqueued_on_the_dashboard_page(): void {
		$this->set_as_admin();
		$instance = DashboardPage::init();

		$reflection = new ReflectionClass( $instance );
		$page_hook  = $reflection->getProperty( 'page_hook' );
		$page_hook->setAccessible( true );
		$page_hook->setValue( $instance, 'graphql-logs_page_wpgraphql-logging-dashboard' );

		$instance->enqueue_admin_styles( 'toplevel_page_wpgraphql-logging-view' );
		$this->assertFalse( wp_style_is( 'wpgraphql-logging-dashboard', 'enqueued' ) );

		$instance->enqueue_admin_styles( 'graphql-logs_page_wpgraphql-logging-dashboard' );
		$this->assertTrue( wp_style_is( 'wpgraphql-logging-dashboard', 'enqueued' ) );
	}

	public function test_get_current_range_defaults_and_validates_input(): void {
		$page = new DashboardPage();
		$this->assertSame( DashboardPage::DEFAULT_RANGE, $page->get_current_range() );

		$_GET['range'] = '24h';
		$this->assertSame( '24h', $page->get_current_range() );

		$_GET['range'] = '<script>alert(1)</script>';
		$this->assertSame( DashboardPage::DEFAULT_RANGE, $page->get_current_range() );

		$_GET['range'] = [ '30d' ];
		$this->assertSame( DashboardPage::DEFAULT_RANGE, $page->get_current_range() );
	}

	public function test_get_ranges_drops_malformed_filtered_ranges(): void {
		add_filter(
			'wpgraphql_logging_dashboard_ranges',
			static function ( array $ranges ): array {
				$ranges['90d']      = [
					'label'          => 'Last 90 days',
					'bucket_seconds' => DAY_IN_SECONDS,
					'bucket_count'   => 90,
				];
				$ranges['broken']   = [ 'label' => 'Broken' ];
				$ranges['zero']     = [
					'label'          => 'Zero',
					'bucket_seconds' => 0,
					'bucket_count'   => 10,
				];
				$ranges['Bad Slug'] = [
					'label'          => 'Bad',
					'bucket_seconds' => 60,
					'bucket_count'   => 10,
				];
				return $ranges;
			}
		);

		$ranges = ( new DashboardPage() )->get_ranges();

		$this->assertSame( [ '24h', '7d', '30d', '90d' ], array_keys( $ranges ) );
	}

	public function test_get_ranges_falls_back_to_defaults_when_filter_returns_garbage(): void {
		add_filter( 'wpgraphql_logging_dashboard_ranges', '__return_false' );
		$this->assertSame( [ '24h', '7d', '30d' ], array_keys( ( new DashboardPage() )->get_ranges() ) );
	}

	public function test_get_window_hourly_ends_after_now(): void {
		$page   = new DashboardPage();
		$now    = new DateTimeImmutable( '2026-03-10 14:25:00', new DateTimeZone( 'UTC' ) );
		$window = $page->get_window(
			[
				'label'          => '24h',
				'bucket_seconds' => HOUR_IN_SECONDS,
				'bucket_count'   => 24,
			],
			$now
		);

		$this->assertCount( 24, $window['bucket_starts'] );
		$this->assertSame( '2026-03-09 15:00:00', gmdate( 'Y-m-d H:i:s', $window['start'] ) );
		$this->assertSame( '2026-03-10 15:00:00', gmdate( 'Y-m-d H:i:s', $window['end'] ) );
		$this->assertSame( '2026-03-10 14:00:00', gmdate( 'Y-m-d H:i:s', end( $window['bucket_starts'] ) ) );
	}

	public function test_get_window_daily_uses_local_calendar_days_across_dst(): void {
		update_option( 'timezone_string', 'America/New_York' );
		$page = new DashboardPage();

		// Daylight saving time ends in New York at 02:00 on 2026-11-01, so that day has 25 hours.
		$now    = new DateTimeImmutable( '2026-11-01 23:30:00', new DateTimeZone( 'America/New_York' ) );
		$window = $page->get_window(
			[
				'label'          => '7d',
				'bucket_seconds' => DAY_IN_SECONDS,
				'bucket_count'   => 7,
			],
			$now
		);

		$local = static fn ( int $timestamp ): string => wp_date( 'Y-m-d H:i', $timestamp );

		$this->assertSame( '2026-10-26 00:00', $local( $window['start'] ) );
		$this->assertSame( '2026-11-02 00:00', $local( $window['end'] ) );
		$this->assertSame( '2026-11-01 00:00', $local( end( $window['bucket_starts'] ) ) );
		$this->assertSame( 169 * HOUR_IN_SECONDS, $window['end'] - $window['start'] );
		$this->assertGreaterThanOrEqual( end( $window['bucket_starts'] ), $now->getTimestamp() );
		$this->assertLessThan( $window['end'], $now->getTimestamp() );
	}

	public function test_fold_series_sums_into_uneven_buckets(): void {
		$start  = 1000;
		$source = [
			0 => [ 'total' => 1, 'errors' => 0 ],
			1 => [ 'total' => 2, 'errors' => 1 ],
			2 => [ 'total' => 3, 'errors' => 0 ],
			4 => [ 'total' => 4, 'errors' => 2 ],
		];

		// Buckets of 1, 3 and 1 source units.
		$folded = DashboardPage::fold_series( $source, $start, 10, [ 1000, 1010, 1040 ] );

		$this->assertSame(
			[
				[ 'total' => 1, 'errors' => 0 ],
				[ 'total' => 5, 'errors' => 1 ],
				[ 'total' => 4, 'errors' => 2 ],
			],
			$folded
		);
		$this->assertSame( [], DashboardPage::fold_series( $source, $start, 10, [] ) );
	}

	public function test_percent_and_level_modifier(): void {
		$this->assertSame( 0.0, DashboardPage::percent( 5, 0 ) );
		$this->assertSame( 25.0, DashboardPage::percent( 1, 4 ) );
		$this->assertSame( 'errors', DashboardPage::get_level_modifier( 400 ) );
		$this->assertSame( 'errors', DashboardPage::get_level_modifier( 600 ) );
		$this->assertSame( 'warnings', DashboardPage::get_level_modifier( 300 ) );
		$this->assertSame( 'entries', DashboardPage::get_level_modifier( 250 ) );
	}

	public function test_get_stats_service_defaults_to_database_and_is_filterable(): void {
		$page = new DashboardPage();
		$this->assertInstanceOf( WordPressDatabaseLogStats::class, $page->get_stats_service() );

		add_filter( 'wpgraphql_logging_dashboard_stats_service', '__return_null' );
		$this->assertNull( $page->get_stats_service() );
		$this->assertNull( $page->get_dashboard_data( '7d' ) );
	}

	public function test_get_dashboard_data_returns_null_for_unknown_range(): void {
		$this->assertNull( ( new DashboardPage() )->get_dashboard_data( 'nope' ) );
	}

	public function test_get_dashboard_data_aggregates_and_caches(): void {
		$now = time();
		$this->insert_row( $now - 60 );
		$this->insert_row( $now - 120, 400, 'ERROR', 'GetPage' );
		$this->insert_row( $now - ( 40 * DAY_IN_SECONDS ) ); // Outside every range.

		$page = new DashboardPage();
		$data = $page->get_dashboard_data( '7d' );

		$this->assertIsArray( $data );
		$this->assertSame( [ 'total' => 2, 'errors' => 1, 'operations' => 2 ], $data['summary'] );
		$this->assertCount( 7, $data['series'] );
		$this->assertCount( 7, $data['bucket_starts'] );
		$this->assertSame( 2, array_sum( array_column( $data['series'], 'total' ) ) );
		$this->assertNotFalse( get_transient( DashboardPage::CACHE_KEY_PREFIX . '7d' ) );

		// A new row is not visible until the cache expires.
		$this->insert_row( $now - 30 );
		$this->assertSame( 2, $page->get_dashboard_data( '7d' )['summary']['total'] );

		delete_transient( DashboardPage::CACHE_KEY_PREFIX . '7d' );
		$this->assertSame( 3, $page->get_dashboard_data( '7d' )['summary']['total'] );
	}

	public function test_cache_can_be_disabled(): void {
		add_filter( 'wpgraphql_logging_dashboard_cache_ttl', '__return_zero' );
		( new DashboardPage() )->get_dashboard_data( '24h' );
		$this->assertFalse( get_transient( DashboardPage::CACHE_KEY_PREFIX . '24h' ) );
	}

	public function test_custom_stats_service_is_used(): void {
		$stub = new class() implements LogStatsInterface {
			public function get_summary( DateTimeInterface $start, DateTimeInterface $end ): array {
				return [ 'total' => 42, 'errors' => 0, 'operations' => 1 ];
			}

			public function get_time_series( DateTimeInterface $start, int $bucket_seconds, int $bucket_count ): array {
				return array_fill( 0, $bucket_count, [ 'total' => 0, 'errors' => 0 ] );
			}

			public function get_level_breakdown( DateTimeInterface $start, DateTimeInterface $end ): array {
				return [];
			}

			public function get_top_operations( DateTimeInterface $start, DateTimeInterface $end, int $limit = 10 ): array {
				return [];
			}
		};
		add_filter( 'wpgraphql_logging_dashboard_stats_service', static fn () => $stub );

		$data = ( new DashboardPage() )->get_dashboard_data( '30d' );
		$this->assertSame( 42, $data['summary']['total'] );
		$this->assertCount( 30, $data['series'] );
	}

	public function test_label_series_formats_hourly_and_daily_labels(): void {
		update_option( 'date_format', 'Y-m-d' );
		update_option( 'time_format', 'H:i' );
		$page  = new DashboardPage();
		$start = gmmktime( 0, 0, 0, 1, 2, 2026 );

		$hourly = $page->label_series( [ [ 'total' => 3, 'errors' => 1 ] ], [ $start ], HOUR_IN_SECONDS );
		$this->assertSame( '00:00', $hourly[0]['label'] );
		$this->assertSame( '2026-01-02 00:00', $hourly[0]['full_label'] );
		$this->assertSame( 3, $hourly[0]['total'] );

		// Missing counts are treated as zero.
		$daily = $page->label_series( [], [ $start ], DAY_IN_SECONDS );
		$this->assertSame( 'Jan 2', $daily[0]['label'] );
		$this->assertSame( '2026-01-02', $daily[0]['full_label'] );
		$this->assertSame( 0, $daily[0]['total'] );
	}

	public function test_render_page_outputs_dashboard_with_escaped_operation_names(): void {
		$this->set_as_admin();
		$this->insert_row( time() - 60, 200, 'INFO', '<img src=x onerror=alert(1)>' );

		ob_start();
		( new DashboardPage() )->render_page();
		$html = (string) ob_get_clean();

		$this->assertStringContainsString( 'WPGraphQL Logging Dashboard', $html );
		$this->assertStringContainsString( 'role="img"', $html );
		$this->assertStringContainsString( 'Show data table', $html );
		$this->assertStringContainsString( 'aria-current="page"', $html );
		$this->assertStringNotContainsString( '<img src=x', $html );
	}

	public function test_render_page_shows_notice_when_dashboard_unavailable(): void {
		$this->set_as_admin();
		add_filter( 'wpgraphql_logging_dashboard_stats_service', '__return_null' );

		ob_start();
		( new DashboardPage() )->render_page();
		$html = (string) ob_get_clean();

		$this->assertStringContainsString( 'notice-warning', $html );
		$this->assertStringNotContainsString( 'role="img"', $html );
	}
}
