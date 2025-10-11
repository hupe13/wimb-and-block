<?php
/**
 * Plugin Name:       Browser access control via WhatIsMyBrowser
 * Plugin URI:        https://leafext.de/hp/
 * Description:       Detects the browser and checks whether it is up to date. Blocks old versions and suspicious browsers.
 * Update URI:        https://github.com/hupe13/wimb-and-block
 * Version:           251011
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

if (
	( is_multisite() && is_main_site() && is_plugin_active_for_network( WIMB_BASENAME ) ) ||
	( is_multisite() && ! is_plugin_active_for_network( WIMB_BASENAME ) ) ||
	! is_multisite()
) {
	$wimbblock_options = wimbblock_get_options();
	if ( $wimbblock_options['rotate'] === 'yes' ) {
		require __DIR__ . '/php/cron.php';
	}
}

if ( is_admin() ) {
	require_once __DIR__ . '/admin.php';
	require_once __DIR__ . '/admin/settings.php';
	require_once __DIR__ . '/admin/blocking.php';
	require_once __DIR__ . '/admin/mgt-table.php';
	require_once __DIR__ . '/admin/emergency.php';
	require_once __DIR__ . '/admin/block-unknown-empty.php';
}

require __DIR__ . '/php/mysql.php';
require __DIR__ . '/php/wimb.php';
require __DIR__ . '/php/old-agents.php';
require __DIR__ . '/php/faked-crawlers.php';
require __DIR__ . '/github-wimb-and-block.php';

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

function wimbblock_get_options() {
	global $wpdb;
	$defaults          = array(
		'error'       => '3',
		'wimb_api'    => '',
		'table_name'  => $wpdb->prefix . 'wimb_table',
		'location'    => 'local',
		'db_user'     => '',
		'db_password' => '',
		'db_name'     => '',
		'db_host'     => '',
		'rotate'      => 'no',
		'logfile'     => '',
	);
	$wimbblock_options = get_option( 'wimbblock_settings' );
	if ( is_multisite() && ! is_main_site() ) {
		if ( is_plugin_active_for_network( WIMB_BASENAME ) ) {
			$wimbblock_options = get_blog_option( get_main_site_id(), 'wimbblock_settings' );
		}
	}
	if ( $wimbblock_options === false || count( $wimbblock_options ) === 0 ) {
		$wimbblock_options = $defaults;
	}
	return $wimbblock_options;
}

function wimbblock_get_default_browsers() {
	$defaults = array(
		// https://en.wikipedia.org/wiki/Google_Chrome
		'Chrome'            => 128,
		// https://en.wikipedia.org/wiki/Microsoft_Edge#New_Edge_release_history
		// https://www.cvedetails.com/version-list/26/32367/1/Microsoft-Edge.html?order=0
		'Edge'              => 128,
		// https://de.wikipedia.org/wiki/Versionsgeschichte_von_Mozilla_Firefox
		'Firefox'           => 128,
		'Internet Explorer' => 9999,
		'Netscape'          => 9999,
		// https://en.wikipedia.org/wiki/History_of_the_Opera_web_browser
		'Opera'             => 83,
		// https://developer.apple.com/documentation/safari-release-notes
		'Safari'            => 17,
	);
	return $defaults;
}

function wimbblock_get_browsers_custom() {
	$defaults          = wimbblock_get_default_browsers();
	$wimbblock_options = get_option( 'wimbblock_browsers' );
	if ( is_multisite() && ! is_main_site() ) {
		if ( is_plugin_active_for_network( WIMB_BASENAME ) ) {
			$wimbblock_options = get_blog_option( get_main_site_id(), 'wimbblock_browsers' );
		}
	}
	return $wimbblock_options;
}

function wimbblock_get_all_browsers() {
	$defaults = wimbblock_get_default_browsers();
	$customs  = wimbblock_get_browsers_custom();

	$out = array();
	if ( $customs !== false && count( $customs ) > 0 ) {
		foreach ( $defaults as $name => $default ) {
			if ( array_key_exists( $name, $customs ) ) {
				$out[ $name ] = $customs[ $name ];
			} else {
				$out[ $name ] = $default;
			}
		}
		foreach ( $customs as $name => $option ) {
			if ( ! array_key_exists( $name, $out ) ) {
				$out[ $name ] = $customs[ $name ];
			}
		}
	} else {
		$out = $defaults;
	}
	return $out;
}

function wimbblock_check_agent() {
	$stop = get_option( 'wimbblock_emergency', array( 'on' => '1' ) );
	if ( $stop['on'] === '0' ) {
		return;
	}
	global $user_login;
	global $wimbblock_software;
	global $is_crawler;
	$is_crawler = false;

	$agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
	$ip    = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$file  = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );

	$wpdb_options = wimbblock_get_options();
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
	&& strpos( $agent, 'Mastodon' ) === false
	&& ! is_404()
	) {
		global $wimb_datatable;
		if ( is_null( $wimb_datatable ) ) {
			wimbblock_open_wpdb();
		}
		list ( $software, $system, $version, $blocked, $id ) = wimbblock_check_wimb( $agent, $table_name );
		wimbblock_old_system( $table_name, $system, $id );
		wimbblock_faked_crawler( $agent, $software, $ip );
		if ( ! $is_crawler ) {
			wimbblock_unknown_agent( $table_name, $agent, $software, $id );
		}
		if ( $software !== '' ) {
			wimbblock_check_modern_browser( $table_name, $software, $version, $system, $id );
		}
		wimbblock_counter( $table_name, 'count', $id );
		$wimbblock_software = $software;
	}
}
add_action( 'init', 'wimbblock_check_agent', 8 );
