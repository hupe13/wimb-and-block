<?php
/**
 * Plugin Name:       Block old browser versions and suspicious browsers
 * Plugin URI:        https://leafext.de/hp/wimb/
 * Description:       The plugin uses the service of WhatIsMyBrowser.com to detect old and suspicious browsers and denies them access to your website. It provides a robots.txt file to prohibit crawling and blocks crawlers if they do so anyway.
 * Update URI:        https://github.com/hupe13/wimb-and-block
 * Version:           251121
 * Requires PHP:      8.3
 * Author:            hupe13
 * Author URI:        https://leafext.de/hp/
 * Network:           true
 * License:           GPL v2 or later
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern:
defined( 'ABSPATH' ) || die();

define( 'WIMB_BASENAME', plugin_basename( __FILE__ ) ); // wimb-and-block/wimb-and-block.php
define( 'WIMB_NAME', basename( __DIR__ ) ); // wimb-and-block

require_once __DIR__ . '/php/wimb-options.php';
require_once __DIR__ . '/php/mysql.php';
require_once __DIR__ . '/php/wimb-dbdelta.php';
require_once __DIR__ . '/php/wimb.php';
require_once __DIR__ . '/php/old-agents.php';
require_once __DIR__ . '/php/faked-crawlers.php';
require_once __DIR__ . '/php/init-check-agent.php';
require_once __DIR__ . '/php/init-robots.php';
require_once __DIR__ . '/php/always-block.php';

if ( is_admin() ) {
	require_once __DIR__ . '/admin.php';
	require_once __DIR__ . '/admin/settings.php';
	require_once __DIR__ . '/admin/versions.php';
	require_once __DIR__ . '/admin/mgt-table.php';
	require_once __DIR__ . '/admin/emergency.php';
	require_once __DIR__ . '/admin/exclude.php';
	require_once __DIR__ . '/admin/always-block.php';
	require_once __DIR__ . '/admin/deleting.php';
	require_once __DIR__ . '/admin/logging.php';
	require_once __DIR__ . '/admin/logfile.php';
	require_once __DIR__ . '/admin/log-anonym.php';
	require_once __DIR__ . '/github-wimb-and-block.php';
}

// Set the initial version of the database schema
function wimbblock_activate() {
	add_option( 'wimbblock_db_version', '251011' );
}
register_activation_hook( __FILE__, 'wimbblock_activate' );

function wimbblock_update() {
	if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMB_BASENAME ) ) ) {
		$options = wimbblock_get_options_db();
		if ( $options['error'] === '0' ) {
			$current_version = get_option( 'wimbblock_db_version', '251000' );
			$new_version     = '251020'; // Update this to your new version
			if ( version_compare( $current_version, $new_version, '<' ) ) {
				$options = wimbblock_get_options_db();
				wimbblock_table_install( $options['table_name'] ); // Call the migration function
				update_option( 'wimbblock_db_version', $new_version ); // Update the version
			}
		}
	}
}
add_action( 'plugins_loaded', 'wimbblock_update' );

if ( is_main_site() ) {
	$wimbblock_options = wimbblock_get_options_db();
	if ( $wimbblock_options['rotate'] === 'yes' ) {
		require_once __DIR__ . '/php/cron.php';
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
