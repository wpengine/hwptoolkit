<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Events;

use WPGraphQL\Logging\Admin\Settings\Fields\Tab\Basic_Configuration_Tab;
use WPGraphQL\Logging\Logger\LoggerService;

/**
 * WPGraphQL Query Event Lifecycle Orchestrator.
 *
 * This class acts as a facade, orchestrating the logging of GraphQL query
 * events by delegating responsibilities to specialized logger classes.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class QueryEventLifecycle {
    /**
     * The single instance of the class.
     *
     * @var \WPGraphQL\Logging\Events\QueryEventLifecycle|null
     */
    private static ?QueryEventLifecycle $instance = null;

    /**
     * The logger service instance.
     *
     * @var \WPGraphQL\Logging\Logger\LoggerService
     */
    protected LoggerService $logger;

    /**
     * The basic configuration settings.
     *
     * @var array<string, string|int|bool|array<string>>
     */
    protected array $config;

    /**
     * The logger for handling WordPress action hooks.
     *
     * @var \WPGraphQL\Logging\Events\QueryActionLogger
     */
    protected QueryActionLogger $action_logger;

    /**
     * The logger for handling WordPress filter hooks.
     *
     * @var \WPGraphQL\Logging\Events\QueryFilterLogger
     */
    protected QueryFilterLogger $filter_logger;

    /**
     * QueryEventLifecycle constructor.
     *
     * @param \WPGraphQL\Logging\Logger\LoggerService $logger The logger instance.
     */
    protected function __construct( LoggerService $logger ) {
        $this->logger = $logger;
        $full_config  = get_option( WPGRAPHQL_LOGGING_SETTINGS_KEY, [] );
        $this->config = $full_config['basic_configuration'] ?? [];

        // Initialize the specialized logger components.
        $this->action_logger = new QueryActionLogger( $this->logger, $this->config );
        $this->filter_logger = new QueryFilterLogger( $this->logger, $this->config );
    }

    /**
     * Get or create the single instance of the class.
     *
     * @return QueryEventLifecycle
     */
    public static function init(): QueryEventLifecycle {
        if ( null === self::$instance ) {
            $logger         = LoggerService::get_instance();
            self::$instance = new self( $logger );
            self::$instance->setup();
        }

        return self::$instance;
    }

    /**
     * Register actions and filters to log the query event lifecycle.
     *
     * @psalm-suppress HookNotFound
     */
    protected function setup(): void {
		// Map of action events to their corresponding logger methods and accepted args.
		$action_events = [
			Events::PRE_REQUEST              => [ 'method' => 'log_pre_request', 'accepted_args' => 3 ],
			Events::BEFORE_GRAPHQL_EXECUTION => [ 'method' => 'log_graphql_before_execute', 'accepted_args' => 1 ],
			Events::BEFORE_RESPONSE_RETURNED => [ 'method' => 'log_before_response_returned', 'accepted_args' => 8 ],
		];

		// Map of filter events to their corresponding logger methods and accepted args.
		$filter_events = [
			Events::REQUEST_DATA             => [ 'method' => 'log_graphql_request_data', 'accepted_args' => 1 ],
			Events::REQUEST_RESULTS          => [ 'method' => 'log_graphql_request_results', 'accepted_args' => 7 ],
			Events::RESPONSE_HEADERS_TO_SEND => [ 'method' => 'add_logging_headers', 'accepted_args' => 1 ],
		];

		// Add action hooks.
		foreach ( $action_events as $event_name => $data ) {
			add_action( $event_name, [ $this->action_logger, $data['method'] ], 10, $data['accepted_args'] );
		}

		// Add filter hooks.
		foreach ( $filter_events as $event_name => $data ) {
			add_filter( $event_name, [ $this->filter_logger, $data['method'] ], 10, $data['accepted_args'] );
		}
	}
}