<?php
/**
 * Extracts and sanitizes UTM parameters from request data.
 *
 * @package ClarityMomentum\OrderSourceTracker\Tracker
 */

declare(strict_types=1);

namespace ClarityMomentum\OrderSourceTracker\Tracker;

class UtmExtractor {

	private const UTM_KEYS = [
		'utm_source',
		'utm_medium',
		'utm_campaign',
		'utm_content',
		'utm_term',
	];

	/**
	 * Extract only UTM keys from a params array, sanitized.
	 *
	 * @param array<string, mixed> $params Raw query params.
	 * @return array<string, string>
	 */
	public function extract( array $params ): array {
		$result = [];
		foreach ( self::UTM_KEYS as $key ) {
			if ( isset( $params[ $key ] ) && '' !== $params[ $key ] ) {
				$result[ $key ] = $this->sanitize( (string) $params[ $key ] );
			}
		}
		return $result;
	}

	/**
	 * Returns true if any UTM key is present in params.
	 *
	 * @param array<string, mixed> $params
	 */
	public function hasUtm( array $params ): bool {
		foreach ( self::UTM_KEYS as $key ) {
			if ( ! empty( $params[ $key ] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Extract UTM params from a full URL string.
	 *
	 * @param string $url
	 * @return array<string, string>
	 */
	public function extractFromUrl( string $url ): array {
		$parsed = parse_url( $url );
		if ( ! $parsed || empty( $parsed['query'] ) ) {
			return [];
		}
		parse_str( $parsed['query'], $query );
		return $this->extract( $query );
	}

	/**
	 * Strip HTML tags (including script/style content), trim whitespace, truncate to 200 chars.
	 */
	public function sanitize( string $value ): string {
		// Remove script and style blocks including their inner content.
		$value = (string) preg_replace( '/<(script|style)[^>]*>.*?<\/\1>/is', '', $value );
		$value = strip_tags( $value );
		$value = trim( $value );
		return substr( $value, 0, 200 );
	}
}
