<?php
/**
 * GraphQL Query Complexity Calculator.
 *
 * @package WPGraphQL\Debug\Analysis\Metrics
 */

declare(strict_types=1);

namespace WPGraphQL\Debug\Analysis\Metrics;

use GraphQL\Language\AST\BooleanValueNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\FragmentSpreadNode;
use GraphQL\Language\AST\InlineFragmentNode;
use GraphQL\Language\AST\VariableNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Visitor;
use GraphQL\Type\Schema;
use GraphQL\Error\SyntaxError;

/**
 * Class Complexity
 *
 * Calculates the complexity of a GraphQL query based on field, fragment, and inline fragment counts,
 * with optional consideration for @skip and @include directives.
 */
class Complexity {

	/**
	 * Calculates the estimated complexity of a GraphQL query.
	 *
	 * This calculation counts each Field, FragmentSpread, and InlineFragment as 1 unit of complexity.
	 * It also attempts to respect @skip and @include directives based on provided variables.
	 *
	 * @param string      $query     The GraphQL query string.
	 * @param array       $variables Optional: Variables provided with the query, used for directive evaluation.
	 * @param Schema|null $schema    Optional: The GraphQL schema, useful if advanced directive resolution (beyond simple boolean/variable check) is needed.
	 * @return array The calculated complexity.
	 * @throws SyntaxError If the query string is invalid and cannot be parsed.
	 */
	public function calculate( string $query, array $variables = [], ?Schema $schema = null ): array {
		try {
			$ast = Parser::parse( $query );
		} catch (SyntaxError $error) {
			throw $error;
		}

		$complexity = 0;

		Visitor::visit(
			$ast,
			[ 
				'enter' => function ($node) use (&$complexity, $variables, $schema) {
					// Count Field nodes
					if ( $node instanceof FieldNode ) {
						$include = true;

						// Handle @skip and @include directives
						if ( ! empty( $node->directives ) ) {
							foreach ( $node->directives as $directive ) {
								$name = $directive->name->value;
								$ifArg = null;

								foreach ( $directive->arguments as $arg ) {
									if ( 'if' === $arg->name->value ) {
										$ifArg = $arg->value;
										break;
									}
								}

								$ifValue = true; // Default behavior if 'if' argument is missing or not a boolean/variable.
		
								if ( $ifArg instanceof VariableNode ) {
									$varName = $ifArg->name->value;
									$ifValue = $variables[ $varName ] ?? true;
								} elseif ( $ifArg instanceof BooleanValueNode ) {
									// Use the boolean literal value
									$ifValue = $ifArg->value;
								}

								if ( 'skip' === $name && true === $ifValue ) {
									$include = false;
									break;
								}
								if ( 'include' === $name && false === $ifValue ) {
									$include = false;
									break;
								}
							}
						}

						if ( $include ) {
							$complexity += 1;
						}
					} elseif ( $node instanceof FragmentSpreadNode ) {
						// Count FragmentSpread nodes
						$complexity += 1;
					} elseif ( $node instanceof InlineFragmentNode ) {
						// Count InlineFragment nodes
						$complexity += 1;
					}
				},
			]
		);

		$value = $complexity;

		return [ 
			'value' => $value,
			'note' => $this->getComplexityNote( $value ),
		];
	}

	/**
	 * Determines the descriptive note for the complexity value based on predefined ranges.
	 *
	 * @param int|null $complexityValue The calculated complexity value.
	 * @return string The descriptive note.
	 */
	private function getComplexityNote( ?int $complexityValue ): string {
		if ( ! is_numeric( $complexityValue ) ) {
			return 'Complexity could not be determined.';
		}

		if ( $complexityValue <= 20 ) {
			return 'Low complexity, excellent for performance.';
		} elseif ( $complexityValue <= 50 ) {
			return 'Moderate complexity, generally good for most applications.';
		} elseif ( $complexityValue <= 100 ) {
			return 'High complexity, consider optimizing larger queries for better performance.';
		} else {
			return 'Very high complexity, significant optimization is highly recommended to prevent performance issues.';
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getKey(): string {
		return 'complexity';
	}
}
