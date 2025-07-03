<?php

namespace WPGraphQL\Velocity\Analysis;

use WPGraphQL\Velocity\Data\WPGraphQLMonitorDataSource;

class TrackerService {

	protected string $start_time = '';

	protected array $query_data = [];


	/**
	 * The instance of the TrackerService class.
	 *
	 * @var \WPGraphQL\Velocity\Tracker\TrackerService|null
	 */
	protected static ?TrackerService $instance = null;

	/**
	 * Initialize the hooks for the preview functionality.
	 */
	public static function init(): self {
		if ( ! isset( self::$instance ) || ! ( is_a( self::$instance, self::class ) ) ) {
			self::$instance = new self();
		}
		apply_filters('wpgraphql_velocity_tracker_service_init', self::$instance);
		return self::$instance;
	}

	public function track_request(string $query, string $operation_name = '',  array $variables = []) {
		$this->start_time = microtime(true);
		$this->query_data[$query] = $variables;
		$this->query_data['query'] = $query;
		$this->query_data['memory_start'] = memory_get_usage();
		$this->query_data['operation_name'] = $operation_name;
		$this->query_data['variables'] = $variables;
		apply_filters('wpgraphql_velocity_tracker_service_start', $this->query_data, $this->start_time);
	}

	public function get_query_data(): array {
		return $this->query_data;
	}

	public function get_start_time(): string {
		return $this->start_time;
	}
}
