<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\Tests\Admin\Dashboard;

use DOMDocument;
use lucatume\WPBrowser\TestCase\WPTestCase;
use WPGraphQL\Logging\Admin\Dashboard\TimeSeriesChart;

/**
 * Tests for the inline SVG time series chart.
 *
 * @package WPGraphQL\Logging
 *
 * @since 1.1.0
 */
class TimeSeriesChartTest extends WPTestCase {

	/**
	 * Renders a chart and returns the SVG markup.
	 *
	 * @param array<int, array{label: string, full_label: string, total: int, errors: int}> $points
	 */
	private function render( array $points, string $title = 'Entries', string $description = 'Description' ): string {
		ob_start();
		( new TimeSeriesChart() )->render( $points, 'test-chart', $title, $description );
		return (string) ob_get_clean();
	}

	private function point( string $label, int $total, int $errors = 0 ): array {
		return [
			'label'      => $label,
			'full_label' => 'Full ' . $label,
			'total'      => $total,
			'errors'     => $errors,
		];
	}

	public function test_nice_scale(): void {
		$this->assertSame( [ 4, 1 ], TimeSeriesChart::nice_scale( 0 ) );
		$this->assertSame( [ 1, 1 ], TimeSeriesChart::nice_scale( 1 ) );
		$this->assertSame( [ 8, 2 ], TimeSeriesChart::nice_scale( 7 ) );
		$this->assertSame( [ 15, 5 ], TimeSeriesChart::nice_scale( 13 ) );
		$this->assertSame( [ 40, 10 ], TimeSeriesChart::nice_scale( 37 ) );
		$this->assertSame( [ 1000, 500 ], TimeSeriesChart::nice_scale( 999 ) );
	}

	public function test_render_outputs_accessible_well_formed_svg(): void {
		$svg = $this->render(
			[
				$this->point( 'Mon', 10, 2 ),
				$this->point( 'Tue', 0 ),
				$this->point( 'Wed', 3, 3 ),
			]
		);

		$document = new DOMDocument();
		$this->assertTrue( $document->loadXML( $svg ), 'Chart markup should be well-formed XML.' );

		$this->assertStringContainsString( 'role="img"', $svg );
		$this->assertStringContainsString( 'aria-labelledby="test-chart-title test-chart-desc"', $svg );
		$this->assertStringContainsString( '<title id="test-chart-title">Entries</title>', $svg );
		$this->assertStringContainsString( '<desc id="test-chart-desc">Description</desc>', $svg );

		// One hover group per point, each with a native tooltip.
		$this->assertSame( 3, substr_count( $svg, 'class="wpgraphql-logging-chart__slot"' ) );
		$this->assertStringContainsString( '<title>Full Mon: 10 entries, 2 errors</title>', $svg );
		$this->assertStringContainsString( '<title>Full Tue: 0 entries, 0 errors</title>', $svg );

		// Mon has two segments, Tue none, Wed only an error segment.
		$this->assertSame( 2, substr_count( $svg, 'wpgraphql-logging-chart__bar--errors' ) );
		$this->assertSame( 1, substr_count( $svg, 'wpgraphql-logging-chart__bar--entries' ) );
	}

	public function test_render_escapes_text(): void {
		$svg = $this->render( [ $this->point( '<b>x</b>', 1 ) ], 'A & B <script>', 'C "quoted"' );

		$this->assertTrue( ( new DOMDocument() )->loadXML( $svg ) );
		$this->assertStringNotContainsString( '<script>', $svg );
		$this->assertStringNotContainsString( '<b>', $svg );
		$this->assertStringContainsString( 'A &amp; B &lt;script&gt;', $svg );
	}

	public function test_render_handles_no_points(): void {
		$svg = $this->render( [] );

		$this->assertTrue( ( new DOMDocument() )->loadXML( $svg ) );
		$this->assertStringNotContainsString( 'wpgraphql-logging-chart__slot', $svg );
		// The empty axis still draws gridlines from 0 to 4.
		$this->assertSame( 5, substr_count( $svg, '<line ' ) );
	}

	public function test_render_thins_axis_labels_for_many_points(): void {
		$points = [];
		for ( $i = 0; $i < 30; $i++ ) {
			$points[] = $this->point( 'L' . $i, $i );
		}

		$svg = $this->render( $points );

		// 30 points with at most 8 labels: every 4th label is drawn.
		$this->assertSame( 8, substr_count( $svg, 'text-anchor="middle"' ) );
		$this->assertStringContainsString( '>L0<', $svg );
		$this->assertStringContainsString( '>L4<', $svg );
		$this->assertStringNotContainsString( '>L1<', $svg );
	}
}
