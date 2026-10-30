<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin;

use DateTimeImmutable;
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
	 * Computes the window and bucket boundaries for a range, aligned to the site time zone.
	 *
	 * Hourly ranges start at the top of a local hour. Daily ranges use local calendar days,
	 * so a day can be 23 or 25 hours long when daylight saving time changes. The last bucket
	 * always contains "now".
	 *
	 * @param array{label: string, bucket_seconds: int, bucket_count: int} $range The range definition.
	 * @param \DateTimeImmutable|null                                      $now   The current time (for testing).
	 *
	 * @return array{start: int, end: int, bucket_starts: array<int, int>} Unix timestamps.
	 */
	public function get_window( array $range, ?DateTimeImmutable $now = null ): array {
		$now            = ( $now ?? new DateTimeImmutable( 'now' ) )->setTimezone( wp_timezone() );
		$bucket_seconds = max( 1, $range['bucket_seconds'] );
		$bucket_count   = max( 1, $range['bucket_count'] );
		$bucket_starts  = [];

		if ( DAY_IN_SECONDS === $bucket_seconds ) {
			$day = $now->setTime( 0, 0 )->modify( sprintf( '-%d days', $bucket_count - 1 ) );
			for ( $i = 0; $i < $bucket_count; $i++ ) {
				$bucket_starts[] = $day->getTimestamp();
				$day             = $day->modify( '+1 day' );
			}

			return [
				'start'         => $bucket_starts[0],
				'end'           => $day->getTimestamp(),
				'bucket_starts' => $bucket_starts,
			];
		}

		$aligned = HOUR_IN_SECONDS === $bucket_seconds
			? $now->setTime( (int) $now->format( 'G' ), 0 )->getTimestamp()
			: $now->getTimestamp() - ( $now->getTimestamp() % $bucket_seconds );
		$start   = $aligned - ( $bucket_seconds * ( $bucket_count - 1 ) );

		for ( $i = 0; $i < $bucket_count; $i++ ) {
			$bucket_starts[] = $start + ( $i * $bucket_seconds );
		}

		return [
			'start'         => $start,
			'end'           => $start + ( $bucket_seconds * $bucket_count ),
			'bucket_starts' => $bucket_starts,
		];
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
	 * Gets the aggregated dashboard data for a range, using a short-lived transient cache.
	 *
	 * Only raw numbers and timestamps are cached; labels are formatted per request so each
	 * user sees dates in their own locale.
	 *
	 * @param string $range The range slug.
	 *
	 * @return array{range: string, start: int, end: int, bucket_starts: array<int, int>, summary: array{total: int, errors: int, operations: int}, series: array<int, array{total: int, errors: int}>, levels: array<int, array{level: int, level_name: string, total: int}>, operations: array<int, array{operation: string, total: int, errors: int}>}|null
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
			/** @var array{range: string, start: int, end: int, bucket_starts: array<int, int>, summary: array{total: int, errors: int, operations: int}, series: array<int, array{total: int, errors: int}>, levels: array<int, array{level: int, level_name: string, total: int}>, operations: array<int, array{operation: string, total: int, errors: int}>} $cached */
			return $cached;
		}

		$window = $this->get_window( $ranges[ $range ] );
		$start  = new DateTimeImmutable( '@' . $window['start'] );
		$end    = new DateTimeImmutable( '@' . $window['end'] );

		$data = [
			'range'         => $range,
			'start'         => $window['start'],
			'end'           => $window['end'],
			'bucket_starts' => $window['bucket_starts'],
			'summary'       => $stats->get_summary( $start, $end ),
			'series'        => $this->get_series( $stats, $window, $ranges[ $range ]['bucket_seconds'] ),
			'levels'        => $stats->get_level_breakdown( $start, $end ),
			'operations'    => $stats->get_top_operations( $start, $end, self::TOP_OPERATIONS_LIMIT ),
		];

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
		$data          = $this->get_dashboard_data( $current_range );
		$series        = null === $data ? [] : $this->label_series( $data['series'], $data['bucket_starts'], $ranges[ $current_range ]['bucket_seconds'] ); // @phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
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
	 * Adds display labels to the bucket counts.
	 *
	 * @param array<int, array{total: int, errors: int}> $series         Bucket counts.
	 * @param array<int, int>                            $bucket_starts  Bucket start timestamps.
	 * @param int                                        $bucket_seconds The nominal bucket size.
	 *
	 * @return array<int, array{label: string, full_label: string, total: int, errors: int}>
	 */
	public function label_series( array $series, array $bucket_starts, int $bucket_seconds ): array {
		$daily       = $bucket_seconds >= DAY_IN_SECONDS;
		$date_format = (string) get_option( 'date_format', 'F j, Y' );
		$time_format = (string) get_option( 'time_format', 'g:i a' );
		/* translators: Short date format for chart axis labels, see https://www.php.net/manual/datetime.format.php */
		$short_date = __( 'M j', 'wpgraphql-logging' );
		$labelled   = [];

		foreach ( array_values( $bucket_starts ) as $index => $timestamp ) {
			$counts     = $series[ $index ] ?? [
				'total'  => 0,
				'errors' => 0,
			];
			$labelled[] = [
				'label'      => (string) wp_date( $daily ? $short_date : $time_format, $timestamp ),
				'full_label' => (string) wp_date( $daily ? $date_format : $date_format . ' ' . $time_format, $timestamp ),
				'total'      => $counts['total'],
				'errors'     => $counts['errors'],
			];
		}

		return $labelled;
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
	 * Shortens a label for display, keeping multibyte characters intact.
	 *
	 * @param string $text       The text to shorten.
	 * @param int    $max_length Maximum number of characters before an ellipsis is added.
	 */
	public static function truncate( string $text, int $max_length = 80 ): string {
		if ( mb_strlen( $text ) <= $max_length ) {
			return $text;
		}

		return mb_substr( $text, 0, $max_length ) . "\u{2026}";
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
	 * Sums fixed-size source buckets into (possibly uneven) target buckets.
	 *
	 * @param array<int, array{total: int, errors: int}> $source         Source counts keyed by index.
	 * @param int                                        $source_start   Start of source bucket 0 (Unix timestamp).
	 * @param int                                        $source_seconds Size of each source bucket in seconds.
	 * @param array<int, int>                            $bucket_starts  Ascending start timestamps of the target buckets.
	 *
	 * @return array<int, array{total: int, errors: int}> Target counts, one per bucket start.
	 */
	public static function fold_series( array $source, int $source_start, int $source_seconds, array $bucket_starts ): array {
		$bucket_starts = array_values( $bucket_starts );
		$count         = count( $bucket_starts );
		if ( 0 === $count ) {
			return [];
		}

		$totals = array_fill( 0, $count, 0 );
		$errors = array_fill( 0, $count, 0 );

		ksort( $source );
		$bucket = 0;
		foreach ( $source as $index => $counts ) {
			$timestamp = $source_start + ( $index * $source_seconds );
			while ( $bucket + 1 < $count && $timestamp >= $bucket_starts[ $bucket + 1 ] ) {
				++$bucket;
			}
			$totals[ $bucket ] += $counts['total'];
			$errors[ $bucket ] += $counts['errors'];
		}

		return array_map(
			static fn ( int $total, int $error_count ): array => [
				'total'  => $total,
				'errors' => $error_count,
			],
			$totals,
			$errors
		);
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
	 * Gets the counts for each bucket in a window.
	 *
	 * Daily buckets follow local calendar days, which are not always 24 hours long, so they
	 * are built from hourly counts rather than fixed 24-hour SQL buckets.
	 *
	 * @param \WPGraphQL\Logging\Logger\Api\LogStatsInterface             $stats          The stats service.
	 * @param array{start: int, end: int, bucket_starts: array<int, int>} $window         The window.
	 * @param int                                                         $bucket_seconds The nominal bucket size.
	 *
	 * @return array<int, array{total: int, errors: int}>
	 */
	protected function get_series( LogStatsInterface $stats, array $window, int $bucket_seconds ): array {
		$start = new DateTimeImmutable( '@' . $window['start'] );

		if ( DAY_IN_SECONDS !== $bucket_seconds ) {
			return array_values( $stats->get_time_series( $start, $bucket_seconds, count( $window['bucket_starts'] ) ) );
		}

		$hours  = (int) ceil( ( $window['end'] - $window['start'] ) / HOUR_IN_SECONDS );
		$hourly = $stats->get_time_series( $start, HOUR_IN_SECONDS, max( 1, $hours ) );

		return self::fold_series( $hourly, $window['start'], HOUR_IN_SECONDS, $window['bucket_starts'] );
	}
}
