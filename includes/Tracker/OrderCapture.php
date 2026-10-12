<?php
/**
 * Captures UTM parameters on init and saves attribution data to WooCommerce orders.
 *
 * @package ClarityMomentum\OrderSourceTracker\Tracker
 */

declare(strict_types=1);

namespace ClarityMomentum\OrderSourceTracker\Tracker;

class OrderCapture {

	public function __construct(
		private readonly UtmExtractor $extractor,
		private readonly SourceClassifier $classifier,
		private readonly CookieStore $cookieStore,
	) {}

	/**
	 * Register WordPress hooks.
	 */
	public function register(): void {
		add_action( 'init', [ $this, 'captureFromRequest' ] );
		add_action( 'woocommerce_checkout_order_created', [ $this, 'saveToOrder' ] );
	}

	/**
	 * Called on the WordPress init hook — reads superglobals and delegates.
	 */
	public function captureFromRequest(): void {
		$referrer     = $_SERVER['HTTP_REFERER'] ?? null;
		$landing_page = ( isset( $_SERVER['REQUEST_URI'] ) && isset( $_SERVER['HTTP_HOST'] ) )
			? 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
			: null;

		$this->captureUtms(
			$_GET ?? [],  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$referrer,
			$landing_page,
		);
	}

	/**
	 * Capture UTMs from explicit params (testable without superglobals).
	 *
	 * @param array<string, mixed> $get          Query params (e.g. $_GET).
	 * @param string|null          $referrer     HTTP_REFERER value.
	 * @param string|null          $landing_page Full URL of current page.
	 */
	public function captureUtms( array $get, ?string $referrer, ?string $landing_page ): void {
		$utms = $this->extractor->extract( $get );

		if ( ! empty( $utms ) ) {
			$payload = $utms;
			if ( null !== $referrer ) {
				$payload['referrer'] = $referrer;
			}
			if ( null !== $landing_page ) {
				$payload['landing_page'] = $landing_page;
			}
			$payload = $this->addGaClientId( $payload );

			$this->cookieStore->setFirst( $payload );
			$this->cookieStore->setLast( $payload );
			return;
		}

		// No UTMs but referrer present - treat as referral touch.
		if ( ! empty( $referrer ) ) {
			$payload = [ 'referrer' => $referrer ];
			if ( null !== $landing_page ) {
				$payload['landing_page'] = $landing_page;
			}
			$payload = $this->addGaClientId( $payload );

			$this->cookieStore->setFirst( $payload );
		}
	}

	/**
	 * Save attribution data from cookies to the order as meta.
	 *
	 * Hooked on woocommerce_checkout_order_created.
	 *
	 * @param \WC_Order $order
	 */
	public function saveToOrder( \WC_Order $order ): void {
		$first = $this->cookieStore->readFirst();
		$last  = $this->cookieStore->readLast();

		if ( null !== $first ) {
			$first['source_type'] = $this->classifier->classify( $first );
			$order->update_meta_data( '_cm_ost_first', $first );
		}

		if ( null !== $last ) {
			$last['source_type'] = $this->classifier->classify( $last );
			$order->update_meta_data( '_cm_ost_last', $last );
		}

		if ( null !== $first || null !== $last ) {
			$order->save();
		}
	}

	/**
	 * Parse the GA4 client ID from the _ga cookie and add it to the payload.
	 *
	 * GA cookie format: GA1.1.{client_id_part1}.{client_id_part2}
	 *
	 * @param array<string, mixed> $payload
	 * @return array<string, mixed>
	 */
	private function addGaClientId( array $payload ): array {
		if ( empty( $_COOKIE['_ga'] ) ) {
			return $payload;
		}

		$parts = explode( '.', (string) $_COOKIE['_ga'] );
		if ( count( $parts ) >= 4 ) {
			$payload['ga_client_id'] = $parts[2] . '.' . $parts[3];
		}

		return $payload;
	}
}
