<?php

namespace WPGraphQL\Velocity\Data;


class WPGraphQLMonitorDataSource implements DataSourceInterface {

	protected array $data;

	// @TODO should really be an entity but will work with this for now
	public function __construct(array $data) {
		$this->data = $data;
	}

	public function get_id() : string {
		return 'wpgraphql_monitor';
	}

	public function get_type() : string {
		return 'sql_analysis';
	}

	public function get_data() : array {
		// @TODO - Sanitize and prepare the query before returning
		return $this->data;
	}
}
