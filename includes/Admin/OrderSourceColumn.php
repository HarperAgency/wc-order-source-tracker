<?php
/**
 * Adds a Source column to the WooCommerce orders list (HPOS + legacy).
 *
 * @package ClarityMomentum\OrderSourceTracker\Admin
 */

declare(strict_types=1);

namespace ClarityMomentum\OrderSourceTracker\Admin;

use ClarityMomentum\OrderSourceTracker\Tracker\SourceClassifier;

class OrderSourceColumn {

	public function __construct(
		private readonly SourceClassifier $classifier,
	) {}

	/**
	 * Register WordPress hooks.
	 */
	public function register(): void {
		// HPOS.
		add_filter( 'manage_woocommerce_page_wc-orders_columns', [ $this, 'addColumn' ] );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', [ $this, 'renderColumn' ], 10, 2 );

		// Legacy post-based orders.
		add_filter( 'manage_edit-shop_order_columns', [ $this, 'addColumn' ] );
		add_action( 'manage_shop_order_posts_custom_column', [ $this, 'renderLegacyColumn' ], 10, 2 );

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ] );
	}

	/**
	 * Insert the cm_source column after order_status.
	 *
	 * @param array<string, string> $columns
	 * @return array<string, string>
	 */
	public function addColumn( array $columns ): array {
		$result = [];
		foreach ( $columns as $key => $label ) {
			$result[ $key ] = $label;
			if ( 'order_status' === $key ) {
				$result['cm_source'] = __( 'Source', 'cm-order-source-tracker' );
			}
		}
		// Fallback: if order_status not found, append.
		if ( ! isset( $result['cm_source'] ) ) {
			$result['cm_source'] = __( 'Source', 'cm-order-source-tracker' );
		}
		return $result;
	}

	/**
	 * Render the column for HPOS orders list.
	 *
	 * @param string    $column Column key.
	 * @param \WC_Order $order
	 */
	public function renderColumn( string $column, \WC_Order $order ): void {
		if ( 'cm_source' !== $column ) {
			return;
		}
		$this->renderSourceCell( $order );
	}

	/**
	 * Render the column for legacy (post-based) orders list.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 */
	public function renderLegacyColumn( string $column, int $post_id ): void {
		if ( 'cm_source' !== $column ) {
			return;
		}
		$order = wc_get_order( $post_id );
		if ( ! $order instanceof \WC_Order ) {
			echo '<span class="cm-ost-source cm-ost-source--empty">&mdash;</span>';
			return;
		}
		$this->renderSourceCell( $order );
	}

	/**
	 * Enqueue column CSS on the orders admin page.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueueAssets( string $hook ): void {
		if ( 'edit.php' !== $hook && 'woocommerce_page_wc-orders' !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'cm-ost-admin',
			plugin_dir_url( CM_OST_FILE ) . 'admin/css/order-source.css',
			[],
			CM_OST_VERSION,
		);
	}

	/**
	 * Output the source cell HTML for a given order.
	 */
	private function renderSourceCell( \WC_Order $order ): void {
		$first = $order->get_meta( '_cm_ost_first', true );

		if ( ! is_array( $first ) || empty( $first ) ) {
			echo '<span class="cm-ost-source cm-ost-source--empty">&mdash;</span>';
			return;
		}

		$label       = esc_html( $this->classifier->label( $first ) );
		$source_type = esc_attr( $first['source_type'] ?? 'direct' );

		printf(
			'<span class="cm-ost-source cm-ost-source--%s">%s</span>',
			$source_type,
			$label,
		);
	}
}
