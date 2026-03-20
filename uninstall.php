<?php
/**
 * Uninstall handler.
 *
 * @package wimb-and-block
 */

// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

function wimbblock_uninstall_delete_options() {
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wimbblock_option_names = $wpdb->get_results(
		$wpdb->prepare(
			'SELECT option_name FROM %i WHERE option_name LIKE %s ',
			$wpdb->options,
			'wimbblock_%'
		),
		ARRAY_A
	);
	foreach ( $wimbblock_option_names as $key => $value ) {
		delete_option( $value['option_name'] );
	}
	delete_transient( 'wimbblock_logfile' );
}

global $wpdb;
// Erstmal alle Einstellungen holen, bevor sie gelöscht werden.

$wimbblock_wpdb_options  = get_option( 'wimbblock_settings' );
$wimbblock_table_name    = $wimbblock_wpdb_options['table_name'];
$wimbblock_local         = $wimbblock_wpdb_options['location'];
$wimbblock_should_delete = get_option( 'wimbblock_deleting' );

if ( $wimbblock_should_delete !== false ) {
	if ( isset( $wimbblock_should_delete['on'] ) && $wimbblock_should_delete['on'] === '1' ) {
		if ( is_multisite() ) {
			$wimbblock_sites = get_sites( array( 'fields' => 'ids' ) );
			foreach ( $wimbblock_sites as $wimbblock_id ) {
				switch_to_blog( $wimbblock_id );
				wimbblock_uninstall_delete_options();
				restore_current_blog();
			}
		} else {
			wimbblock_uninstall_delete_options();
		}
	}

	$wimbblock_local = $wimbblock_wpdb_options['location'];
	if ( $wimbblock_local === 'local' ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
				'DROP TABLE IF EXISTS %i',
				$wimbblock_table_name
			)
		);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
				'DROP TABLE IF EXISTS %i',
				$wimbblock_table_name . '_crawler'
			)
		);
	}
}
