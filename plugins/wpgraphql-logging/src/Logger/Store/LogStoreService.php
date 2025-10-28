<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger\Store;

use WPGraphQL\Logging\Logger\Api\LogServiceInterface;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseLogService;

/**
 * Class LogStoreService
 *
 * @package WPGraphQL\Logging\Logger\Store
 *
 * @since 0.0.1
 */
class LogStoreService {
	/**
	 * Holds the instance of the log service.
	 *
	 * @var ?\WPGraphQL\Logging\Logger\Api\LogServiceInterface
	 */
	protected static ?LogServiceInterface $instance = null;

	/**
	 * Retrieves the log service instance.
	 *
	 * This method will instantiate the appropriate log service based on saved settings.
	 * Currently, it defaults to WordPressDatabaseLogService, but it is designed to be extensible.
	 *
	 * @return \WPGraphQL\Logging\Logger\Api\LogServiceInterface The log service instance.
	 */
	public static function get_log_service(): LogServiceInterface {

		if ( null !== self::$instance ) {
			return self::$instance;
		}

		// Allow developers to add their own log store service implementation.
		$log_service = apply_filters( 'wpgraphql_logging_log_store_service', null );

		if ( $log_service instanceof LogServiceInterface ) {
			self::$instance = $log_service;
			return self::$instance;
		}

		self::$instance = new WordPressDatabaseLogService();
		return self::$instance;
	}
}
