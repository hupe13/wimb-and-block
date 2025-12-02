<?php
/**
 * Function robots.txt
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_get_robots_txt() {
	$site      = wp_parse_url( get_home_url() );
	$http_host = $site['host'];
	$response  = wp_remote_get( 'https://' . $http_host . '/robots.txt' );
	if ( is_array( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
		header( 'Content-Type: text/plain; charset=UTF-8' );
		echo esc_html( $response['body'] ); // use the content
		exit;
	}
	header( 'Content-Type: text/plain; charset=UTF-8' );
	do_robots();
	exit;
}

function wimbblock_check_robots_txt( $posts ) {
	global $wp;

	if ( ! is_admin()
	&& ( strtolower( $wp->request ) === 'robots-check' || strtolower( $wp->request ) === 'robots.txt' )
	) {

		$ip        = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		$agent     = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
		$agent     = trim( $agent, '"\' ' );
		$server_ip = sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ?? '' ) );

		if ( $ip === $server_ip && $agent !== 'wimb-and-block test agent' ) {
			// wimbblock_error_log( 'Server Task: ' . $agent );
			wimbblock_get_robots_txt();
		}

		if ( $ip === '127.0.0.1' && $agent !== 'wimb-and-block test agent' ) {
			wimbblock_get_robots_txt();
		}

		if ( $agent === '' ) {
			wimbblock_error_log( 'robots no agent - blocked: ' . $ip );
			status_header( 404 );
			echo 'You have been blocked.';
			exit();
		}

		$stop = wimbblock_get_option( 'wimbblock_emergency' );
		if ( $stop !== false ) {
			if ( $stop === '0' ) {
				wimbblock_get_robots_txt();
			}
		}

		$wpdb_options = wimbblock_get_options_db();
		if ( $wpdb_options['error'] !== '0' || $wpdb_options['wimb_api'] === '' ) {
			wimbblock_get_robots_txt();
		}

		$excludes = wimbblock_get_option( 'wimbblock_exclude' );
		if ( $excludes !== false ) {
			foreach ( $excludes as $exclude ) {
				if ( stripos( $agent, $exclude ) !== false ) {
					$logging = wimbblock_get_option( 'wimbblock_log' );
					wimbblock_error_log( 'Excluded - robots.txt okay: ' . $agent . ' * ' . $exclude, $logging['excluded'] ?? true );
					wimbblock_get_robots_txt();
				}
			}
		}

		global $wimb_datatable;
		if ( is_null( $wimb_datatable ) ) {
			wimbblock_open_wpdb();
		}
		$wpdb_options = wimbblock_get_options_db();
		$table_name   = $wpdb_options['table_name'];

		global $wimbblock_is_crawler;
		$wimbblock_is_crawler = false;

		list ( $software, $system, $version, $blocked, $id ) = wimbblock_check_wimb( $agent, $table_name );

		if ( (int) $blocked > 0 ) {
			wimbblock_counter( $table_name, 'robots', $id );
			$logging = wimbblock_get_option( 'wimbblock_log' );
			wimbblock_error_log( 'robots.txt forbidden: ' . ( $software !== '' ? $software : $agent ), $logging['robotsforbidden'] ?? true );
			header( 'Content-Type: text/plain; charset=UTF-8' );
			echo "User-agent: *\r\n" .
			'Disallow: /' . "\r\n";
			exit;
		}

		wimbblock_always( $table_name, $agent, $blocked, $id, true );
		wimbblock_old_system( $table_name, $agent, $system, $blocked, $id, true );

		wimbblock_faked_crawler( $agent, $software, $ip, true );
		if ( $wimbblock_is_crawler === false ) {
			wimbblock_unknown_agent( $table_name, $agent, $software, $blocked, $id, true );
			if ( $software !== '' ) {
				wimbblock_check_modern_browser( $table_name, $software, $version, $system, $blocked, $id, true );
			}
		}
		$logging = wimbblock_get_option( 'wimbblock_log' );
		wimbblock_error_log( 'robots.txt okay: ' . ( $software !== '' ? $software : $agent ), $logging['robotsokay'] ?? true );
		wimbblock_counter( $table_name, 'robots', $id );
		wimbblock_get_robots_txt();
	}
	return $posts;
}
add_filter( 'the_posts', 'wimbblock_check_robots_txt', -10 );
