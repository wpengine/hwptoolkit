<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin;

use WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldCollection;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\BasicConfigurationTab;
use WPGraphQL\Logging\Admin\Settings\Fields\Tab\SettingsTabInterface;
use WPGraphQL\Logging\Admin\Settings\Menu\MenuPage;
use WPGraphQL\Logging\Admin\Settings\SettingsFormManager;

/**
 * SettingsPage class for WPGraphQL Logging.
 *
 * This class handles the registration of the settings page, settings fields, and loading of scripts and styles for the plugin.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class SettingsPage {
	/**
	 * @var string The slug for the plugin menu.
	 */
	public const PLUGIN_MENU_SLUG = 'wpgraphql-logging';

	/**
	 * The field collection.
	 *
	 * @var \WPGraphQL\Logging\Admin\Settings\Fields\SettingsFieldCollection|null
	 */
	protected ?SettingsFieldCollection $field_collection = null;

	/**
	 * The instance of the plugin.
	 *
	 * @var \WPGraphQL\Logging\Admin\SettingsPage|null
	 */
	protected static ?SettingsPage $instance = null;

	/**
	 * Initializes the settings page.
	 */
	public static function init(): ?SettingsPage {
		if ( ! current_user_can( 'manage_options' ) ) {
			return null;
		}

		if ( ! isset( self::$instance ) || ! ( is_a( self::$instance, self::class ) ) ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		/**
		 * Fire off init action.
		 *
		 * @param \WPGraphQL\Logging\Admin\SettingsPage $instance the instance of the plugin class.
		 */
		do_action( 'wpgraphql_logging_settings_init', self::$instance );

		return self::$instance;
	}

	/**
	 * Sets up the settings page by registering hooks.
	 */
	public function setup(): void {
		add_action( 'init', [ $this, 'init_field_collection' ], 10, 0 );
		add_action( 'admin_menu', [ $this, 'register_settings_page' ], 10, 0 );
		add_action( 'admin_init', [ $this, 'register_settings_fields' ], 10, 0 );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_scripts_styles' ], 10, 1 );
	}

	/**
	 * Initialize the field collection.
	 */
	public function init_field_collection(): void {
		$this->field_collection = new SettingsFieldCollection();
	}

	/**
	 * Get the field collection.
	 */
	public function get_field_collection(): ?SettingsFieldCollection {
		return $this->field_collection;
	}

	/**
	 * Registers the settings page.
	 */
	public function register_settings_page(): void {
		$collection = $this->get_field_collection();
		if ( is_null( $collection ) ) {
			return;
		}

		$tabs = $collection->get_tabs();

		$tab_labels = [];
		foreach ( $tabs as $tab_key => $tab ) {
			if ( ! is_a( $tab, SettingsTabInterface::class ) ) {
				continue;
			}

			$tab_labels[ $tab_key ] = $tab::get_label();
		}

		$page = new MenuPage(
			__( 'WPGraphQL Logging Settings', 'wpgraphql-logging' ),
			'WPGraphQL Logging',
			self::PLUGIN_MENU_SLUG,
			$this->get_admin_template(),
			[
				'wpgraphql_logging_main_page_config' => [
					'tabs'        => $tab_labels,
					'current_tab' => $this->get_current_tab(),
				],
			],
		);

		$page->register_page();
	}

	/**
	 * Get the admin template path.
	 *
	 * @return string The path to the admin template file.
	 */
	public function get_admin_template() : string {
		$template_path = trailingslashit( WPGRAPHQL_LOGGING_PLUGIN_DIR ) . 'src/Admin/Settings/Templates/admin.php';
		return (string) apply_filters( 'wpgraphql_logging_admin_template_path', $template_path );
	}

	/**
	 * Registers the settings fields for each tab.
	 */
	public function register_settings_fields(): void {
		$collection = $this->get_field_collection();
		if ( ! isset( $collection ) ) {
			return;
		}
		$settings_manager = new SettingsFormManager( $collection );
		$settings_manager->render_form();
	}

	/**
	 * Get the current tab for the settings page.
	 *
	 * @param array<string, \WPGraphQL\Logging\Admin\Settings\Fields\Tab\SettingsTabInterface> $tabs Optional. The available tabs. If not provided, uses the instance tabs.
	 *
	 * @return string The current tab slug.
	 */
	public function get_current_tab( array $tabs = [] ): string {
		$tabs = $this->get_tabs( $tabs );
		if ( empty( $tabs ) ) {
			return $this->get_default_tab();
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET parameter for tab navigation only, no form processing
		if ( ! isset( $_GET['tab'] ) || ! is_string( $_GET['tab'] ) ) {
			return $this->get_default_tab();
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading GET parameter for tab navigation only, no form processing
		$tab = sanitize_text_field( $_GET['tab'] );

		if ( ! is_string( $tab ) || '' === $tab ) {
			return $this->get_default_tab();
		}

		if ( array_key_exists( $tab, $tabs ) ) {
			return $tab;
		}

		return $this->get_default_tab();
	}

	/**
	 * Get the default tab slug.
	 *
	 * @return string The default tab slug.
	 */
	public function get_default_tab(): string {
		return BasicConfigurationTab::get_name();
	}

	/**
	 * Load scripts and styles for the admin page.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function load_scripts_styles( string $hook_suffix ): void {
		// Only load on our settings page.
		if ( ! str_contains( $hook_suffix, self::PLUGIN_MENU_SLUG ) ) {
			return;
		}

		// Enqueue admin styles if they exist.
		$style_path = trailingslashit( WPGRAPHQL_LOGGING_PLUGIN_URL ) . 'assets/css/settings/wp-graphql-logging-settings.css';
		if ( file_exists( trailingslashit( WPGRAPHQL_LOGGING_PLUGIN_DIR ) . 'assets/css/settings/wp-graphql-logging-settings.css' ) ) {
			wp_enqueue_style(
				'wpgraphql-logging-settings-css',
				$style_path,
				[],
				WPGRAPHQL_LOGGING_VERSION
			);
		}

		// Enqueue admin scripts if they exist.
		$script_path = trailingslashit( WPGRAPHQL_LOGGING_PLUGIN_URL ) . 'assets/js/settings/wp-graphql-logging-settings.js';
		if ( ! file_exists( trailingslashit( WPGRAPHQL_LOGGING_PLUGIN_DIR ) . 'assets/js/settings/wp-graphql-logging-settings.js' ) ) {
			return;
		}

		wp_enqueue_script(
			'wpgraphql-logging-settings-js',
			$script_path,
			[],
			WPGRAPHQL_LOGGING_VERSION,
			true
		);

		do_action( 'wpgraphql_logging_admin_enqueue_scripts', $hook_suffix );
	}

	/**
	 * Get the tabs for the settings page.
	 *
	 * @param array<string, \WPGraphQL\Logging\Admin\Settings\Fields\Tab\SettingsTabInterface> $tabs Optional. The available tabs. If not provided, uses the instance tabs.
	 *
	 * @return array<string, \WPGraphQL\Logging\Admin\Settings\Fields\Tab\SettingsTabInterface> The tabs.
	 */
	protected function get_tabs(array $tabs = []): array {
		if ( ! empty( $tabs ) ) {
			return $tabs;
		}

		$collection = $this->get_field_collection();
		if ( ! is_null( $collection ) ) {
			return $collection->get_tabs();
		}

		return [];
	}
}
