<?php

namespace WPGraphQL\Logging\MCP;

use WPGraphQL\Logging\Logger\Database\DatabaseEntity;

class McpLoggerTool {


	/**
	 * The single instance of the class.
	 *
	 * @var \WPGraphQL\Logging\MCP\McpLoggerTool|null
	 */
	private static ?McpLoggerTool $instance = null;

	/**
	 * Get or create the single instance of the class.
	 */
	public static function init(): McpLoggerTool {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		return self::$instance;
	}

	public function setup(): void {
		add_action( 'wordpress_mcp_init', [ $this, 'register_tool' ] );
	}

	public function register_tool(): void {
		WPMCP()->register_tool([
			'type' => 'read',
			'name' => 'wpgraphql_logging_custom_tool',
			'description' => 'Reads WPGraphQL Logging custom tool data',
			'inputSchema' => [
				'type' => 'object',
				'properties' => [
					'timeframe_hours' => [
						'type'        => 'integer',
						'description' => 'The number of hours to look back. Defaults to 24.',
						'default'     => 24,
					],
					'level' => [
						'type'        => 'string',
						'description' => "The log level to filter by (e.g., 'ERROR', 'WARNING'). Optional.",
					],
					'search_text' => [
						'type'        => 'string',
						'description' => 'Text to search for within the log message. Optional.',
					],
				],
				'required' => ['param1']
			],
			'callback' => [$this, 'execute'],
		]);
	}

	public function execute( array $args ): array {
		// Testing
//		return ['result' => 'success'];

		// @TODO - Get args working
		$args = [
			'timeframe_hours' => 24,
			'level'           => 'ERROR',
		];

		/**
		 * Note this needs to be tidied up as mostly generated from AI prompts.
		 *
		 * We probably shouldd use the resource too if possible
		 */
		global $wpdb;
		$table_name = DatabaseEntity::get_table_name();

		$sql = "SELECT id, message, level_name, datetime, context, extra FROM {$table_name} WHERE 1=1";
		$params = [];

		$timeframe_hours = isset($args['timeframe_hours']) ? intval($args['timeframe_hours']) : 24;
		$lookback_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$timeframe_hours} hours" ) );
		$sql .= " AND datetime >= %s";
		$params[] = $lookback_date;

		if ( ! empty( $args['level'] ) ) {
			$level_op = '=';
			$level_val = 0;

			// Handle Monolog level strings and convert to integer codes for querying
			switch (strtoupper($args['level'])) {
				case 'DEBUG':
					$level_val = 100;
					break;
				case 'INFO':
					$level_val = 200;
					break;
				case 'NOTICE':
					$level_val = 250;
					break;
				case 'WARNING':
					$level_val = 300;
					break;
				case 'ERROR':
					$level_val = 400;
					break;
				case 'CRITICAL':
					$level_val = 500;
					break;
				case 'ALERT':
					$level_val = 550;
					break;
				case 'EMERGENCY':
					$level_val = 600;
					break;
				// Special keywords for range-based searches
				case 'ERRORS_AND_ABOVE':
					$level_op = '>=';
					$level_val = 400;
					break;
				case 'WARNINGS_AND_ABOVE':
					$level_op = '>=';
					$level_val = 300;
					break;
			}

			if ( $level_val > 0 ) {
				// If a valid level was found, query the numeric 'level' column
				$sql .= " AND level {$level_op} %d";
				$params[] = $level_val;
			} else {
				// Fallback to original string matching on 'level_name' if the input is not a recognized level
				$sql .= " AND level_name = %s";
				$params[] = $args['level'];
			}
		}

		if ( ! empty( $args['search_text'] ) ) {
			$sql .= " AND message LIKE %s";
			$params[] = '%' . $wpdb->esc_like( $args['search_text'] ) . '%';
		}

		$sql .= " ORDER BY datetime DESC LIMIT 100"; // Add a limit to prevent huge responses

		$logs = $wpdb->get_results( $wpdb->prepare( $sql, $params ) );

		if ( empty( $logs ) ) {
			return [ 'message' => 'No matching logs found.' ];
		}

		$logs = $wpdb->get_results( $wpdb->prepare( $sql, $params ) );

		foreach ( $logs as $log ) {
			$extra_data = json_decode( $log->extra, true );
			$operation_name = ! empty( $extra_data['wpgraphql_operation_name'] ) ? $extra_data['wpgraphql_operation_name'] : 'Unknown Operation';

			// Construct a more descriptive title using the operation name.
			$title = "{$log->level_name} in '{$operation_name}' at {$log->datetime}";

			// Construct a detailed snippet including the error, query, and variables.
			$snippet_parts = [];
			$snippet_parts[] = "Error: " . substr( $log->message, 0, 100 ) . ( strlen( $log->message ) > 100 ? '...' : '' );

			if ( ! empty( $extra_data['wpgraphql_query'] ) ) {
				$snippet_parts[] = "Query: " . substr( $extra_data['wpgraphql_query'], 0, 150 ) . ( strlen( $extra_data['wpgraphql_query'] ) > 150 ? '...' : '' );
			}

			if ( ! empty( $extra_data['wpgraphql_variables'] ) ) {
				$variables_json = is_array( $extra_data['wpgraphql_variables'] ) ? json_encode( $extra_data['wpgraphql_variables'] ) : $extra_data['wpgraphql_variables'];
				$snippet_parts[] = "Variables: " . substr( $variables_json, 0, 100 ) . ( strlen( $variables_json ) > 100 ? '...' : '' );
			}

			$text = implode( "\n", $snippet_parts );

			$resource_links[] = [
				'uri'   => 'log://wpgraphql_logging/' . $log->id,
				'title' => $title,
				'text'  => $text,
			];
		}

		return [ 'found_logs' => $resource_links ];
	}

}
