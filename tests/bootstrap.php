<?php
/**
 * PHPUnit bootstrap for CM Order Source Tracker unit tests.
 * Does NOT load WordPress or WooCommerce — tests provide their own stubs.
 *
 * @package ClarityMomentum\OrderSourceTracker\Tests
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}
if ( ! defined( 'ARRAY_N' ) ) {
	define( 'ARRAY_N', 'ARRAY_N' );
}
if ( ! defined( 'OBJECT' ) ) {
	define( 'OBJECT', 'OBJECT' );
}

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// ── WordPress function stubs ─────────────────────────────────────────────────

if ( ! function_exists( '__' ) ) {
	function __( string $text, string $domain = 'default' ): string {
		return $text;
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( string $text ): string {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( string $text ): string {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( string $url ): string {
		return htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( string $text, string $domain = 'default' ): string {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_html_e' ) ) {
	function esc_html_e( string $text, string $domain = 'default' ): void {
		echo htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( string $hook, mixed $callback, int $priority = 10, int $args = 1 ): void {}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( string $hook, mixed $callback, int $priority = 10, int $args = 1 ): void {}
}

if ( ! function_exists( 'wp_enqueue_style' ) ) {
	function wp_enqueue_style( string $handle, string $src = '', array $deps = [], mixed $ver = false ): void {}
}

if ( ! function_exists( 'plugin_dir_url' ) ) {
	function plugin_dir_url( string $file ): string {
		return 'https://example.com/wp-content/plugins/cm-order-source-tracker/';
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( mixed $value ): int {
		return abs( (int) $value );
	}
}

if ( ! function_exists( 'wc_get_order' ) ) {
	function wc_get_order( int $id ): ?\WC_Order {
		return null;
	}
}

if ( ! function_exists( 'setcookie' ) ) {
	// Allow tests to override setcookie via test double.
	// Tests that need to intercept cookies do so by mocking CookieStore directly.
}

// ── WooCommerce class stubs ──────────────────────────────────────────────────

if ( ! class_exists( 'WC_Order' ) ) {
	class WC_Order {
		public function get_id(): int {
			return 0;
		}

		public function get_meta( string $key, bool $single = true ): mixed {
			return '';
		}

		public function update_meta_data( string $key, mixed $value ): void {}

		public function save(): int {
			return 0;
		}
	}
}
