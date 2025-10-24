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

$wpgraphql_logging_current_level      = isset( $_POST['level_filter'] ) ? sanitize_text_field( wp_unslash( $_POST['level_filter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
$wpgraphql_logging_current_start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
$wpgraphql_logging_current_end_date   = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

// Currently only use info and error.
$wpgraphql_logging_log_levels = [ 'info', 'error' ];
$wpgraphql_logging_log_levels = apply_filters( 'wpgraphql_logging_log_levels', $wpgraphql_logging_log_levels );
?>

<div class="alignleft actions" style="display: inline-flex; align-items: center; gap: 8px; margin-right: 10px;">
	<input type="text"
			name="start_date"
			class="wpgraphql-logging-datepicker"
			placeholder="Start Date"
			value="<?php echo esc_attr( $wpgraphql_logging_current_start_date ); ?>"
			style="width: 120px;" />

	<input type="text"
			name="end_date"
			class="wpgraphql-logging-datepicker"
			placeholder="End Date"
			value="<?php echo esc_attr( $wpgraphql_logging_current_end_date ); ?>"
			style="width: 120px;" />

	<select name="level_filter" style="width: 120px;">
		<option value="">All Levels</option>
		<?php foreach ( $wpgraphql_logging_log_levels as $wpgraphql_logging_level ) : ?>
			<option value="<?php echo esc_attr( $wpgraphql_logging_level ); ?>" <?php selected( $wpgraphql_logging_current_level, $wpgraphql_logging_level ); ?>>
				<?php echo esc_html( ucfirst( $wpgraphql_logging_level ) ); ?>
			</option>
		<?php endforeach; ?>
	</select>

	<?php submit_button( __( 'Filter', 'wpgraphql-logging' ), 'secondary', '', false, [ 'style' => 'margin: 0;' ] ); ?>

	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpgraphql-logging-view' ) ); ?>"
		class="clear-all-button"
		style="margin: 0; margin-left: 5px; text-decoration: none;">
		<?php esc_html_e( 'Clear All', 'wpgraphql-logging' ); ?>
	</a>
</div>
