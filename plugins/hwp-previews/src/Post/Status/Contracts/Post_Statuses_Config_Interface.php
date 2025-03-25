<?php

declare(strict_types=1);

namespace HWP\Previews\Post\Status\Contracts;

interface Post_Statuses_Config_Interface {

	public function get_post_statuses(): array;

	public function is_post_status_applicable( string $post_status ): bool;

}
