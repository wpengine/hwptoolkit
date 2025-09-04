<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\View\List;

use WPGraphQL\Logging\Logger\Database\DatabaseEntity;
use WPGraphQL\Logging\Logger\Database\LogsRepository;
use WP_List_Table;

// Include the WP_List_Table class if not already loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php'; // @phpstan-ignore-line
}

/**
 * List_Table class for WPGraphQL Logging.
 *
 * This class handles the display of the logs in a table format.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class List_Table extends WP_List_Table {
	/**
	 * Default number of items per page.
	 *
	 * @var int
	 */
	public const DEFAULT_PER_PAGE = 25;

	/**
	 * Constructor.
	 *
	 * @param \WPGraphQL\Logging\Logger\Database\LogsRepository $repository The logs repository.
	 * @param array<mixed>                                      $args Optional. An array of arguments.
	 */
	public function __construct(
		public readonly LogsRepository $repository,
		$args = []
	) {
		$args = wp_parse_args(
			$args,
			[
				'singular' => __( 'Log', 'wpgraphql-logging' ),
				'plural'   => __( 'Logs', 'wpgraphql-logging' ),
				'ajax'     => false,
			]
		);
		parent::__construct( $args );
	}

	/**
	 * Prepare items for display.
	 */
	public function prepare_items(): void {
		$this->process_bulk_action();
		$this->_column_headers =
		apply_filters(
			'wpgraphql_logging_logs_table_column_headers',
			[
				$this->get_columns(),
				[], // hidden.
				$this->get_sortable_columns(),
				'id',
			]
		);

		// @TODO
		$per_page     = $this->get_items_per_page( 'logs_per_page', self::DEFAULT_PER_PAGE );
		$current_page = $this->get_pagenum();
		$total_items  = $this->repository->get_log_count();

		$this->set_pagination_args(
			[
				'total_items' => $total_items,
				'per_page'    => $per_page,
			]
		);

		$this->items = $this->repository->get_logs(
			[
				'number' => $per_page,
				'offset' => ( $current_page - 1 ) * $per_page,
			]
		);
	}

	/**
	 * Define bulk actions.
	 */
	public function get_bulk_actions(): array {
		return [
			'delete'     => __( 'Delete Selected', 'wpgraphql-logging' ),
			'delete_all' => __( 'Delete All', 'wpgraphql-logging' ),
		];
	}

	/**
	 * Handle bulk actions.
	 */
	public function process_bulk_action(): void {
		$repository = $this->repository;

		if ('delete' === $this->current_action() && !empty($_POST['log'])) {
			$ids = array_map('absint', (array) $_POST['log']);
			foreach ($ids as $id) {
				$repository->delete($id);
			}
		}

		if ('delete_all' === $this->current_action()) {
			$repository->delete_all();
		}
	}

	/**
	 * Get the columns for the logs table.
	 *
	 * @return array<string, string> The columns.
	 */
	public function get_columns(): array {
		return apply_filters(
			'wpgraphql_logging_logs_table_column_headers',
			[
				'cb'              => '<input type="checkbox" />',
				'id'              => __( 'ID', 'wpgraphql-logging' ),
				'date'            => __( 'Date', 'wpgraphql-logging' ),
				'wpgraphql_query' => __( 'Query', 'wpgraphql-logging' ),
				'level'           => __( 'Level', 'wpgraphql-logging' ),
				'level_name'      => __( 'Level Name', 'wpgraphql-logging' ),
				'event'           => __( 'Event', 'wpgraphql-logging' ),
				'process_id'      => __( 'Process ID', 'wpgraphql-logging' ),
				'headers'         => __( 'Headers', 'wpgraphql-logging' ),
				'time'            => __( 'Time', 'wpgraphql-logging' ),
				'memory_usage'    => __( 'Memory Usage', 'wpgraphql-logging' ),
			]
		);
	}

	/**
	 * Get the default column value for a log entry.
	 *
	 * @param mixed|\WPGraphQL\Logging\Logger\Database\DatabaseEntity $item The log entry item.
	 * @param string                                                  $column_name The column name.
	 *
	 * @phpcs:disable Generic.Metrics.CyclomaticComplexity.MaxExceeded
	 *
	 * @return mixed The default column value or null.
	 */
	public function column_default( $item, $column_name ): mixed {
		if ( ! $item instanceof DatabaseEntity ) {
			return null;
		}

		switch ( $column_name ) {
			case 'date':
				return $item->get_datetime();
			case 'channel':
				return $item->get_channel();
			case 'level':
				return $item->get_level();
			case 'level_name':
				return $item->get_level_name();
			case 'message':
				return $item->get_message();
			case 'event':
				return $this->get_event( $item );
			case 'process_id':
				return $this->get_process_id( $item );
			case 'memory_usage':
				return $this->get_memory_usage( $item );
			case 'wpgraphql_query':
				return $this->get_query( $item );
			default:
				// Users can add their own custom columns and render functionality.
				return apply_filters( 'wpgraphql_logging_logs_table_column_value', '', $item, $column_name );
		}
	}

	/**
	 * Renders the checkbox column for a log entry.
	 *
	 * @param mixed|\WPGraphQL\Logging\Logger\Database\DatabaseEntity $item The log entry item.
	 *
	 * @return string The rendered checkbox column or null.
	 */
	public function column_cb( $item ): string {
		if ( ! $item instanceof DatabaseEntity ) {
			return '';
		}
		return sprintf(
			'<input type="checkbox" name="log[]" value="%d" />',
			$item->get_id()
		);
	}

	/**
	 * Renders the ID column for a log entry.
	 *
	 * @param \WPGraphQL\Logging\Logger\Database\DatabaseEntity $item The log entry item.
	 *
	 * @return string The rendered ID column or null.
	 */
	public function column_id( DatabaseEntity $item ): string {
		$url     = \WPGraphQL\Logging\Admin\View_Logs_Page::ADMIN_PAGE_SLUG;
		$actions = [
			'view' => sprintf(
				'<a href="?page=%s&action=%s&log=%d">%s</a>',
				esc_attr( $url ),
				'view',
				$item->get_id(),
				esc_html__( 'View', 'wpgraphql-logging' )
			),
		];

		return sprintf(
			'%1$s %2$s',
			$item->get_id(),
			$this->row_actions( $actions )
		);
	}

	/**
	 * Renders the query column for a log entry.
	 *
	 * @param \WPGraphQL\Logging\Logger\Database\DatabaseEntity $item The log entry item.
	 *
	 * @return string|null The rendered query column or null.
	 */
	public function column_query( DatabaseEntity $item ): ?string {
		$extra = $item->get_extra();
		return ! empty( $extra['wpgraphql_query'] ) ? esc_html( $extra['wpgraphql_query'] ) : '';
	}

	/**
	 * Gets the query from extra.
	 *
	 * @return string The query
	 */
	public function get_query(DatabaseEntity $item): string {
		$extra = $item->get_extra();
		return ! empty( $extra['wpgraphql_query'] ) ? esc_html( $extra['wpgraphql_query'] ) : '';
	}

	/**
	 * Gets the event from extra.
	 *
	 * @return string The event
	 */
	public function get_event(DatabaseEntity $item): string {

		$extra = $item->get_extra();
		return ! empty( $extra['wpgraphql_event'] ) ? esc_html( $extra['wpgraphql_event'] ) : $item->get_message();
	}

	/**
	 * Gets the event from extra.
	 *
	 * @param \WPGraphQL\Logging\Logger\Database\DatabaseEntity $item The log entry item.
	 *
	 * @return int The event
	 */
	public function get_process_id(DatabaseEntity $item): int {
		$extra = $item->get_extra();
		return ! empty( $extra['process_id'] ) ? (int) $extra['process_id'] : 0;
	}

	/**
	 * Gets the event from extra.
	 *
	 * @return string The event
	 */
	public function get_memory_usage(DatabaseEntity $item): string {
		$extra = $item->get_extra();
		return ! empty( $extra['memory_peak_usage'] ) ? esc_html( $extra['memory_peak_usage'] ) : '';
	}
}
