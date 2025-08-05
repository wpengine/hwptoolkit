<?php
/**
 * Interface for a debug extension analyzer item (metric or rule).
 *
 * @package WPGraphQL\Debug\Analysis\Interfaces
 */

declare(strict_types=1);

namespace WPGraphQL\Debug\Analysis\Interfaces;

use GraphQL\Type\Schema;

interface AnalyzerItemInterface {
    /**
     * Executes the analysis (calculates metric or evaluates rule).
     *
     * @param string      $query     The GraphQL query string.
     * @param array<string,mixed> $variables Optional: Variables provided with the query.
     * @param Schema|null $schema    Optional: The GraphQL schema.
     * @return array<string,mixed> An associative array representing the analysis result.
     * For metrics, it might contain 'value' and 'note'.
     * For rules, it might contain 'triggered' and 'message'.
     */
    public function analyze( string $query, array $variables = [], ?Schema $schema = null ): array;

    /**
     * Returns the key under which this item's result should appear in the 'debugExtensions' output.
     * E.g., 'complexity', 'nestedQueryRule', 'excessiveFieldsRule'.
     *
     * @return string The unique key for the analyzer item.
     */
    public function getKey(): string;
}