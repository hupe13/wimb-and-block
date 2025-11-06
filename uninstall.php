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
	$option_names = $wpdb->get_results(
		$wpdb->prepare(
			'SELECT option_name FROM %i WHERE option_name LIKE %s ',
			$wpdb->options,
			'wimbblock_%'
		),
		ARRAY_A
	);
	foreach ( $option_names as $key => $value ) {
		delete_option( $value['option_name'] );
	}
	delete_transient( 'wimbblock_logfile' );
}

global $wpdb;
// Erstmal alle Einstellungen holen, bevor sie gelöscht werden.
if ( is_main_site() ) {
	$wpdb_options = get_option( 'wimbblock_settings' );
	$table_name   = $wpdb_options['table_name'];
	$local        = $wpdb_options['location'];
}

if ( is_multisite() ) {
	switch_to_blog( get_main_site_id() );
	$should_delete = get_option( 'wimbblock_deleting' );
	restore_current_blog();
	if ( ( ! ( isset( $should_delete['on'] ) && $should_delete['on'] === '0' ) ) && $should_delete !== false ) {
		foreach ( get_sites() as $site ) {
			switch_to_blog( $site->blog_id );
			wimbblock_uninstall_delete_options();
			restore_current_blog();
		}
	}
} else {
	$should_delete = get_option( 'wimbblock_deleting' );
	if ( ( ! ( isset( $should_delete['on'] ) && $should_delete['on'] === '0' ) ) && $should_delete !== false ) {
		wimbblock_uninstall_delete_options();
	}
}

if ( is_main_site() ) {
	if ( ( ! ( isset( $should_delete['on'] ) && $should_delete['on'] === '0' ) ) && $should_delete !== false ) {
		$local = $wpdb_options['location'];
		if ( $local === 'local' ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
					'DROP TABLE IF EXISTS %i',
					$table_name
				)
			);
		}
	}
}
