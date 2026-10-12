<?php
/**
 * Reads and writes first-click and last-click attribution cookies.
 *
 * @package ClarityMomentum\OrderSourceTracker\Tracker
 */

declare(strict_types=1);

namespace ClarityMomentum\OrderSourceTracker\Tracker;

class CookieStore {

	public const FIRST_COOKIE = 'cm_ost_first';
	public const LAST_COOKIE  = 'cm_ost_last';

	private const EXPIRY_DAYS = 30;

	/**
	 * Read and decode the first-click cookie.
	 *
	 * @return array<string, mixed>|null
	 */
	public function readFirst(): ?array {
		return $this->read( self::FIRST_COOKIE );
	}

	/**
	 * Read and decode the last-click cookie.
	 *
	 * @return array<string, mixed>|null
	 */
	public function readLast(): ?array {
		return $this->read( self::LAST_COOKIE );
	}

	/**
	 * Returns true if the first-click cookie is already set.
	 */
	public function hasFirst(): bool {
		return isset( $_COOKIE[ self::FIRST_COOKIE ] );
	}

	/**
	 * Set the first-click cookie. No-op if already set.
	 *
	 * @param array<string, mixed> $data
	 */
	public function setFirst( array $data ): void {
		if ( $this->hasFirst() ) {
			return;
		}
		$this->writeCookie( self::FIRST_COOKIE, (string) json_encode( $data ), time() + ( self::EXPIRY_DAYS * DAY_IN_SECONDS ) );
	}

	/**
	 * Set the last-click cookie. Always overwrites.
	 *
	 * @param array<string, mixed> $data
	 */
	public function setLast( array $data ): void {
		$this->writeCookie( self::LAST_COOKIE, (string) json_encode( $data ), time() + ( self::EXPIRY_DAYS * DAY_IN_SECONDS ) );
	}

	/**
	 * Write a cookie. Overridden in tests.
	 */
	protected function writeCookie( string $name, string $value, int $expires ): void {
		setcookie( $name, $value, [
			'expires'  => $expires,
			'path'     => '/',
			'secure'   => true,
			'httponly' => true,
			'samesite' => 'Lax',
		] );
	}

	/**
	 * Read and JSON-decode a cookie value.
	 *
	 * @return array<string, mixed>|null
	 */
	private function read( string $name ): ?array {
		if ( ! isset( $_COOKIE[ $name ] ) ) {
			return null;
		}
		$decoded = json_decode( $_COOKIE[ $name ], true );
		return is_array( $decoded ) ? $decoded : null;
	}
}
