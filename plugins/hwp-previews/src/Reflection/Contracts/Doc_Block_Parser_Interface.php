<?php

declare( strict_types=1 );

namespace HWP\Previews\Reflection\Contracts;

interface Doc_Block_Parser_Interface {

	public function parse_description( $doc_comment ): string;

}