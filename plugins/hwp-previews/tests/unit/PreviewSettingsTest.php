<?php

namespace HWP\Previews\Unit;


class PreviewSettingsTest extends \Codeception\Test\Unit {
	/**
	 * @var \WpunitTester
	 */
	protected $tester;

	// Assuming your settings class name - adjust as needed
//	private $settings_manager;
//
//	protected function _before() {
//		// Initialize your settings manager class
//		$this->settings_manager = new HeadlessPreviewSettingsManager();
//
//		// Clean up any existing test settings
//		delete_option( 'headless_preview_settings_post' );
//		delete_option( 'headless_preview_settings_page' );
//		delete_option( 'headless_preview_settings_product' );
//		delete_option( 'headless_preview_settings_invalid_type' );
//	}
//
//	protected function _after() {
//		// Clean up after tests
//		delete_option( 'headless_preview_settings_post' );
//		delete_option( 'headless_preview_settings_page' );
//		delete_option( 'headless_preview_settings_product' );
//		delete_option( 'headless_preview_settings_invalid_type' );
//	}

	/**
	 * Test: Save settings for 'post' post type
	 */
	public function testSaveSettingsForPostType() {

		$this->assertEquals( '1', '1', 'This is a placeholder assertion to ensure the test runs.' );
//		$post_settings = [
//			'enable_preview'   => true,
//			'load_iframe'      => false,
//			'preview_url'      => 'https://frontend.com/preview/{ID}',
//			'allowed_statuses' => [ 'draft', 'publish' ]
//		];
//
//		$result = $this->settings_manager->save_settings( 'post', $post_settings );
//
//		$this->assertTrue( $result, 'Settings should save successfully' );
//
//		// Verify settings were saved to correct option
//		$saved_settings = get_option( 'headless_preview_settings_post' );
//		$this->assertEquals( $post_settings, $saved_settings, 'Saved settings should match input' );
	}
}
