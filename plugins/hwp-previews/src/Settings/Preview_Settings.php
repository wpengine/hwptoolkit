<?php

declare(strict_types=1);

namespace HWP\Previews\Settings;

use HWP\Previews\Plugin;
use HWP\Previews\Settings\Contracts\Post_Types_Settings_Interface;

class Preview_Settings implements Post_Types_Settings_Interface {

	/**
	 * The settings cache group.
	 *
	 * @var \HWP\Previews\Settings\Settings_Cache_Group
	 */
	private Settings_Cache_Group $group;

	/**
	 * Constructor.
	 *
	 * @param \HWP\Previews\Settings\Settings_Cache_Group $group The settings cache group.
	 */
	public function __construct( Settings_Cache_Group $group ) {
		$this->group = $group;
	}

	/**
	 * Get all post types that are enabled in the settings.
	 *
	 * @param array<string> $default_value Default post types to return if none are enabled.
	 *
	 * @return array<string>
	 */
	public function post_types_enabled( array $default_value = [] ): array {
		$value = $this->group->get_cache_settings();

		$post_types = array_filter( $value, static fn( $item ) => isset( $item[ Plugin::ENABLED_FIELD ] ) && $item[ Plugin::ENABLED_FIELD ] === true );

		return ! empty( $post_types ) ? array_keys( $post_types ) : $default_value;
	}

	/**
	 * Get Unique Post Slugs setting value for the given post type.
	 *
	 * @param string $post_type The post type to get the setting for.
	 * @param bool   $default_value   The default value to return if the setting is not set.
	 *
	 * @return bool
	 */
	public function unique_post_slugs( string $post_type, bool $default_value = false ): bool {
		return $this->group->get_bool( Plugin::UNIQUE_POST_SLUGS_FIELD, $post_type, $default_value );
	}

	/**
	 * Get Post Statuses as Parent setting value for the given post type.
	 *
	 * @param string $post_type The post type to get the setting for.
	 * @param bool   $default_value   The default value to return if the setting is not set.
	 *
	 * @return bool
	 */
	public function post_statuses_as_parent( string $post_type, bool $default_value = false ): bool {
		return $this->group->get_bool( Plugin::POST_STATUSES_AS_PARENT_FIELD, $post_type, $default_value );
	}

	/**
	 * Show In iframe value for the given post type.
	 *
	 * @param string $post_type The post type to get the setting for.
	 * @param bool   $default_value   The default value to return if the setting is not set.
	 *
	 * @return bool
	 */
	public function in_iframe( string $post_type, bool $default_value = false ): bool {
		return $this->group->get_bool( Plugin::IN_IFRAME_FIELD, $post_type, $default_value );
	}

	/**
	 * URL template setting value for the given post type.
	 *
	 * @param string $post_type The post type to get the setting for.
	 * @param string $default_value   The default value to return if the setting is not set.
	 *
	 * @return string
	 */
	public function url_template( string $post_type, string $default_value = '' ): string {
		return $this->group->get_string( Plugin::PREVIEW_URL_FIELD, $post_type, $default_value );
	}

}
