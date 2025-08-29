<?php
/**
 * Webhooks List Table
 *
 * @package WPGraphQL\Webhooks\Admin
 */

namespace WPGraphQL\Webhooks\Admin;

use WPGraphQL\Webhooks\Repository\WebhookRepository;

// Include the WP_List_Table class if not already loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Webhooks List Table class extending WP_List_Table
 */
class WebhooksListTable extends \WP_List_Table {

	/**
	 * Repository instance
	 *
	 * @var WebhookRepository
	 */
	private $repository;

	/**
	 * Constructor
	 *
	 * @param WebhookRepository $repository Repository instance.
	 */
	public function __construct( WebhookRepository $repository ) {
		$this->repository = $repository;
		
		parent::__construct( [
			'singular' => __( 'Webhook', 'graphql-webhooks' ),
			'plural'   => __( 'Webhooks', 'graphql-webhooks' ),
			'ajax'     => false,
		] );
	}

	/**
	 * Get columns
	 *
	 * @return array
	 */
	public function get_columns() {
		return [
			'cb'      => '<input type="checkbox" />',
			'name'    => __( 'Name', 'graphql-webhooks' ),
			'event'   => __( 'Event', 'graphql-webhooks' ),
			'method'  => __( 'Method', 'graphql-webhooks' ),
			'url'     => __( 'URL', 'graphql-webhooks' ),
			'headers' => __( 'Headers', 'graphql-webhooks' ),
		];
	}

	/**
	 * Get sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return [
			'name'   => [ 'name', false ],
			'event'  => [ 'event', false ],
			'method' => [ 'method', false ],
		];
	}

	/**
	 * Get bulk actions
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return [
			'delete' => __( 'Delete', 'graphql-webhooks' ),
		];
	}


	/**
	 * Process bulk actions
	 */
	public function process_bulk_action() {
		// Only handle delete action
		if ( 'delete' !== $this->current_action() ) {
			return;
		}

		// Verify nonce
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'bulk-' . $this->_args['plural'] ) ) {
			wp_die( esc_html__( 'Security check failed.', 'graphql-webhooks' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'graphql-webhooks' ) );
		}

		// Get selected webhooks
		$webhook_ids = isset( $_REQUEST['webhook'] ) ? array_map( 'intval', (array) $_REQUEST['webhook'] ) : [];
		if ( empty( $webhook_ids ) ) {
			return;
		}

		// Delete webhooks
		$deleted = 0;
		foreach ( $webhook_ids as $webhook_id ) {
			if ( $this->repository->delete( $webhook_id ) ) {
				$deleted++;
			}
		}

		// Redirect with success message
		if ( $deleted > 0 ) {
			wp_redirect( add_query_arg( [ 'deleted' => $deleted ], remove_query_arg( [ 'action', 'action2', 'webhook', '_wpnonce' ] ) ) );
			exit;
		}
	}

	/**
	 * Prepare items for display
	 */
	public function prepare_items() {
		// Process bulk actions first
		$this->process_bulk_action();
		
		$per_page = $this->get_items_per_page( 'webhooks_per_page', 20 );
		$current_page = $this->get_pagenum();
		
		// Get all webhooks
		$webhooks = $this->repository->get_all();
		$total_items = count( $webhooks );
		
		// Handle sorting
		$orderby = ! empty( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'name';
		$order = ! empty( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'asc';
		
		usort( $webhooks, function( $a, $b ) use ( $orderby, $order ) {
			$result = 0;
			
			switch ( $orderby ) {
				case 'name':
					$result = strcmp( $a->name, $b->name );
					break;
				case 'event':
					$result = strcmp( $a->event, $b->event );
					break;
				case 'method':
					$result = strcmp( $a->method, $b->method );
					break;
			}
			
			return ( 'asc' === $order ) ? $result : -$result;
		} );
		
		// Pagination
		$this->items = array_slice( $webhooks, ( $current_page - 1 ) * $per_page, $per_page );
		
		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		] );
		
		$columns = $this->get_columns();
		$hidden = [];
		$sortable = $this->get_sortable_columns();
		
		$this->_column_headers = [ $columns, $hidden, $sortable ];
	}

	/**
	 * Default column renderer
	 *
	 * @param object $item        Webhook item.
	 * @param string $column_name Column name.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'name':
				return esc_html( $item->name );
			case 'event':
				$events = $this->repository->get_allowed_events();
				return esc_html( $events[ $item->event ] ?? $item->event );
			case 'url':
				return '<span class="webhook-url" title="' . esc_attr( $item->url ) . '">' . esc_html( $item->url ) . '</span>';
			case 'method':
				return '<span class="webhook-method">' . esc_html( $item->method ) . '</span>';
			case 'headers':
				$count = is_array( $item->headers ) ? count( $item->headers ) : 0;
				return $count > 0 ? sprintf( __( '%d headers', 'graphql-webhooks' ), $count ) : '—';
			default:
				return '';
		}
	}

	/**
	 * Checkbox column
	 *
	 * @param object $item Webhook item.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="webhook[]" value="%s" />',
			$item->id
		);
	}

	/**
	 * Name column with row actions
	 *
	 * @param object $item Webhook item.
	 * @return string
	 */
	public function column_name( $item ) {
		$edit_url = add_query_arg( [
			'page'   => 'graphql-webhooks',
			'action' => 'edit',
			'id'     => $item->id,
		], admin_url( 'admin.php' ) );
		
		$delete_url = wp_nonce_url(
			add_query_arg( [
				'page'   => 'graphql-webhooks',
				'action' => 'delete',
				'webhook' => $item->id,
			], admin_url( 'admin.php' ) ),
			'delete-webhook-' . $item->id
		);
		
		$actions = [
			'edit' => sprintf( '<a href="%s">%s</a>', esc_url( $edit_url ), __( 'Edit', 'graphql-webhooks' ) ),
			'test' => sprintf( '<a href="#" class="test-webhook" data-webhook-id="%d">%s</a>', $item->id, __( 'Test', 'graphql-webhooks' ) ),
			'delete' => sprintf( '<a href="%s" class="submitdelete">%s</a>', esc_url( $delete_url ), __( 'Delete', 'graphql-webhooks' ) ),
		];
		
		return sprintf(
			'<strong><a href="%s">%s</a></strong>%s',
			esc_url( $edit_url ),
			esc_html( $item->name ),
			$this->row_actions( $actions )
		);
	}

	/**
	 * Display when no items
	 */
	public function no_items() {
		esc_html_e( 'No webhooks found.', 'graphql-webhooks' );
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * @param string $which Top or bottom.
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
			?>
			<div class="alignleft actions">
				<?php
				// Add filter dropdowns here if needed in the future
				?>
			</div>
			<?php
		}
	}
}
