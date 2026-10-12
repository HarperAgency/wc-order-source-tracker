<?php
/**
 * Unit tests for SourceClassifier.
 *
 * @package ClarityMomentum\OrderSourceTracker\Tests\Unit\Tracker
 */

declare(strict_types=1);

namespace ClarityMomentum\OrderSourceTracker\Tests\Unit\Tracker;

use ClarityMomentum\OrderSourceTracker\Tracker\SourceClassifier;
use PHPUnit\Framework\TestCase;

class SourceClassifierTest extends TestCase {

	private SourceClassifier $classifier;

	protected function setUp(): void {
		$this->classifier = new SourceClassifier();
	}

	// ── classify() ───────────────────────────────────────────────────────────

	public function testClassifiesDirectWhenNoSourceData(): void {
		$this->assertSame( 'direct', $this->classifier->classify( [] ) );
	}

	public function testClassifiesPaidSearchForCpcMediumWithGoogle(): void {
		$this->assertSame(
			'paid_search',
			$this->classifier->classify( [ 'utm_source' => 'google', 'utm_medium' => 'cpc' ] )
		);
	}

	public function testClassifiesPaidSearchForPpcMedium(): void {
		$this->assertSame(
			'paid_search',
			$this->classifier->classify( [ 'utm_source' => 'bing', 'utm_medium' => 'ppc' ] )
		);
	}

	public function testClassifiesPaidSocialForCpcMediumWithFacebook(): void {
		$this->assertSame(
			'paid_social',
			$this->classifier->classify( [ 'utm_source' => 'facebook', 'utm_medium' => 'cpc' ] )
		);
	}

	public function testClassifiesPaidSocialForMetaSource(): void {
		$this->assertSame(
			'paid_social',
			$this->classifier->classify( [ 'utm_source' => 'meta', 'utm_medium' => 'paid' ] )
		);
	}

	public function testClassifiesEmailForEmailMedium(): void {
		$this->assertSame(
			'email',
			$this->classifier->classify( [ 'utm_source' => 'newsletter', 'utm_medium' => 'email' ] )
		);
	}

	public function testClassifiesSocialForSocialMedium(): void {
		$this->assertSame(
			'social',
			$this->classifier->classify( [ 'utm_source' => 'instagram', 'utm_medium' => 'social' ] )
		);
	}

	public function testClassifiesOrganicForGoogleWithNoMedium(): void {
		$this->assertSame(
			'organic',
			$this->classifier->classify( [ 'utm_source' => 'google' ] )
		);
	}

	public function testClassifiesOrganicForBingWithNoMedium(): void {
		$this->assertSame(
			'organic',
			$this->classifier->classify( [ 'utm_source' => 'bing' ] )
		);
	}

	public function testClassifiesReferralFromReferrerWithNoUtms(): void {
		$this->assertSame(
			'referral',
			$this->classifier->classify( [ 'referrer' => 'https://somesite.com/article/' ] )
		);
	}

	public function testClassifiesDirectWhenReferrerIsEmpty(): void {
		$this->assertSame( 'direct', $this->classifier->classify( [ 'referrer' => '' ] ) );
	}

	// ── label() ──────────────────────────────────────────────────────────────

	public function testLabelReturnsUtmSourceWhenPresent(): void {
		$result = $this->classifier->label( [ 'utm_source' => 'google', 'utm_medium' => 'cpc' ] );
		$this->assertSame( 'google', $result );
	}

	public function testLabelReturnsReferrerDomainWhenNoUtm(): void {
		$result = $this->classifier->label( [ 'referrer' => 'https://somesite.com/article/' ] );
		$this->assertSame( 'somesite.com', $result );
	}

	public function testLabelReturnsDirectWhenNeitherPresent(): void {
		$result = $this->classifier->label( [] );
		$this->assertSame( 'Direct', $result );
	}
}
