<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Updates;

use stdClass;

/**
 * Pulls plugin updates from the WP Engine plugin updates API.
 *
 * Ported from FaustWP's updater (plugins/faustwp/includes/updates in
 * wpengine/faustjs, GPL-2.0-or-later, Copyright WP Engine). It uses the same
 * endpoint, cache option names and response mapping, adapted to this plugin's
 * class-based, strictly typed style.
 *
 * The plugin manifest is read from
 * https://wpe-plugin-updates.wpengine.com/{plugin_slug}/info.json and cached
 * in non-autoloaded options for CACHE_TTL seconds.
 *
 * @package WPGraphQL\Logging
 *
 * @since 1.1.0
 */
class PluginUpdater {
	/**
	 * Base URL of the WP Engine plugin updates API.
	 *
	 * @var string
	 */
	public const API_URL = 'https://wpe-plugin-updates.wpengine.com/';

	/**
	 * How long (in seconds) to cache the plugin manifest before fetching it again (5 hours).
	 *
	 * @var int
	 */
	public const CACHE_TTL = 18000;

	/**
	 * Prefix for the option names used to cache the manifest.
	 *
	 * @var string
	 */
	public const CACHE_OPTION_PREFIX = 'wpesu-plugin-';

	/**
	 * The slug registered with the WP Engine plugin updates API.
	 *
	 * Must match the key in https://wpe-plugin-updates.wpengine.com/plugins.json.
	 *
	 * @var string
	 */
	protected string $plugin_slug;

	/**
	 * The plugin basename, e.g. "wpgraphql-logging/wpgraphql-logging.php".
	 *
	 * @var string
	 */
	protected string $plugin_basename;

	/**
	 * The plugin directory name, used as the slug in the plugins_api modal.
	 *
	 * @var string
	 */
	protected string $plugin_dirname = '';

	/**
	 * The installed plugin version.
	 *
	 * @var string
	 */
	protected string $plugin_version = '';

	/**
	 * Constructor.
	 *
	 * @param string $plugin_slug     The slug registered with the WP Engine plugin updates API.
	 * @param string $plugin_basename The plugin basename as returned by plugin_basename().
	 */
	public function __construct( string $plugin_slug, string $plugin_basename ) {
		$this->plugin_slug     = $plugin_slug;
		$this->plugin_basename = $plugin_basename;
	}

	/**
	 * Create an updater for the given plugin and register its hooks.
	 *
	 * Intended to be called on `admin_init`.
	 *
	 * @param string $plugin_slug     The slug registered with the WP Engine plugin updates API.
	 * @param string $plugin_basename The plugin basename as returned by plugin_basename().
	 *
	 * @return self|null The updater, or null if the plugin could not be found.
	 */
	public static function check_for_updates( string $plugin_slug, string $plugin_basename ): ?self {
		$updater = new self( $plugin_slug, $plugin_basename );

		return $updater->register() ? $updater : null;
	}

	/**
	 * Resolve the installed plugin and register the update filters.
	 *
	 * @return bool Whether the filters were registered.
	 */
	public function register(): bool {
		if ( '' === $this->plugin_slug || '' === $this->plugin_basename ) {
			return false;
		}

		if ( ! $this->load_plugin_data() ) {
			return false;
		}

		add_filter( 'plugins_api', [ $this, 'filter_plugin_update_info' ], 20, 3 );
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'filter_plugin_update_transient' ], 10, 1 );

		return true;
	}

	/**
	 * Add this plugin to the update_plugins transient when a newer version is available.
	 *
	 * @param mixed $transient The value of the `update_plugins` site transient.
	 *
	 * @return mixed The (possibly) modified transient.
	 */
	public function filter_plugin_update_transient( mixed $transient ): mixed {
		if ( ! $transient instanceof stdClass ) {
			return $transient;
		}

		$result = $this->fetch_plugin_info();

		if ( null === $result || ! version_compare( $this->plugin_version, $result->version, '<' ) ) {
			return $transient;
		}

		return $this->add_update_to_transient( $transient, $result );
	}

	/**
	 * Provide the plugin information shown in the "View details" modal.
	 *
	 * @param mixed  $res    The result object or array. Default false.
	 * @param string $action The type of information being requested from the Plugin Installation API.
	 * @param mixed  $args   Plugin API arguments.
	 *
	 * @return mixed The plugin information, or the unmodified result.
	 */
	public function filter_plugin_update_info( mixed $res, string $action, mixed $args ): mixed {
		if ( 'plugin_information' !== $action ) {
			return $res;
		}

		if ( ! is_object( $args ) || ! isset( $args->slug ) || $this->plugin_dirname !== $args->slug ) {
			return $res;
		}

		$result = $this->fetch_plugin_info();

		if ( null === $result ) {
			return $res;
		}

		return $this->parse_plugin_info( $result );
	}

	/**
	 * The URL of this plugin's manifest on the updates API.
	 */
	public function get_manifest_url(): string {
		return trailingslashit( self::API_URL ) . trailingslashit( $this->plugin_slug ) . 'info.json';
	}

	/**
	 * Name of the option that caches the manifest body.
	 */
	public function get_cache_option_name(): string {
		return self::CACHE_OPTION_PREFIX . sanitize_title( $this->plugin_dirname );
	}

	/**
	 * Name of the option that stores the cache expiry timestamp.
	 */
	public function get_cache_expiry_option_name(): string {
		return $this->get_cache_option_name() . '-expiry';
	}

	/**
	 * Look up the installed plugin and store its directory name and version.
	 *
	 * @return bool Whether the plugin was found.
	 */
	protected function load_plugin_data(): bool {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();

		if ( ! isset( $plugins[ $this->plugin_basename ] ) ) {
			return false;
		}

		$this->plugin_dirname = dirname( $this->plugin_basename );
		$this->plugin_version = (string) $plugins[ $this->plugin_basename ]['Version'];

		return true;
	}

	/**
	 * Fetch the plugin manifest from the cache or the updates API.
	 *
	 * @return \stdClass|null The decoded manifest, or null if it is unavailable or invalid.
	 */
	protected function fetch_plugin_info(): ?stdClass {
		$body = $this->get_cached_manifest();

		if ( null === $body ) {
			$body = $this->request_manifest();
		}

		if ( null === $body ) {
			return null;
		}

		$decoded = json_decode( $body );

		if ( ! $decoded instanceof stdClass || ! $this->is_valid_manifest( $decoded ) ) {
			return null;
		}

		return $decoded;
	}

	/**
	 * Return the cached manifest body if it has not expired.
	 */
	protected function get_cached_manifest(): ?string {
		$expiry = (int) get_option( $this->get_cache_expiry_option_name(), 0 );
		$body   = get_option( $this->get_cache_option_name() );

		if ( time() > $expiry || ! is_string( $body ) || '' === $body ) {
			return null;
		}

		return $body;
	}

	/**
	 * Request the manifest from the updates API and cache a successful response.
	 */
	protected function request_manifest(): ?string {
		// Admin-only and cached for CACHE_TTL, so the 10s timeout used by FaustWP is acceptable here.
		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get
		$response = wp_remote_get(
			$this->get_manifest_url(),
			[
				'timeout' => 10, // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
				'headers' => [
					'Accept' => 'application/json',
				],
			]
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( '' === $body ) {
			return null;
		}

		update_option( $this->get_cache_expiry_option_name(), time() + self::CACHE_TTL, false );
		update_option( $this->get_cache_option_name(), $body, false );

		return $body;
	}

	/**
	 * Check that the manifest has the fields needed to offer an update.
	 *
	 * @param \stdClass $manifest The decoded manifest.
	 */
	protected function is_valid_manifest( stdClass $manifest ): bool {
		foreach ( [ 'name', 'slug', 'version', 'download_link' ] as $field ) {
			if ( ! isset( $manifest->$field ) || ! is_string( $manifest->$field ) || '' === $manifest->$field ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Add the update offer for this plugin to the update_plugins transient.
	 *
	 * @param \stdClass $transient The `update_plugins` site transient.
	 * @param \stdClass $manifest  The decoded manifest.
	 */
	protected function add_update_to_transient( stdClass $transient, stdClass $manifest ): stdClass {
		$response = isset( $transient->response ) && is_array( $transient->response ) ? $transient->response : [];
		$checked  = isset( $transient->checked ) && is_array( $transient->checked ) ? $transient->checked : [];

		$response[ $this->plugin_basename ] = $this->parse_plugin_info( $manifest );
		$checked[ $this->plugin_basename ]  = $manifest->version;

		$transient->response = $response;
		$transient->checked  = $checked;

		return $transient;
	}

	/**
	 * Map the manifest to the object WordPress expects for updates and plugin information.
	 *
	 * @param \stdClass $manifest The decoded manifest.
	 */
	protected function parse_plugin_info( stdClass $manifest ): stdClass {
		$res                = new stdClass();
		$res->name          = $manifest->name;
		$res->slug          = $manifest->slug;
		$res->version       = $manifest->version;
		$res->requires      = $manifest->requires ?? '';
		$res->requires_php  = $manifest->requires_php ?? '';
		$res->download_link = $manifest->download_link;
		$res->trunk         = $manifest->download_link;
		$res->new_version   = $manifest->version;
		$res->plugin        = $this->plugin_basename;
		$res->package       = $manifest->download_link;
		$res->tested        = $this->get_tested_version( (string) ( $manifest->tested ?? '' ) );
		$res->sections      = [
			'description' => (string) ( $manifest->sections->description ?? '' ),
			'changelog'   => (string) ( $manifest->sections->changelog ?? '' ),
		];

		return $res;
	}

	/**
	 * Work out the "tested up to" version to report.
	 *
	 * The plugin information modal and core update table compare versions
	 * strictly. If the site runs a newer major version than the plugin was
	 * tested with, report the plugin's tested version; otherwise report the
	 * site's exact version to avoid a false "untested" warning.
	 *
	 * @param string $tested The "tested up to" version from the manifest.
	 */
	protected function get_tested_version( string $tested ): string {
		global $wp_version;

		$site_version = is_string( $wp_version ) ? $wp_version : '';

		if ( '' === $tested ) {
			return $site_version;
		}

		return 1 === version_compare( substr( $site_version, 0, 3 ), $tested ) ? $tested : $site_version;
	}
}
