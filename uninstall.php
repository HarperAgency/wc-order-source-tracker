<?php
/**
 * Runs when the plugin is deleted from the WordPress admin.
 * Removes all order meta written by this plugin.
 *
 * @package ClarityMomentum\OrderSourceTracker
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Remove from legacy post meta table.
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->postmeta} WHERE meta_key IN (%s, %s)",
		'_cm_ost_first',
		'_cm_ost_last',
	)
);

// Remove from HPOS order meta table if present.
$hpos_table = $wpdb->prefix . 'wc_orders_meta';
if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $hpos_table ) ) === $hpos_table ) {
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$hpos_table} WHERE meta_key IN (%s, %s)",
			'_cm_ost_first',
			'_cm_ost_last',
		)
	);
}
