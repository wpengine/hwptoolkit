<?php
/**
 * Extends WPGraphQL's Query Analyzer to add custom heuristic rules and metrics.
 *
 * @package WPGraphQL\Debug
 */

declare(strict_types=1);

namespace WPGraphQL\Debug\Analysis;

use WPGraphQL\Utils\QueryAnalyzer as OriginalQueryAnalyzer;
use WPGraphQL\Request;
use GraphQL\Type\Schema;

/**
 * Class QueryAnalyzerExtension
 *
 * This class hooks into the WPGraphQL Query Analyzer to add custom analysis.
 */
class QueryAnalyzer {

    /**
     * @var QueryAnalyzer The instance of the WPGraphQL Query Analyzer.
     */
    protected OriginalQueryAnalyzer $query_analyzer;

    /**
     * Constructor for the QueryAnalyzerExtension.
     *
     * @param OriginalQueryAnalyzer $query_analyzer The instance of the WPGraphQL Query Analyzer.
     */
    public function __construct( OriginalQueryAnalyzer $query_analyzer ) {
        $this->query_analyzer = $query_analyzer;
    }

    /**
     * Initializes the extension by adding necessary WordPress hooks.
     */
    public function init(): void {
        add_filter( 'graphql_query_analyzer_graphql_keys', [ $this, 'addMetricsToAnalyzerOutput' ], 10, 5 );
    }

    /**
     * Adds new metrics and analysis results to the Query Analyzer's output.
     * This method is a callback for the 'graphql_query_analyzer_graphql_keys' filter.
     *
     * @param array<string,mixed> $graphql_keys      Existing data from the Query Analyzer.
     * @param string              $return_keys       The keys returned to the X-GraphQL-Keys header.
     * @param string              $skipped_keys      The keys that were skipped.
     * @param string[]            $return_keys_array The keys returned in array format.
     * @param string[]            $skipped_keys_array The keys skipped in array format.
     * @return array<string,mixed> The modified GraphQL keys with custom metrics.
     */
    public function addMetricsToAnalyzerOutput(
        array $graphql_keys,
        string $return_keys,
        string $skipped_keys,
        array $return_keys_array,
        array $skipped_keys_array
    ): array {
        // Simulate deeply nested queries check
        $hasDeepNesting = $this->analyzeNestingDepth();

        // Simulate excessive field selection check
        $hasExcessiveFields = $this->analyzeFieldSelection();

        // Simulate a custom complexity score calculation
        $customComplexityScore = $this->calculateCustomComplexity();

        // Add your custom data under a new key within the 'queryAnalyzer' extension.
        $graphql_keys['DebugExtensionsAnalysis'] = [
            'heuristicRules' => [
                'deepNestingDetected'   => $hasDeepNesting,
                'excessiveFieldsDetected' => $hasExcessiveFields,
            ],
            'performanceMetrics' => [
                'customComplexityScore' => $customComplexityScore,
                'dummyMemoryUsageKB'    => rand( 1024, 8192 ),
                'dummyExecutionTimeMs'  => rand( 50, 500 ),
            ],
        ];

        return $graphql_keys;
    }

    /**
     * Placeholder method to simulate analysis of nesting depth.
     * In a real implementation, this would parse the query AST
     * and determine nesting levels.
     *
     * @return bool True if deep nesting is detected, false otherwise.
     */
    protected function analyzeNestingDepth(): bool {
        // For now, return a random boolean for demonstration.
        return (bool) rand( 0, 1 );
    }

    /**
     * Placeholder method to simulate analysis of excessive field selection.
     * In a real implementation, this would count fields selected per type
     * and compare against a threshold.
     *
     * @return bool True if excessive fields are detected, false otherwise.
     */
    protected function analyzeFieldSelection(): bool {
        // For now, return a random boolean for demonstration.
        return (bool) rand( 0, 1 );
    }

    /**
     * Placeholder method to simulate a custom complexity score calculation.
     * This could combine factors like nesting, field count, list fetches, etc.
     *
     * @return int A dummy complexity score.
     */
    protected function calculateCustomComplexity(): int {
        // For now, return a random integer.
        return rand( 100, 1000 );
    }
}