<?php
declare( strict_types=1 );

namespace HWP\Previews\Post\Type;


use HWP\Previews\Post\Type\Contracts\Post_Types_Config_Interface;
use WP_Post_Type;

class Post_Types_Config implements Post_Types_Config_Interface {

	/**
	 * @var string[]
	 */
	private array $post_types;

	public function __construct( array $post_types ) {
		$this->post_types = $post_types;
	}

	public function get_post_types(): array {
		return $this->post_types;
	}

	public function is_post_type_applicable( string $post_type ): bool {
		return in_array( $post_type, $this->post_types, true ) && post_type_exists( $post_type );
	}

	public function is_hierarchical( WP_Post_Type $post_type ): bool {
		return $post_type->hierarchical;
	}

	public function supports_gutenberg( WP_Post_Type $post_type ): bool {
		if (
			empty( $post_type->show_in_rest ) ||
			empty( $post_type->supports ) ||
			! is_array( $post_type->supports ) ||
			! in_array( 'editor', $post_type->supports )
		) {
			return false;
		}

		if ( ! is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
			return true;
		}

		$classic_editor_settings = (array) get_option( 'classic-editor-settings', [] );

		return ! (
			! empty( $classic_editor_settings['post_types'] ) &&
			is_array( $classic_editor_settings['post_types'] ) &&
			in_array( $post_type->name, $classic_editor_settings['post_types'] )
		);
	}

}