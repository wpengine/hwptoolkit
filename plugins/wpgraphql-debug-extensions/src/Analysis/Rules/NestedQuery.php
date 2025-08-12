<?php
/**
 * Corrected Rule to detect and warn about overly nested GraphQL queries,
 * with proper fragment handling.
 *
 * @package WPGraphQL\Debug\Analysis\Rules
 */

declare(strict_types=1);

namespace WPGraphQL\Debug\Analysis\Rules;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\FragmentDefinitionNode;
use GraphQL\Language\AST\FragmentSpreadNode;
use GraphQL\Language\AST\InlineFragmentNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Language\AST\SelectionSetNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Error\SyntaxError;
use WPGraphQL\Debug\Analysis\Interfaces\AnalyzerItemInterface;

class NestedQuery implements AnalyzerItemInterface {

    protected int $maxDepth;
    protected ?string $message = null;

    /**
     * @param int $maxDepth The maximum allowed nesting depth.
     */
    public function __construct( int $maxDepth = 8 ) {
        $this->maxDepth = $maxDepth;
    }

    /**
     * @inheritDoc
     */
    public function analyze( string $query, array $variables = [], ?Schema $schema = null ): array {
        $triggered = false;
        $maxDepthReached = 0;

        try {
            /** @var DocumentNode $ast */
            $ast = Parser::parse( $query );
        } catch ( SyntaxError $error ) {
            $this->message = 'Failed to analyze nesting depth due to GraphQL syntax error: ' . $error->getMessage();
            return [
                'triggered' => false,
                'message'   => $this->message,
                'details'   => [ 'maxDepthReached' => 0 ],
            ];
        }

        // Store fragment definitions in a map for easy lookup
        $fragments = [];
        foreach ( $ast->definitions as $definition ) {
            if ( $definition instanceof FragmentDefinitionNode ) {
                $fragments[ $definition->name->value ] = $definition;
            }
        }

        // Find the operation definition and analyze its depth
        foreach ( $ast->definitions as $definition ) {
            if ( $definition instanceof OperationDefinitionNode ) {
                // Start the recursion with a depth of 1, as the first field is the first level of nesting.
                $currentDepth = $this->getSelectionSetDepth( $definition->selectionSet, $fragments, 1 );
                $maxDepthReached = max( $maxDepthReached, $currentDepth );
            }
        }

        // Use >= to trigger the rule when the max depth is reached or exceeded.
        if ( $maxDepthReached >= $this->maxDepth ) {
            $triggered = true;
            $this->message = sprintf(
                'Nested query depth of %d reached or exceeded the configured maximum of %d.',
                $maxDepthReached,
                $this->maxDepth
            );
        } else {
            $this->message = sprintf(
                'Nested query depth of %d is within the allowed limit of %d.',
                $maxDepthReached,
                $this->maxDepth
            );
        }

        return [
            'triggered' => $triggered,
            'message'   => $this->message,
            'details'   => [
                'maxDepthReached' => $maxDepthReached,
                'maxAllowed'      => $this->maxDepth,
            ],
        ];
    }

    /**
     * Recursively calculates the maximum depth of a SelectionSet, including fragments.
     *
     * @param SelectionSetNode $selectionSet The selection set to analyze.
     * @param array $fragments Map of fragment definitions.
     * @param int $currentDepth The current depth of the traversal.
     *
     * @return int The maximum depth found.
     */
    protected function getSelectionSetDepth( SelectionSetNode $selectionSet, array $fragments, int $currentDepth = 0 ): int {
        $maxDepth = $currentDepth;

        foreach ( $selectionSet->selections as $selection ) {
            $selectionMaxDepth = 0;

            if ( $selection instanceof FieldNode && $selection->selectionSet instanceof SelectionSetNode ) {
                $selectionMaxDepth = $this->getSelectionSetDepth( $selection->selectionSet, $fragments, $currentDepth + 1 );
            }

            if ( $selection instanceof FragmentSpreadNode ) {
                // If a fragment spread is found, get the fragment definition and recurse.
                if ( isset( $fragments[ $selection->name->value ] ) ) {
                    /** @var FragmentDefinitionNode $fragmentDefinition */
                    $fragmentDefinition = $fragments[ $selection->name->value ];
                    // The depth of the fragment is added to the current depth.
                    $selectionMaxDepth = $this->getSelectionSetDepth( $fragmentDefinition->selectionSet, $fragments, $currentDepth + 1 );
                }
            }

            if ( $selection instanceof InlineFragmentNode && $selection->selectionSet instanceof SelectionSetNode ) {
                // An inline fragment is also a new selection set, so recurse.
                $selectionMaxDepth = $this->getSelectionSetDepth( $selection->selectionSet, $fragments, $currentDepth + 1 );
            }

            $maxDepth = max( $maxDepth, $selectionMaxDepth );
        }

        return $maxDepth;
    }

    /**
     * @inheritDoc
     */
    public function getKey(): string {
        return 'nestedQuery';
    }
}