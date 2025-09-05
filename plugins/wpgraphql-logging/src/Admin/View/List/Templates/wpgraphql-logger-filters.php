<?php

declare(strict_types=1);

/**
 * Loggers list view template using WP_List_Table.
 *
 * @package WPGraphQL\Logger\Admin\View\List\Templates
 *
 * @var \WPGraphQL\Logging\Admin\View\List\List_Table $list_table List table instance.
 *
 * @since 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="alignleft actions">
	<?php
	// Nonce for security.
	wp_nonce_field( 'wpgraphql_logging_filter', 'wpgraphql_logging_nonce' );

	// Get current filter values.
	$wpgraphql_logging_current_level      = '';
	$wpgraphql_logging_current_start_date = '';
	$wpgraphql_logging_current_end_date   = '';

	$wpgraphql_logging_current_level      = isset( $_GET['level_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['level_filter'] ) ) : ''; // @phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$wpgraphql_logging_current_start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : ''; // @phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$wpgraphql_logging_current_end_date   = isset( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) : ''; // @phpcs:ignore WordPress.Security.NonceVerification.Recommended

	/**
	 * Log levels for filtering.
	 * Based on Monolog levels.
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc5424
	 */
	$wpgraphql_logging_log_levels = [ 'debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency' ];
	?>
	<input type="text" name="start_date" placeholder="Start Date (YYYY-MM-DD)" value="<?php echo esc_attr( $wpgraphql_logging_current_start_date ); ?>" />
	<input type="text" name="end_date" placeholder="End Date (YYYY-MM-DD)" value="<?php echo esc_attr( $wpgraphql_logging_current_end_date ); ?>" />

	<select name="level_filter">
		<option value="">All Levels</option>
		<?php foreach ( $wpgraphql_logging_log_levels as $wpgraphql_logging_log_level ) : ?>
			<option value="<?php echo esc_attr( $wpgraphql_logging_log_level ); ?>" <?php selected( $wpgraphql_logging_current_level, $wpgraphql_logging_log_level ); ?>>
				<?php echo esc_html( ucfirst( $wpgraphql_logging_log_level ) ); ?>
			</option>
		<?php endforeach; ?>
	</select>

	<?php submit_button( __( 'Filter', 'wpgraphql-logging' ), 'secondary', '', false ); ?>

</div>
