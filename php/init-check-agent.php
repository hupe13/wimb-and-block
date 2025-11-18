<?php
/**
 * Function check-agents
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_check_agent() {
	$stop = wimbblock_get_option( 'wimbblock_emergency' );
	if ( $stop !== false ) {
		if ( $stop === '0' ) {
			return;
		}
	}
	$wpdb_options = wimbblock_get_options_db();
	if ( $wpdb_options['error'] !== '0' || $wpdb_options['wimb_api'] === '' ) {
		return;
	}

	$server_ip = sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ?? '' ) );
	$ip        = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$agent     = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
	$agent     = trim( $agent, '"\' ' );

	if ( $ip === $server_ip && $agent !== 'wimb-and-block test agent' ) {
		// wimbblock_error_log( 'Server Task: ' . $agent );
		return;
	}

	if ( strpos( $agent, 'WordPress/Private' ) !== false
		|| boolval( preg_match( '#WordPress/.+' . get_home_url() . '#', $agent ) ) !== false
		|| strpos( $agent, 'WP-URLDetails' ) !== false
		|| strpos( $agent, 'cronBROWSE' ) !== false ) {
			wimbblock_error_log( 'Never? : ' . $agent );
	}

	if ( $ip === '' ) {
		wimbblock_error_log( 'no ip - blocked: ' . $agent );
		status_header( 404 );
		echo 'You have been blocked.';
		exit();
	}

	if ( $agent === '' ) {
		wimbblock_error_log( 'no agent - blocked: ' . $ip );
		status_header( 404 );
		echo 'You have been blocked.';
		exit();
	}

	$excludes = wimbblock_get_option( 'wimbblock_exclude' );
	if ( $excludes !== false ) {
		foreach ( $excludes as $exclude ) {
			if ( stripos( $agent, $exclude ) !== false ) {
				$logging = wimbblock_get_option( 'wimbblock_log' );
				wimbblock_error_log( 'Excluded: ' . $agent . ' * ' . $exclude, $logging['excluded'] ?? true );
				return;
			}
		}
	}

	global $user_login;
	global $wimbblock_software;
	global $wimbblock_is_crawler;
	$wimbblock_is_crawler = false;

	$file       = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
	$table_name = $wpdb_options['table_name'];

	if (
		! is_admin()
		&& $user_login === ''
		&& $ip !== '127.0.0.1'
		&& strpos( $agent, 'WordPress/Private' ) === false
		&& boolval( preg_match( '#WordPress/.+' . get_home_url() . '#', $agent ) ) === false
		&& strpos( $agent, 'WP-URLDetails' ) === false
		&& strpos( $agent, 'cronBROWSE' ) === false
		&& strpos( $file, 'robots.txt' ) === false
		&& strpos( $file, 'robots-check' ) === false
		&& ! is_404()
	) {
		global $wimb_datatable;
		if ( is_null( $wimb_datatable ) ) {
			wimbblock_open_wpdb();
		}
		list ( $software, $system, $version, $blocked, $id ) = wimbblock_check_wimb( $agent, $table_name );
		if ( (int) $blocked > 0 ) {
			wimbblock_counter( $table_name, 'block', $id );
			$logging = wimbblock_logging_levels_settings();
			wimbblock_error_log( 'Blocked again: ' . ( ( $software === '' || stripos( $software, 'unknown' ) !== false ) ? $agent : $software ), $logging['blockagain'] ?? true );
			status_header( 404 );
			echo 'Blocked - agent is old or suspicious or forbidden: ' . esc_html( $agent );
			exit();
		}
		wimbblock_always( $table_name, $agent, $blocked, $id, false );
		wimbblock_old_system( $table_name, $system, $blocked, $id, false );
		wimbblock_faked_crawler( $agent, $software, $ip, false );
		if ( $wimbblock_is_crawler === false ) {
			wimbblock_unknown_agent( $table_name, $agent, $software, $blocked, $id, false );
			if ( $software !== '' ) {
				wimbblock_check_modern_browser( $table_name, $software, $version, $system, $blocked, $id, false );
			}
		}
		wimbblock_counter( $table_name, 'count', $id );
		$wimbblock_software = $software;
	}
}
add_action( 'init', 'wimbblock_check_agent', 8 );
