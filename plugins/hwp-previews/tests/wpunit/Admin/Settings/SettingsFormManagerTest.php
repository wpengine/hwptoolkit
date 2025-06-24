<?php

namespace HWP\Previews\wpunit\Admin\Settings;

use HWP\Previews\Admin\Settings\Fields\Settings_Field_Collection;
use HWP\Previews\Admin\Settings\Settings_Form_Manager;
use HWP\Previews\Preview\Post\Post_Preview_Service;
use lucatume\WPBrowser\TestCase\WPTestCase;

class SettingsFormManagerTest extends WPTestCase {

	public function test_sanitize_settings() {

		$post_preview_service = new Post_Preview_Service();
		$form_manager         = new Settings_Form_Manager(
			$post_preview_service->get_post_types(),
			new Settings_Field_Collection()
		);

		$data = [
			"page" => [
				"enabled"                 => "1",
				"post_statuses_as_parent" => "1",
				"in_iframe"               => "1",
				"preview_url"             => "https://localhost:3000/page?preview=true&post_id={ID}&name={slug}"
			]
		];

		$sanitized_data = $form_manager->sanitize_settings( $data );
		$this->assertEquals( $sanitized_data, $data );
	}

	public function test_sanitize_settings_invalid_field() {

		$post_preview_service = new Post_Preview_Service();
		$form_manager         = new Settings_Form_Manager(
			$post_preview_service->get_post_types(),
			new Settings_Field_Collection()
		);

		$data = [
			"page" => [
				"enabled"                 => "1",
				'not_registered_field'    => "This field is not registered in the field collection so it should be removed",
				"post_statuses_as_parent" => "1",
				"in_iframe"               => "1",
				"preview_url"             => "https://localhost:3000/page?preview=true&post_id={ID}&name={slug}"
			]
		];

		$sanitized_data = $form_manager->sanitize_settings( $data );

		// The 'not_registered_field' should be removed from the sanitized data.
		unset( $data["page"]["not_registered_field"] );

		$this->assertEquals( $sanitized_data, $data );
	}

	public function test_sanitize_settings_invalid_format() {

		$post_preview_service = new Post_Preview_Service();
		$form_manager         = new Settings_Form_Manager(
			$post_preview_service->get_post_types(),
			new Settings_Field_Collection()
		);

		$data = [
			"enabled"                 => "1",
			"post_statuses_as_parent" => "1",
			"in_iframe"               => "1",
			"preview_url"             => "https://localhost:3000/page?preview=true&post_id={ID}&name={slug}"
		];

		$this->assertEmpty( $form_manager->sanitize_settings( $data ) );
		$this->assertEmpty( $form_manager->sanitize_settings([]) );
	}
}
