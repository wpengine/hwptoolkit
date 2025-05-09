<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Link;

use HWP\Previews\Preview\Parameter\Contracts\Preview_Parameter_Interface;
use HWP\Previews\Preview\Parameter\Preview_Parameter_Registry;
use WP_Post;

class Preview_Link_Placeholder_Resolver {

	/**
	 * Placeholder pattern in curly brackets - https://example.com/preview={example}.
	 *
	 * @var string
	 */
	public const PLACEHOLDER_REGEX = '/\{([A-Za-z0-9_]+)\}/';

	/**
	 * Placeholder not found message.
	 *
	 * @var string
	 */
	public const PLACEHOLDER_NOT_FOUND = 'PARAMETER_NOT_FOUND';

	/**
	 * Preview parameter registry.
	 *
	 * @var \HWP\Previews\Preview\Parameter\Preview_Parameter_Registry
	 */
	private Preview_Parameter_Registry $registry;

	/**
	 * Constructor.
	 *
	 * @param \HWP\Previews\Preview\Parameter\Preview_Parameter_Registry $registry Preview parameter registry.
	 */
	public function __construct(Preview_Parameter_Registry $registry) {
		$this->registry = $registry;
	}

	/**
	 * Replace all {PLACEHOLDER} tokens in template string with urlencoded string values from callbacks.
	 *
	 * @param string   $template The string containing {KEY} placeholders.
	 * @param \WP_Post $post   The post object to resolve the tokens against.
	 *
	 * @return string
	 */
	public function resolve_placeholders(string $template, WP_Post $post ): string {
		return preg_replace_callback(
			self::PLACEHOLDER_REGEX,
			fn(array $matches): string => rawurlencode( $this->resolve_token( $matches[1], $post ) ),
			$template
		);
	}

	/**
	 * Resolve individual token by key.
	 *
	 * @param string   $key The token key without braces.
	 * @param \WP_Post $post Post object to resolve the token against.
	 *
	 * @return string
	 */
	private function resolve_token( string $key, WP_Post $post ): string {
		$parameter = $this->registry->get( $key );
		if ( ! $parameter instanceof Preview_Parameter_Interface ) {
			return self::PLACEHOLDER_NOT_FOUND;
		}

		return $parameter->get_value( $post );
	}

}
