<?php
/**
 * Uninstall script for Reading Time Estimator.
 *
 * Called automatically by WordPress when the plugin is deleted from the
 * Plugins screen. Removes all plugin data from the database.
 *
 * @package ReadingTimeEstimator
 * @since   1.0.0
 */

// Only run when WordPress is uninstalling this plugin.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove global settings.
delete_option( 'rte_settings' );

// Remove all post meta keys added by this plugin.
global $wpdb;

$meta_keys = array(
	'_rte_reading_time_override',
	'_rte_disable_badge',
	'_rte_disable_progress',
);

foreach ( $meta_keys as $key ) {
	$wpdb->delete(
		$wpdb->postmeta,
		array( 'meta_key' => $key ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		array( '%s' )
	);
}
