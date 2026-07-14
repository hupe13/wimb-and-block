<?php
/**
 * Functions detect old agents (browsers) and systems
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_check_modern_browser( $table_name, $agent, $software, $version, $system, $blocked, $id, $robots ) {
	// $software ist nie leer, wird vorher geblockt.
	$checking            = wimbblock_get_all_browsers();
	$versions_controlled = false;
	foreach ( $checking as $key => $value ) {
		if ( stripos( $software, $key ) !== false ) {
			if ( $version === '' ) {
				$version = preg_replace( '%.*' . $key . ' ([0-9]+)[^0-9].*%', '${1}', $software );
			}
			if ( $version !== '' ) {
				if ( (int) $version < (int) $value ) {
					$why = 'Blocked - old browser: ' . $software;
					wimbblock_return_error( $table_name, $agent, $blocked, $id, $robots, $why );
				}
			}
			$versions_controlled = true;
		}
	}

	// Browsers like Chromium / Chrome / Brave / Edge / and others
	// Iceweasel, Fennec, and other Firefox derivates
	if ( $versions_controlled === false ) {
		$checking  = wimbblock_get_all_browsers();
		$derivates = array( 'Chrome', 'Firefox' );
		foreach ( $derivates as $derivate ) {
			if ( strpos( $agent, $derivate . '/' ) !== false ) {
				// Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Brave Chrome/80.0.3987.162 Safari/537.36
				// Mozilla/5.0 (X11; Linux x86_64; rv:5.0) Gecko/20100101 Firefox/5.0 Iceweasel/5.0
				$version = preg_replace( '%.*' . $derivate . '/([0-9]+)[^0-9].*%', '${1}', $agent );
				if ( (int) $version < (int) $checking[ $derivate ] ) {
					$why = 'Blocked - old ' . $derivate . ' like browser: ' . $software;
					wimbblock_return_error( $table_name, $agent, $blocked, $id, $robots, $why );
				}
			}
		}
	}

	if (
		// modern Opera are Chromium based
		( strpos( $software, 'Opera' ) !== false && strpos( $agent, 'Chrome/' ) === false )
		|| strpos( $software, 'Internet Explorer' ) !== false
		|| strpos( $software, 'Netscape' ) !== false
		|| strpos( $software, 'Symbian' ) !== false
		) {
		$why = 'Blocked - old browser: ' . $software;
		wimbblock_return_error( $table_name, $agent, $blocked, $id, $robots, $why );
	}

	// Gecko/ aber kein Firefox oder Thunderbird (WHERE `browser` LIKE '%Gecko/%' AND software NOT LIKE '%Firefox%')
	// Safari/xxx Version < 537.36
	// Mozilla/xxx Version < 5
	// KHTML/
	$bad = '';
	if ( strpos( $agent, 'KHTML/' ) !== false
		|| ( strpos( $agent, 'Gecko/' ) !== false
			&& strpos( $agent, 'Firefox' ) === false
			&& strpos( $agent, 'Thunderbird' ) === false
		)
	) {
		$bad = 'KHTML/,Gecko/';
	} else {
		$strings = array(
			'Safari'  => '537.36',
			'Mozilla' => '5',
		);
		foreach ( $strings as $key => $value ) {
			if ( strpos( $agent, $key . '/' ) !== false ) {
				$version = preg_replace( '%.*' . $key . '/([0-9\.]+)%', '${1}', $agent );
				if ( (float) $version < (float) $value ) {
					$bad = $key . '/' . $version;
				}
			}
		}
	}
	if ( $bad !== '' ) {
		$why = 'Blocked - old Agent (' . $bad . ')';
		wimbblock_return_error( $table_name, $agent, $blocked, $id, $robots, $why );
	}
}

function wimbblock_unknown_agent( $table_name, $agent, $software, $blocked, $id, $robots ) {
	if ( $software === '' ) {
		$why = 'Blocked - unknown software: ';
		wimbblock_return_error( $table_name, $agent, $blocked, $id, $robots, $why );
	} elseif ( stripos( $software, 'unknown' ) !== false ) {
		$why = 'Blocked - unknown webbrowser: ' . $software;
		wimbblock_return_error( $table_name, $agent, $blocked, $id, $robots, $why );
	}
}

function wimbblock_old_system( $table_name, $agent, $system, $blocked, $id, $robots ) {
	$checking = wimbblock_get_all_systems();
	if ( strpos( $agent, 'Android' ) !== false ) {
		$version = preg_replace( '%.*Android ([0-9]+)[^0-9].*%', '${1}', $agent );
		if ( strpos( $agent, 'Chrome/' ) === false && strpos( $agent, 'Firefox/' ) === false ) { // They are using an actual Chrome / Firefox
			if ( (int) $version < (int) $checking['Android'] ) {
				$why = 'Blocked - old Android: ' . $system . ' * ' . $version;
				wimbblock_return_error( $table_name, $agent, $blocked, $id, $robots, $why );
			}
		}
	}
	if ( $system !== '' ) {
		if ( strpos( $system, 'iOS' ) !== false ) {
			$version = preg_replace( '%.*iOS ([0-9]+)[^0-9]?.*%', '${1}', $system );
			if ( (int) $version < (int) $checking['iOS'] ) {
				$why = 'Blocked - old iOS: ' . $system . ' * ' . $version;
				wimbblock_return_error( $table_name, $agent, $blocked, $id, $robots, $why );
			}
		}
	}
	// https://gs.statcounter.com/os-version-market-share/macos/desktop/worldwide
	// Apple are incorrectly reporting all macOS releases since Catalina 10.15 as Catalina 10.15.
	if ( strpos( $agent, 'Mac OS X ' ) !== false ) {
		$version = preg_replace( '%.*Mac OS X ([0-9]+)[^0-9].*%', '${1}', $agent );
		$actual  = false;
		if ( (int) $version > 10 ) {
			$actual = true;
		} elseif ( (int) $version === 10 ) {
			$version = preg_replace( '%.*Mac OS X 10.([0-9]+)[^0-9].*%', '${1}', $agent );
			if ( (int) $version === 15 ) { // Catalina
				$actual = true;
			}
		}
		if ( ! $actual ) {
			$why = 'Blocked - old Mac OS X: ' . $system . ' * ' . $version;
			wimbblock_return_error( $table_name, $agent, $blocked, $id, $robots, $why );
		}
	}

	if ( $system !== '' ) {
		if ( strpos( $system, 'Windows' ) !== false ) {
			if ( strpos( $system, 'Windows 1' ) === false ) {
				$why = 'Blocked - old Windows: ' . $system;
				wimbblock_return_error( $table_name, $agent, $blocked, $id, $robots, $why );
			}
		}
	}
}

function wimbblock_return_error( $table_name, $agent, $blocked, $id, $robots, $why ) {
	$logging = wimbblock_logging_levels_settings();
	global $wimbtest_to_block;
	if ( $robots === 'testing' ) {
		$wimbtest_to_block = $why;
	} elseif ( $robots === false ) {
		wimbblock_counter( $table_name, 'block', $id );
		wimbblock_error_log( $why . ': ' . $agent, $logging['oldagents'] ?? true );
		status_header( 404 );
		echo 'Please use a modern browser to access this website';
		exit;
	} else {
		if ( $blocked === '0' ) {
			wimbblock_counter( $table_name, 'block', $id );
		}
		wimbblock_counter( $table_name, 'robots', $id );
		wimbblock_error_log( 'robots.txt - ' . $why . ': ' . $agent, $logging['robotsforbidden'] ?? true );
		header( 'Content-Type: text/plain; charset=UTF-8' );
		echo "User-agent: *\r\n" .
		'Disallow: /' . "\r\n";
		exit;
	}
}
