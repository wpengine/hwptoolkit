<?php
/**
 * GraphQL Rule to identify excessive field selection (over-fetching).
 *
 * @package WPGraphQL\Debug\Analysis\Rules
 */

declare(strict_types=1);

namespace WPGraphQL\Debug\Analysis\Rules;

use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Visitor;
use GraphQL\Type\Schema;
use GraphQL\Error\SyntaxError;
use GraphQL\Utils\TypeInfo;
use WPGraphQL\Debug\Analysis\Interfaces\AnalyzerItemInterface;

class ExcessiveFields implements AnalyzerItemInterface {

	/**
	 * @var string|null A descriptive note about the analysis result.
	 */
	protected ?string $internalNote = null;

	/**
	 * Default field threshold.
	 *
	 * This value determines how many fields may be requested
	 * on a single type before the rule considers it "excessive."
	 *
	 * Developers may override this using the
	 * `graphql_debug_excessive_fields_threshold` WordPress filter.
	 *
	 * @var int
	 */
	protected int $fieldThreshold = 15;

	/**
	 * Analyze a GraphQL query for excessive field selections.
	 *
	 * @param string     $query     The raw GraphQL query string.
	 * @param array      $variables Optional query variables.
	 * @param Schema|null $schema   The GraphQL schema (required for type resolution).
	 *
	 * @return array{
	 *     triggered: bool,
	 *     message: string
	 * }
	 */
	public function analyze( string $query, array $variables = [], ?Schema $schema = null ): array {
		if ( null === $schema ) {
			$schema = $this->query_analyzer->get_schema();
		}
		if ( null === $schema ) {
			$this->internalNote = 'Excessive field selection analysis requires a GraphQL schema.';
			return [ 
				'triggered' => false,
				'message' => $this->internalNote,
			];
		}

		$fieldCounts = [];

		try {
			$ast = Parser::parse( $query );
		} catch (SyntaxError $error) {
			$this->internalNote = 'Excessive field selection analysis failed due to GraphQL syntax error: ' . $error->getMessage();
			error_log( 'WPGraphQL Debug Extensions: ' . $this->internalNote );
			return [ 
				'triggered' => false,
				'message' => $this->internalNote,
			];
		}

		// Use TypeInfo to correctly resolve parent types during traversal
		$typeInfo = new TypeInfo( $schema );

		Visitor::visit(
			$ast,
			Visitor::visitWithTypeInfo(
				$typeInfo,
				[ 
					'Field' => function (FieldNode $node) use (&$fieldCounts, $typeInfo) {
						$parentType = $typeInfo->getParentType();
						if ( $parentType ) {
							$typeName = $parentType->name;
							if ( ! isset( $fieldCounts[ $typeName ] ) ) {
								$fieldCounts[ $typeName ] = 0;
							}
							$fieldCounts[ $typeName ]++;
						}
					},
				]
			)
		);

		$threshold = apply_filters(
			'graphql_debug_rule_excessive_fields_threshold',
			$this->fieldThreshold,
			$query,
			$variables,
			$schema
		);

		$triggered = false;
		$details = [];
		foreach ( $fieldCounts as $type => $count ) {
			if ( $count > $threshold ) {
				$triggered = true;
				$details[] = sprintf(
					'Type "%s" selects %d fields, exceeding the threshold of %d.',
					$type,
					$count,
					$threshold
				);
			}
		}

		$message = $triggered
			? 'Over-fetching detected: ' . implode( ' ', $details )
			: 'No excessive fields detected.';

		$this->internalNote = $message;

		return [ 
			'triggered' => $triggered,
			'message' => $message,
		];
	}

	/**
	 * Return the unique key for this analyzer rule.
	 *
	 * @return string
	 */
	public function getKey(): string {
		return 'excessiveFieldsRule';
	}
}
