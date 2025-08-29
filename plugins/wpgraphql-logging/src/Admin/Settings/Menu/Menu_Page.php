<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\Settings\Menu;

/**
 * Menu class for WordPress admin settings page.
 *
 * This class is responsible for creating a menu page in the WordPress admin area.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class Menu_Page {
	/**
	 * The title of the page.
	 *
	 * @var string
	 */
	protected string $page_title;

	/**
	 * The title of the menu.
	 *
	 * @var string
	 */
	protected string $menu_title;

	/**
	 * The slug name to refer to this menu by (should be unique for this menu).
	 *
	 * @var string
	 */
	protected string $menu_slug;

	/**
	 * The path of a template file to be used to display the page content.
	 *
	 * @var string
	 */
	protected string $template;

	/**
	 * The array of arguments that will be passed to the template.
	 * The key is the name of the query var for the arguments, and the value is the array of a key value arguments to set into the query var.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	protected array $args;

	/**
	 * Constructor.
	 *
	 * @param string                              $page_title The text to be displayed in the title tags of the page when the menu is selected.
	 * @param string                              $menu_title The text to be used for the menu.
	 * @param string                              $menu_slug The slug name to refer to this menu by. Should be unique for this menu and only include lowercase alphanumeric, dashes, and underscores characters to be compatible with sanitize_key().
	 * @param string                              $template The template that will be included in the callback.
	 * @param array<string, array<string, mixed>> $args The args array for the template.
	 */
	public function __construct(
		string $page_title,
		string $menu_title,
		string $menu_slug,
		string $template,
		array $args = []
	) {
		$this->page_title = $page_title;
		$this->menu_title = $menu_title;
		$this->menu_slug  = $menu_slug;
		$this->template   = $template;
		$this->args       = $args;
	}

	/**
	 * Registers the menu page in the WordPress admin.
	 */
	public function register_page(): void {
		add_submenu_page(
			'options-general.php',
			$this->page_title,
			$this->menu_title,
			'manage_options',
			$this->menu_slug,
			[ $this, 'registration_callback' ]
		);
	}

	/**
	 * Callback function to display the content of the menu page.
	 */
	public function registration_callback(): void {
		if ( empty( $this->template ) || ! file_exists( $this->template ) ) {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html__( 'The WPGraphQL Logging Settings template does not exist.', 'wpgraphql-logging' )
			);

			return;
		}
		foreach ( $this->args as $query_var => $args ) {
			set_query_var( $query_var, $args );
		}

        // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable -- $this->template is validated and defined within the class
		include_once $this->template;
	}
}
