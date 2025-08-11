<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Events;

/**
 * List of available events that users can subscribe to with the EventManager.
 */
final class Events {
	/**
	 * Before the request is processed.
	 *
	 * @var string
	 */
	public const PRE_REQUEST = 'pre_request';

	/**
	 * After the request is processed.
	 *
	 * @var string
	 */
	public const POST_REQUEST = 'post_request';
}
