<?php
/**
 * Check sec-fetch-* and Sec-CH-UA, Sec-CH-UA-Platform header
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_sec_fetch( $agent, $software, $logging ) {
	$dest = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_FETCH_DEST'] ?? '' ) );
	$mode = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_FETCH_MODE'] ?? '' ) );
	$site = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_FETCH_SITE'] ?? '' ) );
	$uri  = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );

	// All of these have sec-fetch header!
	$browser_has_sec_fetch = array(
		'Chrome',
		'Chromium',
		'Edge',
		'Safari',
		'Firefox',
		'Opera',
		'Samsung Internet',
		'Android WebView',
		'UC Browser',
		'QQ Browser',
		'KaiOS Browser',
	);
	if ( $dest === '' || $mode === '' || $site === '' ) {
		if ( ( $software !== '' && str_replace( $browser_has_sec_fetch, '', $software ) !== $software )
		|| (
		( strpos( $agent, 'Chrome/' ) !== false
		|| strpos( $agent, 'Chromium/' ) !== false
		|| strpos( $agent, 'Firefox/' ) !== false )
		)
		) {
			wimbblock_error_log(
				'Blocked header: Sec-Fetch missing: ' .
				' * dest ' . $dest .
				' * mode ' . $mode .
				' * site ' . $site .
				' * software ' . $software .
				' * uri ' . $uri .
				' * ' . $agent,
				$logging['suspect'] ?? true
			);
			status_header( 403 );
			echo '403 suspicious.';
			exit();
		}
	}
}

function wimbblock_check_ch_ua( $agent, $logging ) {
	$message = '';
	// https://developer.mozilla.org/de/docs/Web/HTTP/Reference/Headers/Sec-CH-UA
	// all have "Chrome/" in agent string
	// Chrome/149.0.0.0 * "Google Chrome";v="149", "Chromium";v="149", "Not)A;Brand";v="24"
	// Chrome/152.0.0.0 * "Not?A_Brand";v="24", "Chromium";v="152"
	// Chrome/149.0 Edg/149.0 * "Microsoft Edge";v="149", "Chromium";v="149"
	// Chrome/149.0 OPR/133.0 * "Opera";v="133", "Chromium";v="149"
	// SamsungBrowser/30.0 Chrome/143.0.0.0 * "Samsung Internet";v="30.0", "Chromium";v="143"
	if ( strpos( $agent, 'Chrome/' ) !== false || strpos( $agent, 'CriOS/' ) !== false ) {
		$sec_ua = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_CH_UA'] ?? '' ) );
		if ( $sec_ua !== '' ) {
			// alles mit "Chrome/xxx" bzw. "CriOS/xxx"
			$version = preg_replace( '%.* (Chrome|CriOS)/([0-9]+)[^0-9].*%', '${2}', $agent );
			if ( $version !== '' ) {
				$versionstypes = array(
					'v="' . $version . '.',
					'v="' . $version . '"',
				);
				if ( str_replace( $versionstypes, '', $sec_ua ) === $sec_ua ) {
					wimbblock_error_log( 'Blocked header: Sec-CH-UA version incorrect * ' . $version . ' * ' . $sec_ua, $logging['suspect'] ?? true );
					status_header( 403 );
					echo '403 suspicious.';
					exit();
				}
			} else {
				$message .= ' * Chrome/xxx missing - Sec-CH-UA ' . $sec_ua;
			}
		} else {
			$message .= ' * Sec-CH-UA missing';
		}
	}
	return $message;
}

function wimbblock_check_platform( $agent, $software, $system, $logging ) {
	$message = '';
	// https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Sec-CH-UA-Platform
	// all have "Chrome/" in agent string, see above
	$browser_has_platform = array(
		'Chrome',
		'Chromium',
		'Edge',
		'Opera',
		'Samsung Internet',
		'Android WebView',
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
	$platform             = trim( sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_CH_UA_PLATFORM'] ?? '' ) ), '"' );

	if ( $platform !== '' ) {
		if ( ! in_array( $platform, $valid_platforms, true ) ) {
			wimbblock_error_log( 'Blocked header: Sec-CH-UA-Platform not valid: ' . $platform, $logging['suspect'] ?? true );
				status_header( 403 );
				echo '403 suspicious.';
				exit;
		}
		if ( $system !== '' ) {
			if ( strpos( $system, $platform ) === false ) { // Sec-CH-UA-Platform stimmt mit dem System nicht ueberein
				// 'Samsung Internet' sends in Desktop mode "Linux" instead "Android"
				if ( ! ( $software !== '' && strpos( $software, 'Samsung Internet' ) !== false && $platform === 'Linux' ) ) {
					wimbblock_error_log( 'Blocked header: Sec-CH-UA-Platform faked: ' . $platform . ' * system ' . $system, $logging['suspect'] ?? true );
					status_header( 403 );
					echo '403 suspicious.';
					exit;
				}
			}
		} else {
			$message .= ' * no system * Sec-CH-UA-Platform ' . $platform;
		}
	} elseif ( strpos( $agent, 'Chrome/' ) !== false || strpos( $agent, 'CriOS/' ) !== false ) {
		// Wenn der Browser diesen header haben sollte aber nicht hat
		$message .= ' * Sec-CH-UA-Platform missing';
	}
	return $message;
}

function wimbblock_check_secheaders( $agent, $software, $system ) {
	$logging = wimbblock_logging_levels_settings();
	// all Firefox, Chrome usw. MUST have these Headers!
	wimbblock_sec_fetch( $agent, $software, $logging );
	$message  = wimbblock_check_ch_ua( $agent, $logging );
	$message .= wimbblock_check_platform( $agent, $software, $system, $logging );
	if ( function_exists( 'wimbblock_prefetch_block' ) ) {
		$message .= wimbblock_prefetch_block( $logging );
	}
	if ( function_exists( 'wimbblock_log_secchua' ) ) {
		$message .= wimbblock_log_secchua( $agent, $logging );
	}
	if ( $message !== '' ) {
		wimbblock_error_log( 'Test Sec-CH Headers' . $message . ' * agent ' . $agent, $logging['tests'] ?? false );
	}
}
