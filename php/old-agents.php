<?php
/**
 * Functions detect old agents (browsers) and systems
 *
 * @package wimb-and-block
 */

//
function wimbblock_check_modern_browser( $table_name, $software, $version, $system, $id ) {
	$checking = wimbblock_get_all_browsers();
	foreach ( $checking as $key => $value ) {
		wimbblock_old_agent( $table_name, $software, $version, $id, $key, $value );
	}

	// Brave is like Chromium / Chrome
	$agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
	if ( strpos( $agent, 'Brave' ) !== false ) {
		// Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Brave Chrome/80.0.3987.162 Safari/537.36
		$version = preg_replace( '%.*Chrome/%', '', $agent );
		$version = preg_replace( '%\..*%', '', $version );
		wimbblock_error_log( 'Brave Version: ' . $software . ' * ' . $agent . ' * ' . $version );
		if ( (int) $version < (int) $checking['Chrome'] ) {
			wimbblock_counter( $table_name, 'block', $id );
			wimbblock_error_log( 'Blocked - old Brave: ' . $software . ' * ' . $agent . ' * ' . $version );
			status_header( 404, 'Please use a modern webbrowser to access this website' );
			exit();
		}
	}
}

function wimbblock_old_agent( $table_name, $software, $version, $id, $browser, $min_version ) {
	if ( $software !== '' ) {
		if ( strpos( $software, $browser ) !== false ) {
			if ( $version !== '' ) {
				if ( (int) $version < (int) $min_version ) {
					wimbblock_counter( $table_name, 'block', $id );
					wimbblock_error_log( 'Blocked - old browser: ' . $browser . ' * ' . $software . ' * ' . $version );
					status_header( 404, 'Please use a modern webbrowser to access this website' );
					exit();
				}
			} else {
				if ( (int) $min_version !== 9999 ) {
					preg_match_all( '!\d+!', $software, $version );
					$is_version = isset( $version[0][0] ) ? $version[0][0] : 0;
				} else {
					$is_version = 0;
				}
				if ( (int) $is_version < (int) $min_version ) {
					wimbblock_counter( $table_name, 'block', $id );
					wimbblock_error_log( 'Blocked - old browser: ' . $browser . ' * ' . $software );
					status_header( 404, 'Please use a modern webbrowser to access this website' );
					exit();
				}
			}
		}
	}
}

function wimbblock_unknown_agent( $table_name, $agent, $software, $id ) {
	$blocking = get_option(
		'wimbblock_unknown_empty',
		array(
			'unknown' => '1',
			'empty'   => '1',
		)
	);
	if ( $software === '' && $blocking['empty'] === '1' ) {
		wimbblock_counter( $table_name, 'block', $id );
		wimbblock_error_log( 'Blocked - unknown software: ' . $agent );
		status_header( 404, 'Blocked - unknown software.' );
		exit();
	}

	if ( stripos( $software, 'unknown' ) !== false && $blocking['unknown'] === '1' ) {
		wimbblock_counter( $table_name, 'block', $id );
		wimbblock_error_log( 'Blocked - unknown webbrowser: ' . $agent . ' * ' . $software );
		status_header( 404, 'Blocked - unknown webbrowser' );
		exit();
	}
}

function wimbblock_old_system( $table_name, $system, $id ) {
	if ( $system !== '' ) {
		$old_systems = array( 'Vista', 'Windows XP', 'Windows 9', 'Windows CE', 'Windows NT', 'Windows 7', 'Windows 8', 'Windows 2000' );
		// , 'Android ('
		foreach ( $old_systems as $old_system ) {
			if ( strpos( $system, $old_system ) !== false ) {
				wimbblock_counter( $table_name, 'block', $id );
				wimbblock_error_log( 'Blocked - old system: ' . $system );
				status_header( 404, 'Please use a modern operating system to access this website' );
				exit();
			}
		}
	}
}
