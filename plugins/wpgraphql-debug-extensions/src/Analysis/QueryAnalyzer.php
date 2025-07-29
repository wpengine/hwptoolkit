<?php
/**
 * Extends WPGraphQL's Query Analyzer to add custom heuristic rules and metrics.
 *
 * @package WPGraphQL\Debug
 */

declare(strict_types=1);

namespace WPGraphQL\Debug\Analysis;

use WPGraphQL\Debug\Analysis\Metrics\Complexity;
use WPGraphQL\Utils\QueryAnalyzer as OriginalQueryAnalyzer;

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
	 * @var string|null The GraphQL query string for the current request.
	 */
	protected ?string $currentQuery = null;

	/**
	 * @var array<string,mixed> The variables for the current GraphQL request.
	 */
	protected array $currentVariables = [];

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
		$complexityValue = null;
		$complexityNote = 'Could not compute complexity';

		$request = $this->query_analyzer->get_request();
		$currentQuery = $request->params->query ?? null;
		$currentVariables = (array) ( $request->params->variables ?? [] );

		// Add some logging to debug.
		error_log( 'QueryAnalyzerExtension: addCustomMetricsToAnalyzerOutput called.' );
		error_log( 'QueryAnalyzerExtension: Retrieved Query: ' . ( $currentQuery ?? 'NULL' ) );
		error_log( 'QueryAnalyzerExtension: Retrieved Variables: ' . print_r( $currentVariables, true ) );
		if ( ! empty( $currentQuery ) ) {
			try {
				$complexityMetrics = new Complexity();
				$schema = $this->query_analyzer->get_schema();
				$complexityValue = $complexityMetrics->calculate( $currentQuery, $currentVariables, $schema );

			} catch (\Exception $e) {
				error_log( 'WPGraphQL Debug Extensions: Complexity calculation failed: ' . $e->getMessage() );
				$complexityNote .= ': ' . $e->getMessage();
			}
		}
		if ( ! isset( $graphql_keys['debugExtensions'] ) ) {
			$graphql_keys['debugExtensions'] = [];
		}
		$graphql_keys['debugExtensions']['complexity'] = $complexityValue;

		return $graphql_keys;
	}
}