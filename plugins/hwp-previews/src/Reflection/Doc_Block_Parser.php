<?php

declare( strict_types=1 );

namespace HWP\Previews\Reflection;

use HWP\Previews\Reflection\Contracts\Doc_Block_Parser_Interface;

class Doc_Block_Parser implements Doc_Block_Parser_Interface {

	public function parse_description( $doc_comment ): string {
		if ( ! $doc_comment ) {
			return '';
		}

		$description    = '';
		$lines          = preg_split( '/\r\n|\r|\n/', $doc_comment );
		$in_annotations = false;

		foreach ( $lines as $line ) {
			$line = trim( preg_replace( '/^\s*\/\*+\s*|\s*\*+\/\s*|\s*\*\s*/m', '', $line ) );

			if ( empty( $line ) ) {
				continue;
			}

			if ( strpos( $line, '@' ) === 0 ) {
				$in_annotations = true;
			} elseif ( ! $in_annotations ) {
				$description .= ' ' . $line;
			}
		}

		return trim( $description );
	}
}