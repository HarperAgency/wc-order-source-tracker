<?php
/**
 * Unit tests for CookieStore.
 *
 * @package ClarityMomentum\OrderSourceTracker\Tests\Unit\Tracker
 */

declare(strict_types=1);

namespace ClarityMomentum\OrderSourceTracker\Tests\Unit\Tracker;

use ClarityMomentum\OrderSourceTracker\Tracker\CookieStore;
use PHPUnit\Framework\TestCase;

class CookieStoreTest extends TestCase {

	protected function tearDown(): void {
		// Clean up superglobal between tests.
		unset( $_COOKIE[ CookieStore::FIRST_COOKIE ] );
		unset( $_COOKIE[ CookieStore::LAST_COOKIE ] );
	}

	// ── readFirst() ──────────────────────────────────────────────────────────

	public function testReadFirstReturnsParsedArrayWhenCookieSet(): void {
		$data                                   = [ 'utm_source' => 'google', 'utm_medium' => 'cpc' ];
		$_COOKIE[ CookieStore::FIRST_COOKIE ]   = json_encode( $data );
		$store                                  = new CookieStore();

		$result = $store->readFirst();
		$this->assertIsArray( $result );
		$this->assertSame( 'google', $result['utm_source'] );
	}

	public function testReadFirstReturnsNullWhenCookieAbsent(): void {
		$store = new CookieStore();
		$this->assertNull( $store->readFirst() );
	}

	public function testReadFirstReturnsNullWhenCookieContainsInvalidJson(): void {
		$_COOKIE[ CookieStore::FIRST_COOKIE ] = 'not-valid-json{{{';
		$store                                = new CookieStore();
		$this->assertNull( $store->readFirst() );
	}

	// ── readLast() ───────────────────────────────────────────────────────────

	public function testReadLastReturnsParsedArrayWhenCookieSet(): void {
		$data                                  = [ 'utm_source' => 'facebook', 'utm_medium' => 'cpc' ];
		$_COOKIE[ CookieStore::LAST_COOKIE ]   = json_encode( $data );
		$store                                 = new CookieStore();

		$result = $store->readLast();
		$this->assertIsArray( $result );
		$this->assertSame( 'facebook', $result['utm_source'] );
	}

	public function testReadLastReturnsNullWhenCookieAbsent(): void {
		$store = new CookieStore();
		$this->assertNull( $store->readLast() );
	}

	// ── hasFirst() ───────────────────────────────────────────────────────────

	public function testHasFirstReturnsTrueWhenCookieSet(): void {
		$_COOKIE[ CookieStore::FIRST_COOKIE ] = json_encode( [ 'utm_source' => 'google' ] );
		$store                                = new CookieStore();
		$this->assertTrue( $store->hasFirst() );
	}

	public function testHasFirstReturnsFalseWhenCookieAbsent(): void {
		$store = new CookieStore();
		$this->assertFalse( $store->hasFirst() );
	}
}
