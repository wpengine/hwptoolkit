<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Url;

use HWP\Previews\Preview\Parameter\Preview_Parameter;
use HWP\Previews\Preview\Parameter\Preview_Parameter_Registry;
use WP_Post;

class Preview_Url_Resolver_Service {
	/**
	 * Placeholder pattern in curly brackets - https://localhost:3000/preview={example}.
	 *
	 * @var string
	 */
	public const PLACEHOLDER_REGEX = '/\{([A-Za-z0-9_]+)\}/';

	/**
	 * Placeholder fpr not found message.
	 *
	 * @var string
	 */
	public const PLACEHOLDER_NOT_FOUND = 'PARAMETER_NOT_FOUND';

	/**
	 * The registry of preview parameters.
	 *
	 * @var \HWP\Previews\Preview\Parameter\Preview_Parameter_Registry
	 */
	protected Preview_Parameter_Registry $parameter_registry;

	/**
	 * Constructor.
	 *
	 * @param \HWP\Previews\Preview\Parameter\Preview_Parameter_Registry $parameter_registry The registry of preview parameters.
	 */
	public function __construct(Preview_Parameter_Registry $parameter_registry ) {
		$this->parameter_registry = $parameter_registry;
	}

	/**
	 * Resolves the parameters in the given URL using the post data.
	 *
	 * @param \WP_Post $post The post object.
	 * @param string   $url  The URL containing placeholders to resolve.
	 *
	 * @return string|null The URL with resolved parameters or null if not found.
	 */
	public function resolve( WP_Post $post, string $url ): ?string {
		return (string) preg_replace_callback(
			self::PLACEHOLDER_REGEX,
			fn(array $matches): string => rawurlencode( $this->resolve_token( $matches[1], $post ) ),
			$url
		);
	}

	/**
	 * Resolve individual token by key.
	 *
	 * @param string   $key The token key without braces.
	 * @param \WP_Post $post Post object to resolve the token against.
	 */
	public function resolve_token( string $key, WP_Post $post ): string {
		$parameter = $this->parameter_registry->get( $key );
		if ( ! $parameter instanceof Preview_Parameter ) {
			return self::PLACEHOLDER_NOT_FOUND;
		}

		return $parameter->get_value( $post );
	}
}
