<?php
/**
 * Check sec-fetch-* and Sec-CH-UA, Sec-CH-UA-Platform header
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

// Sec-CH-UA-Platform-Version
// Windows 10: values between 1.0.0 and 10.0.0
// Windows 11: 13.0.0 and above

function wimbblock_log_sec_headers( $info ) {
	$logging = wimbblock_logging_levels_settings();
	$todo    = $logging['tests'] ?? false;
	// zum Testen auskommentieren
	// $todo = false;
	if ( $todo ) {
		$message     = array();
		$interesting = array(
			'HTTP_ACCEPT',
			'HTTP_REFERER',
		);
		$server_vars = $_SERVER;
		$agent       = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
		foreach ( $server_vars as $server_var => $value ) {
			if ( stripos( $server_var, 'sec_' ) !== false || in_array( $server_var, $interesting, true ) ) {
				$message[] = str_replace( 'HTTP_', '', $server_var ) . ' - ' . str_replace( '\\', '', $value );
			}
		}

		if ( count( $message ) > 0 ) {
			$known   = array(
				'SEC_FETCH_DEST',
				'SEC_FETCH_MODE',
				'SEC_FETCH_SITE',
				'SEC_CH_UA',
				'SEC_CH_UA_MOBILE',
				'SEC_CH_UA_PLATFORM',
				'ACCEPT',
				'HTTP_REFERER',
			);
			$headers = array();
			foreach ( $known as $header ) {
				$keys = array_keys( preg_grep( '/' . $header . ' - /', $message ) );
				foreach ( $keys as $key ) {
					if ( strpos( $message[ $key ], 'ACCEPT' ) !== false && strpos( $message[ $key ], ',' ) !== false ) {
						// wimbblock_error_log( 'unset ' . $message[ $key ], $logging['tests'] ?? false );
						unset( $message[ $key ] );
					} else {
						$headers[] = $message[ $key ];
						unset( $message[ $key ] );
					}
				}
			}
			foreach ( $message as $part ) {
				$headers[] = $part;
			}
			if ( count( $headers ) > 0 ) {
				wimbblock_error_log(
					'Test Header ' . $info . ': ' .
					implode( ' * ', $headers ) .
					' * ' . $agent,
					$logging['tests'] ?? false
				);
			}
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

		if ( str_replace( $browser_has_sec_fetch, '', $software ) !== $software ) {
			if ( $dest === '' || $mode === '' || $site === '' ) {
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
			// Servers should ignore this header if it contains any other value.
			$sec_fetch_dest = array(
				'document',
				'empty',
				'iframe',
				'image',
				// and many others
			);
			// Servers should ignore this header if it contains any other value.
			$sec_fetch_mode = array(
				'cors',
				'navigate',
				'no-cors',
				'same-origin',
				'websocket',
			);
			$sec_fetch_site = array(
				'cross-site',
				'same-origin',
				'same-site',
				'none',
			);
			$message        = '';
			if ( ! in_array( $dest, $sec_fetch_dest, true ) ) {
				$message .= ' Dest * ' . $dest;
			}
			if ( ! in_array( $mode, $sec_fetch_mode, true ) ) {
				$message .= ' Mode * ' . $mode;
			}
			if ( ! in_array( $site, $sec_fetch_site, true ) ) {
				$message .= ' Site * ' . $site;
			}
			if ( $message !== '' ) {
				wimbblock_error_log( 'Test Debug Header not valid:' . $message . ' * ' . $agent, $logging['tests'] ?? false );
			}
		}
	}
}

function wimbblock_check_ch_ua( $agent, $software, $version ) {
	$message = '';
	$logging = wimbblock_logging_levels_settings();
	// https://developer.mozilla.org/de/docs/Web/HTTP/Reference/Headers/Sec-CH-UA
	$has_ch_ua = array(
		// Chrome/149.0.0.0 * "Google Chrome";v="149", "Chromium";v="149", "Not)A;Brand";v="24"
		'Chrome',
		// Chrome/152.0.0.0 * "Not?A_Brand";v="24", "Chromium";v="152"
		'Chromium',
		// Chrome/149.0 Edg/149.0 * "Microsoft Edge";v="149", "Chromium";v="149"
		'Edge',
		// Chrome/149.0 OPR/133.0 * "Opera";v="133", "Chromium";v="149"
		'Opera',
		// SamsungBrowser/30.0 Chrome/143.0.0.0 * "Samsung Internet";v="30.0", "Chromium";v="143"
		'Samsung Internet',
		'Android WebView',
	);

	$sec_ua = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_CH_UA'] ?? '' ) );
	if ( $sec_ua !== '' ) {
		if ( strpos( $agent, 'Chrome/' ) !== false ) {
			// alles mit "Chrome/xxx"
			$version = preg_replace( '%.* Chrome/([0-9]+)[^0-9].*%', '${1}', $agent );
			if ( $version !== '' ) {
				$versionstypes = array(
					'v="' . $version . '.',
					'v="' . $version . '"',
				);
				if ( str_replace( $versionstypes, '', $sec_ua ) === $sec_ua ) {
					wimbblock_log_sec_headers( 'blocked' );
					wimbblock_error_log( 'Blocked header: Sec-CH-UA version incorrect * ' . $version . ' * ' . $sec_ua, $logging['suspect'] ?? true );
					status_header( 403 );
					echo '403 suspicious.';
					exit();
				}
			} else {
				$message .= ' * Chrome/xxx missing - ' . $agent . ' * ' . $sec_ua;
			}
		} else {
			$message .= ' * Chrome/ missing - ' . $agent . ' * ' . $sec_ua;
		}
	}
	if ( $software !== '' ) {
		if ( str_replace( $has_ch_ua, '', $software ) !== $software ) { // ist eines der o.g.
			if ( $sec_ua === '' ) {
				$message .= ' * SEC_CH_UA missing - ' . $agent . ' * ' . $software;
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
		$platform             = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_CH_UA_PLATFORM'] ?? '' ) );
		$platform             = trim( $platform, '"' );
		$logging              = wimbblock_logging_levels_settings();

		if ( $platform !== '' ) {
			if ( strpos( $system, $platform ) === false ) { // Sec-CH-UA-Platform stimmt mit dem System nicht ueberein
				// 'Samsung Internet' sends in Desktop mode "Linux" instead "Android"
				if ( ! ( strpos( $software, 'Samsung Internet' ) !== false && $platform === 'Linux' ) ) {
					wimbblock_log_sec_headers( 'blocked' );
					wimbblock_error_log( 'Blocked header: Sec-CH-UA-Platform faked: ' . $platform . ' * system ' . $system, $logging['suspect'] ?? true );
					status_header( 403 );
					echo '403 suspicious.';
					exit;
				}
			}
		}
		if ( str_replace( $browser_has_platform, '', $software ) !== $software ) { // Wenn der Browser diesen header haben sollte
			if ( str_replace( $valid_platforms, '', $system ) !== $system ) { // Wenn das system eines der o.g. Platformen ist
				if ( $platform === '' ) { // hat den header nicht
					$message .= ' * platform missing - ' . $system;
				} elseif ( str_replace( $valid_platforms, '', $platform ) === $platform ) { // oder er ist aber falsch
					$message .= ' * platform no valid value';
				}
			}
		}
	}
	return $message;
}

function wimbblock_prefetch_block() {
	$purpose = sanitize_text_field( wp_unslash( $_SERVER['HTTP_SEC_PURPOSE'] ?? '' ) );
	if ( $purpose === 'prefetch;anonymous-client-ip' ) {
		wimbblock_log_sec_headers( 'blocked' );
		status_header( 403 );
		exit;
	} elseif ( $purpose !== '' ) {
		return ' * purpose ' . $purpose;
	}
}

function wimbblock_check_secheaders( $software, $system, $version ) {
	$logging = wimbblock_logging_levels_settings();
	$agent   = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
	wimbblock_sec_fetch( $software, $agent );  // alle Firefox, Chrome usw. MUESSEN diese Header haben!
	$message  = wimbblock_check_ch_ua( $agent, $software, $version );
	$message .= wimbblock_check_platform( $software, $system );
	// $message .= wimbblock_prefetch_block();
	if ( $message !== '' ) {
		wimbblock_log_sec_headers( 'Debug' );
		wimbblock_error_log( 'Test Debug' . $message, $logging['tests'] ?? false );
	}
}
