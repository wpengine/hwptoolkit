<?php

declare(strict_types=1);

namespace HWP\Previews\Admin\Settings\Fields;

interface Settings_Field_Interface {
	/**
	 * Render the settings field
	 *
	 * @param array<string> $option_value The option value.
	 * @param string        $setting_key  The setting key.
	 * @param string        $post_type    The post-type.
	 */
	public function render_field( array $option_value, string $setting_key, string $post_type ): string;

	/**
	 * Get the field ID
	 */
	public function get_id(): string;

	/**
	 * Whether the field should be rendered for hierarchical post types
	 */
	public function is_hierarchical(): bool;

	/**
	 * Whether the field should be rendered for hierarchical post-type
	 */
	public function should_render_field( bool $post_type_hierarchal ): bool;

	/**
	 * Add the settings field
	 *
	 * @param string               $section The section ID.
	 * @param string               $page    The page ID.
	 * @param array<string, mixed> $args The field arguments.
	 */
	public function add_settings_field( string $section, string $page, array $args ): void;

	/**
	 * Sanitize field value
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function sanitize_field( $value );
}
