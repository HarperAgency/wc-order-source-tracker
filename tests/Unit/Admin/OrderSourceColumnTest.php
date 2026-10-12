<?php
/**
 * Unit tests for OrderSourceColumn.
 *
 * @package ClarityMomentum\OrderSourceTracker\Tests\Unit\Admin
 */

declare(strict_types=1);

namespace ClarityMomentum\OrderSourceTracker\Tests\Unit\Admin;

use ClarityMomentum\OrderSourceTracker\Admin\OrderSourceColumn;
use ClarityMomentum\OrderSourceTracker\Tracker\SourceClassifier;
use PHPUnit\Framework\TestCase;

class OrderSourceColumnTest extends TestCase {

	private SourceClassifier $classifier;

	protected function setUp(): void {
		$this->classifier = new SourceClassifier();
	}

	// ── addColumn() ──────────────────────────────────────────────────────────

	public function testAddColumnInsertsCmSourceKey(): void {
		$column = new OrderSourceColumn( $this->classifier );
		$cols   = [ 'order_status' => 'Status', 'order_date' => 'Date' ];

		$result = $column->addColumn( $cols );

		$this->assertArrayHasKey( 'cm_source', $result );
	}

	public function testAddColumnInsertsAfterOrderStatus(): void {
		$column = new OrderSourceColumn( $this->classifier );
		$cols   = [ 'order_status' => 'Status', 'order_date' => 'Date' ];

		$result = $column->addColumn( $cols );
		$keys   = array_keys( $result );

		$status_pos = array_search( 'order_status', $keys, true );
		$source_pos = array_search( 'cm_source', $keys, true );

		$this->assertSame( $status_pos + 1, $source_pos );
	}

	public function testAddColumnPreservesExistingColumns(): void {
		$column = new OrderSourceColumn( $this->classifier );
		$cols   = [ 'order_status' => 'Status', 'order_date' => 'Date', 'order_total' => 'Total' ];

		$result = $column->addColumn( $cols );

		$this->assertArrayHasKey( 'order_status', $result );
		$this->assertArrayHasKey( 'order_date', $result );
		$this->assertArrayHasKey( 'order_total', $result );
	}

	// ── renderColumn() ───────────────────────────────────────────────────────

	public function testRenderColumnOutputsSourceLabelForPaidSearchOrder(): void {
		$column = new OrderSourceColumn( $this->classifier );
		$order  = $this->makeOrderWithMeta( '_cm_ost_first', [ 'utm_source' => 'google', 'utm_medium' => 'cpc', 'source_type' => 'paid_search' ] );

		ob_start();
		$column->renderColumn( 'cm_source', $order );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'google', $output );
	}

	public function testRenderColumnOutputsDashWhenNoSourceMeta(): void {
		$column = new OrderSourceColumn( $this->classifier );
		$order  = $this->makeOrderWithMeta( '_cm_ost_first', null );

		ob_start();
		$column->renderColumn( 'cm_source', $order );
		$output = ob_get_clean();

		$this->assertStringContainsString( '&mdash;', $output );
	}

	public function testRenderColumnSkipsNonCmSourceColumn(): void {
		$column = new OrderSourceColumn( $this->classifier );
		$order  = $this->makeOrderWithMeta( '_cm_ost_first', [ 'utm_source' => 'google' ] );

		ob_start();
		$column->renderColumn( 'order_date', $order );
		$output = ob_get_clean();

		$this->assertSame( '', $output );
	}

	// ── Helpers ──────────────────────────────────────────────────────────────

	private function makeOrderWithMeta( string $key, mixed $value ): \WC_Order {
		return new class( $key, $value ) extends \WC_Order {
			public function __construct(
				private readonly string $metaKey,
				private readonly mixed $metaValue,
			) {}

			public function get_id(): int {
				return 42;
			}

			public function get_meta( string $key, bool $single = true ): mixed {
				return $key === $this->metaKey ? $this->metaValue : null;
			}
		};
	}
}
