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
	 * After the request is processed.
	 *
	 * @var string
	 */
	public const POST_REQUEST = 'post_request';
}
