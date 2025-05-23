<?php

declare(strict_types=1);

namespace HWP\Previews\Admin\Settings\Contracts;

interface General_Settings_Interface {
	/**
	 * Gets the 'enable_all_statuses_as_parent' setting value.
	 */
	public function enable_all_statuses_as_parent(): bool;

	/**
	 * Gets the 'enable_unique_post_status' setting value.
	 */
	public function enable_unique_post_status(): bool;
}
