<?php

namespace HWP\Previews\wpunit\Hooks;

use http\Env\Url;
use HWP\Previews\Admin\Settings\Fields\Settings_Field_Collection;
use HWP\Previews\Hooks\Preview_Hooks;
use HWP\Previews\Preview\Template\Template_Resolver_Service;
use lucatume\WPBrowser\TestCase\WPTestCase;

use ReflectionClass;
use WP_Mock;
use WP_Post;
use WP_REST_Response;


class PreviewHooksTest extends WPTestCase {

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


	private ?WP_Post $post = null;

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

		$this->post = WPTestCase::factory()->post->create_and_get( [
			'post_type'   => 'post',
			'post_status' => 'draft',
		] );


	}

	public function tearDown(): void {
		$this->delete_option();
		$this->remove_all_filters();
		parent::tearDown();
	}

	public function remove_all_filters() {
		remove_all_filters( 'hwp_previews_settings_group_option_key' );
		remove_all_filters( 'hwp_previews_settings_group_settings_group' );
		remove_all_filters( 'hwp_previews_template_path' );
	}

	public function delete_option() {
		delete_option( $this->test_option_key );
		wp_cache_flush();
	}

	public function test_preview_hooks_instance() {


		$reflection       = new ReflectionClass( Preview_Hooks::class );
		$instanceProperty = $reflection->getProperty( 'instance' );
		$instanceProperty->setAccessible( true );
		$instanceProperty->setValue( null );

		$this->assertNull( $instanceProperty->getValue() );
		$instance = Preview_Hooks::init();

		$this->assertInstanceOf( Preview_Hooks::class, $instanceProperty->getValue() );
		$this->assertSame( $instance, $instanceProperty->getValue(), 'Preview_Hooks::init() should set the static instance property' );
	}

	public function test_enable_post_statuses_as_parent_asserts_true() {

		$test_config = [
			'page' => [
				Settings_Field_Collection::ENABLED_FIELD_ID                 => true,
				Settings_Field_Collection::POST_STATUSES_AS_PARENT_FIELD_ID => true,
			]
		];
		update_option( $this->test_option_key, $test_config );

		$args = [
			'post_type' => 'page'
		];

		$preview_hooks = new Preview_Hooks();
		$newArgs       = $preview_hooks->enable_post_statuses_as_parent( $args );
		$this->assertArrayHasKey( 'post_type', $newArgs );
		$this->assertArrayHasKey( 'post_status', $newArgs, 'Post type is not enabled for post statuses for parent.' );

		$this->assertEquals( $newArgs['post_type'], 'page' );
		$this->assertIsArray( $newArgs['post_status'] );
	}

	public function test_enable_post_statuses_as_parent_asserts_false_no_config_values() {

		$args = [
			'post_type' => 'page'
		];

		$preview_hooks = new Preview_Hooks();
		$newArgs       = $preview_hooks->enable_post_statuses_as_parent( $args );

		$this->assertArrayHasKey( 'post_type', $newArgs );
		$this->assertArrayNotHasKey( 'post_status', $newArgs );
		$this->assertEquals( $args, $newArgs );
	}

	public function test_enable_post_statuses_as_parent_asserts_false_option_not_enabled() {

		$test_config = [
			'page' => [
				Settings_Field_Collection::ENABLED_FIELD_ID                 => true,
				Settings_Field_Collection::POST_STATUSES_AS_PARENT_FIELD_ID => false,
			]
		];
		update_option( $this->test_option_key, $test_config );

		$args = [
			'post_type' => 'page'
		];

		$preview_hooks = new Preview_Hooks();
		$newArgs       = $preview_hooks->enable_post_statuses_as_parent( $args );

		$this->assertArrayNotHasKey( 'post_status', $newArgs );
		$this->assertEquals( $args, $newArgs );
	}

	public function test_enable_post_statuses_as_parent_asserts_false_not_hierarchal_post_type() {

		$test_config = [
			'page' => [
				Settings_Field_Collection::ENABLED_FIELD_ID                 => true,
				Settings_Field_Collection::POST_STATUSES_AS_PARENT_FIELD_ID => false,
			],
			'post' => [
				Settings_Field_Collection::ENABLED_FIELD_ID                 => true,
				Settings_Field_Collection::POST_STATUSES_AS_PARENT_FIELD_ID => false, // Note this doesn't appear in the admin but lets pretend so we can assert false
			]
		];
		update_option( $this->test_option_key, $test_config );

		$args = [
			'post_type' => 'post'
		];

		$preview_hooks = new Preview_Hooks();
		$newArgs       = $preview_hooks->enable_post_statuses_as_parent( $args );

		$this->assertArrayNotHasKey( 'post_status', $newArgs );
		$this->assertEquals( $args, $newArgs );
	}

	public function test_enable_post_statuses_as_parent_asserts_false_not_post_type() {

		$test_config = [
			'post' => [
				Settings_Field_Collection::ENABLED_FIELD_ID                 => true,
				Settings_Field_Collection::POST_STATUSES_AS_PARENT_FIELD_ID => true,
			]
		];
		update_option( $this->test_option_key, $test_config );

		$args = [
		];

		$preview_hooks = new Preview_Hooks();
		$newArgs       = $preview_hooks->enable_post_statuses_as_parent( $args );

		$this->assertEquals( $args, $newArgs );
	}

	public function test_should_return_iframe_template() {

		$test_config = [
			'post' => [
				Settings_Field_Collection::ENABLED_FIELD_ID     => true,
				Settings_Field_Collection::IN_IFRAME_FIELD_ID   => true,
				Settings_Field_Collection::PREVIEW_URL_FIELD_ID => 'https://localhost:3000/post?preview=true&post_id={ID}&status={status}',
			]
		];
		update_option( $this->test_option_key, $test_config );

		// Set is_preview to true
		global $wp_query;
		$wp_query->is_preview = true;

		// Set global post to a WP_Post object
		$new_post = $this->post;

		global $post;
		$post = $new_post;

		$expected_template = '';
		add_filter(
			'hwp_previews_template_path',
			function ( $template ) use ( &$expected_template ) {
				$expected_template = $template;

				return $template;
			}
		);


		$preview  = new Preview_Hooks();
		$template = $preview->add_iframe_preview_template( 'default-template.php' );
		$this->assertEquals( $expected_template, $template );

		// Assert that the query variable is set correctly in add_iframe_preview_template function
		$this->assertEquals( Template_Resolver_Service::get_query_variable(), 'https://localhost:3000/post?preview=true&post_id=' . $new_post->ID . '&status=draft' );
	}

	public function test_should_return_iframe_template_return_default_not_is_preview() {

		$test_config = [
			'post' => [
				Settings_Field_Collection::ENABLED_FIELD_ID     => true,
				Settings_Field_Collection::IN_IFRAME_FIELD_ID   => true,
				Settings_Field_Collection::PREVIEW_URL_FIELD_ID => 'https://localhost:3000/post?preview=true&post_id={ID}&status={status}',
			]
		];
		update_option( $this->test_option_key, $test_config );

		// Set is_preview to false
		global $wp_query;
		$wp_query->is_preview = false;

		// Set global post to a WP_Post object
		$new_post = $this->post;

		global $post;
		$post = $new_post;

		$preview  = new Preview_Hooks();
		$template = $preview->add_iframe_preview_template( 'default-template.php' );
		$this->assertEquals( $template, 'default-template.php' );
	}


	public function test_should_return_iframe_template_return_default_no_post() {

		$test_config = [
			'post' => [
				Settings_Field_Collection::ENABLED_FIELD_ID     => true,
				Settings_Field_Collection::IN_IFRAME_FIELD_ID   => true,
				Settings_Field_Collection::PREVIEW_URL_FIELD_ID => 'https://localhost:3000/post?preview=true&post_id={ID}&status={status}',
			]
		];
		update_option( $this->test_option_key, $test_config );

		// Set is_preview to true
		global $wp_query;
		$wp_query->is_preview = true;

		// Set global post to a WP_Post object
		$new_post = $this->post;

		// No post set
		global $post;
		$post = '';

		$preview  = new Preview_Hooks();
		$template = $preview->add_iframe_preview_template( 'default-template.php' );
		$this->assertEquals( $template, 'default-template.php' );
	}

	public function test_should_return_iframe_template_return_default_no_iframe_template() {

		$test_config = [
			'post' => [
				Settings_Field_Collection::ENABLED_FIELD_ID     => true,
				Settings_Field_Collection::IN_IFRAME_FIELD_ID   => true,
				Settings_Field_Collection::PREVIEW_URL_FIELD_ID => 'https://localhost:3000/post?preview=true&post_id={ID}&status={status}',
			]
		];
		update_option( $this->test_option_key, $test_config );

		// Set is_preview to true
		global $wp_query;
		$wp_query->is_preview = true;

		// Set global post to a WP_Post object
		$new_post = $this->post;

		global $post;
		$post = $new_post;

		// Ensure that the filter returns an empty string so that the template is not found
		add_filter(
			'hwp_previews_template_path',
			function ( $template ) {
				return '';
			}
		);


		$preview  = new Preview_Hooks();
		$template = $preview->add_iframe_preview_template( 'default-template.php' );
		$this->assertEquals( $template, 'default-template.php' );
	}

	public function test_should_return_iframe_template_return_default_not_enabled_for_previews() {

		$test_config = [
			'post' => [
				Settings_Field_Collection::ENABLED_FIELD_ID     => false, // Not enabled for previews
				Settings_Field_Collection::IN_IFRAME_FIELD_ID   => true,
				Settings_Field_Collection::PREVIEW_URL_FIELD_ID => 'https://localhost:3000/post?preview=true&post_id={ID}&status={status}',
			]
		];
		update_option( $this->test_option_key, $test_config );

		// Set is_preview to true
		global $wp_query;
		$wp_query->is_preview = true;

		// Set global post to a WP_Post object
		$new_post = $this->post;

		global $post;
		$post = $new_post;

		$preview  = new Preview_Hooks();
		$template = $preview->add_iframe_preview_template( 'default-template.php' );
		$this->assertEquals( $template, 'default-template.php' );
	}


	public function test_generate_preview_url_no_url() {

		$test_config = [
			'post' => [
				Settings_Field_Collection::ENABLED_FIELD_ID     => true,
				Settings_Field_Collection::IN_IFRAME_FIELD_ID   => true,
				Settings_Field_Collection::PREVIEW_URL_FIELD_ID => '',
			]
		];
		update_option( $this->test_option_key, $test_config );

		$new_post = $this->post;
		$preview  = new Preview_Hooks();
		$url      = $preview->generate_preview_url( $new_post );

		$this->assertEquals( '', $url );
	}

	public function test_generate_preview_url_no_url_not_enabled() {

		$test_config = [
			'post' => [
				Settings_Field_Collection::ENABLED_FIELD_ID     => false,
				Settings_Field_Collection::IN_IFRAME_FIELD_ID   => true,
				Settings_Field_Collection::PREVIEW_URL_FIELD_ID => 'https://localhost:3000/post?preview=true&post_id={ID}&status={status}',
			]
		];
		update_option( $this->test_option_key, $test_config );

		$new_post = $this->post;
		$preview  = new Preview_Hooks();
		$url      = $preview->generate_preview_url( $new_post );

		$this->assertEquals( '', $url );
	}

	public function test_generate_preview_url_return_valid_url() {

		// Note: More tests in TemplateResolverTest.php

		$test_config = [
			'post' => [
				Settings_Field_Collection::ENABLED_FIELD_ID     => true,
				Settings_Field_Collection::IN_IFRAME_FIELD_ID   => true,
				Settings_Field_Collection::PREVIEW_URL_FIELD_ID => 'https://localhost:3000/post?preview=true&post_id={ID}&status={status}',
			]
		];
		update_option( $this->test_option_key, $test_config );

		$new_post = $this->post;
		$preview  = new Preview_Hooks();

		$url = $preview->generate_preview_url( $new_post );
		$this->assertEquals( 'https://localhost:3000/post?preview=true&post_id=' . $new_post->ID . '&status=draft', $url );
	}

	public function test_update_preview_post_link_returns_generated_url() {

		$preview_link = 'https://localhost:3000/post?preview=true&post_id={ID}&status={status}';

		$test_config = [
			'post' => [
				Settings_Field_Collection::ENABLED_FIELD_ID     => true,
				Settings_Field_Collection::IN_IFRAME_FIELD_ID   => false,
				Settings_Field_Collection::PREVIEW_URL_FIELD_ID => $preview_link,
			]
		];
		update_option( $this->test_option_key, $test_config );

		$new_post = $this->post;
		$preview  = new Preview_Hooks();

		$url = $preview->update_preview_post_link( $preview_link, $new_post );
		$this->assertNotEquals( $url, $preview_link, 'The URL should not be the same as the preview link' );
	}

	public function test_update_preview_post_link_returns_default_previews_not_enabled() {

		$preview_link = 'https://localhost:3000/post?preview=true&post_id={ID}&status={status}';

		$test_config = [
			'post' => [
				Settings_Field_Collection::ENABLED_FIELD_ID     => false,
				Settings_Field_Collection::IN_IFRAME_FIELD_ID   => false,
				Settings_Field_Collection::PREVIEW_URL_FIELD_ID => $preview_link,
			]
		];
		update_option( $this->test_option_key, $test_config );

		$new_post = $this->post;
		$preview  = new Preview_Hooks();

		$url = $preview->update_preview_post_link( $preview_link, $new_post );
		$this->assertEquals( $url, $preview_link, 'The URL should be the same as the preview link as preview is not enabled for posts' );
	}

	public function test_update_preview_post_link_returns_default_iframe_enabled() {

		$preview_link = 'https://localhost:3000/post?preview=true&post_id={ID}&status={status}';

		$test_config = [
			'post' => [
				Settings_Field_Collection::ENABLED_FIELD_ID     => true,
				Settings_Field_Collection::IN_IFRAME_FIELD_ID   => true,
				Settings_Field_Collection::PREVIEW_URL_FIELD_ID => $preview_link,
			]
		];
		update_option( $this->test_option_key, $test_config );

		$new_post = $this->post;
		$preview  = new Preview_Hooks();

		$url = $preview->update_preview_post_link( $preview_link, $new_post );
		$this->assertEquals( $url, $preview_link, 'The URL should be the same as the preview link as iframe is enabled for posts' );
	}


	public function test_update_preview_post_link_returns_default_no_preview_url() {

		$preview_link = 'https://localhost:3000/post?preview=true&post_id={ID}&status={status}';

		$test_config = [
			'post' => [
				Settings_Field_Collection::ENABLED_FIELD_ID   => true,
				Settings_Field_Collection::IN_IFRAME_FIELD_ID => false,
			]
		];
		update_option( $this->test_option_key, $test_config );

		// Set global post to a WP_Post object
		$new_post = $this->post;

		$preview = new Preview_Hooks();

		$url = $preview->update_preview_post_link( $preview_link, $new_post );
		$this->assertEquals( $url, $preview_link, 'The URL should be the same as the preview link as post type was removed from allowed post types' );
	}


	public function test_filter_rest_prepare_link_adds_link() {

		$preview_link = 'https://localhost:3000/post?preview=true&post_id={ID}&status={status}';

		$test_config = [
			'post' => [
				Settings_Field_Collection::ENABLED_FIELD_ID   => true,
				Settings_Field_Collection::IN_IFRAME_FIELD_ID => false,
				Settings_Field_Collection::PREVIEW_URL_FIELD_ID => $preview_link,
			]
		];
		update_option( $this->test_option_key, $test_config );

		$new_post = $this->post;

		$original_response  = new WP_REST_Response(['foo' => 'bar']);
		$preview = new Preview_Hooks();

		$response = $preview->filter_rest_prepare_link( $original_response, $new_post );
		$data = $response->get_data();
		$this->assertArrayHasKey( 'link', $data );

		$this->assertEquals( 'https://localhost:3000/post?preview=true&post_id=' . $new_post->ID . '&status=' . $new_post->post_status, $data[ 'link' ] );
	}

	public function test_filter_rest_prepare_link_no_link_iframe_enabled() {

		$preview_link = 'https://localhost:3000/post?preview=true&post_id={ID}&status={status}';

		$test_config = [
			'post' => [
				Settings_Field_Collection::ENABLED_FIELD_ID   => true,
				Settings_Field_Collection::IN_IFRAME_FIELD_ID => true,
				Settings_Field_Collection::PREVIEW_URL_FIELD_ID => $preview_link,
			]
		];
		update_option( $this->test_option_key, $test_config );

		$new_post = $this->post;

		$original_response  = new WP_REST_Response(['foo' => 'bar']);
		$preview = new Preview_Hooks();

		$response = $preview->filter_rest_prepare_link( $original_response, $new_post );
		$data = $response->get_data();
		$this->assertArrayNotHasKey( 'link', $data );
		$this->assertEquals($original_response, $response);
	}

	public function test_filter_rest_prepare_link_no_link_previews_not_enabled() {

		$preview_link = 'https://localhost:3000/post?preview=true&post_id={ID}&status={status}';

		$test_config = [
			'post' => [
				Settings_Field_Collection::ENABLED_FIELD_ID   => false,
				Settings_Field_Collection::IN_IFRAME_FIELD_ID => false,
				Settings_Field_Collection::PREVIEW_URL_FIELD_ID => $preview_link,
			]
		];
		update_option( $this->test_option_key, $test_config );

		$new_post = $this->post;

		$original_response  = new WP_REST_Response(['foo' => 'bar']);
		$preview = new Preview_Hooks();

		$response = $preview->filter_rest_prepare_link( $original_response, $new_post );
		$data = $response->get_data();
		$this->assertArrayNotHasKey( 'link', $data );
		$this->assertEquals($original_response, $response);
	}

	public function test_filter_rest_prepare_link_no_link_previews_no_preview_url() {

		$test_config = [
			'post' => [
				Settings_Field_Collection::ENABLED_FIELD_ID   => true,
				Settings_Field_Collection::IN_IFRAME_FIELD_ID => false,
			]
		];
		update_option( $this->test_option_key, $test_config );

		$new_post = $this->post;

		$original_response  = new WP_REST_Response(['foo' => 'bar']);
		$preview = new Preview_Hooks();

		$response = $preview->filter_rest_prepare_link( $original_response, $new_post );
		$data = $response->get_data();
		$this->assertArrayNotHasKey( 'link', $data );
		$this->assertEquals($original_response, $response);
	}
}
