<?php
/**
 * Unit tests for UtmExtractor.
 *
 * @package ClarityMomentum\OrderSourceTracker\Tests\Unit\Tracker
 */

declare(strict_types=1);

namespace ClarityMomentum\OrderSourceTracker\Tests\Unit\Tracker;

use ClarityMomentum\OrderSourceTracker\Tracker\UtmExtractor;
use PHPUnit\Framework\TestCase;

class UtmExtractorTest extends TestCase {

	private UtmExtractor $extractor;

	protected function setUp(): void {
		$this->extractor = new UtmExtractor();
	}

	// ── extract() ────────────────────────────────────────────────────────────

	public function testExtractsUtmSource(): void {
		$result = $this->extractor->extract( [ 'utm_source' => 'google' ] );
		$this->assertSame( [ 'utm_source' => 'google' ], $result );
	}

	public function testExtractsAllFiveUtmParams(): void {
		$params = [
			'utm_source'   => 'google',
			'utm_medium'   => 'cpc',
			'utm_campaign' => 'black-friday',
			'utm_content'  => 'banner-a',
			'utm_term'     => 'woocommerce plugins',
		];
		$result = $this->extractor->extract( $params );
		$this->assertSame( $params, $result );
	}

	public function testIgnoresNonUtmParams(): void {
		$params = [
			'utm_source' => 'google',
			'page'       => 'shop',
			'ref'        => 'affiliate',
		];
		$result = $this->extractor->extract( $params );
		$this->assertArrayHasKey( 'utm_source', $result );
		$this->assertArrayNotHasKey( 'page', $result );
		$this->assertArrayNotHasKey( 'ref', $result );
	}

	public function testReturnsEmptyArrayWhenNoUtmParams(): void {
		$result = $this->extractor->extract( [ 'page' => 'shop', 'ref' => 'foo' ] );
		$this->assertSame( [], $result );
	}

	public function testReturnsEmptyArrayForEmptyInput(): void {
		$result = $this->extractor->extract( [] );
		$this->assertSame( [], $result );
	}

	// ── hasUtm() ─────────────────────────────────────────────────────────────

	public function testHasUtmReturnsTrueWhenSourcePresent(): void {
		$this->assertTrue( $this->extractor->hasUtm( [ 'utm_source' => 'google' ] ) );
	}

	public function testHasUtmReturnsTrueForAnyUtmParam(): void {
		$this->assertTrue( $this->extractor->hasUtm( [ 'utm_medium' => 'email' ] ) );
	}

	public function testHasUtmReturnsFalseWithEmptyParams(): void {
		$this->assertFalse( $this->extractor->hasUtm( [] ) );
	}

	public function testHasUtmReturnsFalseWithOnlyNonUtmParams(): void {
		$this->assertFalse( $this->extractor->hasUtm( [ 'ref' => 'foo', 'page' => '2' ] ) );
	}

	// ── sanitize() ───────────────────────────────────────────────────────────

	public function testSanitizeStripsHtmlTags(): void {
		$result = $this->extractor->sanitize( '<script>alert(1)</script>google' );
		$this->assertSame( 'google', $result );
	}

	public function testSanitizeTruncatesAt200Chars(): void {
		$long   = str_repeat( 'a', 250 );
		$result = $this->extractor->sanitize( $long );
		$this->assertSame( 200, strlen( $result ) );
	}

	public function testSanitizeTrimsWhitespace(): void {
		$result = $this->extractor->sanitize( '  google  ' );
		$this->assertSame( 'google', $result );
	}

	// ── extractFromUrl() ─────────────────────────────────────────────────────

	public function testExtractFromUrlParsesQueryString(): void {
		$url    = 'https://example.com/shop/?utm_source=google&utm_medium=cpc';
		$result = $this->extractor->extractFromUrl( $url );
		$this->assertSame( 'google', $result['utm_source'] );
		$this->assertSame( 'cpc', $result['utm_medium'] );
	}

	public function testExtractFromUrlReturnsEmptyArrayForUrlWithNoUtms(): void {
		$result = $this->extractor->extractFromUrl( 'https://example.com/shop/?ref=foo' );
		$this->assertSame( [], $result );
	}

	public function testExtractFromUrlHandlesMalformedUrl(): void {
		$result = $this->extractor->extractFromUrl( 'not-a-url' );
		$this->assertSame( [], $result );
	}
}
