<?php

declare(strict_types=1);

namespace HWP\Previews\Settings\Menu;

class Submenu_Page extends Menu_Page {
	/**
	 * The slug name for the parent menu (or the file name of a standard WordPress admin page).
	 *
	 * @var string
	 */
	private string $parent_slug;

	/**
	 * Constructor. Extends the parent class constructor by adding a parent slug.
	 *
	 * @param string               $parent_slug The slug name for the parent menu (or the file name of a standard WordPress admin page).
	 * @param string               $page_title The text to be displayed in the title tags of the page when the menu is selected.
	 * @param string               $menu_title The text to be used for the menu.
	 * @param string               $menu_slug The slug name to refer to this menu by. Should be unique for this menu and only include lowercase alphanumeric, dashes, and underscores characters to be compatible with sanitize_key().
	 * @param string               $template The path of a template file to be used to display the page content.
	 * @param array<string, mixed> $args An array of arguments to be passed to the template.
	 */
	public function __construct(
		string $parent_slug,
		string $page_title,
		string $menu_title,
		string $menu_slug,
		string $template,
		array $args = []
	) {
		$this->parent_slug = $parent_slug;
		parent::__construct( $page_title, $menu_title, $menu_slug, $template, $args );
	}

	/**
	 * Register the submenu page. Should be called on the 'admin_menu' action.
	 */
	public function register_page(): void {
		add_submenu_page(
			$this->parent_slug,
			$this->page_title,
			$this->menu_title,
			'manage_options',
			$this->menu_slug,
			[ $this, 'registration_callback' ]
		);
	}
}
