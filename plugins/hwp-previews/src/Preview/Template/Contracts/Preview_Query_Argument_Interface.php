<?php

declare(strict_types=1);

namespace HWP\Previews\Preview\Template\Contracts;

interface Preview_Query_Argument_Interface {

	public static function get_template_query_var(): string ;
	public function set_template_query_var(): void;

}