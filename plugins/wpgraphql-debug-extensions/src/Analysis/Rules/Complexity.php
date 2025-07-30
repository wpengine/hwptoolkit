<?php
/**
 * GraphQL Query Complexity Metric.
 *
 * @package WPGraphQL\Debug\Rules
 */

declare(strict_types=1);

namespace WPGraphQL\Debug\Analysis\Rules;

use GraphQL\Language\AST\BooleanValueNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\FragmentSpreadNode;
use GraphQL\Language\AST\InlineFragmentNode;
use GraphQL\Language\AST\VariableNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Visitor;
use GraphQL\Type\Schema;
use GraphQL\Error\SyntaxError;
use WPGraphQL\Debug\Analysis\Interfaces\AnalyzerItemInterface;

class Complexity implements AnalyzerItemInterface {
    protected ?string $internalNote = null;

    /**
     * @inheritDoc
     */
    public function analyze( string $query, array $variables = [], ?Schema $schema = null ): array {
        $value = null;

        try {
            $ast = Parser::parse( $query );
        } catch ( SyntaxError $error ) {
            $this->internalNote = 'Complexity calculation failed due to GraphQL syntax error: ' . $error->getMessage();
            error_log( 'WPGraphQL Debug Extensions: ' . $this->internalNote );
            return [
                'value' => null,
                'note'  => $this->internalNote,
            ];
        }

        $complexity = 0;
        Visitor::visit(
            $ast,
            [
                'enter' => function ( $node ) use ( &$complexity, $variables ) {
                    if ( $node instanceof FieldNode ) {
                        $include = true;

                        if ( ! empty( $node->directives ) ) {
                            foreach ( $node->directives as $directive ) {
                                $name  = $directive->name->value;
                                $ifArg = null;

                                foreach ( $directive->arguments as $arg ) {
                                    if ( 'if' === $arg->name->value ) {
                                        $ifArg = $arg->value;
                                        break;
                                    }
                                }

                                $ifValue = true;

                                if ( $ifArg instanceof VariableNode ) {
                                    $varName = $ifArg->name->value;
                                    $ifValue = $variables[ $varName ] ?? true;
                                } elseif ( $ifArg instanceof BooleanValueNode ) {
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
                        $complexity += 1;
                    } elseif ( $node instanceof InlineFragmentNode ) {
                        $complexity += 1;
                    }
                },
            ]
        );

        $value = $complexity;
        $this->internalNote = $this->getComplexityNote( $value );

        return [
            'value' => $value,
            'note'  => $this->internalNote,
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
