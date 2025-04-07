<?php

declare( strict_types=1 );

namespace HWP\Previews\Preview\Parameters;

use HWP\Previews\Preview\Parameters\Contracts\Preview_Parameter_Interface;

abstract class Abstract_Preview_Parameter implements Preview_Parameter_Interface {

	protected string $name;

	protected string $description;

	public function __construct( string $name, string $description ) {
		$this->name        = $name;
		$this->description = $description;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function get_description(): string {
		return $this->description;
	}

}