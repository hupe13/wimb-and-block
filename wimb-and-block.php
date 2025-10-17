<?php
/**
 * Plugin Name:       Browser access control via WhatIsMyBrowser
 * Plugin URI:        https://leafext.de/hp/
 * Description:       Detects the browser and checks whether it is up to date. Blocks old versions and suspicious browsers.
 * Update URI:        https://github.com/hupe13/wimb-and-block
 * Version:           251017
 * Requires PHP:      8.3
 * Author:            hupe13
 * Author URI:        https://leafext.de/hp/
 * License:           GPL v2 or later
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern:
defined( 'ABSPATH' ) || die();

define( 'WIMB_BASENAME', plugin_basename( __FILE__ ) ); // wimb-and-block/wimb-and-block.php
define( 'WIMB_DIR', plugin_dir_path( __FILE__ ) ); // /pfad/wp-content/plugins/wimb-and-block/ .
define( 'WIMB_NAME', basename( WIMB_DIR ) ); // wimb-and-block

require __DIR__ . '/php/wimb-options.php';
require __DIR__ . '/php/mysql.php';
require __DIR__ . '/php/dbdelta.php';
require __DIR__ . '/php/wimb.php';
require __DIR__ . '/php/old-agents.php';
require __DIR__ . '/php/faked-crawlers.php';
require __DIR__ . '/github-wimb-and-block.php';

if ( is_admin() ) {
	require_once __DIR__ . '/admin.php';
	require_once __DIR__ . '/admin/settings.php';
	require_once __DIR__ . '/admin/blocking.php';
	require_once __DIR__ . '/admin/mgt-table.php';
	require_once __DIR__ . '/admin/emergency.php';
	// require_once __DIR__ . '/admin/main-blocking.php';
	// require_once __DIR__ . '/admin/block-unknown-empty.php';
	require_once __DIR__ . '/admin/exclude.php';
}

// Set the initial version of the database schema
function wimbblock_activate() {
	add_option( 'wimbblock_db_version', '251011' );
}
register_activation_hook( __FILE__, 'wimbblock_activate' );

function wimbblock_update() {
	if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMB_BASENAME ) ) ) {
		$current_version = get_option( 'wimbblock_db_version', '251000' );
		$new_version     = '251014'; // Update this to your new version
		if ( version_compare( $current_version, $new_version, '<' ) ) {
			$options = wimbblock_get_options_db();
			wimbblock_table_install( $options['table_name'] ); // Call the migration function
			update_option( 'wimbblock_db_version', $new_version ); // Update the version
		}
	}
}
add_action( 'plugins_loaded', 'wimbblock_update' );

if (
	( is_multisite() && is_main_site() && is_plugin_active_for_network( WIMB_BASENAME ) ) ||
	( is_multisite() && ! is_plugin_active_for_network( WIMB_BASENAME ) ) ||
	! is_multisite()
) {
	$wimbblock_options = wimbblock_get_options_db();
	if ( $wimbblock_options['rotate'] === 'yes' ) {
		require __DIR__ . '/php/cron.php';
	}
}

// Add settings to plugin page
function wimbblock_add_action_links( $actions ) {
	$actions[] = '<a href="' . esc_url( admin_url( 'options-general.php' ) . '?page=' . WIMB_NAME ) . '">' . esc_html__( 'Settings', 'wimb-and-block' ) . '</a>';
	return $actions;
}
add_filter( 'plugin_action_links_' . WIMB_BASENAME, 'wimbblock_add_action_links' );

// Add settings to network plugin page
function wimbblock_network_add_action_links( $actions, $plugin ) {
	if ( $plugin === WIMB_BASENAME ) {
		$actions[] = '<a href="' . esc_url( admin_url( 'options-general.php' ) . '?page=' . WIMB_NAME ) . '">' . esc_html__( 'Settings', 'wimb-and-block' ) . '</a>';
	}
	return $actions;
}
add_filter( 'network_admin_plugin_action_links', 'wimbblock_network_add_action_links', 10, 4 );

function wimbblock_check_agent() {
	$stop = wimbblock_get_option( 'wimbblock_emergency' );
	if ( $stop !== false ) {
		if ( $stop['on'] === '0' ) {
			return;
		}
	}
	global $user_login;
	global $wimbblock_software;
	global $is_crawler;
	$is_crawler = false;

	$agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
	$ip    = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$file  = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );

	$excludes = wimbblock_get_option( 'wimbblock_exclude' );
	if ( $excludes !== false ) {
		foreach ( $excludes as $exclude ) {
			if ( strpos( $agent, $exclude ) !== false ) {
				wimbblock_error_log( 'Excluded: ' . $agent . ' * ' . $exclude );
				return;
			}
		}
	}

	$wpdb_options = wimbblock_get_options_db();
	// var_dump($wpdb_options); wp_die('tot');
	$table_name = $wpdb_options['table_name'];

	if (
	! is_admin()
	&& $wpdb_options['error'] === '0'
	&& $wpdb_options['wimb_api'] !== ''
	&& $ip !== '127.0.0.1'
	&& $ip !== false
	&& $user_login === ''
	&& $agent !== ''
	&& strpos( $agent, 'WordPress' ) === false
	&& strpos( $agent, 'WP-URLDetails' ) === false
	&& strpos( $agent, 'cronBROWSE' ) === false
	&& strpos( $agent, get_site_url() ) === false
	&& strpos( $file, 'robots.txt' ) === false
	&& strpos( $file, 'robots-check' ) === false
	&& ! is_404()
	) {
		global $wimb_datatable;
		if ( is_null( $wimb_datatable ) ) {
			wimbblock_open_wpdb();
		}
		list ( $software, $system, $version, $blocked, $id ) = wimbblock_check_wimb( $agent, $table_name );
		wimbblock_old_system( $table_name, $system, $id );
		wimbblock_faked_crawler( $agent, $software, $ip );
		if ( $is_crawler === false ) {
			wimbblock_unknown_agent( $table_name, $agent, $software, $id );
			if ( $software !== '' ) {
				wimbblock_check_modern_browser( $table_name, $software, $version, $system, $id );
			}
		}
		wimbblock_counter( $table_name, 'count', $id );
		$wimbblock_software = $software;
	}
}
add_action( 'init', 'wimbblock_check_agent', 8 );
