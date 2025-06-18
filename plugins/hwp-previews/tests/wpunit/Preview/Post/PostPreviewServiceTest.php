<?php


declare( strict_types=1 );

namespace HWP\Previews\Tests\Unit\Preview\Post;

use HWP\Previews\Preview\Post\Post_Preview_Service;
use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Test class for Post_Preview_Service
 */
class Post_Preview_Service_Test extends WPTestCase {

	private Post_Preview_Service $service;

	public function setUp(): void {
		parent::setUp();
		$this->remove_all_filters();
		$this->service = new Post_Preview_Service();
	}

	public function tearDown(): void {
		$this->remove_all_filters();
		parent::tearDown();
	}

	public function remove_all_filters() {
		remove_all_filters( 'hwp_previews_filter_available_post_types' );
		remove_all_filters( 'hwp_previews_filter_available_post_statuses' );
	}

	public function test_get_allowed_post_types_returns_array(): void {
		$result = $this->service->get_allowed_post_types();

		$this->assertIsArray( $result );
		$this->assertNotEmpty( $result );
	}

	public function test_get_post_statuses_returns_default_statuses(): void {
		$result = $this->service->get_post_statuses();

		$expected = [
			'publish',
			'future',
			'draft',
			'pending',
			'private',
			'auto-draft',
		];
		$this->assertEquals($expected, $result);
	}

	public function test_get_parent_post_statuses_returns_default_statuses(): void {
		$result = $this->service->get_parent_post_statuses();

		$expected = [
			'publish',
			'future',
			'draft',
			'pending',
			'private'
		];
		$this->assertEquals($expected, $result);
	}

	public function test_get_post_types_returns_same_as_get_allowed_post_types(): void {
		$allowed_types = $this->service->get_allowed_post_types();
		$post_types = $this->service->get_post_types();

		$this->assertEquals($allowed_types, $post_types);
	}

	public function test_post_types_filter_is_applied(): void {
		$custom_post_types = ['custom_post' => 'Custom Post Type'];
		add_filter('hwp_previews_filter_available_post_types', function() use ($custom_post_types) {
			return $custom_post_types;
		});

		$service = new Post_Preview_Service();
		$result = $service->get_post_types();
		$this->assertEquals($custom_post_types, $result);
	}

	public function test_post_statuses_filter_is_applied(): void {
		$custom_statuses = ['custom_status'];
		add_filter('hwp_previews_filter_available_post_statuses', function() use ($custom_statuses) {
			return $custom_statuses;
		});

		$service = new Post_Preview_Service();
		$result = $service->get_post_statuses();
		$this->assertEquals($custom_statuses, $result);
	}

	public function test_parent_post_statuses_filter_is_applied(): void {
		$custom_statuses = ['custom_status'];
		add_filter('hwp_previews_filter_available_parent_post_statuses', function() use ($custom_statuses) {
			return $custom_statuses;
		});

		$service = new Post_Preview_Service();
		$result = $service->get_parent_post_statuses();
		$this->assertEquals($custom_statuses, $result);
	}

	public function test_constructor_initializes_post_types_and_statuses(): void {
		$service = new Post_Preview_Service();

		$this->assertIsArray($service->get_post_types());
		$this->assertIsArray($service->get_post_statuses());
		$this->assertNotEmpty($service->get_post_statuses());
	}
}
