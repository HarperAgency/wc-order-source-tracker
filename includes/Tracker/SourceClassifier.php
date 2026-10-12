<?php
/**
 * Classifies attribution source and produces a human-readable label.
 *
 * @package ClarityMomentum\OrderSourceTracker\Tracker
 */

declare(strict_types=1);

namespace ClarityMomentum\OrderSourceTracker\Tracker;

class SourceClassifier {

	private const SEARCH_SOURCES  = [ 'google', 'bing', 'yahoo', 'duckduckgo', 'baidu', 'yandex' ];
	private const SOCIAL_SOURCES  = [ 'facebook', 'meta', 'instagram', 'tiktok', 'linkedin', 'twitter', 'x', 'pinterest', 'snapchat', 'reddit' ];
	private const PAID_MEDIUMS    = [ 'cpc', 'ppc', 'paid', 'paidsearch', 'paid_search', 'paidsocial', 'paid_social' ];
	private const EMAIL_MEDIUMS   = [ 'email', 'e-mail', 'newsletter' ];
	private const SOCIAL_MEDIUMS  = [ 'social', 'social-media', 'social_media' ];

	/**
	 * Classify source data into a source type string.
	 *
	 * @param array<string, string> $data
	 * @return string 'direct'|'organic'|'paid_search'|'paid_social'|'email'|'social'|'referral'
	 */
	public function classify( array $data ): string {
		$source = strtolower( $data['utm_source'] ?? '' );
		$medium = strtolower( $data['utm_medium'] ?? '' );

		// Paid medium — distinguish search vs social.
		if ( $medium && in_array( $medium, self::PAID_MEDIUMS, true ) ) {
			if ( in_array( $source, self::SOCIAL_SOURCES, true ) ) {
				return 'paid_social';
			}
			return 'paid_search';
		}

		// Email.
		if ( $medium && in_array( $medium, self::EMAIL_MEDIUMS, true ) ) {
			return 'email';
		}

		// Social (organic social via medium).
		if ( $medium && in_array( $medium, self::SOCIAL_MEDIUMS, true ) ) {
			return 'social';
		}

		// Organic search - known search engine source with no paid medium.
		if ( $source && in_array( $source, self::SEARCH_SOURCES, true ) ) {
			return 'organic';
		}

		// Referral from external site (no UTMs).
		if ( ! empty( $data['referrer'] ) ) {
			return 'referral';
		}

		return 'direct';
	}

	/**
	 * Produce a human-readable source label.
	 *
	 * @param array<string, string> $data
	 */
	public function label( array $data ): string {
		if ( ! empty( $data['utm_source'] ) ) {
			return $data['utm_source'];
		}

		if ( ! empty( $data['referrer'] ) ) {
			$host = parse_url( $data['referrer'], PHP_URL_HOST );
			return $host ?: $data['referrer'];
		}

		return 'Direct';
	}
}
