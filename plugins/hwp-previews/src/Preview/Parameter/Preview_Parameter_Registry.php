<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Parameter;

use HWP\Previews\Preview\Parameter\Contracts\Preview_Parameter_Interface;
use WP_Post;

/**
 * Class Preview_Parameter_Registry.
 *
 * This class is responsible for registering and managing preview parameters.
 */
class Preview_Parameter_Registry {
	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	protected static $instance = null;

	/**
	 * Registered parameters.
	 *
	 * @var array<\HWP\Previews\Preview\Parameter\Contracts\Preview_Parameter_Interface>
	 */
	private array $parameters = [];

	/**
	 * Instance of the registry class.
	 */
	public static function get_instance(): self {

		$instance = self::$instance;

		if ( $instance instanceof self ) {
			return $instance;
		}

		$instance       = new self();
		self::$instance = self::add_initial_parameters( $instance );

		return self::$instance;
	}

	/**
	 * Register initial parameters for the parameter registry.
	 */
	public static function add_initial_parameters( self $instance ): self {
		$instance
			->register(
				new Preview_Parameter( 'ID', static fn( WP_Post $post ) => (string) $post->ID,  'Post ID.' )
			)->register(
				new Preview_Parameter( 'author_ID', static fn( WP_Post $post ) => $post->post_author, 'ID of post author.')
			)->register(
				new Preview_Parameter( 'status', static fn( WP_Post $post ) => $post->post_status, 'The post status.' )
			)->register(
				new Preview_Parameter( 'slug', static fn( WP_Post $post ) => $post->post_name, 'The post slug.' )
			)->register(
				new Preview_Parameter( 'parent_ID', static fn( WP_Post $post ) => (string) $post->post_parent, 'ID of a post parent post.' )
			)->register(
				new Preview_Parameter( 'type', static fn( WP_Post $post ) => $post->post_type, 'The post type, like post or page.' )
			)->register(
				new Preview_Parameter( 'uri', static fn( WP_Post $post ) => (string) get_page_uri( $post ), 'The URI path for a page.' )
			)->register(
				new Preview_Parameter( 'template', static fn( WP_Post $post ) => (string) get_page_template_slug( $post ), 'Specific template filename for a given post.' )
			);

		// Allow users to register/unregister parameters.
		return apply_filters( 'hwp_previews_register_parameters', $instance );
	}

	/**
	 * Register a parameter.
	 *
	 * @param \HWP\Previews\Preview\Parameter\Contracts\Preview_Parameter_Interface $parameter The parameter object.
	 */
	public function register( Preview_Parameter_Interface $parameter ): self {
		$this->parameters[ $parameter->get_name() ] = $parameter;

		return $this;
	}

	/**
	 * Unregister a parameter.
	 *
	 * @param string $name The parameter name.
	 */
	public function unregister( string $name ): self {
		if ( isset( $this->parameters[ $name ] ) ) {
			unset( $this->parameters[ $name ] );
		}

		return $this;
	}

	/**
	 * Get all registered parameters.
	 *
	 * @return array<string, \HWP\Previews\Preview\Parameter\Contracts\Preview_Parameter_Interface>
	 */
	public function get_all(): array {
		return $this->parameters;
	}

	/**
	 * Get all registered parameters as an array of their names and descriptions.
	 *
	 * @return array<string, string>
	 */
	public function get_descriptions(): array {
		$descriptions = [];
		foreach ( $this->parameters as $parameter ) {
			$descriptions[ $parameter->get_name() ] = $parameter->get_description();
		}

		return $descriptions;
	}

	/**
	 * Get a specific parameter by name. Returns null if not found.
	 *
	 * @param string $name The parameter name.
	 */
	public function get( string $name ): ?Preview_Parameter_Interface {
		return $this->parameters[ $name ] ?? null;
	}
}
