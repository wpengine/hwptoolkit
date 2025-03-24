<?php

declare( strict_types=1 );

namespace HWP\Previews\Reflection;

use HWP\Previews\Reflection\Contracts\Property_Type_Resolver_Interface;
use ReflectionProperty;

class Property_Type_Resolver implements Property_Type_Resolver_Interface {

	public function resolve_type( ReflectionProperty $property ): string {
		$nativeType = $this->resolve_native_type( $property );
		if ( $nativeType ) {
			return $nativeType;
		}

		return $this->resolve_doc_block_type( $property );
	}

	public function resolve_native_type( ReflectionProperty $property ): string {
		if ( $property->hasType() ) {
			$type = $property->getType();

			return $type->getName();
		}

		return '';
	}

	private function resolve_doc_block_type( ReflectionProperty $property ): string {
		$docComment = $property->getDocComment();
		if ( ! $docComment ) {
			return '';
		}

		if ( preg_match( '/@var\s+([^\s]+)/', $docComment, $matches ) ) {
			return $matches[1];
		}

		return '';
	}
}