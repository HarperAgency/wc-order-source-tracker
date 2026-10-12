<?php
/**
 * Unit tests for OrderCapture.
 *
 * @package ClarityMomentum\OrderSourceTracker\Tests\Unit\Tracker
 */

declare(strict_types=1);

namespace ClarityMomentum\OrderSourceTracker\Tests\Unit\Tracker;

use ClarityMomentum\OrderSourceTracker\Tracker\CookieStore;
use ClarityMomentum\OrderSourceTracker\Tracker\OrderCapture;
use ClarityMomentum\OrderSourceTracker\Tracker\SourceClassifier;
use ClarityMomentum\OrderSourceTracker\Tracker\UtmExtractor;
use PHPUnit\Framework\TestCase;

class OrderCaptureTest extends TestCase {

	private UtmExtractor $extractor;
	private SourceClassifier $classifier;

	protected function setUp(): void {
		$this->extractor  = new UtmExtractor();
		$this->classifier = new SourceClassifier();
	}

	protected function tearDown(): void {
		unset( $_COOKIE[ CookieStore::FIRST_COOKIE ] );
		unset( $_COOKIE[ CookieStore::LAST_COOKIE ] );
		unset( $_SERVER['HTTP_REFERER'] );
		unset( $_SERVER['REQUEST_URI'] );
	}

	// ── captureUtms() ────────────────────────────────────────────────────────

	public function testCaptureUtmsSetsFirstCookieWhenUtmsPresentAndNoFirstCookie(): void {
		$cookieStore = $this->makeCookieStore();
		$capture     = new OrderCapture( $this->extractor, $this->classifier, $cookieStore );

		$capture->captureUtms( [ 'utm_source' => 'google', 'utm_medium' => 'cpc' ], null, null );

		$first = $cookieStore->readFirst();
		$this->assertIsArray( $first );
		$this->assertSame( 'google', $first['utm_source'] );
	}

	public function testCaptureUtmsDoesNotOverwriteFirstCookieWhenAlreadySet(): void {
		$cookieStore                          = $this->makeCookieStore();
		$_COOKIE[ CookieStore::FIRST_COOKIE ] = json_encode( [ 'utm_source' => 'original' ] );

		$capture = new OrderCapture( $this->extractor, $this->classifier, $cookieStore );
		$capture->captureUtms( [ 'utm_source' => 'new-source' ], null, null );

		$first = $cookieStore->readFirst();
		$this->assertSame( 'original', $first['utm_source'] );
	}

	public function testCaptureUtmsAlwaysUpdatesLastCookieWhenUtmsPresent(): void {
		$cookieStore                         = $this->makeCookieStore();
		$_COOKIE[ CookieStore::LAST_COOKIE ] = json_encode( [ 'utm_source' => 'old' ] );

		$capture = new OrderCapture( $this->extractor, $this->classifier, $cookieStore );
		$capture->captureUtms( [ 'utm_source' => 'new-source', 'utm_medium' => 'cpc' ], null, null );

		$last = $cookieStore->readLast();
		$this->assertSame( 'new-source', $last['utm_source'] );
	}

	public function testCaptureUtmsDoesNothingWhenNoUtmsAndNoReferrer(): void {
		$cookieStore = $this->makeCookieStore();
		$capture     = new OrderCapture( $this->extractor, $this->classifier, $cookieStore );

		$capture->captureUtms( [], null, null );

		$this->assertNull( $cookieStore->readFirst() );
		$this->assertNull( $cookieStore->readLast() );
	}

	public function testCaptureUtmsSetsFirstCookieFromReferrerWhenNoUtms(): void {
		$cookieStore = $this->makeCookieStore();
		$capture     = new OrderCapture( $this->extractor, $this->classifier, $cookieStore );

		$capture->captureUtms( [], 'https://somesite.com/article/', 'https://example.com/shop/' );

		$first = $cookieStore->readFirst();
		$this->assertIsArray( $first );
		$this->assertSame( 'https://somesite.com/article/', $first['referrer'] );
	}

	// ── saveToOrder() ────────────────────────────────────────────────────────

	public function testSaveToOrderSavesFirstClickMetaToOrder(): void {
		$cookieStore                          = $this->makeCookieStore();
		$_COOKIE[ CookieStore::FIRST_COOKIE ] = json_encode(
			[ 'utm_source' => 'google', 'utm_medium' => 'cpc' ]
		);

		$capture      = new OrderCapture( $this->extractor, $this->classifier, $cookieStore );
		$order        = $this->makeOrder();
		$capture->saveToOrder( $order );

		$saved = $order->getSavedMeta( '_cm_ost_first' );
		$this->assertIsArray( $saved );
		$this->assertSame( 'google', $saved['utm_source'] );
	}

	public function testSaveToOrderSavesLastClickMetaToOrder(): void {
		$cookieStore                         = $this->makeCookieStore();
		$_COOKIE[ CookieStore::LAST_COOKIE ] = json_encode(
			[ 'utm_source' => 'facebook', 'utm_medium' => 'cpc' ]
		);

		$capture = new OrderCapture( $this->extractor, $this->classifier, $cookieStore );
		$order   = $this->makeOrder();
		$capture->saveToOrder( $order );

		$saved = $order->getSavedMeta( '_cm_ost_last' );
		$this->assertIsArray( $saved );
		$this->assertSame( 'facebook', $saved['utm_source'] );
	}

	public function testSaveToOrderIncludesSourceTypeInPayload(): void {
		$cookieStore                          = $this->makeCookieStore();
		$_COOKIE[ CookieStore::FIRST_COOKIE ] = json_encode(
			[ 'utm_source' => 'google', 'utm_medium' => 'cpc' ]
		);

		$capture = new OrderCapture( $this->extractor, $this->classifier, $cookieStore );
		$order   = $this->makeOrder();
		$capture->saveToOrder( $order );

		$saved = $order->getSavedMeta( '_cm_ost_first' );
		$this->assertArrayHasKey( 'source_type', $saved );
		$this->assertSame( 'paid_search', $saved['source_type'] );
	}

	public function testSaveToOrderSavesNullMetaWhenNoCookiesSet(): void {
		$cookieStore = $this->makeCookieStore();
		$capture     = new OrderCapture( $this->extractor, $this->classifier, $cookieStore );
		$order       = $this->makeOrder();
		$capture->saveToOrder( $order );

		$this->assertNull( $order->getSavedMeta( '_cm_ost_first' ) );
		$this->assertNull( $order->getSavedMeta( '_cm_ost_last' ) );
	}

	public function testCaptureUtmsIncludesGaClientIdWhenGaCookiePresent(): void {
		$cookieStore              = $this->makeCookieStore();
		$_COOKIE['_ga']           = 'GA1.1.123456789.1000000000';
		$capture                  = new OrderCapture( $this->extractor, $this->classifier, $cookieStore );

		$capture->captureUtms( [ 'utm_source' => 'google', 'utm_medium' => 'cpc' ], null, null );

		$first = $cookieStore->readFirst();
		$this->assertArrayHasKey( 'ga_client_id', $first );
		$this->assertSame( '123456789.1000000000', $first['ga_client_id'] );

		unset( $_COOKIE['_ga'] );
	}

	// ── Helpers ──────────────────────────────────────────────────────────────

	private function makeCookieStore(): CookieStore {
		return new class extends CookieStore {
			private array $written = [];

			protected function writeCookie( string $name, string $value, int $expires ): void {
				$_COOKIE[ $name ] = $value;
				$this->written[]  = $name;
			}
		};
	}

	private function makeOrder(): \WC_Order {
		return new class extends \WC_Order {
			private array $meta = [];

			public function get_id(): int {
				return 1;
			}

			public function update_meta_data( string $key, mixed $value ): void {
				$this->meta[ $key ] = $value;
			}

			public function save(): int {
				return 1;
			}

			public function getSavedMeta( string $key ): mixed {
				return $this->meta[ $key ] ?? null;
			}
		};
	}
}
