<?php
/**
 * Check sec-fetch-* and Sec-CH-UA, Sec-CH-UA-Platform header
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_log_sec_headers() {
	$logging = wimbblock_logging_levels_settings();
	$todo    = $logging['tests'] ?? false;
	if ( $todo ) {
		$message     = '';
		$server_vars = $_SERVER;
		$agent       = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
		foreach ( $server_vars as $server_var => $value ) {
			if ( stripos( $server_var, 'sec_' ) !== false ) {
				$message .= ' * ' . str_replace( 'HTTP_', '', $server_var ) . ' * ' . str_replace( '\\', '', $value );
			}
		}
		if ( $message !== '' ) {
			wimbblock_error_log(
				'Header all:' . $message .
				' * ' . $agent,
				$logging['tests'] ?? false
			);
		}
	}
}

function wimbblock_sec_fetch( $software, $agent ) {
	if ( $software !== '' ) {
		$logging = wimbblock_logging_levels_settings();
		$dest    = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_FETCH_DEST'] ?? '' ) );
		$mode    = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_FETCH_MODE'] ?? '' ) );
		$site    = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_FETCH_SITE'] ?? '' ) );
		$uri     = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );

		// All of these have sec-fetch header!
		$browser_has_sec_fetch = array(
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

		if ( str_replace( $browser_has_sec_fetch, '', $software ) !== $software ) {
			if ( $dest === '' || $mode === '' || $site === '' ) {
				wimbblock_error_log(
					'Header suspect:' .
					' * dest ' . $dest .
					' * mode ' . $mode .
					' * site ' . $site .
					' * uri ' . $uri .
					' * ' . $agent,
					$logging['suspect'] ?? true
				);
				wimbblock_error_log( 'Blocked - header sec-fetch missing', $logging['suspect'] ?? true );
				status_header( 403 );
				echo '403 suspicious.';
				exit;
			}
		}
	}
}

function wimbblock_check_ch_ua( $software, $version ) {
	$message = '';
	if ( $software !== '' ) {
		$logging = wimbblock_logging_levels_settings();
		// https://developer.mozilla.org/de/docs/Web/HTTP/Reference/Headers/Sec-CH-UA
		$has_ch_ua = array(
			'Chrome',
			'Edge',
			'Opera',
			'Samsung Internet',
			'WebView Android',
		);
		$sec_ua    = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_CH_UA'] ?? '' ) );
		if ( str_replace( $has_ch_ua, '', $software ) !== $software ) {
			if ( $sec_ua === '' ) {
				$message = ' SEC_CH_UA missing * ' . $software;
			} elseif ( $version !== '' ) {
				if ( strpos( $sec_ua, 'v="' . $version . '"' ) === false ) {
					wimbblock_error_log( 'Blocked - header Sec-CH-UA version incorrect * ' . $version . ' * ' . $sec_ua, $logging['suspect'] ?? true );
					status_header( 403 );
					echo '403 suspicious.';
					exit;
				}
			}
		}
	}
	return $message;
}

function wimbblock_check_platform( $software, $system ) {
	$message = '';
	if ( $system !== '' && $software !== '' ) {
		// https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Sec-CH-UA-Platform
		$browser_has_platform = array(
			'Chrome',
			'Edge',
			'Opera',
			'Samsung Internet',
			'WebView Android',
		);
		$valid_platforms      = array(
			'Android',
			'Chrome OS',
			'Chromium OS',
			'iOS',
			'Linux',
			'macOS',
			'Windows',
			'Unknown',
		);
		$platform             = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_CH_UA_PLATFORM'] ?? '' ) );
		$platform             = trim( $platform, '"' );
		$logging              = wimbblock_logging_levels_settings();
		if ( str_replace( $browser_has_platform, '', $software ) !== $software ) { // Wenn der Browser diesen header haben sollte
			if ( str_replace( $valid_platforms, '', $system ) !== $system ) { // Wenn das system eines der o.g. Platformen ist
				if ( $platform === '' ) { // hat den header nicht
					$message .= ' platform missing * ' . $system;
				} elseif ( str_replace( $valid_platforms, '', $platform ) === $platform ) { // oder er ist aber falsch
					$message .= ' platform no valid value';
				} elseif ( strpos( $system, $platform ) === false ) { // oder er stimmt mit dem System nicht ueberein
					wimbblock_error_log( 'Blocked - header Sec-CH-UA-Platform faked: ' . $platform . ' * system ' . $system, $logging['suspect'] ?? true );
					status_header( 403 );
					echo '403 suspicious.';
					exit;
				}
			}
		}
	}
	return $message;
}

function wimbblock_check_secheaders( $software, $system, $version ) {
	$logging = wimbblock_logging_levels_settings();
	$agent   = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
	wimbblock_sec_fetch( $software, $agent );  // alle Firefox, Chrome usw. MUESSEN diese Header haben!
	$message  = wimbblock_check_ch_ua( $software, $version );
	$message .= wimbblock_check_platform( $software, $system );
	if ( $message !== '' ) {
		wimbblock_error_log( $message . ' * ' . $agent, $logging['tests'] ?? false );
	}
}
