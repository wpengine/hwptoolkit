<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Post;

use HWP\Previews\Admin\Settings\Fields\Settings_Field_Collection;
use WP_Post;

/**
 * Post-Type Service class
 *
 * This class provides methods to retrieve and manage post-settings for the HWP Previews plugin.
 *
 * @package HWP\Previews
 *
 * @since 0.0.1
 */
class Post_Type_Service {
	/**
	 * The post-object
	 *
	 * @var \WP_Post
	 */
	protected WP_Post $post;

	/**
	 * Post-preview service that provide information about the post-type for previews.
	 *
	 * @var \HWP\Previews\Preview\Post\Post_Preview_Service
	 */
	private Post_Preview_Service $post_preview_service;

	/**
	 * Settings service to retrieve post-type settings.
	 *
	 * @var \HWP\Previews\Preview\Post\Post_Settings_Service
	 */
	private Post_Settings_Service $post_settings_service;

	/**
	 * @param \WP_Post                                         $post
	 * @param \HWP\Previews\Preview\Post\Post_Preview_Service  $post_preview_service
	 * @param \HWP\Previews\Preview\Post\Post_Settings_Service $post_settings_service
	 */
	public function __construct( WP_Post $post, Post_Preview_Service $post_preview_service, Post_Settings_Service $post_settings_service ) {
		$this->post                  = $post;
		$this->post_preview_service  = $post_preview_service;
		$this->post_settings_service = $post_settings_service;
	}

	/**
	 * Checks if the post-type is allowed for previews.
	 */
	public function is_allowed_for_previews(): bool {
		return $this->is_enabled() && $this->is_allowed_post_type() && $this->is_allowed_post_status();
	}

	/**
	 * Checks if the post-type is enabled for previews.
	 */
	public function is_enabled(): bool {
		$config = $this->post_settings_service->get_post_type_config( $this->post->post_type );
		if ( ! is_array( $config ) ) {
			return false;
		}

		$field_id = Settings_Field_Collection::ENABLED_FIELD_ID;

		if ( ! isset( $config[ $field_id ] ) ) {
			return false;
		}

		return (bool) $config[ $field_id ];
	}

	/**
	 * Checks if the post-type is allowed for previews.
	 */
	public function is_allowed_post_type(): bool {
		$post_type  = $this->post->post_type;
		$post_types = $this->post_preview_service->get_post_types();

		return array_key_exists( $post_type, $post_types );
	}

	/**
	 * Checks if the post-status is allowed for previews.
	 */
	public function is_allowed_post_status(): bool {
		$post_status   = $this->post->post_status;
		$post_statuses = $this->post_preview_service->get_post_statuses();

		return in_array( $post_status, $post_statuses, true );
	}

	/**
	 * Checks if the post-type is allowed for iframe previews.
	 */
	public function is_iframe(): bool {
		$config = $this->post_settings_service->get_post_type_config( $this->post->post_type );
		if ( ! is_array( $config ) ) {
			return false;
		}

		$field_id = Settings_Field_Collection::IN_IFRAME_FIELD_ID;
		if ( ! isset( $config[ $field_id ] ) ) {
			return false;
		}

		return (bool) $config[ $field_id ];
	}

	/**
	 * Retrieves the settings URL for the given post.
	 *
	 * @return string|null The settings URL or null if not found.
	 */
	public function get_preview_url(): ?string {
		$config = $this->post_settings_service->get_post_type_config( $this->post->post_type );
		if ( ! is_array( $config ) ) {
			return null;
		}

		$field_id = Settings_Field_Collection::PREVIEW_URL_FIELD_ID;
		if ( ! isset( $config[ $field_id ] ) ) {
			return null;
		}

		return (string) $config[ $field_id ];
	}
}
