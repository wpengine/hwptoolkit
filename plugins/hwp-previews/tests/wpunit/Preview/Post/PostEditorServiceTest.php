<?php

declare( strict_types=1 );

namespace HWP\Previews\Tests\Preview\Post;

use HWP\Previews\Preview\Post\Post_Editor_Service;
use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Test class for Post_Editor_Service
 */
class Post_Editor_Service_Test extends WPTestCase {

	private ?Post_Editor_Service $service = null;

	protected $original_settings = null;

	public function setUp(): void {
		parent::setUp();
		$this->service = new Post_Editor_Service();

		$this->original_settings = get_option( 'classic-editor-settings', [] );
		$this->clean_up_filter_options();
	}

	public function tearDown(): void {
		$this->clean_up_filter_options();
		update_option( 'classic-editor-settings', $this->original_settings );
		parent::tearDown();
	}

	public function clean_up_filter_options() {
		remove_all_filters( 'pre_option_classic-editor-settings' );
		delete_option( 'classic-editor-settings' );
	}


	public function test_gutenberg_editor_enabled_returns_true_when_conditions_met(): void {

		$post_type = 'events';
		register_post_type( $post_type, [
			'label'        => 'Events',
			'description'  => 'Custom post type for events',
			'public'       => true,
			'show_in_rest' => true, // Gutenberg supported
			'supports'     => array( 'title', 'editor', 'author', 'thumbnail' ),
		] );

		$result = $this->service->gutenberg_editor_enabled( $post_type );

		$this->assertTrue( $result );
		unregister_post_type( $post_type );
	}

	public function test_gutenberg_editor_enabled_returns_false_when_gutenberg_not_supported(): void {
		$post_type = 'events_no_gutenberg';
		register_post_type($post_type, [
			'label'        => 'Events',
			'description'  => 'Custom post type for events',
			'public' => true,
			'show_in_rest' => false, // Gutenberg not supported
			'supports' => ['title', 'editor']
		]);

		$result = $this->service->gutenberg_editor_enabled($post_type);
		$this->assertFalse($result);

		unregister_post_type($post_type);
	}

	public function test_gutenberg_editor_enabled_returns_false_when_post_type_not_exists(): void {
		$result = $this->service->gutenberg_editor_enabled( 'nonexistent_post_type' );

		$this->assertFalse( $result );
	}


	public function test_gutenberg_editor_enabled_returns_false_when_classic_editor_forced(): void {
		$post_type = 'events_classic';
		register_post_type($post_type, [
			'public' => true,
			'show_in_rest' => true,
			'supports' => ['title', 'editor']
		]);

		// Mock classic editor being active and configured
		tests_add_filter( 'pre_option_active_plugins', function( $plugins ) {
			$plugins[] = 'classic-editor/classic-editor.php';
			return $plugins;
		} );


		// Set classic editor settings to force this post type
		update_option('classic-editor-settings', [
			'post_types' => [$post_type]
		]);


		$result = $this->service->gutenberg_editor_enabled($post_type);

		$this->assertFalse($result);
		unregister_post_type($post_type);
	}


	public function test_is_gutenberg_supported_returns_false_when_editor_not_supported(): void {
		$post_type = 'no_editor_support';
		register_post_type($post_type, [
			'public' => true,
			'show_in_rest' => true,
			'supports' => ['title'] // No 'editor' support
		]);

		$result = $this->service->gutenberg_editor_enabled($post_type);

		$this->assertFalse($result);
		unregister_post_type($post_type);
	}



}
