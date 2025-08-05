<?php //phpcs:ignore
/**
 * A WordPress tool to read WPGraphQL logging data based on filters.
 *
 * This file contains the McpLoggerTool class, which registers a custom tool for
 * the WordPress MCP framework. The tool allows querying WPGraphQL log entries
 * from the database with various filters.
 *
 * @package WPGraphQL\Logging\MCP
 */

namespace WPGraphQL\Logging\MCP;

use Automattic\WordpressMcp\Core\RegisterMcpTool;
use WPGraphQL\Logging\Logger\Database\DatabaseEntity;

/**
 * McpLoggerTool class.
 *
 * This class registers and provides the callback logic for a tool that reads
 * WPGraphQL log data from the database.
 */
class McpLoggerTool {

    /**
     * The single instance of the class.
     *
     * @var \WPGraphQL\Logging\MCP\McpLoggerTool|null
     */
    private static ?McpLoggerTool $instance = null;

    /**
     * Get or create the single instance of the class.
     *
     * @return McpLoggerTool
     */
    public static function init(): McpLoggerTool {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->setup();
        }

        return self::$instance;
    }

    /**
     * Set up the action hook to register the tool.
     */
    public function setup(): void {
        add_action( 'wordpress_mcp_init', [ $this, 'register_tool' ] );
    }

    /**
     * Register the custom tool with the WordPress MCP framework.
     */
    public function register_tool(): void {

        new RegisterMcpTool(
            array(
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
                            'description' => "The log level to filter by (e.g., 'ERROR', 'WARNING', 'ERRORS_AND_ABOVE'). Optional.",
                        ],
                        'search_text' => [
                            'type'        => 'string',
                            'description' => 'Text to search for within the log message. Optional.',
                        ],
                    ],
                    'required' => [],
                ],
                'callback' => [ $this, 'execute' ],
                'permission_callback' => [ $this, 'permission_callback' ],
            )
        );
    }

    /**
     * Permission callback to ensure only users with 'manage_options' can use this tool.
     *
     * @return bool
     */
    public function permission_callback(): bool {
        return current_user_can( 'manage_options' );
    }

    /**
     * Execute the tool's logic to fetch log entries from the database.
     *
     * @param array $args The arguments passed to the tool.
     *
     * @return array
     */
    public function execute( array $args ): array {
        global $wpdb;

        $table_name = DatabaseEntity::get_table_name();
        $sql           = "SELECT id, message, level_name, level, datetime, extra FROM {$table_name}";
        $where_clauses = [ '1=1' ]; // Base clause to simplify dynamic WHERE.
        $params        = [];

        // 1. Handle timeframe filtering.
        $timeframe_hours = isset( $args['timeframe_hours'] ) ? absint( $args['timeframe_hours'] ) : 24;
        $lookback_date   = gmdate( 'Y-m-d H:i:s', strtotime( "-{$timeframe_hours} hours" ) );
        $where_clauses[] = 'datetime >= %s';
        $params[]        = $lookback_date;

        // 2. Handle log level filtering.
        if ( ! empty( $args['level'] ) && is_string( $args['level'] ) ) {
            $level_op  = '=';
            $level_val = 0;

            // Handle Monolog level strings and special keywords.
            switch ( strtoupper( $args['level'] ) ) {
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
                case 'ERRORS_AND_ABOVE':
                    $level_op  = '>=';
                    $level_val = 400;
                    break;
                case 'WARNINGS_AND_ABOVE':
                    $level_op  = '>=';
                    $level_val = 300;
                    break;
            }

            if ( $level_val > 0 ) {
                // Query the numeric 'level' column for a valid level.
                $where_clauses[] = "level {$level_op} %d";
                $params[]        = $level_val;
            } else {
                // Fallback to original string matching on 'level_name'.
                $where_clauses[] = 'level_name = %s';
                $params[]        = $args['level'];
            }
        }

        // 3. Handle search text filtering.
        if ( ! empty( $args['search_text'] ) && is_string( $args['search_text'] ) ) {
            $where_clauses[] = 'message LIKE %s';
            $params[]        = '%' . $wpdb->esc_like( $args['search_text'] ) . '%';
        }

        // Combine
        $sql .= ' WHERE ' . implode( ' AND ', $where_clauses );
        $sql .= ' ORDER BY datetime DESC LIMIT 100';

        $prepared_sql = $wpdb->prepare( $sql, $params );

        $logs = $wpdb->get_results( $prepared_sql );

        if ( empty( $logs ) ) {
            return [ 'message' => 'No matching logs found.' ];
        }

        // 4. Format the logs into resource links with enhanced context.
        $resource_links = [];
        foreach ( $logs as $log ) {
            // Attempt to safely decode the extra data.
            $extra_data = json_decode( $log->extra, true );
            $extra_data = is_array( $extra_data ) ? $extra_data : [];

            $operation_name = $extra_data['wpgraphql_operation_name'] ?? 'Unknown Operation';

            $title = "{$log->level_name} in '{$operation_name}' at {$log->datetime}";
            $detailed_text_parts = [];
            $detailed_text_parts[] = 'Log ID: ' . $log->id;
            $detailed_text_parts[] = 'Timestamp: ' . $log->datetime;
            $detailed_text_parts[] = 'Level Name: ' . $log->level_name . ' (' . $log->level . ')';
            $detailed_text_parts[] = 'Message: ' . $log->message; // Full message

            if ( ! empty( $extra_data['wpgraphql_query'] ) ) {
                $detailed_text_parts[] = 'GraphQL Query: ' . $extra_data['wpgraphql_query']; // Full query
            }

            if ( ! empty( $extra_data['wpgraphql_variables'] ) ) {
                $variables_json = is_array( $extra_data['wpgraphql_variables'] ) ? wp_json_encode( $extra_data['wpgraphql_variables'], JSON_PRETTY_PRINT ) : $extra_data['wpgraphql_variables'];
                $detailed_text_parts[] = 'GraphQL Variables: ' . $variables_json;
            }
            if ( ! empty( $extra_data ) ) {
                $detailed_text_parts[] = 'Additional Context (Extra Data): ' . wp_json_encode( $extra_data, JSON_PRETTY_PRINT );
            }

            $text = implode( "\n", $detailed_text_parts );

            $resource_links[] = [
                'uri'   => 'log://wpgraphql_logging/' . $log->id,
                'title' => $title,
                'text'  => $text,
            ];
        }

        return [ 'found_logs' => $resource_links ];
    }
}
