<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Events;

/**
 * List of available events that users can subscribe to with the EventManager.
 */
final class Events {
	/**
	 * WPGraphQL action: do_graphql_request.
	 *
	 * Before the request is processed.
	 *
	 * @var string
	 */
	public const PRE_REQUEST = 'do_graphql_request';

	/**
	 * WPGraphQL action: graphql_before_execute.
	 *
	 * @var string
	 */
	public const BEFORE_GRAPHQL_EXECUTION = 'graphql_before_execute';

	/**
	 * WPGraphQL action: graphql_return_response
	 *
	 * Before the response is returned to the client.
	 *
	 * @var string
	 */
	public const BEFORE_RESPONSE_RETURNED = 'graphql_return_response';

	/**
	 * WPGraphQL filter: graphql_request_data.
	 *
	 * Allows the request data to be filtered. Ideal for capturing the
	 * full payload before processing.
	 *
	 * @var string
	 */
	public const REQUEST_DATA = 'graphql_request_data';

	/**
	 * WPGraphQL filter: graphql_response_headers_to_send.
	 *
	 * Filters the headers to send in the GraphQL response.
	 *
	 * @var string
	 */
	public const RESPONSE_HEADERS_TO_SEND = 'graphql_response_headers_to_send';

	/**
	 * WPGraphQL filter: graphql_request_results.
	 *
	 * Filters the final results of the GraphQL execution.
	 *
	 * @var string
	 */
	public const REQUEST_RESULTS = 'graphql_request_results';

	/**
	 * WPGraphQL filter: graphql_debug_enabled.
	 *
	 * Determines if GraphQL Debug is enabled. Useful for toggling logging.
	 *
	 * @var string
	 */
	public const DEBUG_ENABLED = 'graphql_debug_enabled';

	/**
	 * WPGraphQL filter: graphql_app_context_config.
	 *
	 * Filters the config for the AppContext. Useful for storing temporary
	 * data for the duration of a request.
	 *
	 * @var string
	 */
	public const APP_CONTEXT_CONFIG = 'graphql_app_context_config';
}
