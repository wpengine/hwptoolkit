<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Admin\Dashboard;

/**
 * Renders a stacked column chart as server-side inline SVG.
 *
 * No JavaScript or third-party chart library is used. Each column has a native
 * SVG <title> for hover, and the dashboard template pairs the chart with a data table.
 *
 * @package WPGraphQL\Logging
 *
 * @since 1.1.0
 */
class TimeSeriesChart {
	/**
	 * SVG viewBox width.
	 *
	 * @var int
	 */
	public const WIDTH = 720;

	/**
	 * SVG viewBox height.
	 *
	 * @var int
	 */
	public const HEIGHT = 240;

	/**
	 * Plot margins: top, right, bottom, left.
	 *
	 * @var array<int, float>
	 */
	protected const MARGIN = [ 12.0, 8.0, 28.0, 48.0 ];

	/**
	 * Maximum column width so a short range (e.g. 7 days) does not draw slabs.
	 *
	 * @var float
	 */
	protected const MAX_BAR_WIDTH = 40.0;

	/**
	 * Gap in pixels between stacked segments.
	 *
	 * @var float
	 */
	protected const SEGMENT_GAP = 2.0;

	/**
	 * Corner radius for the top (data end) of each column.
	 *
	 * @var float
	 */
	protected const RADIUS = 4.0;

	/**
	 * Outputs the chart SVG.
	 *
	 * @param array<int, array{label: string, full_label: string, total: int, errors: int}> $points      The data points, oldest first.
	 * @param string                                                                        $id          Unique ID prefix for the SVG title/description.
	 * @param string                                                                        $title       Accessible chart title.
	 * @param string                                                                        $description Accessible chart description.
	 */
	public function render( array $points, string $id, string $title, string $description ): void {
		$points    = array_values( $points );
		$max_value = 0;
		foreach ( $points as $point ) {
			$max_value = max( $max_value, $point['total'] );
		}
		[ $axis_max, $step ] = self::nice_scale( $max_value );

		printf(
			'<svg class="wpgraphql-logging-chart__svg" viewBox="0 0 %1$s %2$s" preserveAspectRatio="xMidYMid meet" role="img" aria-labelledby="%3$s-title %3$s-desc" focusable="false">',
			esc_attr( self::num( self::WIDTH ) ),
			esc_attr( self::num( self::HEIGHT ) ),
			esc_attr( $id )
		);
		printf( '<title id="%1$s-title">%2$s</title>', esc_attr( $id ), esc_html( $title ) );
		printf( '<desc id="%1$s-desc">%2$s</desc>', esc_attr( $id ), esc_html( $description ) );

		$this->render_grid( $axis_max, $step );

		$count      = max( 1, count( $points ) );
		$label_step = (int) max( 1, ceil( $count / 8 ) );
		foreach ( $points as $index => $point ) {
			$this->render_column( $point, $index, $count, $axis_max, 0 === $index % $label_step );
		}

		echo '</svg>';
	}

	/**
	 * Computes a "nice" axis maximum and tick step for a maximum data value.
	 *
	 * @param int $max_value The largest value to plot.
	 *
	 * @return array{0: int, 1: int} The axis maximum and the tick step.
	 */
	public static function nice_scale( int $max_value ): array {
		if ( $max_value <= 0 ) {
			return [ 4, 1 ];
		}

		$raw_step  = max( 1.0, (float) $max_value / 4.0 );
		$magnitude = 10.0 ** floor( log10( $raw_step ) );
		$residual  = $raw_step / $magnitude;

		// Pick the smallest of 1, 2, 5 or 10 (times the magnitude) that covers the raw step.
		$nice = 10.0;
		foreach ( [ 1.0, 2.0, 5.0 ] as $candidate ) {
			if ( $residual <= $candidate ) {
				$nice = $candidate;
				break;
			}
		}

		$step = max( 1, (int) round( $nice * $magnitude ) );

		return [ (int) ceil( $max_value / $step ) * $step, $step ];
	}

	/**
	 * Outputs recessive horizontal grid lines and y-axis labels.
	 *
	 * @param int $axis_max The axis maximum.
	 * @param int $step     The tick step.
	 */
	protected function render_grid( int $axis_max, int $step ): void {
		$plot = self::get_plot_area();

		echo '<g class="wpgraphql-logging-chart__grid" aria-hidden="true">';
		for ( $value = 0; $value <= $axis_max; $value += $step ) {
			$y = $plot['baseline'] - ( (float) $value / (float) $axis_max ) * $plot['height'];
			printf(
				'<line x1="%1$s" x2="%2$s" y1="%3$s" y2="%3$s" class="%4$s" />',
				esc_attr( self::num( $plot['left'] ) ),
				esc_attr( self::num( $plot['left'] + $plot['width'] ) ),
				esc_attr( self::num( $y ) ),
				0 === $value ? 'wpgraphql-logging-chart__axis' : 'wpgraphql-logging-chart__gridline'
			);
			printf(
				'<text x="%1$s" y="%2$s" text-anchor="end" dominant-baseline="middle" class="wpgraphql-logging-chart__tick">%3$s</text>',
				esc_attr( self::num( $plot['left'] - 6.0 ) ),
				esc_attr( self::num( $y ) ),
				esc_html( number_format_i18n( $value ) )
			);
		}
		echo '</g>';
	}

	/**
	 * Outputs one stacked column: non-error entries at the baseline, errors on top.
	 *
	 * @param array{label: string, full_label: string, total: int, errors: int} $point      The data point.
	 * @param int                                                               $index      The column index.
	 * @param int                                                               $count      The number of columns.
	 * @param int                                                               $axis_max   The axis maximum.
	 * @param bool                                                              $show_label Whether to draw the x-axis label.
	 */
	protected function render_column( array $point, int $index, int $count, int $axis_max, bool $show_label ): void {
		$plot       = self::get_plot_area();
		$slot_width = $plot['width'] / (float) $count;
		$bar_width  = min( self::MAX_BAR_WIDTH, max( 2.0, $slot_width - max( 2.0, $slot_width * 0.3 ) ) );
		$slot_x     = $plot['left'] + ( (float) $index * $slot_width );
		$bar_x      = $slot_x + ( ( $slot_width - $bar_width ) / 2.0 );
		$errors     = max( 0, $point['errors'] );
		$others     = max( 0, $point['total'] - $errors );
		$scale      = $plot['height'] / (float) $axis_max;

		echo '<g class="wpgraphql-logging-chart__slot">';
		printf(
			'<title>%s</title>',
			esc_html(
				sprintf(
					/* translators: 1: date/time label, 2: number of log entries, 3: number of error entries. */
					__( '%1$s: %2$s entries, %3$s errors', 'wpgraphql-logging' ),
					$point['full_label'],
					number_format_i18n( $point['total'] ),
					number_format_i18n( $errors )
				)
			)
		);

		// Hit target covers the full slot so small columns are still easy to hover.
		printf(
			'<rect class="wpgraphql-logging-chart__hit" x="%1$s" y="%2$s" width="%3$s" height="%4$s" />',
			esc_attr( self::num( $slot_x ) ),
			esc_attr( self::num( $plot['top'] ) ),
			esc_attr( self::num( $slot_width ) ),
			esc_attr( self::num( $plot['height'] ) )
		);

		$others_height = (float) $others * $scale;
		if ( $others > 0 ) {
			$this->render_segment( 'wpgraphql-logging-chart__bar--entries', $bar_x, $plot['baseline'] - $others_height, $bar_width, $others_height, 0 === $errors );
		}
		if ( $errors > 0 ) {
			$gap           = $others > 0 ? self::SEGMENT_GAP : 0.0;
			$errors_height = max( 1.0, ( (float) $errors * $scale ) - $gap );
			$this->render_segment( 'wpgraphql-logging-chart__bar--errors', $bar_x, $plot['baseline'] - $others_height - $gap - $errors_height, $bar_width, $errors_height, true );
		}

		if ( $show_label ) {
			printf(
				'<text x="%1$s" y="%2$s" text-anchor="middle" class="wpgraphql-logging-chart__tick" aria-hidden="true">%3$s</text>',
				esc_attr( self::num( $slot_x + ( $slot_width / 2.0 ) ) ),
				esc_attr( self::num( $plot['baseline'] + 18.0 ) ),
				esc_html( $point['label'] )
			);
		}
		echo '</g>';
	}

	/**
	 * Outputs one column segment. The top segment gets rounded top corners; the baseline stays square.
	 *
	 * @param string $css_class The segment CSS class.
	 * @param float  $x         Left edge.
	 * @param float  $y         Top edge.
	 * @param float  $width     Segment width.
	 * @param float  $height    Segment height.
	 * @param bool   $is_top    Whether this is the topmost segment of the column.
	 */
	protected function render_segment( string $css_class, float $x, float $y, float $width, float $height, bool $is_top ): void {
		$radius = $is_top ? min( self::RADIUS, $width / 2.0, $height ) : 0.0;

		if ( $radius <= 0 ) {
			printf(
				'<rect class="%1$s" x="%2$s" y="%3$s" width="%4$s" height="%5$s" />',
				esc_attr( $css_class ),
				esc_attr( self::num( $x ) ),
				esc_attr( self::num( $y ) ),
				esc_attr( self::num( $width ) ),
				esc_attr( self::num( $height ) )
			);
			return;
		}

		$path = sprintf(
			'M%1$s %2$s V%3$s Q%1$s %4$s %5$s %4$s H%6$s Q%7$s %4$s %7$s %3$s V%2$s Z',
			self::num( $x ),
			self::num( $y + $height ),
			self::num( $y + $radius ),
			self::num( $y ),
			self::num( $x + $radius ),
			self::num( $x + $width - $radius ),
			self::num( $x + $width )
		);
		printf( '<path class="%1$s" d="%2$s" />', esc_attr( $css_class ), esc_attr( $path ) );
	}

	/**
	 * Gets the plot area geometry inside the margins.
	 *
	 * @return array{top: float, left: float, width: float, height: float, baseline: float}
	 */
	protected static function get_plot_area(): array {
		[ $top, $right, $bottom, $left ] = self::MARGIN;

		$height = (float) self::HEIGHT - $top - $bottom;

		return [
			'top'      => $top,
			'left'     => $left,
			'width'    => (float) self::WIDTH - $left - $right,
			'height'   => $height,
			'baseline' => $top + $height,
		];
	}

	/**
	 * Formats a coordinate with at most two decimals using a locale-independent decimal point.
	 *
	 * @param float|int $value The value to format.
	 */
	protected static function num( float|int $value ): string {
		$formatted = rtrim( rtrim( number_format( (float) $value, 2, '.', '' ), '0' ), '.' );
		return ( '' === $formatted || '-0' === $formatted ) ? '0' : $formatted;
	}
}
