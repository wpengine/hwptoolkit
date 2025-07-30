<?php
/**
 * Rule to warn about queries requesting large lists of items without appropriate filtering or pagination.
 *
 * @package WPGraphQL\Debug\Analysis\Rules
 */

declare(strict_types=1);

namespace WPGraphQL\Debug\Analysis\Rules;

use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Visitor;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use GraphQL\Error\SyntaxError;
use GraphQL\Utils\TypeInfo;
use WPGraphQL\Debug\Analysis\Interfaces\AnalyzerItemInterface;

class UnfilteredLists implements AnalyzerItemInterface {

    protected array $paginationArgs;
    protected ?string $message = null;

    /**
     * Constructor for UnfilteredListsRule.
     *
     * @param array $paginationArgs An array of argument names considered to be pagination arguments.
     */
    public function __construct( array $paginationArgs = ['first', 'last', 'after', 'before', 'offset', 'limit'] ) {
        $this->paginationArgs = $paginationArgs;
    }

    /**
     * @inheritDoc
     */
    public function analyze( string $query, array $variables = [], ?Schema $schema = null ): array {
        $triggered          = false;
        $unpaginatedLists   = [];

        if ( ! $schema instanceof Schema ) {
            $this->message = 'Schema not provided, cannot analyze unfiltered lists accurately.';
            return [
                'triggered' => false,
                'message'   => $this->message,
                'details'   => [],
            ];
        }

        try {
            $ast = Parser::parse( $query );
        } catch ( SyntaxError $error ) {
            $this->message = 'Failed to analyze unfiltered lists due to GraphQL syntax error: ' . $error->getMessage();
            return [
                'triggered' => false,
                'message'   => $this->message,
                'details'   => [],
            ];
        }

        $typeInfo = new TypeInfo( $schema );

        Visitor::visit(
            $ast,
            Visitor::visitWithTypeInfo(
                $typeInfo,
                [
                    'enter' => function ( $node ) use ( &$unpaginatedLists, $typeInfo ) {
                        if ( $node instanceof FieldNode ) {
                            $parentType = $typeInfo->getParentType();

                            // Only proceed if we are in an ObjectType context (e.g., RootQuery, Post)
                            if ( $parentType instanceof ObjectType ) {
                                try {
                                    $fieldDefinition = $parentType->getField( $node->name->value );
                                } catch ( \Exception $e ) {
                                    // Field not found in parent type's definition, skip.
                                    // This can happen for introspection fields or aliased fields not directly
                                    // resolvable without full schema traversal (TypeInfo usually handles this).
                                    return;
                                }

                                // Check if the field definition itself accepts any of our pagination arguments
                                $fieldDefinitionAcceptsPagination = false;
                                if ( ! empty( $fieldDefinition->args ) ) {
                                    foreach ( $fieldDefinition->args as $argDef ) {
                                        if ( in_array( $argDef->name, $this->paginationArgs, true ) ) {
                                            $fieldDefinitionAcceptsPagination = true;
                                            break;
                                        }
                                    }
                                }

                                // If the schema definition for this field indicates it can be paginated,
                                // now check if the *actual query* for this field includes pagination arguments.
                                if ( $fieldDefinitionAcceptsPagination ) {
                                    $queryHasPagination = false;
                                    if ( ! empty( $node->arguments ) ) {
                                        foreach ( $node->arguments as $arg ) {
                                            if ( in_array( $arg->name->value, $this->paginationArgs, true ) ) {
                                                $queryHasPagination = true;
                                                break;
                                            }
                                        }
                                    }

                                    if ( ! $queryHasPagination ) {
                                        // This field is a paginatable connection in the schema,
                                        // but the current query does not apply pagination.
                                        $unpaginatedLists[] = $node->name->value;
                                    }
                                }
                            }
                        }
                    },
                ]
            )
        );

        if ( ! empty( $unpaginatedLists ) ) {
            $triggered     = true;
            $this->message = sprintf(
                'Unfiltered list queries detected for: %s. Consider adding pagination (e.g., %s) to these fields for performance.',
                implode( ', ', array_unique( $unpaginatedLists ) ),
                implode( ', ', $this->paginationArgs )
            );
        } else {
            $this->message = 'No unfiltered list queries detected.';
        }

        return [
            'triggered'             => $triggered,
            'message'               => $this->message,
            'details'               => array_values( array_unique( $unpaginatedLists ) ), // Ensure unique and re-indexed
            'paginationArgsChecked' => $this->paginationArgs,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getKey(): string {
        return 'unfilteredLists';
    }
}