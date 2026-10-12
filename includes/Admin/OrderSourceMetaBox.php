<?php
/**
 * UTM detail panel displayed in the WooCommerce order admin view.
 *
 * @package ClarityMomentum\OrderSourceTracker\Admin
 */

declare(strict_types=1);

namespace ClarityMomentum\OrderSourceTracker\Admin;

use ClarityMomentum\OrderSourceTracker\Tracker\SourceClassifier;

class OrderSourceMetaBox {

	public function __construct(
		private readonly SourceClassifier $classifier,
	) {}

	/**
	 * Register WordPress hooks.
	 */
	public function register(): void {
		// HPOS.
		add_action( 'add_meta_boxes', [ $this, 'registerMetaBox' ] );
	}

	/**
	 * Register the meta box for both HPOS and legacy order screens.
	 */
	public function registerMetaBox(): void {
		$screens = [ 'woocommerce_page_wc-orders', 'shop_order' ];
		foreach ( $screens as $screen ) {
			add_meta_box(
				'cm_ost_source',
				__( 'Order Source', 'cm-order-source-tracker' ),
				[ $this, 'render' ],
				$screen,
				'side',
				'default',
			);
		}
	}

	/**
	 * Render the meta box content.
	 *
	 * @param \WC_Order $order
	 */
	public function render( \WC_Order $order ): void {
		$first = $order->get_meta( '_cm_ost_first', true );
		$last  = $order->get_meta( '_cm_ost_last', true );

		echo '<div class="cm-ost-metabox">';

		if ( is_array( $first ) && ! empty( $first ) ) {
			echo '<h4>' . esc_html__( 'First Click', 'cm-order-source-tracker' ) . '</h4>';
			$this->renderAttributionTable( $first );
		}

		if ( is_array( $last ) && ! empty( $last ) ) {
			echo '<h4>' . esc_html__( 'Last Click', 'cm-order-source-tracker' ) . '</h4>';
			$this->renderAttributionTable( $last );
		}

		if ( ( ! is_array( $first ) || empty( $first ) ) && ( ! is_array( $last ) || empty( $last ) ) ) {
			echo '<p>' . esc_html__( 'No attribution data captured.', 'cm-order-source-tracker' ) . '</p>';
		}

		echo '</div>';
	}

	/**
	 * Render a table of attribution fields.
	 *
	 * @param array<string, mixed> $data
	 */
	private function renderAttributionTable( array $data ): void {
		$labels = [
			'source_type'  => __( 'Type', 'cm-order-source-tracker' ),
			'utm_source'   => __( 'Source', 'cm-order-source-tracker' ),
			'utm_medium'   => __( 'Medium', 'cm-order-source-tracker' ),
			'utm_campaign' => __( 'Campaign', 'cm-order-source-tracker' ),
			'utm_content'  => __( 'Content', 'cm-order-source-tracker' ),
			'utm_term'     => __( 'Term', 'cm-order-source-tracker' ),
			'referrer'     => __( 'Referrer', 'cm-order-source-tracker' ),
			'landing_page' => __( 'Landing Page', 'cm-order-source-tracker' ),
			'ga_client_id' => __( 'GA Client ID', 'cm-order-source-tracker' ),
		];

		echo '<table class="cm-ost-table">';
		foreach ( $labels as $key => $label ) {
			if ( empty( $data[ $key ] ) ) {
				continue;
			}
			printf(
				'<tr><th>%s</th><td>%s</td></tr>',
				esc_html( $label ),
				esc_html( (string) $data[ $key ] ),
			);
		}
		echo '</table>';
	}
}
