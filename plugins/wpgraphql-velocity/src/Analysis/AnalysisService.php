<?php

namespace WPGraphQL\Velocity\Analysis;

use WPGraphQL\Velocity\Data\WPGraphQLMonitorDataSource;

class AnalysisService {

	protected ?TrackerService $tracker_service = null;

	/**
	 * The instance of the AnalysisService class.
	 *
	 * @var \WPGraphQL\Velocity\Tracker\AnalysisService|null
	 */
	protected static ?AnalysisService $instance = null;

	/**
	 * Initialize the hooks for the preview functionality.
	 */
	public static function init(): self {
		if ( ! isset( self::$instance ) || ! ( is_a( self::$instance, self::class ) ) ) {
			self::$instance = new self();
		}
		apply_filters('wpgraphql_velocity_analysis_service_init', self::$instance);
		return self::$instance;
	}

	public function analyze_response(TrackerService $tracker_service, $response)  {


		$this->tracker_service = $tracker_service;


		apply_filters('wpgraphql_velocity_before_analysis', $tracker_service, $response);

		$analysis = [
			'version' => WPGRAPHQL_VELOCITY_VERSION,
			'analysis'  => $this->get_analysis($response)
		];

		apply_filters('wpgraphql_velocity_after_analysis', $analysis, $response);


		if ( is_array( $response ) ) {
			$response['extensions']['wpgraphql_velocity'] = $analysis;
		} elseif ( is_object( $response ) ) {
			$response->extensions['tracing'] = $analysis;
		}

		return $response;
	}

	/**
	 * @return array[]
	 */
	public function get_analysis($response): array {

		// @TODO
		// This is a placeholder for the actual analysis logic.

		return [
			'complexity' => [
				'analysis' => 'Fake Analysis: This query is complex and may take a long time to execute.',
				'level'    => 'warning',
			],
			'pagination' => [
				'analysis' => 'Fake Analysis: This query is paginated, good job.',
				'level'    => 'success',
			],
		];

		$data_sources = $this->get_data_sources($response);
		if (! is_array( $data_sources ) || empty( $data_sources ) ) {
			return [];
		}

		$rule_engine_service = new RuleEngineService();
		$analysis = [];
		foreach ( $data_sources as $data_source ) {
			$rule_engine_service->analyze( $data_source, $analysis );
		}

		return $analysis;
	}

	public function get_data_sources($response): array {

		if (is_object($response)) {
			/** @var $response \GraphQL\Executor\ExecutionResult */
			$data = $response->data ?? [];
		} else {
			$data = $response['data'] ?? [];
		}

		if ( empty( $data ) ) {
			// Should do something here, but for now just return an empty array
			return [];
		}

		// @TODO
		// For MVP just returning one data source but probably should get them from a collection
		// This is not best practice but getting work for now as part of a hack day
		// Should be an entity but for now just returning a single data source
		global $wpdb;
		$last_log = $wpdb->get_row(
			"SELECT * FROM {$wpdb->prefix}wpgraphql_query_logs ORDER BY id DESC LIMIT 1",
			ARRAY_A
		);
		return [
			new WPGraphQLMonitorDataSource(
				$last_log
			)
		];
	}

}
