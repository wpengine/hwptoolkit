<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Scheduler;

use WPGraphQL\Logging\Admin\Settings\Fields\Tab\Data_Management_Tab;
use WPGraphQL\Logging\Logger\Database\LogsRepository;

/**
 * Data Deletion Scheduler class.
 *
 * Handles scheduled deletion of old log data based on retention settings.
 *
 * @package WPGraphQL\Logging\Scheduler
 *
 * @since 0.0.1
 */
class DataDeletionScheduler {
	/**
	 * The cron hook name for the deletion task.
	 *
	 * @var string
	 */
	public const DELETION_HOOK = 'wpgraphql_logging_deletion_cleanup';

	/**
	 * Data retention and deletion configuration.
	 *
	 * @var array<string, string|int|bool|array<string>>
	 */
	protected array $config;

	/**
	 * The single instance of the class.
	 *
	 * @var \WPGraphQL\Logging\Logger\Scheduler\DataDeletionScheduler|null
	 */
	protected static ?DataDeletionScheduler $instance = null;

	/**
	 * Private constructor to prevent direct instantiation.
	 */
	protected function __construct(readonly LogsRepository $repository) {
		$full_config  = get_option( WPGRAPHQL_LOGGING_SETTINGS_KEY, [] );
		$this->config = $full_config['data_management'] ?? [];
	}

	/**
	 * Initialize the scheduler.
	 */
	public static function init(): self {

		if ( null === self::$instance ) {
			self::$instance = new self( new LogsRepository() );
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Schedule the daily deletion task if it's not already scheduled.
	 */
	public function schedule_deletion(): void {
		if ( false === wp_next_scheduled( self::DELETION_HOOK ) ) {
			wp_schedule_event( time(), 'daily', self::DELETION_HOOK );
		}
	}

	/**
	 * Perform the actual deletion of old log data.
	 *
	 * This method is called by the WordPress cron system.
	 */
	public function perform_deletion(): void {

		if ( false === (bool) $this->config[ Data_Management_Tab::DATA_DELETION_ENABLED ] ) {
			return;
		}

		$retention_days = $this->config[ Data_Management_Tab::DATA_RETENTION_DAYS ];
		if ( ! is_numeric( $retention_days ) ) {
			return;
		}
		$retention_days = (int) $retention_days;
		if ( $retention_days < 1 ) {
			return;
		}

		try {
			self::delete_old_logs( $retention_days );
		} catch ( \Throwable $e ) {
			do_action('wpgraphql_logging_cleanup_error', [
				'error_message'  => $e->getMessage(),
				'retention_days' => $retention_days,
				'timestamp'      => current_time( 'mysql' ),
			]);
		}
	}

	/**
	 * Clear the scheduled cleanup task.
	 *
	 * This is typically called on plugin deactivation.
	 */
	public static function clear_scheduled_deletion(): void {
		$timestamp = wp_next_scheduled( self::DELETION_HOOK );
		if ( false !== $timestamp ) {
			wp_unschedule_event( $timestamp, self::DELETION_HOOK );
		}
	}

	/**
	 * Initialize the scheduler by registering hooks.
	 */
	protected function setup(): void {
		add_action( 'init', [ $this, 'schedule_deletion' ], 10, 0 );
		add_action( self::DELETION_HOOK, [ $this, 'perform_deletion' ], 10, 0 );

		// Clear scheduled event on deactivation.
		register_deactivation_hook( __FILE__, [ $this, 'clear_scheduled_deletion' ] );
	}

	/**
	 * Delete log entries older than the specified number of days.
	 *
	 * @param int $retention_days Number of days to retain logs.
	 */
	protected function delete_old_logs(int $retention_days): void {
		$date_time = new \DateTime();
		$date_time->modify( "-{$retention_days} days" );
		$this->repository->delete_log_older_than( $date_time );
	}
}
