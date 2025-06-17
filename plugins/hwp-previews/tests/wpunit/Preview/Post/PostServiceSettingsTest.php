<?php

declare( strict_types=1 );

namespace HWP\Previews\Tests\Unit\Preview\Post;

use HWP\Previews\Preview\Post\Post_Settings_Service;
use lucatume\WPBrowser\TestCase\WPTestCase;

/**
 * Test class for Post_Settings_Service
 */
class Post_Settings_Service_Test extends WPTestCase {

	private Post_Settings_Service $service;

	/**
	 * The option key used for testing.
	 *
	 * @var string
	 */
	private string $test_option_key = 'test_hwp_previews_settings';

	/**
	 * The settings group used for testing.
	 *
	 * @var string
	 */
	private string $test_settings_group = 'test_hwp_previews_group';

	public function setUp(): void {
		parent::setUp();

		$this->remove_all_filters();
		$this->delete_option();

		// Set up test filters so we don't overwrite the actual plugin settings.
		add_filter( 'hwp_previews_settings_group_option_key', function () {
			return $this->test_option_key;
		} );
		add_filter( 'hwp_previews_settings_group_settings_group', function () {
			return $this->test_settings_group;
		} );
	}

	public function tearDown(): void {
		$this->delete_option();
		$this->remove_all_filters();
		parent::tearDown();
	}

	public function remove_all_filters() {
		remove_all_filters( 'hwp_previews_settings_group_option_key' );
		remove_all_filters( 'hwp_previews_settings_group_settings_group' );
	}

	public function delete_option() {
		delete_option( $this->test_option_key );
		wp_cache_flush();
	}

	public function test_get_post_type_config_returns_config_when_exists(): void {
		$test_config = [
			'post' => [ 'enabled' => true, 'in_iframe' => false ],
			'page' => [ 'enabled' => false, 'in_iframe' => true ]
		];
		update_option( $this->test_option_key, $test_config );

		$this->service = new Post_Settings_Service();

		// Act
		$result = $this->service->get_post_type_config( 'post' );

		// Assert
		$this->assertEquals( [ 'enabled' => true, 'in_iframe' => false ], $result );
	}

	public function test_get_post_type_config_returns_null_when_not_exists(): void {
		// Arrange
		$test_config = [ 'post' => [ 'enabled' => true ] ];
		update_option( $this->test_option_key, $test_config );

		$this->service = new Post_Settings_Service();

		// Act
		$result = $this->service->get_post_type_config( 'nonexistent_post_type' );

		// Assert
		$this->assertNull( $result );
	}

	public function test_get_post_type_config_returns_null_when_no_settings(): void {
		// Arrange - no settings saved
		$this->service = new Post_Settings_Service();

		// Act
		$result = $this->service->get_post_type_config( 'post' );

		// Assert
		$this->assertNull( $result );
	}

	public function test_get_option_key_returns_filtered_value(): void {
		// Arrange
		$this->service = new Post_Settings_Service();

		// Act
		$result = $this->service->get_option_key();

		// Assert
		$this->assertEquals( $this->test_option_key, $result );
	}

	public function test_get_settings_group_returns_filtered_value(): void {
		// Arrange
		$this->service = new Post_Settings_Service();

		// Act
		$result = $this->service->get_settings_group();

		// Assert
		$this->assertEquals( $this->test_settings_group, $result );
	}

	public function test_constructor_loads_settings_from_cache_when_available(): void {
		// Arrange
		$cached_data = [ 'post' => [ 'enabled' => true, 'cached' => true ] ];
		wp_cache_set( $this->test_option_key, $cached_data, $this->test_settings_group );

		// Different data in database to ensure cache is used
		$db_data = [ 'post' => [ 'enabled' => false, 'cached' => false ] ];
		update_option( $this->test_option_key, $db_data );

		// Act
		$this->service = new Post_Settings_Service();
		$result        = $this->service->get_post_type_config( 'post' );

		// Assert
		$this->assertEquals( [ 'enabled' => true, 'cached' => true ], $result );
	}

	public function test_constructor_loads_settings_from_database_when_cache_empty(): void {
		// Arrange
		$db_data = [ 'post' => [ 'enabled' => true, 'from_db' => true ] ];
		update_option( $this->test_option_key, $db_data );

		// Ensure cache is empty
		wp_cache_delete( $this->test_option_key, $this->test_settings_group );

		// Act
		$this->service = new Post_Settings_Service();
		$result        = $this->service->get_post_type_config( 'post' );

		// Assert
		$this->assertEquals( [ 'enabled' => true, 'from_db' => true ], $result );
	}

	public function test_constructor_handles_non_array_cache_value(): void {
		// Arrange
		wp_cache_set( $this->test_option_key, 'not_an_array', $this->test_settings_group );

		$db_data = [ 'post' => [ 'enabled' => true ] ];
		update_option( $this->test_option_key, $db_data );

		// Act
		$this->service = new Post_Settings_Service();
		$result        = $this->service->get_post_type_config( 'post' );

		// Assert
		$this->assertEquals( [ 'enabled' => true ], $result );
	}

	public function test_constructor_handles_empty_database_option(): void {
		// Arrange - ensure option doesn't exist
		delete_option( $this->test_option_key );
		wp_cache_delete( $this->test_option_key, $this->test_settings_group );

		// Act
		$this->service = new Post_Settings_Service();
		$result        = $this->service->get_post_type_config( 'post' );

		// Assert
		$this->assertNull( $result );
	}

	public function test_constructor_handles_non_array_database_option(): void {
		// Arrange
		update_option( $this->test_option_key, 'not_an_array' );
		wp_cache_delete( $this->test_option_key, $this->test_settings_group );

		// Act
		$this->service = new Post_Settings_Service();
		$result        = $this->service->get_post_type_config( 'post' );

		// Assert
		$this->assertNull( $result );
	}
}
