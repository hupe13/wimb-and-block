<?php
/**
 * Functions detect old agents (browsers) and systems
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

//
function wimbblock_check_modern_browser( $table_name, $agent, $software, $version, $system, $blocked, $id, $robots ) {
	$checking = wimbblock_get_all_browsers();
	foreach ( $checking as $key => $value ) {
		wimbblock_old_agent( $table_name, $software, $version, $blocked, $id, $key, $value, false );
	}
	wimbblock_check_derivate( $table_name, $agent, $software, $version, $system, $blocked, $id, $robots );

	if ( $software !== '' ) {
		if (
			// modern Opera are Chromium based
			( strpos( $software, 'Opera' ) !== false && strpos( $agent, 'Chrome/' ) === false )
			|| strpos( $software, 'Internet Explorer' ) !== false
			|| strpos( $software, 'Netscape' ) !== false
			) {
			if ( $robots === false ) {
				wimbblock_counter( $table_name, 'block', $id );
				$logging = wimbblock_get_option( 'wimbblock_log' );
				wimbblock_error_log( 'Blocked - old browser: ' . $software . ' * ' . $agent . ' * ' . $version, $logging['oldagents'] ?? true );
				status_header( 404 );
				echo 'Please use a modern webbrowser to access this website';
				exit;
			} else {
				if ( $blocked === '0' ) {
					wimbblock_counter( $table_name, 'block', $id );
				}
				wimbblock_counter( $table_name, 'robots', $id );
				$logging = wimbblock_get_option( 'wimbblock_log' );
				wimbblock_error_log( 'robots.txt old browser forbidden: ' . $agent, $logging['robotsforbidden'] ?? true );
				header( 'Content-Type: text/plain; charset=UTF-8' );
				echo "User-agent: *\r\n" .
				'Disallow: /' . "\r\n";
				exit;
			}
		}
	}
}

function wimbblock_old_agent( $table_name, $software, $version, $blocked, $id, $browser, $min_version, $robots ) {
	if ( $software !== '' ) {
		if ( stripos( $software, $browser ) !== false ) {
			if ( $version === '' ) {
				$version = preg_replace( '%.*' . $browser . ' ([0-9]+)[^0-9].*%', '${1}', $software );
			}
			if ( $version !== '' ) {
				if ( (int) $version < (int) $min_version ) {
					if ( $robots === false ) {
						wimbblock_counter( $table_name, 'block', $id );
						$logging = wimbblock_get_option( 'wimbblock_log' );
						wimbblock_error_log( 'Blocked - old browser: ' . $browser . ' * ' . $software . ' * ' . $version, $logging['oldagents'] ?? true );
						status_header( 404 );
						echo 'Please use a modern webbrowser to access this website';
						exit;
					} else {
						if ( $blocked === '0' ) {
							wimbblock_counter( $table_name, 'block', $id );
						}
						wimbblock_counter( $table_name, 'robots', $id );
						$logging = wimbblock_get_option( 'wimbblock_log' );
						wimbblock_error_log( 'robots.txt old browser forbidden: ' . $browser . ' * ' . $software . ' * ' . $version, $logging['robotsforbidden'] ?? true );
						header( 'Content-Type: text/plain; charset=UTF-8' );
						echo "User-agent: *\r\n" .
						'Disallow: /' . "\r\n";
						exit;
					}
				}
			}
		}
	}
}

function wimbblock_check_derivate( $table_name, $agent, $software, $version, $system, $blocked, $id, $robots ) {
	// Browsers like Chromium / Chrome / Brave / Edge / and others
	// Iceweasel, Fennec, and other Firefox derivates
	$checking  = wimbblock_get_all_browsers();
	$derivates = array( 'Chrome', 'Firefox' );
	foreach ( $derivates as $derivate ) {
		if ( strpos( $agent, $derivate . '/' ) !== false ) {
			// Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Brave Chrome/80.0.3987.162 Safari/537.36
			// Mozilla/5.0 (X11; Linux x86_64; rv:5.0) Gecko/20100101 Firefox/5.0 Iceweasel/5.0
			$version = preg_replace( '%.*' . $derivate . '/([0-9]+)[^0-9].*%', '${1}', $agent );
			if ( (int) $version < (int) $checking[ $derivate ] ) {
				if ( $robots === false ) {
					wimbblock_counter( $table_name, 'block', $id );
					$logging = wimbblock_get_option( 'wimbblock_log' );
					wimbblock_error_log( 'Blocked - old ' . $derivate . ' like browser: ' . $software . ' * ' . $agent . ' * ' . $version, $logging['oldagents'] ?? true );
					status_header( 404 );
					echo 'Please use a modern webbrowser to access this website';
					exit;
				} else {
					if ( $blocked === '0' ) {
						wimbblock_counter( $table_name, 'block', $id );
					}
					wimbblock_counter( $table_name, 'robots', $id );
					$logging = wimbblock_get_option( 'wimbblock_log' );
					wimbblock_error_log( 'robots.txt old ' . $derivate . ' like forbidden: ' . $agent, $logging['robotsforbidden'] ?? true );
					header( 'Content-Type: text/plain; charset=UTF-8' );
					echo "User-agent: *\r\n" .
					'Disallow: /' . "\r\n";
					exit;
				}
			}
		}
	}
}

function wimbblock_unknown_agent( $table_name, $agent, $software, $blocked, $id, $robots ) {
	if ( $software === '' ) {
		if ( $robots === false ) {
			wimbblock_counter( $table_name, 'block', $id );
			$logging = wimbblock_get_option( 'wimbblock_log' );
			wimbblock_error_log( 'Blocked - unknown software: ' . $agent, $logging['oldagents'] ?? true );
			status_header( 404 );
			echo 'Blocked - unknown software: ' . esc_html( $agent );
			exit;
		} else {
			if ( $blocked === '0' ) {
				wimbblock_counter( $table_name, 'block', $id );
			}
			wimbblock_counter( $table_name, 'robots', $id );
			$logging = wimbblock_get_option( 'wimbblock_log' );
			wimbblock_error_log( 'robots.txt unknown software forbidden: ' . $agent, $logging['robotsforbidden'] ?? true );
			header( 'Content-Type: text/plain; charset=UTF-8' );
			echo "User-agent: *\r\n" .
			'Disallow: /' . "\r\n";
			exit;
		}
	}

	if ( stripos( $software, 'unknown' ) !== false ) {
		if ( $robots === false ) {
			wimbblock_counter( $table_name, 'block', $id );
			$logging = wimbblock_get_option( 'wimbblock_log' );
			wimbblock_error_log( 'Blocked - unknown webbrowser: ' . $agent . ' * ' . $software, $logging['oldagents'] ?? true );
			status_header( 404 );
			echo 'Blocked - unknown webbrowser';
			exit;
		} else {
			if ( $blocked === '0' ) {
				wimbblock_counter( $table_name, 'block', $id );
			}
			wimbblock_counter( $table_name, 'robots', $id );
			$logging = wimbblock_get_option( 'wimbblock_log' );
			wimbblock_error_log( 'robots.txt unknown webbrowser forbidden: ' . $agent . ' * ' . $software, $logging['robotsforbidden'] ?? true );
			header( 'Content-Type: text/plain; charset=UTF-8' );
			echo "User-agent: *\r\n" .
			'Disallow: /' . "\r\n";
			exit;
		}
	}
}

function wimbblock_old_system( $table_name, $agent, $system, $blocked, $id, $robots ) {
	$checking = wimbblock_get_all_systems();
	if ( strpos( $agent, 'Android' ) !== false ) {
		$version = preg_replace( '%.*Android ([0-9]+)[^0-9].*%', '${1}', $agent );
		if ( strpos( $agent, 'Chrome/' ) === false && strpos( $agent, 'Firefox/' ) === false ) { // They are using an actual Chrome / Firefox
			if ( (int) $version < (int) $checking['Android'] ) {
				if ( $robots === false ) {
					wimbblock_counter( $table_name, 'block', $id );
					$logging = wimbblock_get_option( 'wimbblock_log' );
					wimbblock_error_log( 'Blocked - old Android: ' . $system . ' * ' . $version, $logging['oldagents'] ?? true );
					status_header( 404 );
					echo 'Please use a modern operating system to access this website';
					exit;
				} else {
					if ( $blocked === '0' ) {
						wimbblock_counter( $table_name, 'block', $id );
					}
					wimbblock_counter( $table_name, 'robots', $id );
					$logging = wimbblock_get_option( 'wimbblock_log' );
					wimbblock_error_log( 'robots.txt old Android: ' . $system . ' * ' . $version, $logging['robotsforbidden'] ?? true );
					header( 'Content-Type: text/plain; charset=UTF-8' );
					echo "User-agent: *\r\n" .
					'Disallow: /' . "\r\n";
					exit;
				}
			}
		}
	}
	if ( $system !== '' ) {
		if ( strpos( $system, 'iOS' ) !== false ) {
			$version = preg_replace( '%.*iOS ([0-9]+)[^0-9]?.*%', '${1}', $system );
			// wimbblock_error_log( 'Test iOS: ' . $system . ' * ' . $version . ' * ' . $agent, $logging['oldagents'] ?? true );
			if ( (int) $version < (int) $checking['iOS'] ) {
				if ( $robots === false ) {
					wimbblock_counter( $table_name, 'block', $id );
					$logging = wimbblock_get_option( 'wimbblock_log' );
					wimbblock_error_log( 'Blocked - old iOS: ' . $system . ' * ' . $version, $logging['oldagents'] ?? true );
					status_header( 404 );
					echo 'Please use a modern operating system to access this website';
					exit;
				} else {
					if ( $blocked === '0' ) {
						wimbblock_counter( $table_name, 'block', $id );
					}
					wimbblock_counter( $table_name, 'robots', $id );
					$logging = wimbblock_get_option( 'wimbblock_log' );
					wimbblock_error_log( 'robots.txt old iOS: ' . $system . ' * ' . $version, $logging['robotsforbidden'] ?? true );
					header( 'Content-Type: text/plain; charset=UTF-8' );
					echo "User-agent: *\r\n" .
					'Disallow: /' . "\r\n";
					exit;
				}
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
			// wimbblock_error_log( 'Test Mac OS X: ' . $system . ' * ' . $version . ' * ' . $agent, $logging['oldagents'] ?? true );
			if ( $robots === false ) {
				wimbblock_counter( $table_name, 'block', $id );
				$logging = wimbblock_get_option( 'wimbblock_log' );
				wimbblock_error_log( 'Blocked - old Mac OS X: ' . $system . ' * ' . $version, $logging['oldagents'] ?? true );
				status_header( 404 );
				echo 'Please use a modern operating system to access this website';
				exit;
			} else {
				if ( $blocked === '0' ) {
					wimbblock_counter( $table_name, 'block', $id );
				}
				wimbblock_counter( $table_name, 'robots', $id );
				$logging = wimbblock_get_option( 'wimbblock_log' );
				wimbblock_error_log( 'robots.txt old Mac OS X: ' . $system . ' * ' . $version, $logging['robotsforbidden'] ?? true );
				header( 'Content-Type: text/plain; charset=UTF-8' );
				echo "User-agent: *\r\n" .
				'Disallow: /' . "\r\n";
				exit;
			}
		}
	}

	if ( $system !== '' ) {
		if ( strpos( $system, 'Windows' ) !== false ) {
			if ( strpos( $system, 'Windows 1' ) === false ) {
				if ( $robots === false ) {
					wimbblock_counter( $table_name, 'block', $id );
					$logging = wimbblock_get_option( 'wimbblock_log' );
					wimbblock_error_log( 'Blocked - old Windows: ' . $system, $logging['oldagents'] ?? true );
					status_header( 404 );
					echo 'Please use a modern operating system to access this website';
					exit;
				} else {
					if ( $blocked === '0' ) {
						wimbblock_counter( $table_name, 'block', $id );
					}
					wimbblock_counter( $table_name, 'robots', $id );
					$logging = wimbblock_get_option( 'wimbblock_log' );
					wimbblock_error_log( 'robots.txt old Windows forbidden: ' . $system, $logging['robotsforbidden'] ?? true );
					header( 'Content-Type: text/plain; charset=UTF-8' );
					echo "User-agent: *\r\n" .
					'Disallow: /' . "\r\n";
					exit;
				}
			}
		}
	}
}
