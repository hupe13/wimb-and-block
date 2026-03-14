<?php
/**
 * Check sec-fetch header
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_secheaders_log( $software = 'before' ) {
	global $wimbblock_webbrowser;
	$dest    = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_FETCH_DEST'] ?? '' ) );
	$mode    = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_FETCH_MODE'] ?? '' ) );
	$site    = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_FETCH_SITE'] ?? '' ) );
	$proto   = sanitize_text_field( wp_unslash( $_SERVER['SERVER_PROTOCOL'] ?? '' ) );
	$uri     = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
	$agent   = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
	$logging = wimbblock_logging_levels_settings();

	if ( $software === 'before' ) {
		if ( $dest === '' || $mode === '' || $site === '' || $proto === 'HTTP/1.1' ) {
			wimbblock_error_log(
				'Header suspect:' .
				' * dest ' . $dest .
				' * mode ' . $mode .
				' * site ' . $site .
				' * proto ' . $proto .
				' * uri ' . $uri .
				' * ' . $agent,
				$logging['tests'] ?? false
			);
		}
	} else {
		$sec_fetch = array(
			'Chrome',
			'Edge',
			'Safari',
			'Firefox',
			'Opera',
			'Samsung Internet',
			'UC Browser',
			'QQ Browser',
			'KaiOS Browser',
		);
		if ( $wimbblock_webbrowser !== false ) {
			if ( $dest === '' || $mode === '' || $site === '' || $proto === 'HTTP/1.1' ) {
				if ( str_replace( $sec_fetch, '', $software ) !== $software ) {
					wimbblock_error_log(
						'Header should block:' .
						' * dest ' . $dest .
						' * mode ' . $mode .
						' * site ' . $site .
						' * proto ' . $proto .
						' * uri ' . $uri .
						' * ' . $agent,
						true
					);
				} else {
					wimbblock_error_log(
						'Header suspicious:' .
						' * dest ' . $dest .
						' * mode ' . $mode .
						' * site ' . $site .
						' * proto ' . $proto .
						' * uri ' . $uri .
						' * ' . $agent,
						$logging['tests'] ?? false
					);
				}
			}
		}
	}
}
