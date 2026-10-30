<?php

declare( strict_types=1 );

namespace WPGraphQL\Logging\Tests\Updates;

use lucatume\WPBrowser\TestCase\WPTestCase;
use stdClass;
use WP_Error;
use WPGraphQL\Logging\Updates\PluginUpdater;

/**
 * Tests for the WP Engine plugin updater client.
 *
 * @package WPGraphQL\Logging
 *
 * @since 1.1.0
 */
class PluginUpdaterTest extends WPTestCase {

	private const SLUG = 'wpgraphql-logging';

	/**
	 * URLs requested through the HTTP API during a test.
	 *
	 * @var string[]
	 */
	private array $requests = [];

	private string $basename;

	protected function setUp(): void {
		parent::setUp();
		$this->basename = plugin_basename( WPGRAPHQL_LOGGING_PLUGIN_DIR . 'wpgraphql-logging.php' );
		$this->requests = [];
		$this->reset_state();
	}

	protected function tearDown(): void {
		$this->reset_state();
		parent::tearDown();
	}

	private function reset_state(): void {
		remove_all_filters( 'pre_http_request' );
		remove_all_filters( 'plugins_api' );
		remove_all_filters( 'pre_set_site_transient_update_plugins' );
		delete_option( 'wpesu-plugin-wpgraphql-logging' );
		delete_option( 'wpesu-plugin-wpgraphql-logging-expiry' );
	}

	/**
	 * Build a manifest like the one served by the updates API.
	 *
	 * @param array<string, mixed> $overrides Fields to override.
	 */
	private function manifest( array $overrides = [] ): string {
		$manifest = array_merge(
			[
				'name'          => 'WPGraphQL Logging',
				'slug'          => self::SLUG,
				'version'       => '999.0.0',
				'requires'      => '6.5',
				'requires_php'  => '8.1.2',
				'tested'        => '6.9',
				'download_link' => 'https://wpe-plugin-updates.wpengine.com/wpgraphql-logging/wpgraphql-logging.999.0.0.zip',
				'sections'      => [
					'description' => 'Description',
					'changelog'   => 'Changelog',
				],
			],
			$overrides
		);

		return (string) wp_json_encode( $manifest );
	}

	/**
	 * Short-circuit HTTP requests with the given response.
	 *
	 * @param array<string, mixed>|\WP_Error $response The response to return.
	 */
	private function mock_http( $response ): void {
		add_filter(
			'pre_http_request',
			function ( $pre, $args, $url ) use ( $response ) {
				$this->requests[] = $url;
				return $response;
			},
			10,
			3
		);
	}

	private function mock_manifest( string $body, int $code = 200 ): void {
		$this->mock_http(
			[
				'headers'  => [],
				'body'     => $body,
				'response' => [
					'code'    => $code,
					'message' => 200 === $code ? 'OK' : 'Error',
				],
				'cookies'  => [],
				'filename' => null,
			]
		);
	}

	private function updater(): PluginUpdater {
		$updater = PluginUpdater::check_for_updates( self::SLUG, $this->basename );
		$this->assertInstanceOf( PluginUpdater::class, $updater );
		return $updater;
	}

	private function empty_transient(): stdClass {
		$transient           = new stdClass();
		$transient->response = [];
		$transient->checked  = [];
		return $transient;
	}

	public function test_admin_init_hook_is_registered(): void {
		$this->assertSame( 5, has_action( 'admin_init', 'wpgraphql_logging_check_for_updates' ) );
	}

	public function test_check_for_updates_function_registers_filters(): void {
		wpgraphql_logging_check_for_updates();

		$this->assertNotFalse( has_filter( 'pre_set_site_transient_update_plugins' ) );
		$this->assertNotFalse( has_filter( 'plugins_api' ) );
	}

	public function test_register_adds_filters(): void {
		$updater = $this->updater();

		$this->assertSame( 10, has_filter( 'pre_set_site_transient_update_plugins', [ $updater, 'filter_plugin_update_transient' ] ) );
		$this->assertSame( 20, has_filter( 'plugins_api', [ $updater, 'filter_plugin_update_info' ] ) );
	}

	public function test_returns_null_for_unknown_plugin(): void {
		$this->assertNull( PluginUpdater::check_for_updates( self::SLUG, 'not-installed/not-installed.php' ) );
		$this->assertFalse( has_filter( 'pre_set_site_transient_update_plugins' ) );
	}

	public function test_returns_null_for_empty_properties(): void {
		$this->assertNull( PluginUpdater::check_for_updates( '', $this->basename ) );
		$this->assertNull( PluginUpdater::check_for_updates( self::SLUG, '' ) );
	}

	public function test_manifest_url_and_cache_option_names(): void {
		$updater = $this->updater();

		$this->assertSame( 'https://wpe-plugin-updates.wpengine.com/wpgraphql-logging/info.json', $updater->get_manifest_url() );
		$this->assertSame( 'wpesu-plugin-wpgraphql-logging', $updater->get_cache_option_name() );
		$this->assertSame( 'wpesu-plugin-wpgraphql-logging-expiry', $updater->get_cache_expiry_option_name() );
	}

	public function test_transient_gets_update_when_newer_version_available(): void {
		$this->mock_manifest( $this->manifest() );

		$transient = $this->updater()->filter_plugin_update_transient( $this->empty_transient() );

		$this->assertSame( [ 'https://wpe-plugin-updates.wpengine.com/wpgraphql-logging/info.json' ], $this->requests );
		$this->assertArrayHasKey( $this->basename, $transient->response );

		$update = $transient->response[ $this->basename ];
		$this->assertSame( self::SLUG, $update->slug );
		$this->assertSame( '999.0.0', $update->new_version );
		$this->assertSame( $this->basename, $update->plugin );
		$this->assertSame( '8.1.2', $update->requires_php );
		$this->assertStringEndsWith( 'wpgraphql-logging.999.0.0.zip', $update->package );
		$this->assertSame( '999.0.0', $transient->checked[ $this->basename ] );
	}

	public function test_transient_unchanged_when_installed_version_is_current(): void {
		$this->mock_manifest( $this->manifest( [ 'version' => WPGRAPHQL_LOGGING_VERSION ] ) );

		$transient = $this->updater()->filter_plugin_update_transient( $this->empty_transient() );

		$this->assertSame( [], $transient->response );
	}

	public function test_transient_without_response_property_is_handled(): void {
		$this->mock_manifest( $this->manifest() );

		$transient = $this->updater()->filter_plugin_update_transient( new stdClass() );

		$this->assertArrayHasKey( $this->basename, $transient->response );
	}

	public function test_non_object_transient_is_returned_untouched(): void {
		$this->mock_manifest( $this->manifest() );

		$this->assertFalse( $this->updater()->filter_plugin_update_transient( false ) );
		$this->assertSame( [], $this->requests );
	}

	public function test_http_error_leaves_transient_unchanged(): void {
		$this->mock_http( new WP_Error( 'http_request_failed', 'Timeout' ) );

		$transient = $this->updater()->filter_plugin_update_transient( $this->empty_transient() );

		$this->assertSame( [], $transient->response );
		$this->assertFalse( get_option( 'wpesu-plugin-wpgraphql-logging' ) );
	}

	public function test_non_200_response_leaves_transient_unchanged(): void {
		$this->mock_manifest( $this->manifest(), 404 );

		$transient = $this->updater()->filter_plugin_update_transient( $this->empty_transient() );

		$this->assertSame( [], $transient->response );
	}

	public function test_invalid_json_leaves_transient_unchanged(): void {
		$this->mock_manifest( 'not json' );

		$transient = $this->updater()->filter_plugin_update_transient( $this->empty_transient() );

		$this->assertSame( [], $transient->response );
	}

	public function test_manifest_missing_required_fields_is_ignored(): void {
		$this->mock_manifest( $this->manifest( [ 'download_link' => '' ] ) );

		$transient = $this->updater()->filter_plugin_update_transient( $this->empty_transient() );

		$this->assertSame( [], $transient->response );
	}

	public function test_response_is_cached(): void {
		$this->mock_manifest( $this->manifest() );
		$updater = $this->updater();

		$updater->filter_plugin_update_transient( $this->empty_transient() );
		$updater->filter_plugin_update_transient( $this->empty_transient() );

		$this->assertCount( 1, $this->requests );
		$this->assertSame( $this->manifest(), get_option( 'wpesu-plugin-wpgraphql-logging' ) );
		$this->assertGreaterThan( time(), (int) get_option( 'wpesu-plugin-wpgraphql-logging-expiry' ) );

		$alloptions = wp_load_alloptions();
		$this->assertArrayNotHasKey( 'wpesu-plugin-wpgraphql-logging', $alloptions );
	}

	public function test_expired_cache_is_refreshed(): void {
		update_option( 'wpesu-plugin-wpgraphql-logging', $this->manifest( [ 'version' => '1.0.0' ] ), false );
		update_option( 'wpesu-plugin-wpgraphql-logging-expiry', time() - 1, false );
		$this->mock_manifest( $this->manifest() );

		$transient = $this->updater()->filter_plugin_update_transient( $this->empty_transient() );

		$this->assertCount( 1, $this->requests );
		$this->assertSame( '999.0.0', $transient->response[ $this->basename ]->new_version );
	}

	public function test_plugins_api_returns_plugin_information(): void {
		$this->mock_manifest( $this->manifest() );
		$args = (object) [ 'slug' => self::SLUG ];

		$info = $this->updater()->filter_plugin_update_info( false, 'plugin_information', $args );

		$this->assertInstanceOf( stdClass::class, $info );
		$this->assertSame( 'WPGraphQL Logging', $info->name );
		$this->assertSame( '999.0.0', $info->version );
		$this->assertSame( 'Changelog', $info->sections['changelog'] );
		$this->assertSame( 'Description', $info->sections['description'] );
	}

	public function test_plugins_api_ignores_other_actions_and_plugins(): void {
		$this->mock_manifest( $this->manifest() );
		$updater = $this->updater();

		$this->assertFalse( $updater->filter_plugin_update_info( false, 'query_plugins', (object) [ 'slug' => self::SLUG ] ) );
		$this->assertFalse( $updater->filter_plugin_update_info( false, 'plugin_information', (object) [ 'slug' => 'hello-dolly' ] ) );
		$this->assertSame( [], $this->requests );
	}

	public function test_plugins_api_passes_through_on_failure(): void {
		$this->mock_manifest( '', 500 );

		$this->assertFalse( $this->updater()->filter_plugin_update_info( false, 'plugin_information', (object) [ 'slug' => self::SLUG ] ) );
	}

	public function test_tested_version_mapping(): void {
		global $wp_version;
		$original = $wp_version;

		$this->mock_manifest( $this->manifest( [ 'tested' => '6.5' ] ) );
		$updater = $this->updater();

		// Site is on a newer major version than tested: report the tested version.
		$wp_version = '6.8.1';
		$info       = $updater->filter_plugin_update_info( false, 'plugin_information', (object) [ 'slug' => self::SLUG ] );
		$this->assertSame( '6.5', $info->tested );

		// Site is on the tested major version: report the site version.
		$wp_version = '6.5.3';
		$info       = $updater->filter_plugin_update_info( false, 'plugin_information', (object) [ 'slug' => self::SLUG ] );
		$this->assertSame( '6.5.3', $info->tested );

		$wp_version = $original;
	}
}
