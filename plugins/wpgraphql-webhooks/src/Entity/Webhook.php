<?php

namespace WPGraphQL\Webhooks\Entity;

/**
 * Class Webhook
 *
 * Represents a Webhook entity (Data Transfer Object).
 *
 * @package WPGraphQL\Webhooks
 */
class Webhook {
	/** @var int Webhook post ID. */
	public int $id;

	/** @var string Webhook name (post title). */
	public string $name;

	/** @var string Event the webhook listens to. */
	public string $event;

	/** @var string Destination URL for the webhook. */
	public string $url;

	/** @var string HTTP method used for the webhook request. */
	public string $method;

	/** @var array HTTP headers to be sent with the webhook request. */
	public array $headers;

	/**
	 * Webhook constructor.
	 *
	 * @param int    $id      Webhook post ID.
	 * @param string $name    Webhook name.
	 * @param string $event   Event the webhook listens to.
	 * @param string $url     Destination URL for the webhook.
	 * @param string $method  HTTP method used for the webhook request. Defaults to 'POST'.
	 * @param array  $headers HTTP headers to be sent with the request.
	 */
	public function __construct( int $id, string $name, string $event, string $url, string $method = 'POST', array $headers = [] ) {
		$this->id = $id;
		$this->name = $name;
		$this->event = $event;
		$this->url = $url;
		$this->method = $method;
		$this->headers = $headers;
	}
}