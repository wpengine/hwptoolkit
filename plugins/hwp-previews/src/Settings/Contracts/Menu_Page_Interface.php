<?php

declare(strict_types=1);

namespace HWP\Previews\Settings\Contracts;

interface Menu_Page_Interface {

	/**
	 * Registers the menu page in the WordPress admin.
	 *
	 * @return void
	 */
	public function register_page(): void;

	/**
	 * Callback function to display the content of the menu page.
	 *
	 * @return void
	 */
	public function registration_callback(): void;

}
