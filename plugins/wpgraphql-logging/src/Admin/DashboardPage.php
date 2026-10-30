<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin;

use DateTimeImmutable;
use DateTimeZone;
use WPGraphQL\Logging\Admin\Dashboard\TimeSeriesChart;
use WPGraphQL\Logging\Logger\Api\LogStatsInterface;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseLogService;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseLogStats;
use WPGraphQL\Logging\Logger\Store\LogStoreService;

/**
 * The dashboard page for WPGraphQL Logging.
 *
 * Shows aggregate charts for a selectable time range: log entries over time,
 * a breakdown by log level and the operations with the most entries.
 *
 * @package WPGraphQL\Logging
 *
 * @since 1.1.0
 */
class DashboardPage {
	/**
	 * The admin page slug.
	 *
	 * @var string
	 */
	public const ADMIN_PAGE_SLUG = 'wpgraphql-logging-dashboard';

	/**
	 * The default time range.
	 *
	 * @var string
	 */
	public const DEFAULT_RANGE = '7d';

	/**
	 * Transient key prefix for cached dashboard data.
	 *
	 * @var string
	 */
	public const CACHE_KEY_PREFIX = 'wpgraphql_logging_dashboard_';

	/**
	 * Default cache lifetime for dashboard data in seconds.
	 *
	 * @var int
	 */
	public const CACHE_TTL = 60;

	/**
	 * The lowest Monolog level shown as a warning (Monolog\Level::Warning).
	 *
	 * @var int
	 */
	public const WARNING_LEVEL = 300;

	/**
	 * Number of operations shown in the top operations table.
	 *
	 * @var int
	 */
	public const TOP_OPERATIONS_LIMIT = 10;

	/**
	 * The hook suffix for the admin page.
	 *
	 * @var string
	 */
	protected string $page_hook = '';

	/**
	 * The instance of the dashboard page.
	 *
	 * @var self|null
	 */
	protected static ?self $instance = null;

	/**
	 * Initializes the dashboard page.
	 */
	public static function init(): ?self {
		if ( ! current_user_can( 'manage_options' ) ) {
			return null;
		}

		if ( ! isset( self::$instance ) || ! ( is_a( self::$instance, self::class ) ) ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		/**
		 * Fires after the dashboard page is initialized.
		 *
		 * @param \WPGraphQL\Logging\Admin\DashboardPage $instance The dashboard page instance.
		 */
		do_action( 'wpgraphql_logging_dashboard_init', self::$instance );

		return self::$instance;
	}

	/**
	 * Sets up the dashboard page hooks.
	 */
	public function setup(): void {
		// Priority 10 runs after the logs menu is registered (same priority, registered earlier) and before Settings (11).
		add_action( 'admin_menu', [ $this, 'register_page' ], 10, 0 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_styles' ], 10, 1 );
	}

	/**
	 * Registers the dashboard submenu page.
	 */
	public function register_page(): void {
		$page_hook = add_submenu_page(
			ViewLogsPage::ADMIN_PAGE_SLUG,
			esc_html__( 'WPGraphQL Logging Dashboard', 'wpgraphql-logging' ),
			esc_html__( 'Dashboard', 'wpgraphql-logging' ),
			'manage_options',
			self::ADMIN_PAGE_SLUG,
			[ $this, 'render_page' ]
		);

		$this->page_hook = is_string( $page_hook ) ? $page_hook : '';
	}

	/**
	 * Enqueues the dashboard stylesheet on the dashboard page only.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_admin_styles( string $hook_suffix ): void {
		if ( '' === $this->page_hook || $hook_suffix !== $this->page_hook ) {
			return;
		}

		$relative_path = 'assets/css/dashboard/wp-graphql-logging-dashboard.css';
		if ( file_exists( trailingslashit( WPGRAPHQL_LOGGING_PLUGIN_DIR ) . $relative_path ) ) {
			wp_enqueue_style(
				'wpgraphql-logging-dashboard',
				trailingslashit( WPGRAPHQL_LOGGING_PLUGIN_URL ) . $relative_path,
				[],
				WPGRAPHQL_LOGGING_VERSION
			);
		}

		/**
		 * Fires after the dashboard styles are enqueued.
		 *
		 * @param string $hook_suffix The current admin page hook suffix.
		 */
		do_action( 'wpgraphql_logging_dashboard_admin_enqueue_scripts', $hook_suffix );
	}

	/**
	 * Gets the available time ranges.
	 *
	 * @return array<string, array{label: string, bucket_seconds: int, bucket_count: int}> Ranges keyed by slug.
	 */
	public function get_ranges(): array {
		$ranges = [
			'24h' => [
				'label'          => __( 'Last 24 hours', 'wpgraphql-logging' ),
				'bucket_seconds' => HOUR_IN_SECONDS,
				'bucket_count'   => 24,
			],
			'7d'  => [
				'label'          => __( 'Last 7 days', 'wpgraphql-logging' ),
				'bucket_seconds' => DAY_IN_SECONDS,
				'bucket_count'   => 7,
			],
			'30d' => [
				'label'          => __( 'Last 30 days', 'wpgraphql-logging' ),
				'bucket_seconds' => DAY_IN_SECONDS,
				'bucket_count'   => 30,
			],
		];

		/**
		 * Filters the dashboard time ranges.
		 *
		 * @param array<string, array{label: string, bucket_seconds: int, bucket_count: int}> $ranges Ranges keyed by slug.
		 */
		$filtered = apply_filters( 'wpgraphql_logging_dashboard_ranges', $ranges );
		if ( ! is_array( $filtered ) ) {
			return $ranges;
		}

		// Drop malformed ranges added by filters.
		$valid = [];
		foreach ( $filtered as $slug => $range ) {
			if ( ! is_string( $slug ) || ! $this->is_valid_range( $slug, $range ) ) {
				continue;
			}
			/** @var array{label: string, bucket_seconds: int, bucket_count: int} $range */
			$valid[ $slug ] = [
				'label'          => $range['label'],
				'bucket_seconds' => $range['bucket_seconds'],
				'bucket_count'   => $range['bucket_count'],
			];
		}

		return empty( $valid ) ? $ranges : $valid;
	}

	/**
	 * Gets the selected time range slug from the request, falling back to the default.
	 *
	 * This is a read-only view, so no nonce is required; the value is validated against an allow list.
	 */
	public function get_current_range(): string {
		$ranges  = $this->get_ranges();
		$default = array_key_exists( self::DEFAULT_RANGE, $ranges ) ? self::DEFAULT_RANGE : (string) array_key_first( $ranges );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['range'] ) || ! is_string( $_GET['range'] ) ) {
			return $default;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$range = sanitize_key( wp_unslash( $_GET['range'] ) );

		return array_key_exists( $range, $ranges ) ? $range : $default;
	}

	/**
	 * Computes the UTC start of the window for a range.
	 *
	 * Buckets are aligned to the site time zone: hourly ranges start at the top of the hour and
	 * daily ranges start at local midnight, so the last bucket always contains "now".
	 *
	 * @param int                     $bucket_seconds Bucket size in seconds.
	 * @param int                     $bucket_count   Number of buckets.
	 * @param \DateTimeImmutable|null $now            The current time (for testing).
	 */
	public function get_window_start( int $bucket_seconds, int $bucket_count, ?DateTimeImmutable $now = null ): DateTimeImmutable {
		$now = ( $now ?? new DateTimeImmutable( 'now' ) )->setTimezone( wp_timezone() );

		if ( DAY_IN_SECONDS === $bucket_seconds ) {
			// Step back in calendar days so the window starts at local midnight even across DST changes.
			$start = $now->setTime( 0, 0 )->modify( sprintf( '-%d days', $bucket_count - 1 ) )->getTimestamp();
			return ( new DateTimeImmutable( '@' . $start ) )->setTimezone( new DateTimeZone( 'UTC' ) );
		}

		$bucket_seconds = max( 1, $bucket_seconds );
		$aligned        = HOUR_IN_SECONDS === $bucket_seconds
			? $now->setTime( (int) $now->format( 'G' ), 0 )->getTimestamp()
			: $now->getTimestamp() - ( $now->getTimestamp() % $bucket_seconds );
		$start          = $aligned - ( $bucket_seconds * ( $bucket_count - 1 ) );

		return ( new DateTimeImmutable( '@' . $start ) )->setTimezone( new DateTimeZone( 'UTC' ) );
	}

	/**
	 * Gets the stats service used to aggregate log data.
	 *
	 * Returns null when the log store has been replaced and no stats service was provided for it.
	 */
	public function get_stats_service(): ?LogStatsInterface {
		$default = LogStoreService::get_log_service() instanceof WordPressDatabaseLogService
			? new WordPressDatabaseLogStats()
			: null;

		/**
		 * Filters the stats service used by the dashboard.
		 *
		 * Return an implementation of LogStatsInterface to support a custom log store.
		 *
		 * @param \WPGraphQL\Logging\Logger\Api\LogStatsInterface|null $default The default stats service.
		 */
		$service = apply_filters( 'wpgraphql_logging_dashboard_stats_service', $default );

		return $service instanceof LogStatsInterface ? $service : null;
	}

	/**
	 * Gets the dashboard data for a range, using a short-lived transient cache.
	 *
	 * @param string $range The range slug.
	 *
	 * @return array{range: string, start: int, end: int, summary: array{total: int, errors: int, operations: int}, series: array<int, array{label: string, full_label: string, total: int, errors: int}>, levels: array<int, array{level: int, level_name: string, total: int}>, operations: array<int, array{operation: string, total: int, errors: int}>}|null
	 */
	public function get_dashboard_data( string $range ): ?array {
		$ranges = $this->get_ranges();
		if ( ! array_key_exists( $range, $ranges ) ) {
			return null;
		}

		$stats = $this->get_stats_service();
		if ( null === $stats ) {
			return null;
		}

		$cache_key = self::CACHE_KEY_PREFIX . $range;
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) && isset( $cached['range'] ) && $range === $cached['range'] ) {
			/** @var array{range: string, start: int, end: int, summary: array{total: int, errors: int, operations: int}, series: array<int, array{label: string, full_label: string, total: int, errors: int}>, levels: array<int, array{level: int, level_name: string, total: int}>, operations: array<int, array{operation: string, total: int, errors: int}>} $cached */
			return $cached;
		}

		$bucket_seconds = $ranges[ $range ]['bucket_seconds'];
		$bucket_count   = $ranges[ $range ]['bucket_count'];
		$start          = $this->get_window_start( $bucket_seconds, $bucket_count );
		$end            = $start->setTimestamp( $start->getTimestamp() + ( $bucket_seconds * $bucket_count ) );

		$data = [
			'range'      => $range,
			'start'      => $start->getTimestamp(),
			'end'        => $end->getTimestamp(),
			'summary'    => $stats->get_summary( $start, $end ),
			'series'     => $this->label_series( $stats->get_time_series( $start, $bucket_seconds, $bucket_count ), $start->getTimestamp(), $bucket_seconds ),
			'levels'     => $stats->get_level_breakdown( $start, $end ),
			'operations' => $stats->get_top_operations( $start, $end, self::TOP_OPERATIONS_LIMIT ),
		];

		/**
		 * Filters the dashboard data before it is cached and rendered.
		 *
		 * @param array  $data  The dashboard data.
		 * @param string $range The range slug.
		 */
		$data = apply_filters( 'wpgraphql_logging_dashboard_data', $data, $range );

		/**
		 * Filters how long dashboard data is cached, in seconds. Return 0 to disable caching.
		 *
		 * @param int    $ttl   Cache lifetime in seconds.
		 * @param string $range The range slug.
		 */
		$ttl = (int) apply_filters( 'wpgraphql_logging_dashboard_cache_ttl', self::CACHE_TTL, $range );
		if ( $ttl > 0 ) {
			set_transient( $cache_key, $data, $ttl );
		}

		return $data;
	}

	/**
	 * Renders the dashboard page.
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpgraphql-logging' ) );
		}

		$ranges        = $this->get_ranges(); // @phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$current_range = $this->get_current_range();
		$data          = $this->get_dashboard_data( $current_range ); // @phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$chart         = new TimeSeriesChart(); // @phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$base_url      = menu_page_url( self::ADMIN_PAGE_SLUG, false ); // @phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		$logs_url      = menu_page_url( ViewLogsPage::ADMIN_PAGE_SLUG, false ); // @phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable

		$template = (string) apply_filters( 'wpgraphql_logging_dashboard_template_path', __DIR__ . '/View/Templates/WPGraphQLLoggerDashboard.php' );
		if ( ! file_exists( $template ) ) {
			return;
		}

		require $template; // @phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
	}

	/**
	 * Returns a part of a whole as a percentage, or 0 when the whole is empty.
	 *
	 * @param int $part  The part.
	 * @param int $whole The whole.
	 */
	public static function percent( int $part, int $whole ): float {
		if ( $whole <= 0 ) {
			return 0.0;
		}

		return ( (float) $part / (float) $whole ) * 100.0;
	}

	/**
	 * Maps a Monolog level to the CSS modifier used for its bar: errors, warnings or entries.
	 *
	 * @param int $level The Monolog level.
	 */
	public static function get_level_modifier( int $level ): string {
		if ( $level >= WordPressDatabaseLogStats::ERROR_LEVEL ) {
			return 'errors';
		}

		return $level >= self::WARNING_LEVEL ? 'warnings' : 'entries';
	}

	/**
	 * Checks that a filtered range has a key-safe slug, a label and positive integer bucket settings.
	 *
	 * @param string $slug  The range slug.
	 * @param mixed  $range The range definition.
	 */
	protected function is_valid_range( string $slug, $range ): bool {
		if ( sanitize_key( $slug ) !== $slug || ! is_array( $range ) ) {
			return false;
		}

		$label          = $range['label'] ?? null;
		$bucket_seconds = $range['bucket_seconds'] ?? null;
		$bucket_count   = $range['bucket_count'] ?? null;

		return is_string( $label ) && is_int( $bucket_seconds ) && is_int( $bucket_count ) && $bucket_seconds > 0 && $bucket_count > 0;
	}

	/**
	 * Adds display labels to time series buckets.
	 *
	 * @param array<int, array{total: int, errors: int}> $series         Counts keyed by bucket index.
	 * @param int                                        $start          Window start as a Unix timestamp.
	 * @param int                                        $bucket_seconds Bucket size in seconds.
	 *
	 * @return array<int, array{label: string, full_label: string, total: int, errors: int}>
	 */
	protected function label_series( array $series, int $start, int $bucket_seconds ): array {
		$daily       = $bucket_seconds >= DAY_IN_SECONDS;
		$date_format = (string) get_option( 'date_format', 'F j, Y' );
		$time_format = (string) get_option( 'time_format', 'g:i a' );
		$labelled    = [];

		ksort( $series );
		foreach ( $series as $index => $counts ) {
			$timestamp = $start + ( $index * $bucket_seconds );
			if ( $daily ) {
				// Label daily buckets by their midpoint so a DST shift cannot push the label onto the previous day.
				$timestamp += intdiv( $bucket_seconds, 2 );
			}
			$labelled[] = [
				'label'      => (string) wp_date( $daily ? 'M j' : $time_format, $timestamp ),
				'full_label' => (string) wp_date( $daily ? $date_format : $date_format . ' ' . $time_format, $timestamp ),
				'total'      => (int) $counts['total'],
				'errors'     => (int) $counts['errors'],
			];
		}

		return $labelled;
	}
}
