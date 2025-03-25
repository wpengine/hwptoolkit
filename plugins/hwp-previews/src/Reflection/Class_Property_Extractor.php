<?php

declare( strict_types=1 );

namespace HWP\Previews\Reflection;

use Exception;
use HWP\Previews\Reflection\Contracts\Class_Property_Extractor_Interface;
use HWP\Previews\Reflection\Contracts\Doc_Block_Parser_Interface;
use HWP\Previews\Reflection\Contracts\Property_Type_Resolver_Interface;
use ReflectionClass;
use ReflectionProperty;

class Class_Property_Extractor implements Class_Property_Extractor_Interface {

	private Property_Type_Resolver_Interface $type_resolver;

	private Doc_Block_Parser_Interface $doc_block_parser;

	public function __construct(
		Property_Type_Resolver_Interface $type_resolver,
		Doc_Block_Parser_Interface $doc_block_parser
	) {
		$this->type_resolver    = $type_resolver;
		$this->doc_block_parser = $doc_block_parser;
	}

	/**
	 * @param class-string $class_name
	 *
	 * @return Property_Info[]
	 */
	public function extract_public_properties( string $class_name ): array {
		try {
			$reflection = new ReflectionClass( $class_name );
		} catch ( Exception $e ) {
			error_log( $e->getMessage() );

			return [];
		}

		$properties        = $reflection->getProperties( ReflectionProperty::IS_PUBLIC );
		$public_properties = [];

		foreach ( $properties as $property ) {
			if ( $property->isStatic() ) {
				continue;
			}

			$type        = $this->type_resolver->resolve_type( $property );
			$description = $this->doc_block_parser->parse_description( $property->getDocComment() );

			$property_info = new Property_Info(
				$property->getName(),
				$type,
				$description
			);

			$public_properties[ $property->getName() ] = $property_info;
		}

		return $public_properties;
	}
}