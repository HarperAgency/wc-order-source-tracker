<?php
/**
 * Plugin Name:       CM Order Source Tracker
 * Plugin URI:        https://claritymomentum.io/products/cm-order-source-tracker/
 * Description:       Captures UTM parameters, referrer, and landing page from the visitor session and attaches first-click and last-click attribution data to WooCommerce orders.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Author:            Clarity Momentum
 * Author URI:        https://claritymomentum.io
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cm-order-source-tracker
 * Domain Path:       /languages
 * WC requires at least: 7.0
 * WC tested up to:      9.0
 *
 * @package ClarityMomentum\OrderSourceTracker
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CM_OST_VERSION', '1.0.0' );
define( 'CM_OST_FILE', __FILE__ );
define( 'CM_OST_DIR', plugin_dir_path( __FILE__ ) );
define( 'CM_OST_URL', plugin_dir_url( __FILE__ ) );

// Autoloader: Composer first, manual PSR-4 fallback.
if ( file_exists( CM_OST_DIR . 'vendor/autoload.php' ) ) {
	require_once CM_OST_DIR . 'vendor/autoload.php';
} else {
	spl_autoload_register(
		static function ( string $class ): void {
			$prefix = 'ClarityMomentum\\OrderSourceTracker\\';
			if ( 0 !== strpos( $class, $prefix ) ) {
				return;
			}
			$relative = str_replace( '\\', DIRECTORY_SEPARATOR, substr( $class, strlen( $prefix ) ) );
			$file     = CM_OST_DIR . 'includes/' . $relative . '.php';
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	);
}

/**
 * Declare HPOS compatibility before WooCommerce initialises.
 */
add_action(
	'before_woocommerce_init',
	static function (): void {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', CM_OST_FILE, true );
		}
	}
);

/**
 * Bootstrap the plugin after WooCommerce is confirmed active.
 */
add_action(
	'plugins_loaded',
	static function (): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action(
				'admin_notices',
				static function (): void {
					echo '<div class="notice notice-error"><p>';
					esc_html_e( 'CM Order Source Tracker requires WooCommerce to be active.', 'cm-order-source-tracker' );
					echo '</p></div>';
				}
			);
			return;
		}

		$extractor  = new \ClarityMomentum\OrderSourceTracker\Tracker\UtmExtractor();
		$classifier = new \ClarityMomentum\OrderSourceTracker\Tracker\SourceClassifier();
		$store      = new \ClarityMomentum\OrderSourceTracker\Tracker\CookieStore();

		( new \ClarityMomentum\OrderSourceTracker\Tracker\OrderCapture( $extractor, $classifier, $store ) )->register();

		if ( is_admin() ) {
			( new \ClarityMomentum\OrderSourceTracker\Admin\OrderSourceColumn( $classifier ) )->register();
			( new \ClarityMomentum\OrderSourceTracker\Admin\OrderSourceMetaBox( $classifier ) )->register();
		}
	}
);
