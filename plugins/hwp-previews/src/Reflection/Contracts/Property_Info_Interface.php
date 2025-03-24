<?php

declare(strict_types=1);

namespace HWP\Previews\Reflection\Contracts;

interface Property_Info_Interface {

	public function get_name(): string;

	public function get_type(): string;

	public function get_description(): string;

}