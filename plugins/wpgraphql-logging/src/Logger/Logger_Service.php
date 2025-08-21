<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Logger;

use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\ProcessorInterface;
use Monolog\Processor\WebProcessor;
use WPGraphQL\Logging\Logger\Handlers\WordPress_Database_Handler;
use WPGraphQL\Logging\Logger\Processors\WPGraphQL_Query_Processor;

/**
 * Logger_Service class for managing the Monolog logger instance.
 *
 * This class provides a singleton instance of the Monolog logger, allowing for easy logging throughout the application.
 * It supports custom handlers and processors, and provides methods for logging at various levels.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class Logger_Service {
	/**
	 * The default channel for the logger.
	 *
	 * This is used to group log messages by a specific context or component.
	 *
	 * @var string
	 */
	public const DEFAULT_CHANNEL = 'wpgraphql_logging';

	/**
	 * The Monolog logger instance.
	 *
	 * @var \Monolog\Logger
	 */
	protected readonly Logger $monolog;

	/**
	 * The instance of the logger based off the channel name.
	 *
	 * @var array<\WPGraphQL\Logging\Logger\Logger_Service>
	 */
	protected static array $instances = [];

	/**
	 * Constructor for the Logger_Service and initializes the Monolog logger with the provided channel, handlers, processors, and default context.
	 *
	 * @param string                                       $channel The channel for the logger.
	 * @param array<\Monolog\Handler\HandlerInterface>     $handlers The handlers to use for the logger.
	 * @param array<\Monolog\Processor\ProcessorInterface> $processors The processors to use for the logger.
	 * @param array<string, mixed>                         $default_context The default context for the logger.
	 */
	protected function __construct(readonly string $channel, readonly array $handlers, readonly array $processors, readonly array $default_context ) {

		$this->monolog = new Logger( $this->channel );

		foreach ( $this->handlers as $handler ) {
			if ( $handler instanceof HandlerInterface ) {
				$this->monolog->pushHandler( $handler );
			}
		}

		foreach ( $this->processors as $processor ) {
			if ( $processor instanceof ProcessorInterface ) {
				$this->monolog->pushProcessor( $processor );
			}
		}
	}

	/**
	 * Get the instance of the Logger_Service.
	 *
	 * This method implements the singleton pattern to ensure only one instance of the logger is created.
	 * It allows for custom handlers, processors, and default context to be specified.
	 *
	 * @param string                                        $channel The channel for the logger.
	 * @param ?array<\Monolog\Handler\HandlerInterface>     $handlers The handlers to use for the logger.
	 * @param ?array<\Monolog\Processor\ProcessorInterface> $processors The processors to use for the logger.
	 * @param ?array<string, mixed>                         $default_context The default context for the logger.
	 */
	public static function get_instance(
		string $channel = self::DEFAULT_CHANNEL,
		?array $handlers = null,
		?array $processors = null,
		?array $default_context = null
	): Logger_Service {
		if ( isset( self::$instances[ $channel ] ) ) {
			return self::$instances[ $channel ];
		}

		$processors      = $processors ?? self::get_default_processors();
		$handlers        = $handlers ?? self::get_default_handlers();
		$default_context = $default_context ?? self::get_default_context();

		self::$instances[ $channel ] = new self( $channel, $handlers, $processors, $default_context );
		return self::$instances[ $channel ];
	}

	/**
	 * Logs a emergency level message.
	 *
	 * @param string               $message The message to log.
	 * @param array<string, mixed> $context Additional context for the log message.
	 *
	 * @link https://seldaek.github.io/monolog/doc/01-usage.html#log-levels
	 */
	public function emergency( string $message, array $context = [] ): void {
		$this->monolog->emergency( $message, array_merge( $this->default_context, $context ) );
	}

	/**
	 * Logs a alert level message.
	 *
	 * @param string               $message The message to log.
	 * @param array<string, mixed> $context Additional context for the log message.
	 *
	 * @link https://seldaek.github.io/monolog/doc/01-usage.html#log-levels
	 */
	public function alert( string $message, array $context = [] ): void {
		$this->monolog->alert( $message, array_merge( $this->default_context, $context ) );
	}

	/**
	 * Logs a critical level message.
	 *
	 * @param string               $message The message to log.
	 * @param array<string, mixed> $context Additional context for the log message.
	 *
	 * @link https://seldaek.github.io/monolog/doc/01-usage.html#log-levels
	 */
	public function critical( string $message, array $context = [] ): void {
		$this->monolog->critical( $message, array_merge( $this->default_context, $context ) );
	}

	/**
	 * Logs a error level message.
	 *
	 * @param string               $message The message to log.
	 * @param array<string, mixed> $context Additional context for the log message.
	 *
	 * @link https://seldaek.github.io/monolog/doc/01-usage.html#log-levels
	 */
	public function error( string $message, array $context = [] ): void {
		$this->monolog->error( $message, array_merge( $this->default_context, $context ) );
	}

	/**
	 * Logs a warning level message.
	 *
	 * @param string               $message The message to log.
	 * @param array<string, mixed> $context Additional context for the log message.
	 *
	 * @link https://seldaek.github.io/monolog/doc/01-usage.html#log-levels
	 */
	public function warning( string $message, array $context = [] ): void {
		$this->monolog->warning( $message, array_merge( $this->default_context, $context ) );
	}

	/**
	 * Logs a notice level message.
	 *
	 * @param string               $message The message to log.
	 * @param array<string, mixed> $context Additional context for the log message.
	 *
	 * @link https://seldaek.github.io/monolog/doc/01-usage.html#log-levels
	 */
	public function notice( string $message, array $context = [] ): void {
		$this->monolog->notice( $message, array_merge( $this->default_context, $context ) );
	}

	/**
	 * Logs a info level message.
	 *
	 * @param string               $message The message to log.
	 * @param array<string, mixed> $context Additional context for the log message.
	 *
	 * @link https://seldaek.github.io/monolog/doc/01-usage.html#log-levels
	 */
	public function info( string $message, array $context = [] ): void {
		$this->monolog->info( $message, array_merge( $this->default_context, $context ) );
	}

	/**
	 * Logs a debug level message.
	 *
	 * @param string               $message The message to log.
	 * @param array<string, mixed> $context Additional context for the log message.
	 *
	 * @link https://seldaek.github.io/monolog/doc/01-usage.html#log-levels
	 */
	public function debug( string $message, array $context = [] ): void {
		$this->monolog->debug( $message, array_merge( $this->default_context, $context ) );
	}

	/**
	 * Logs a log level message.
	 *
	 *  @param mixed                $level   The log level (a Monolog, PSR-3 or RFC 5424 level).
	 * @param string               $message The message to log.
	 * @param array<string, mixed> $context Additional context for the log message.
	 *
	 * @link https://seldaek.github.io/monolog/doc/01-usage.html#log-levels
	 */
	public function log( $level, string $message, array $context = [] ): void {
		$this->monolog->log( $level, $message, array_merge( $this->default_context, $context ) );
	}

	/**
	 * Returns an array of default processors.
	 *
	 * @link https://seldaek.github.io/monolog
	 *
	 * @return array<\Monolog\Processor\ProcessorInterface>
	 */
	public static function get_default_processors(): array {
		$default_processors = [
			new MemoryUsageProcessor(), // Logs memory usage data.
			new MemoryPeakUsageProcessor(), // Logs memory peak data.
			new WebProcessor(), // Logs web request data. e.g. IP address, request method, URI, etc.
			new ProcessIdProcessor(), // Logs the process ID.
			new WPGraphQL_Query_Processor(), // Custom processor to capture GraphQL request data.
		];

		// Filter for users to add their own processors.
		return apply_filters( 'wpgraphql_logging_default_processors', $default_processors );
	}

	/**
	 * Returns an array of default processors.
	 *
	 * @link https://seldaek.github.io/monolog
	 *
	 * @return array<\Monolog\Handler\AbstractProcessingHandler>
	 */
	public static function get_default_handlers(): array {
		$default_handlers = [
			new WordPress_Database_Handler(),
		];

		// Filter for users to add their own handlers.
		return apply_filters( 'wpgraphql_logging_default_handlers', $default_handlers );
	}

	/**
	 * Gets the default context for the logger.
	 *
	 * @return array<string, mixed> Default context for the logger.
	 */
	public static function get_default_context(): array {
		$context = [
			'wp_debug_mode'  => defined( 'WP_DEBUG' ) && WP_DEBUG,
			'plugin_version' => defined( 'WPGRAPHQL_LOGGING_VERSION' ) ? WPGRAPHQL_LOGGING_VERSION : 'undefined',
			'wp_version'     => get_bloginfo( 'version' ),
			'site_url'       => home_url(),
		];

		// Filter for users to modify the default context.
		return apply_filters( 'wpgraphql_logging_default_context', $context );
	}
}
