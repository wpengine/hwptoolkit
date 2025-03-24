<?php

declare( strict_types=1 );

namespace HWP\Previews\Reflection\Contracts;

interface Class_Property_Extractor_Interface {

	public function extract_public_properties( string $class_name ): array;

}