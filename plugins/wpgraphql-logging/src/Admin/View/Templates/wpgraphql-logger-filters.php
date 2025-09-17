<?php

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Current filter values.
$current_level      = $_POST['level_filter'] ?? '';
$current_start_date = $_POST['start_date'] ?? '';
$current_end_date   = $_POST['end_date'] ?? '';

$log_levels = [ 'debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency' ];
?>

<div class="alignleft actions" style="display: inline-flex; align-items: center; gap: 8px; margin-right: 10px;">
	<input type="text"
			name="start_date"
			class="wpgraphql-logging-datepicker"
			placeholder="Start Date"
			value="<?php echo esc_attr( $current_start_date ); ?>"
			style="width: 120px;" />

	<input type="text"
			name="end_date"
			class="wpgraphql-logging-datepicker"
			placeholder="End Date"
			value="<?php echo esc_attr( $current_end_date ); ?>"
			style="width: 120px;" />

	<select name="level_filter" style="width: 120px;">
		<option value="">All Levels</option>
		<?php foreach ( $log_levels as $level ) : ?>
			<option value="<?php echo esc_attr( $level ); ?>" <?php selected( $current_level, $level ); ?>>
				<?php echo esc_html( ucfirst( $level ) ); ?>
			</option>
		<?php endforeach; ?>
	</select>

	<?php submit_button( __( 'Filter', 'wpgraphql-logging' ), 'secondary', '', false, [ 'style' => 'margin: 0;' ] ); ?>
</div>
