<?php

declare( strict_types=1 );

namespace HWP\Previews\Tests\Unit\Preview\Post;

use HWP\Previews\Admin\Settings\Fields\Settings_Field_Collection;
use HWP\Previews\Preview\Post\Post_Preview_Service;
use HWP\Previews\Preview\Post\Post_Settings_Service;
use HWP\Previews\Preview\Post\Post_Type_Service;
use lucatume\WPBrowser\TestCase\WPTestCase;
use WP_Post;

/**
 * Test class for Post_Type_Service
 */
class Post_Type_Service_Test extends WPTestCase {

	protected Post_Type_Service $service;

	protected int $post_id;

	protected WP_Post $post;
	protected $post_preview_service_mock;
	protected $post_settings_service_mock;

	public function setUp(): void {
		parent::setUp();

		$this->post_id = $this->factory()->post->create(
			[
				'post_type'   => 'post',
				'post_status' => 'publish',
				'post_title'  => 'Test Post',
			]
		);

		$this->post = get_post( $this->post_id );

		// Create mocks for dependencies
		$this->post_preview_service_mock  = $this->createMock( Post_Preview_Service::class );
		$this->post_settings_service_mock = $this->createMock( Post_Settings_Service::class );

		$this->service = new Post_Type_Service(
			$this->post,
			$this->post_preview_service_mock,
			$this->post_settings_service_mock
		);
	}

	public function tearDown(): void {
		remove_all_filters( 'hwp_previews_settings_group_option_key' );
		remove_all_filters( 'hwp_previews_settings_group_settings_group' );
	}

	public function test_is_allowed_for_previews_when_enabled_and_post_type_and_status_is_allowed(): void {

		$this->post_settings_service_mock
			->method( 'get_post_type_config' )
			->willReturn( [ Settings_Field_Collection::ENABLED_FIELD_ID => true ] );

		$this->post_preview_service_mock
			->method( 'get_post_types' )
			->willReturn( [ 'post' => 'Posts' ] );

		$this->post_preview_service_mock
			->method( 'get_post_statuses' )
			->willReturn( [ 'publish', 'draft' ] );

		$result = $this->service->is_allowed_for_previews();
		$this->assertTrue( $result );
	}

	public function test_is_not_allowed_for_previews_when_enabled_and_post_type_and_status_is_not_allowed(): void {

		$this->post_settings_service_mock
			->method( 'get_post_type_config' )
			->willReturn( [ Settings_Field_Collection::ENABLED_FIELD_ID => true ] );

		$this->post_preview_service_mock
			->method( 'get_post_types' )
			->willReturn( [ 'post' => 'Posts' ] );

		$this->post_preview_service_mock
			->method( 'get_post_statuses' )
			->willReturn( [ 'publish', 'draft' ] );

		$post            = $this->post;
		$post->post_type = 'draft';
		$draft_service   = new Post_Type_Service(
			$post,
			$this->post_preview_service_mock,
			$this->post_settings_service_mock
		);

		$result = $draft_service->is_allowed_for_previews();
		$this->assertFalse( $result );


		$post                     = $this->post;
		$post->post_type          = 'media';
		$custom_post_type_service = new Post_Type_Service(
			$post,
			$this->post_preview_service_mock,
			$this->post_settings_service_mock
		);

		$result = $custom_post_type_service->is_allowed_for_previews();
		$this->assertFalse( $result );
	}

	public function test_is_allowed_for_previews_returns_false_when_not_enabled(): void {
		$this->post_settings_service_mock
			->method( 'get_post_type_config' )
			->willReturn( [ Settings_Field_Collection::ENABLED_FIELD_ID => false ] );

		$result = $this->service->is_allowed_for_previews();

		$this->assertFalse( $result );
	}


	public function test_is_enabled_returns_false_when_config_not_array(): void {
		$this->post_settings_service_mock
			->method( 'get_post_type_config' )
			->willReturn( null );

		$result = $this->service->is_enabled();
		$this->assertFalse( $result );
	}

	public function test_is_enabled_returns_false_when_enabled_key_missing(): void {
		$this->post_settings_service_mock
			->method( 'get_post_type_config' )
			->willReturn( [ 'other_setting' => true ] );

		$result = $this->service->is_enabled();
		$this->assertFalse( $result );
	}

	public function test_is_allowed_post_type_returns_true_when_post_type_exists(): void {
		$this->post_preview_service_mock
			->method( 'get_post_types' )
			->willReturn( [ 'post' => 'Posts', 'page' => 'Pages' ] );

		$result = $this->service->is_allowed_post_type();
		$this->assertTrue( $result );
	}

	public function test_is_allowed_post_type_returns_false_when_post_type_not_exists(): void {
		$this->post_preview_service_mock
			->method( 'get_post_types' )
			->willReturn( [ 'page' => 'Pages' ] );

		$result = $this->service->is_allowed_post_type();
		$this->assertFalse( $result );
	}

	public function test_is_iframe_returns_true_when_enabled(): void {
		$this->post_settings_service_mock
			->method( 'get_post_type_config' )
			->willReturn( [ Settings_Field_Collection::IN_IFRAME_FIELD_ID => true ] );

		$result = $this->service->is_iframe();
		$this->assertTrue( $result );
	}

	public function test_is_iframe_returns_false_when_disabled(): void {
		$this->post_settings_service_mock
			->method( 'get_post_type_config' )
			->willReturn( [ Settings_Field_Collection::IN_IFRAME_FIELD_ID => false ] );

		$result = $this->service->is_iframe();
		$this->assertFalse( $result );
	}

	public function test_is_iframe_returns_false_when_config_not_array(): void {
		$this->post_settings_service_mock
			->method( 'get_post_type_config' )
			->willReturn( null );

		$result = $this->service->is_iframe();
		$this->assertFalse( $result );
	}

	public function test_is_iframe_returns_false_when_iframe_key_missing(): void {
		$this->post_settings_service_mock
			->method( 'get_post_type_config' )
			->willReturn( [ Settings_Field_Collection::ENABLED_FIELD_ID => true ] );

		$result = $this->service->is_iframe();
		$this->assertFalse( $result );
	}

	public function test_get_preview_url_returns_url_when_set(): void {
		$expected_url = 'https://example.com/preview';
		$this->post_settings_service_mock
			->method('get_post_type_config')
			->willReturn([ Settings_Field_Collection::PREVIEW_URL_FIELD_ID => $expected_url ]);

		$result = $this->service->get_preview_url();
		$this->assertSame($expected_url, $result);
	}

	public function test_get_preview_url_returns_null_when_config_not_array(): void {
		$this->post_settings_service_mock
			->method('get_post_type_config')
			->willReturn(null);

		$result = $this->service->get_preview_url();
		$this->assertNull($result);
	}

	public function test_get_preview_url_returns_null_when_field_missing(): void {
		$this->post_settings_service_mock
			->method('get_post_type_config')
			->willReturn([ Settings_Field_Collection::ENABLED_FIELD_ID => true ]);

		$result = $this->service->get_preview_url();
		$this->assertNull($result);
	}
}
