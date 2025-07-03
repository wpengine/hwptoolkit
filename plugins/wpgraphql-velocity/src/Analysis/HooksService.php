<?php

namespace WPGraphQL\Velocity\Analysis;

class HooksService {

	/**
	 * The instance of the HooksService class.
	 *
	 * @var \WPGraphQL\Velocity\Analysis\HooksService|null
	 */
	protected static ?HooksService $instance = null;

	/**
	 * TrackerService instance for tracking query performance.
	 *
	 * @var TrackerService
	 */
	protected TrackerService $tracker_service;

	/**
	 * AnalysisService instance for analyzing query responses.
	 *
	 * @var AnalysisService
	 */
	protected AnalysisService $analysis_service;

	public function __construct() {
		$this->tracker_service = TrackerService::init();
		$this->analysis_service = AnalysisService::init();
	}

	/**
	 * Initialize the hooks for the preview functionality.
	 */
	public static function init(): self {
		if ( ! isset( self::$instance ) || ! ( is_a( self::$instance, self::class ) ) ) {
			self::$instance = new self();
			self::$instance->setup();
		}
		apply_filters('wpgraphql_velocity__query_response_service_init', self::$instance);
		return self::$instance;
	}

	public function setup(): void {
		$this->track_incoming_query_requests();
		$this->modify_query_response();
	}

	public function track_incoming_query_requests(): void {
		add_action( 'do_graphql_request', function ( $query, $operation_name, $variables ) {
			$this->tracker_service->track_request($query, $operation_name, $variables);
		}, 10, 3);
	}

	public function modify_query_response(): void {
		add_filter('graphql_request_results', function($response) {
			return $this->analysis_service->analyze_response($this->tracker_service, $response);
		}, 10, 1);
	}

}
