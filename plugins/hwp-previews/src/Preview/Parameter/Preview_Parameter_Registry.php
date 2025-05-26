<?php

declare( strict_types=1 );

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

	public static function get_instance(): self {

		$instance = self::$instance;

		if ( $instance instanceof self ) {
			return $instance;
		}

		$instance       = new self();
		self::$instance = self::addInitialParameters( $instance );

		return self::$instance;
	}

	public static function addInitialParameters( self $instance ): self {
		$instance
			->register(
				new Preview_Parameter( 'ID', static fn( WP_Post $post ) => (string) $post->ID, __( 'Post ID.', HWP_PREVIEWS_TEXT_DOMAIN ) )
			)->register(
				new Preview_Parameter( 'author_ID', static fn( WP_Post $post ) => $post->post_author, __( 'ID of post author..', HWP_PREVIEWS_TEXT_DOMAIN ) )
			)->register(
				new Preview_Parameter( 'status', static fn( WP_Post $post ) => $post->post_status, __( 'The post\'s status..', HWP_PREVIEWS_TEXT_DOMAIN ) )
			)->register(
				new Preview_Parameter( 'slug', static fn( WP_Post $post ) => $post->post_name, __( 'The post\'s slug.', HWP_PREVIEWS_TEXT_DOMAIN ) )
			)->register(
				new Preview_Parameter( 'parent_ID', static fn( WP_Post $post ) => (string) $post->post_parent, __( 'ID of a post\'s parent post.', HWP_PREVIEWS_TEXT_DOMAIN ) )
			)->register(
				new Preview_Parameter( 'type', static fn( WP_Post $post ) => $post->post_type, __( 'The post\'s type, like post or page.', HWP_PREVIEWS_TEXT_DOMAIN ) )
			)->register(
				new Preview_Parameter( 'uri', static fn( WP_Post $post ) => (string) get_page_uri( $post ), __( 'The URI path for a page.', HWP_PREVIEWS_TEXT_DOMAIN ) )
			)->register(
				new Preview_Parameter( 'template', static fn( WP_Post $post ) => (string) get_page_template_slug( $post ), __( 'Specific template filename for a given post.', HWP_PREVIEWS_TEXT_DOMAIN ) )
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
