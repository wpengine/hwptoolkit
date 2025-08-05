<?php
/**
 * Extends WPGraphQL's Query Analyzer to add custom heuristic rules and metrics.
 *
 * @package WPGraphQL\Debug\Analysis
 */

declare(strict_types=1);

namespace WPGraphQL\Debug\Analysis;

use WPGraphQL\Debug\Analysis\Interfaces\AnalyzerItemInterface;
use WPGraphQL\Utils\QueryAnalyzer as OriginalQueryAnalyzer;

/**
 * Class QueryAnalyzer
 *
 * This class hooks into the WPGraphQL Query Analyzer to add custom analysis.
 */
class QueryAnalyzer {

    /**
     * @var OriginalQueryAnalyzer The instance of the WPGraphQL Query Analyzer from the core plugin.
     */
    protected OriginalQueryAnalyzer $query_analyzer;

    /**
     * @var AnalyzerItemInterface[] An array of registered analyzer items (metrics and rules).
     */
    protected array $analyzerItems = [];

    /**
     * Constructor for the QueryAnalyzerExtension.
     *
     * @param OriginalQueryAnalyzer $query_analyzer The instance of the WPGraphQL Query Analyzer.
     */
    public function __construct( OriginalQueryAnalyzer $query_analyzer ) {
        $this->query_analyzer = $query_analyzer;
    }

    /**
     * Adds an AnalyzerItem (metric or rule) to be processed.
     *
     * @param AnalyzerItemInterface $item The item to add.
     * @return void
     */
    public function addAnalyzerItem( AnalyzerItemInterface $item ): void {
        $this->analyzerItems[] = $item;
    }

    /**
     * Initializes the extension by adding necessary WordPress hooks.
     */
    public function init(): void {
        // This filter allows us to inject custom data into the 'debugExtensions' part of the GraphQL response.
        add_filter( 'graphql_query_analyzer_graphql_keys', [ $this, 'addAnalysisToOutput' ], 10, 5 );
    }

    /**
     * Adds new metrics and analysis results to the Query Analyzer's output.
     * This method is a callback for the 'graphql_query_analyzer_graphql_keys' filter.
     *
     * @param array<string,mixed> $graphql_keys      Existing data from the Query Analyzer.
     * @param string              $return_keys       The keys returned to the X-GraphQL-Keys header. (unused here)
     * @param string              $skipped_keys      The keys that were skipped. (unused here)
     * @param string[]            $return_keys_array The keys returned in array format. (unused here)
     * @param string[]            $skipped_keys_array The keys skipped in array format. (unused here)
     * @return array<string,mixed> The modified GraphQL keys with custom metrics.
     */
    public function addAnalysisToOutput(
        array $graphql_keys,
        string $return_keys, // Keep for filter signature, but not used.
        string $skipped_keys, // Keep for filter signature, but not used.
        array $return_keys_array, // Keep for filter signature, but not used.
        array $skipped_keys_array // Keep for filter signature, but not used.
    ): array {
        if ( ! isset( $graphql_keys['debugExtensions'] ) ) {
            $graphql_keys['debugExtensions'] = [];
        }

        $request = $this->query_analyzer->get_request();
        $currentQuery = $request->params->query ?? null;
        $currentVariables = (array) ( $request->params->variables ?? [] );
        $schema = $this->query_analyzer->get_schema();

        foreach ( $this->analyzerItems as $item ) {
            try {
                if ( ! empty( $currentQuery ) ) {
                    $result = $item->analyze( $currentQuery, $currentVariables, $schema );
                } else {
                    $result = [
                        'value' => null,
                        'note' => 'No query provided for analysis.',
                    ];
                }
            } catch ( \Exception $e ) {
                error_log( sprintf(
                    'WPGraphQL Debug Extensions: Analysis item "%s" failed: %s',
                    $item->getKey(),
                    $e->getMessage()
                ) );
                $result = [
                    'value' => null,
                    'note'  => 'Analysis failed: ' . $e->getMessage(),
                    'error' => true,
                ];
            }

            $graphql_keys['debugExtensions'][ $item->getKey() ] = $result;
        }

        return $graphql_keys;
    }
}