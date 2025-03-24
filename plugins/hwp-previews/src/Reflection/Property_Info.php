<?php

declare( strict_types=1 );

namespace HWP\Previews\Reflection;

use HWP\Previews\Reflection\Contracts\Property_Info_Interface;

class Property_Info implements Property_Info_Interface {

	private string $name;
	private string $type;
	private string $description;

	public function __construct( string $name, string $type, string $description = '' ) {
		$this->name        = $name;
		$this->type        = $type;
		$this->description = $description;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function get_type(): string {
		return $this->type;
	}

	public function get_description(): string {
		return $this->description;
	}
}