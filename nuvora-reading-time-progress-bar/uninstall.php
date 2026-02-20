<?php
/**
 * Uninstall script for Nuvora Reading Time.
 *
 * Called automatically by WordPress when the plugin is deleted from the
 * Plugins screen. Removes all plugin data from the database.
 *
 * @package NuvoraReadingTime
 * @since   1.0.0
 */

// Only run when WordPress is uninstalling this plugin.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove global settings.
delete_option( 'nvrtp_settings' );

// Remove all post meta keys added by this plugin.
global $wpdb;

$nvrtp_meta_keys = array(
	'_nvrtp_reading_time_override',
	'_nvrtp_disable_badge',
	'_nvrtp_disable_progress',
);

foreach ( $nvrtp_meta_keys as $nvrtp_key ) {
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	// Direct database query is acceptable in uninstall context for cleanup.
	// No caching needed as this runs once during plugin deletion.
	$wpdb->delete(
		$wpdb->postmeta,
		array( 'meta_key' => $nvrtp_key ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		array( '%s' )
	);
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
}
