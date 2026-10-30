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
	protected string $plugin_dirname;

	/**
	 * The installed plugin version. Null until looked up, empty if the plugin is not installed.
	 *
	 * @var string|null
	 */
	protected ?string $plugin_version = null;

	/**
	 * The manifest fetched during this request, so it is requested at most once per request.
	 *
	 * Null until fetched, false if it could not be fetched.
	 *
	 * @var array<string, string>|false|null
	 */
	protected array|false|null $manifest = null;

	/**
	 * Updaters registered during this request, keyed by plugin basename.
	 *
	 * @var array<string, self>
	 */
	protected static array $instances = [];

	/**
	 * Constructor.
	 *
	 * @param string $plugin_slug     The slug registered with the WP Engine plugin updates API.
	 * @param string $plugin_basename The plugin basename as returned by plugin_basename().
	 */
	public function __construct( string $plugin_slug, string $plugin_basename ) {
		$this->plugin_slug     = $plugin_slug;
		$this->plugin_basename = $plugin_basename;
		$this->plugin_dirname  = dirname( $plugin_basename );
	}

	/**
	 * Create an updater for the given plugin and register its hooks.
	 *
	 * Safe to call more than once per request: later calls return the updater
	 * that is already registered for the plugin.
	 *
	 * @param string $plugin_slug     The slug registered with the WP Engine plugin updates API.
	 * @param string $plugin_basename The plugin basename as returned by plugin_basename().
	 *
	 * @return self|null The updater, or null if the slug or basename is empty.
	 */
	public static function check_for_updates( string $plugin_slug, string $plugin_basename ): ?self {
		if ( '' === $plugin_slug || '' === $plugin_basename ) {
			return null;
		}

		if ( ! isset( self::$instances[ $plugin_basename ] ) ) {
			$updater = new self( $plugin_slug, $plugin_basename );
			$updater->register();
			self::$instances[ $plugin_basename ] = $updater;
		}

		return self::$instances[ $plugin_basename ];
	}

	/**
	 * Register the update filters.
	 */
	public function register(): void {
		add_filter( 'plugins_api', [ $this, 'filter_plugin_update_info' ], 20, 3 );
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'filter_plugin_update_transient' ], 10, 1 );
	}

	/**
	 * Remove the update filters.
	 */
	public function unregister(): void {
		remove_filter( 'plugins_api', [ $this, 'filter_plugin_update_info' ], 20 );
		remove_filter( 'pre_set_site_transient_update_plugins', [ $this, 'filter_plugin_update_transient' ], 10 );
		unset( self::$instances[ $this->plugin_basename ] );
	}

	/**
	 * Report this plugin's update status in the update_plugins transient.
	 *
	 * Adds the plugin to `response` when a newer version is available, or to
	 * `no_update` when it is current (which lets core offer auto-updates).
	 *
	 * @param mixed $transient The value of the `update_plugins` site transient.
	 *
	 * @return mixed The (possibly) modified transient.
	 */
	public function filter_plugin_update_transient( mixed $transient ): mixed {
		if ( ! $transient instanceof stdClass ) {
			return $transient;
		}

		$installed_version = $this->get_installed_version();

		if ( '' === $installed_version ) {
			return $transient;
		}

		$manifest = $this->fetch_plugin_info();

		if ( null === $manifest ) {
			return $transient;
		}

		$has_update = version_compare( $installed_version, $manifest['version'], '<' );

		return $this->set_transient_item( $transient, $this->parse_plugin_info( $manifest ), $has_update );
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

		$manifest = $this->fetch_plugin_info();

		if ( null === $manifest ) {
			return $res;
		}

		return $this->parse_plugin_info( $manifest );
	}

	/**
	 * The URL of this plugin's manifest on the updates API.
	 */
	public function get_manifest_url(): string {
		return self::API_URL . $this->plugin_slug . '/info.json';
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
	 * Look up the installed plugin version.
	 *
	 * Done lazily, only when an update check runs, because get_plugins() reads
	 * every plugin's headers from disk.
	 *
	 * @return string The installed version, or an empty string if the plugin is not installed.
	 */
	protected function get_installed_version(): string {
		if ( null !== $this->plugin_version ) {
			return $this->plugin_version;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$version              = get_plugins()[ $this->plugin_basename ]['Version'] ?? '';
		$this->plugin_version = is_string( $version ) ? $version : '';

		return $this->plugin_version;
	}

	/**
	 * Fetch the plugin manifest from the cache or the updates API.
	 *
	 * The result, including a failure, is kept for the rest of the request so
	 * the API is called at most once even though core saves the update
	 * transient more than once per check.
	 *
	 * @return array<string, string>|null The manifest, or null if it is unavailable or invalid.
	 */
	protected function fetch_plugin_info(): ?array {
		if ( null === $this->manifest ) {
			$this->manifest = $this->get_cached_manifest() ?? $this->request_manifest() ?? false;
		}

		return false === $this->manifest ? null : $this->manifest;
	}

	/**
	 * Return the cached manifest if it has not expired and is still valid.
	 *
	 * @return array<string, string>|null
	 */
	protected function get_cached_manifest(): ?array {
		$expiry = get_option( $this->get_cache_expiry_option_name(), 0 );
		$body   = get_option( $this->get_cache_option_name() );

		if ( ! is_numeric( $expiry ) || time() > (int) $expiry || ! is_string( $body ) ) {
			return null;
		}

		return $this->decode_manifest( $body );
	}

	/**
	 * Request the manifest from the updates API and cache it if it is valid.
	 *
	 * @return array<string, string>|null
	 */
	protected function request_manifest(): ?array {
		// Only runs during update checks and is cached for CACHE_TTL, so the 10s timeout used by FaustWP is acceptable here.
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

		$body     = wp_remote_retrieve_body( $response );
		$manifest = $this->decode_manifest( $body );

		if ( null === $manifest ) {
			return null;
		}

		update_option( $this->get_cache_expiry_option_name(), time() + self::CACHE_TTL, false );
		update_option( $this->get_cache_option_name(), $body, false );

		return $manifest;
	}

	/**
	 * Decode a manifest body into sanitized strings.
	 *
	 * @param string $body The raw JSON body.
	 *
	 * @return array<string, string>|null The manifest, or null if it is invalid or missing a required field.
	 */
	protected function decode_manifest( string $body ): ?array {
		$data = json_decode( $body );

		if ( ! $data instanceof stdClass ) {
			return null;
		}

		$sections = isset( $data->sections ) && $data->sections instanceof stdClass ? $data->sections : new stdClass();

		$manifest = [
			'name'          => sanitize_text_field( $this->get_string( $data, 'name' ) ),
			'slug'          => sanitize_key( $this->get_string( $data, 'slug' ) ),
			'version'       => sanitize_text_field( $this->get_string( $data, 'version' ) ),
			'download_link' => esc_url_raw( $this->get_string( $data, 'download_link' ), [ 'https', 'http' ] ),
			'requires'      => sanitize_text_field( $this->get_string( $data, 'requires' ) ),
			'requires_php'  => sanitize_text_field( $this->get_string( $data, 'requires_php' ) ),
			'tested'        => sanitize_text_field( $this->get_string( $data, 'tested' ) ),
			'description'   => wp_kses_post( $this->get_string( $sections, 'description' ) ),
			'changelog'     => wp_kses_post( $this->get_string( $sections, 'changelog' ) ),
		];

		foreach ( [ 'name', 'slug', 'version', 'download_link' ] as $required ) {
			if ( '' === $manifest[ $required ] ) {
				return null;
			}
		}

		return $manifest;
	}

	/**
	 * Put the plugin's item in `response` or `no_update` and remove it from the other list.
	 *
	 * @param \stdClass $transient  The `update_plugins` site transient.
	 * @param \stdClass $item       The plugin update item.
	 * @param bool      $has_update Whether a newer version is available.
	 */
	protected function set_transient_item( stdClass $transient, stdClass $item, bool $has_update ): stdClass {
		$add_to    = $has_update ? 'response' : 'no_update';
		$remove_at = $has_update ? 'no_update' : 'response';

		$add_list = isset( $transient->$add_to ) && is_array( $transient->$add_to ) ? $transient->$add_to : [];

		$add_list[ $this->plugin_basename ] = $item;
		$transient->$add_to                 = $add_list;

		if ( isset( $transient->$remove_at ) && is_array( $transient->$remove_at ) ) {
			$remove_list = $transient->$remove_at;
			unset( $remove_list[ $this->plugin_basename ] );
			$transient->$remove_at = $remove_list;
		}

		return $transient;
	}

	/**
	 * Map the manifest to the object WordPress expects for updates and plugin information.
	 *
	 * @param array<string, string> $manifest The decoded manifest.
	 */
	protected function parse_plugin_info( array $manifest ): stdClass {
		$res                = new stdClass();
		$res->name          = $manifest['name'];
		$res->slug          = $manifest['slug'];
		$res->version       = $manifest['version'];
		$res->new_version   = $manifest['version'];
		$res->requires      = $manifest['requires'];
		$res->requires_php  = $manifest['requires_php'];
		$res->tested        = $this->get_tested_version( $manifest['tested'] );
		$res->download_link = $manifest['download_link'];
		$res->trunk         = $manifest['download_link'];
		$res->package       = $manifest['download_link'];
		$res->plugin        = $this->plugin_basename;
		$res->sections      = [
			'description' => $manifest['description'],
			'changelog'   => $manifest['changelog'],
		];

		return $res;
	}

	/**
	 * Read a scalar field from a decoded JSON object as a string.
	 *
	 * @param \stdClass $data  The decoded JSON object.
	 * @param string    $field The field name.
	 *
	 * @return string The value, or an empty string if it is missing or not a scalar.
	 */
	protected function get_string( stdClass $data, string $field ): string {
		$value = $data->$field ?? '';

		return is_scalar( $value ) ? (string) $value : '';
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
