<?php

namespace HWP\Previews\Reflection\Contracts;

use ReflectionProperty;

interface Property_Type_Resolver_Interface {

	public function resolve_type( ReflectionProperty $property): string;

}