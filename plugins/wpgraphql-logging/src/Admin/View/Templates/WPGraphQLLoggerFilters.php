<?php
/**
 * Log filters template.
 *
 * @package WPGraphQL\Logging
 *
 * @since 0.0.1
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Filters are submitted by POST and redirected to GET, so read them from the request.
$wpgraphql_logging_current_level      = isset( $_REQUEST['level_filter'] ) && is_string( $_REQUEST['level_filter'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['level_filter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$wpgraphql_logging_current_start_date = isset( $_REQUEST['start_date'] ) && is_string( $_REQUEST['start_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['start_date'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$wpgraphql_logging_current_end_date   = isset( $_REQUEST['end_date'] ) && is_string( $_REQUEST['end_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['end_date'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

// Currently only use info and error.
$wpgraphql_logging_log_levels = [ 'info', 'error' ];
$wpgraphql_logging_log_levels = apply_filters( 'wpgraphql_logging_log_levels', $wpgraphql_logging_log_levels );
?>

<div class="alignleft actions wpgraphql-logging-filters">
	<?php wp_nonce_field( 'wpgraphql_logging_filter', 'wpgraphql_logging_nonce', false ); ?>

	<input type="text"
			name="start_date"
			class="wpgraphql-logging-datepicker"
			placeholder="Start Date"
			value="<?php echo esc_attr( $wpgraphql_logging_current_start_date ); ?>"
			autocomplete="off" />

	<input type="text"
			name="end_date"
			class="wpgraphql-logging-datepicker"
			placeholder="End Date"
			value="<?php echo esc_attr( $wpgraphql_logging_current_end_date ); ?>"
			autocomplete="off" />

	<select name="level_filter" aria-label="<?php esc_attr_e( 'Filter by level', 'wpgraphql-logging' ); ?>">
		<option value="">All Levels</option>
		<?php foreach ( $wpgraphql_logging_log_levels as $wpgraphql_logging_level ) : ?>
			<option value="<?php echo esc_attr( $wpgraphql_logging_level ); ?>" <?php selected( $wpgraphql_logging_current_level, $wpgraphql_logging_level ); ?>>
				<?php echo esc_html( ucfirst( $wpgraphql_logging_level ) ); ?>
			</option>
		<?php endforeach; ?>
	</select>

	<?php submit_button( __( 'Filter', 'wpgraphql-logging' ), 'secondary', '', false, [ 'style' => 'margin: 0;' ] ); ?>

	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpgraphql-logging-view' ) ); ?>"
		class="clear-all-button">
		<?php esc_html_e( 'Clear All', 'wpgraphql-logging' ); ?>
	</a>
</div>
