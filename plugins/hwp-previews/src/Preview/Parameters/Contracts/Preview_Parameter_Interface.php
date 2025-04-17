<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Parameters\Contracts;

use WP_Post;

interface Preview_Parameter_Interface {

	public function get_name(): string;

	public function get_description(): string;

	public function get_value( WP_Post $post ): string;

}