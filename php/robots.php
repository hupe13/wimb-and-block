<?php
/**
 * Function robots.txt
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_get_robots_txt( $posts ) {
	global $wp;
	global $wpdb;
	global $wimb_datatable;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}
	$wpdb_options          = wimbblock_get_options_db();
	$table_name            = $wpdb_options['table_name'];
	$wimbblock_robots_slug = 'robots-check'; // URL slug of the robots.txt fake page
	$ip                    = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$agent                 = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
	$agent                 = trim( $agent, '"\' ' );
	global $is_crawler;
	$is_crawler = false;

	if (
		! is_admin()
		&& $wpdb_options['error'] === '0'
		&& $wpdb_options['wimb_api'] !== ''
		&& $ip !== '127.0.0.1'
		&& $ip !== false
		// && $user_login === ''
		// && $agent !== ''
		&& strpos( $agent, 'WordPress' ) === false
		&& strpos( $agent, 'WP-URLDetails' ) === false
		&& strpos( $agent, 'cronBROWSE' ) === false
		&& strpos( $agent, get_site_url() ) === false
		&& ! is_404()
	) {
		if ( ( strtolower( $wp->request ) === $wimbblock_robots_slug || strtolower( $wp->request ) === 'robots.txt' ) ) {
			if ( $agent === '' ) {
				$logging = wimbblock_get_option( 'wimbblock_log' );
				wimbblock_error_log( 'robots forbidden - no agent: ' . $ip, $logging['robotsforbidden'] );
				header( 'Content-Type: text/plain; charset=UTF-8' );
				echo "User-agent: *\r\n" .
				'Disallow: /' . "\r\n";
				exit;
			}
			list ( $software, $system, $version, $blocked, $id ) = wimbblock_check_wimb( $agent, $table_name );
			if ( (int) $blocked > 0 ) {
				wimbblock_counter( $table_name, 'robots', $id );
				$logging = wimbblock_get_option( 'wimbblock_log' );
				wimbblock_error_log( 'robots.txt forbidden: ' . ( $software !== '' ? $software : $agent ), $logging['robotsforbidden'] );
				header( 'Content-Type: text/plain; charset=UTF-8' );
				echo "User-agent: *\r\n" .
				'Disallow: /' . "\r\n";
				exit;
			}
			wimbblock_always( $table_name, $agent, $blocked, $id, true );
			wimbblock_old_system( $table_name, $system, $blocked, $id, true );

			wimbblock_faked_crawler( $agent, $software, $ip, true );
			if ( $is_crawler === false ) {
				wimbblock_unknown_agent( $table_name, $agent, $software, $blocked, $id, true );
				if ( $software !== '' ) {
					wimbblock_check_modern_browser( $table_name, $software, $version, $system, $blocked, $id, true );
				}
			}
			$logging = wimbblock_get_option( 'wimbblock_log' );
			wimbblock_error_log( 'robots.txt okay: ' . ( $software !== '' ? $software : $agent ), $logging['robotsokay'] );
			wimbblock_counter( $table_name, 'robots', $id );
			// Default 'WordPress/' . get_bloginfo( 'version' ) . ‘; ‘ . get_bloginfo( 'url' ).
			$http_host = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ?? '' ) );
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
	}
	return $posts;
}
add_filter( 'the_posts', 'wimbblock_get_robots_txt', -10 );
