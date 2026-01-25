<?php
/**
 * Functions detect faked browsers SEC_CH_UA
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_check_sec_ch_ua( $table_name, $agent, $software, $version, $system, $id ) {
	// https://caniuse.com/mdn-http_headers_sec-ch-ua
	$sec_ch_ua = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_CH_UA'] ?? '' ) );
	if ( $sec_ch_ua !== '' ) {
		if ( strpos( $sec_ch_ua, 'Chromium' ) !== false ) {
			$parts = explode( ',', $sec_ch_ua );
			if ( ! (
				strpos( $sec_ch_ua, 'Microsoft Edge' ) !== false
				// "Brave";v="143", "Chromium";v="143", "Not A(Brand";v="24"
				|| strpos( $sec_ch_ua, 'Brave' ) !== false
				// "Google Chrome";v="143", "Chromium";v="143", "Not A(Brand";v="24"
				|| strpos( $sec_ch_ua, 'Google Chrome' ) !== false
				// "Opera Mini Android";v="97", "Android WebView";v="143", "Chromium";v="143", "Not A(Brand";v="24"
				|| strpos( $sec_ch_ua, 'Opera' ) !== false
				// "Chromium";v="136", "Samsung Internet";v="29.0", "Not.A/Brand";v="99"
				|| strpos( $sec_ch_ua, 'Samsung Internet' ) !== false
				// "Chromium";v="145", "Not:A-Brand";v="99"
				|| $parts !== 2
				)
			) {
				wimbblock_error_log( 'SEC_CH_UA unknown yet: ' . $sec_ch_ua . ' * ' . $agent, true );
			}
		} else {
			wimbblock_error_log( 'SEC_CH_UA not include Chromium: ' . $sec_ch_ua . ' * ' . $agent, true );
		}
	}
	if ( strpos( $agent, 'Chrome/' ) !== false
		|| ( $software !== '' && ( strpos( $software, 'Edge' ) !== false || strpos( $software, 'Chrome' ) !== false ) ) ) {
		if ( $sec_ch_ua === '' ) {
			wimbblock_error_log( 'SEC_CH_UA missed - block it?: ' . $software . ' * ' . $agent, true );
			// status_header( 404 );
			// echo 'Access forbidden.';
			// exit();
			// } else {
			// wimbblock_error_log( 'has SEC_CH_UA: ' . $sec_ch_ua . ' * ' . $agent, true );
		}
	}
}
