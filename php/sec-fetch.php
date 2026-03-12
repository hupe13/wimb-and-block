<?php
/**
 * Check sec-fetch header
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_secheaders_log() {
	global $wimbblock_webbrowser;
	if ( $wimbblock_webbrowser !== false ) {
		$dest = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_FETCH_DEST'] ?? '' ) );
		$mode = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_FETCH_MODE'] ?? '' ) );
		$site = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_FETCH_SITE'] ?? '' ) );

		if ( $dest === '' || $mode === '' || $site === '' ) {
			$agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
			wimbblock_error_log(
				'Sec-Fetch Header suspect: ' .
				' * dest ' . $dest .
				' * mode ' . $mode .
				' * site ' . $site .
				' * agent ' . $wimbblock_webbrowser .
				' * ' . $agent,
				true
			);
		}
	}
}
