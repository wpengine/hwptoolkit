<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Parameter;

use WP_Post;

/**
 * Class Callable_Preview_Parameter.
 */
class Preview_Parameter {
	/**
	 * The name of the parameter.
	 *
	 * @var string $name
	 */
	protected string $name;

	/**
	 * The description of the parameter.
	 *
	 * @var string $description
	 */
	protected string $description;

	/**
	 * The callback function to get the parameter value string.
	 *
	 * @var callable(\WP_Post $post):string $callback
	 */
	private $callback;

	/**
	 * Class constructor.
	 *
	 * @param string   $name The name of the parameter.
	 * @param callable $callback The callback function to get the parameter value.
	 * @param string   $description The description of the parameter.
	 */
	public function __construct( string $name, callable $callback, string $description = '' ) {
		$this->name        = $name;
		$this->description = $description;
		$this->callback    = $callback;
	}

	/**
	 * Get the name of the parameter.
	 *
	 * @inheritDoc
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Get the description of the parameter.
	 *
	 * @inheritDoc
	 */
	public function get_description(): string {
		return $this->description;
	}

	/**
	 * Get the value of the parameter for a given post.
	 * No need to URL-encode here.
	 *
	 * @param \WP_Post $post The post object.
	 */
	public function get_value( WP_Post $post ): string {
		return call_user_func( $this->callback, $post );
	}
}
