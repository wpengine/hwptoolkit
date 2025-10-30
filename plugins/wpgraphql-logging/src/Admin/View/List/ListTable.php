<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\View\List;

use DateTime;
use WPGraphQL\Logging\Logger\Api\LogServiceInterface;
use WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity;
use WP_List_Table;

// Include the WP_List_Table class if not already loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * ListTable class for WPGraphQL Logging.
 *
 * This class handles the display of the logs in a table format.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */
class ListTable extends WP_List_Table {
	/**
	 * Default number of items per page.
	 *
	 * @var int
	 */
	public const DEFAULT_PER_PAGE = 20;

	/**
	 * Constructor.
	 *
	 * @param \WPGraphQL\Logging\Logger\Api\LogServiceInterface $log_service The log service.
	 * @param array<mixed>                                      $args Optional. An array of arguments.
	 */
	public function __construct(
		public readonly LogServiceInterface $log_service,
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
	 *
	 * @phpcs:disable WordPress.Security.NonceVerification.Recommended
	 *
	 * @psalm-suppress PossiblyInvalidCast
	 */
	public function prepare_items(): void {
		if ( array_key_exists( 'orderby', $_REQUEST ) || array_key_exists( 'order', $_REQUEST ) ) {
			check_admin_referer( 'wpgraphql-logging-sort' );
		}

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

		$per_page     = $this->get_items_per_page( 'logs_per_page', self::DEFAULT_PER_PAGE );
		$current_page = $this->get_pagenum();
		/** @psalm-suppress InvalidArgument */
		$where       = $this->process_where( $_REQUEST );
		$total_items = $this->log_service->count_entities_by_where( $where );

		$this->set_pagination_args(
			[
				'total_items' => $total_items,
				'per_page'    => $per_page,
			]
		);

		$args = [
			'number' => $per_page,
			'offset' => ( $current_page - 1 ) * $per_page,
		];

		if ( array_key_exists( 'orderby', $_REQUEST ) ) {
			$args['orderby'] = sanitize_text_field( wp_unslash( (string) $_REQUEST['orderby'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if ( array_key_exists( 'order', $_REQUEST ) ) {
			$args['order'] = sanitize_text_field( wp_unslash( (string) $_REQUEST['order'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		$args['where'] = $where;
		$this->items   = $this->log_service->find_entities_by_where( apply_filters( 'wpgraphql_logging_logs_table_query_args', $args ) );
	}

	/**
	 * Define bulk actions.
	 *
	 * @return array<string, string> The bulk actions.
	 */
	public function get_bulk_actions(): array {
		return [
			'delete'     => __( 'Delete Selected', 'wpgraphql-logging' ),
			'delete_all' => __( 'Delete All', 'wpgraphql-logging' ),
		];
	}

	/**
	 * Handle bulk actions.
	 *
	 * @phpcs:disable WordPress.Security.NonceVerification.Missing
	 * @phpcs:disable WordPress.Security.NonceVerification.Recommended
	 * @phpcs:disable Generic.Metrics.NestingLevel.TooHigh
	 * @phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
	 * @phpcs:disable Generic.Metrics.CyclomaticComplexity.MaxExceeded
	 * @phpcs:disable SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
	 */
	public function process_bulk_action(): void {
		$action = $this->current_action();

		if ( ! in_array( $action, [ 'delete', 'delete_all' ], true ) ) {
			return;
		}

		$nonce_action = 'bulk-' . esc_attr( $this->_args['plural'] );
		$nonce_value  = isset( $_REQUEST['_wpnonce'] ) && is_string( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

		$nonce = is_string( $nonce_value ) ? $nonce_value : '';

		$nonce_result = wp_verify_nonce( $nonce, $nonce_action );
		if ( false === $nonce_result ) {
			wp_die( esc_html__( 'Nonce verification failed!', 'wpgraphql-logging' ) );
		}

		$deleted_count = 0;

		// WordPress sometimes sends 'delete' for selected items.
		if ( in_array( $action, [ 'delete', 'bulk_delete' ], true ) && ! empty( $_REQUEST['log'] ) ) {
			$ids = array_map( 'absint', (array) $_REQUEST['log'] );
			// Remove redundant empty check since array_map always returns array.
			foreach ( $ids as $id ) {
				if ( $id > 0 ) { // Only process valid IDs.
					$this->log_service->delete_entity_by_id( $id );
				}
			}
			$deleted_count = count( array_filter( $ids, static fn( $id ) => $id > 0 ) );
		}

		if ( 'delete_all' === $action ) {
			$count_before_delete = $this->log_service->count_entities_by_where( [] );
			$this->log_service->delete_all_entities();
			$deleted_count = $count_before_delete;
		}

		if ( $deleted_count <= 0 ) {
			return;
		}

		$preserved_filters = [];
		$filter_keys       = [ 'level_filter', 'start_date', 'end_date' ];

		foreach ( $filter_keys as $key ) {
			$value = isset( $_REQUEST[ $key ] ) && is_string( $_REQUEST[ $key ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ) : null;
			if ( ! empty( $value ) ) {
				$preserved_filters[ $key ] = $value;
			}
		}

		$redirect_url = remove_query_arg( [ 'action', 'action2', 'log', '_wpnonce' ] );
		$redirect_url = add_query_arg(
			array_merge(
				[ 'deleted_count' => $deleted_count ],
				$preserved_filters
			),
			$redirect_url
		);

		if ( ! headers_sent() ) {
			wp_safe_redirect( esc_url_raw( $redirect_url ) );
			exit;
		}

		echo '<meta http-equiv="refresh" content="0;url=' . esc_url( $redirect_url ) . '">';
		printf(
			'<div class="notice notice-success"><p>%s <a href="%s">%s</a></p></div>',
			esc_html__( 'Logs deleted successfully.', 'wpgraphql-logging' ),
			esc_url( $redirect_url ),
			esc_html__( 'Return to Logs', 'wpgraphql-logging' )
		);
		exit;
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
				'request_headers' => __( 'Headers', 'wpgraphql-logging' ),
				'memory_usage'    => __( 'Memory Usage', 'wpgraphql-logging' ),
			]
		);
	}

	/**
	 * Get the default column value for a log entry.
	 *
	 * @param mixed|\WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity $item The log entry item.
	 * @param string                                                           $column_name The column name.
	 *
	 * @phpcs:disable Generic.Metrics.CyclomaticComplexity.MaxExceeded
	 *
	 * @return mixed The default column value or null.
	 */
	public function column_default( $item, $column_name ): mixed {
		if ( ! $item instanceof WordPressDatabaseEntity ) {
			return null;
		}

		$value = '';

		switch ( $column_name ) {
			case 'date':
				$value = $item->get_datetime();
				break;
			case 'channel':
				$value = $item->get_channel();
				break;
			case 'level':
				$value = $item->get_level();
				break;
			case 'level_name':
				$value = $item->get_level_name();
				break;
			case 'message':
				$value = $item->get_message();
				break;
			case 'event':
				$value = $this->get_event( $item );
				break;
			case 'process_id':
				$value = $this->get_process_id( $item );
				break;
			case 'memory_usage':
				$value = $this->get_memory_usage( $item );
				break;
			case 'wpgraphql_query':
				$value = $this->get_query( $item );
				break;
			case 'request_headers':
				$value = $this->get_request_headers( $item );
				break;
		}

		return apply_filters( 'wpgraphql_logging_logs_table_column_value', $value, $item, $column_name );
	}

	/**
	 * Renders the checkbox column for a log entry.
	 *
	 * @param mixed|\WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity $item The log entry item.
	 *
	 * @return string The rendered checkbox column or null.
	 */
	public function column_cb( $item ): string {
		if ( ! $item instanceof WordPressDatabaseEntity ) {
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
	 * @param \WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity $item The log entry item.
	 *
	 * @return string The rendered ID column or null.
	 */
	public function column_id( WordPressDatabaseEntity $item ): string {
		$url            = \WPGraphQL\Logging\Admin\ViewLogsPage::ADMIN_PAGE_SLUG;
		$download_nonce = wp_create_nonce( 'wpgraphql-logging-download_' . $item->get_id() );
		$actions        = [
			'view'     => sprintf(
				'<a href="?page=%s&action=%s&log=%d">%s</a>',
				esc_attr( $url ),
				'view',
				$item->get_id(),
				esc_html__( 'View', 'wpgraphql-logging' )
			),
			'download' => sprintf(
				'<a href="?page=%s&action=%s&log=%d&_wpnonce=%s">%s</a>',
				esc_attr( $url ),
				'download',
				$item->get_id(),
				esc_attr( $download_nonce ),
				esc_html__( 'Download', 'wpgraphql-logging' )
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
	 * @param \WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity $item The log entry item.
	 *
	 * @return string|null The rendered query column or null.
	 */
	public function column_query( WordPressDatabaseEntity $item ): ?string {
		$extra = $item->get_extra();
		return ! empty( $extra['wpgraphql_query'] ) ? esc_html( $extra['wpgraphql_query'] ) : '';
	}

	/**
	 * Gets the query from extra.
	 *
	 * @return string The query
	 */
	public function get_query(WordPressDatabaseEntity $item): string {
		$query = $item->get_query();
		if ( ! is_string( $query ) || '' === $query ) {
			return '';
		}
		return $this->format_code( $query );
	}

	/**
	 * Gets the event from extra.
	 *
	 * @return string The event
	 */
	public function get_event(WordPressDatabaseEntity $item): string {

		$extra = $item->get_extra();
		return ! empty( $extra['wpgraphql_event'] ) ? esc_html( $extra['wpgraphql_event'] ) : $item->get_message();
	}

	/**
	 * Gets the event from extra.
	 *
	 * @param \WPGraphQL\Logging\Logger\Database\WordPressDatabaseEntity $item The log entry item.
	 *
	 * @return int The event
	 */
	public function get_process_id(WordPressDatabaseEntity $item): int {
		$extra = $item->get_extra();
		return ! empty( $extra['process_id'] ) ? (int) $extra['process_id'] : 0;
	}

	/**
	 * Gets the event from extra.
	 *
	 * @return string The event
	 */
	public function get_memory_usage(WordPressDatabaseEntity $item): string {
		$extra = $item->get_extra();
		return ! empty( $extra['memory_peak_usage'] ) ? esc_html( $extra['memory_peak_usage'] ) : '';
	}

	/**
	 * Gets the request headers from extra.
	 *
	 * @return string The event
	 */
	public function get_request_headers(WordPressDatabaseEntity $item): string {
		$extra           = $item->get_extra();
		$request_headers = $extra['request_headers'] ?? [];
		if ( empty( $request_headers ) || ! is_array( $request_headers ) ) {
			return '';
		}

		$formatted_request_headers = wp_json_encode( $request_headers, JSON_PRETTY_PRINT );
		if ( false === $formatted_request_headers ) {
			return '';
		}
		return $this->format_code( $formatted_request_headers );
	}

	/**
	 * Format code for display in a table cell.
	 *
	 * @param string $code The code to format.
	 *
	 * @return string The formatted code.
	 */
	protected function format_code(string $code): string {
		if ( empty( $code ) ) {
			return '';
		}
		return '<pre style="overflow-x: auto; background: #f4f4f4; padding: 15px; border: 1px solid #ddd; border-radius: 4px; max-height: 300px;">' . esc_html( $code ) . '</pre>';
	}

	/**
	 * Process the where clauses for filtering.
	 *
	 * @param array<string, mixed> $request The request data.
	 *
	 * @return array<string, string> The where clauses.
	 */
	protected function process_where(array $request): array {
		$where_clauses = [];

		if ( ! empty( $request['wpgraphql_logging_nonce'] ) && false === wp_verify_nonce( $request['wpgraphql_logging_nonce'], 'wpgraphql_logging_filter' ) ) {
			return [];
		}

		if ( ! empty( $request['level_filter'] ) ) {
			$level           = sanitize_text_field( wp_unslash( (string) $request['level_filter'] ) );
			$where_clauses[] = [
				'column'   => 'level_name',
				'operator' => '=',
				'value'    => $level,
			];
		}

		if ( ! empty( $request['start_date'] ) ) {
			$start_date      = sanitize_text_field( $request['start_date'] );
			$date            = new DateTime( $start_date );
			$where_clauses[] = [
				'column'   => 'datetime',
				'operator' => '>=',
				'value'    => $date->format( 'Y-m-d H:i:s' ),
			];
		}

		if ( ! empty( $request['end_date'] ) ) {
			$end_date        = sanitize_text_field( $request['end_date'] );
			$date            = new DateTime( $end_date );
			$where_clauses[] = [
				'column'   => 'datetime',
				'operator' => '<=',
				'value'    => $date->format( 'Y-m-d H:i:s' ),
			];
		}

		// Allow developers to modify the where clauses.
		return apply_filters( 'wpgraphql_logging_logs_table_where_clauses', $where_clauses, $request );
	}

	/**
	 * Get a list of sortable columns.
	 *
	 * @return array<string, array{0: string, 1: bool}> The sortable columns.
	 */
	protected function get_sortable_columns(): array {
		return [
			'id'         => [ 'id', false ],
			'date'       => [ 'datetime', true ],
			'level'      => [ 'level', false ],
			'level_name' => [ 'level_name', false ],
		];
	}

	/**
	 * Display the table navigation and actions.
	 *
	 * @param string $which The location of the nav ('top' or 'bottom').
	 */
	protected function display_tablenav( $which ): void {
		$which_position = ( 'top' === $which ) ? 'top' : 'bottom';
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<?php if ( 'top' === $which ) : ?>
				<?php $this->render_custom_filters(); ?>
				<?php wp_nonce_field( 'bulk-' . $this->_args['plural'] ); ?>
			<?php endif; ?>

			<div class="alignleft actions bulkactions">
				<?php $this->bulk_actions( $which_position ); ?>
			</div>

			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which_position );
			?>

			<br class="clear" />
		</div>
		<?php
	}

	/**
	 * Render custom filter controls.
	 */
	protected function render_custom_filters(): void {
		$template = apply_filters(
			'wpgraphql_logging_filters_template',
			__DIR__ . '/../Templates/WPGraphQLLoggerFilters.php'
		);

		if ( ! file_exists( $template ) ) {
			return;
		}

		echo '<div class="alignleft actions">';
		require $template; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		echo '</div>';
	}
}
