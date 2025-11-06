<?php
/**
 * Functions detect old agents (browsers) and systems
 *
 * @package wimb-and-block
 */

//
function wimbblock_check_modern_browser( $table_name, $software, $version, $system, $blocked, $id, $robots ) {
	$checking = wimbblock_get_all_browsers();
	foreach ( $checking as $key => $value ) {
		wimbblock_old_agent( $table_name, $software, $version, $blocked, $id, $key, $value, false );
	}

	// Browsers like Chromium / Chrome / Brave and others
	// Iceweasel, Fennec, and other Firefox derivates
	if ( $software !== '' ) {
		$derivates = array( 'Chrome', 'Firefox' );
		foreach ( $derivates as $derivate ) {
			if ( strpos( $software, $derivate ) === false ) {
				$agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
				if ( strpos( $agent, $derivate . '/' ) !== false ) {
					// Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Brave Chrome/80.0.3987.162 Safari/537.36
					// Mozilla/5.0 (X11; Linux x86_64; rv:5.0) Gecko/20100101 Firefox/5.0 Iceweasel/5.0
					$version = preg_replace( '%.*' . $derivate . '/%', '', $agent );
					$version = preg_replace( '%\..*%', '', $version );
					if ( (int) $version < (int) $checking[ $derivate ] ) {
						if ( $robots === false ) {
							wimbblock_counter( $table_name, 'block', $id );
							$logging = wimbblock_get_option( 'wimbblock_log' );
							wimbblock_error_log( 'Blocked - old ' . $derivate . ' like browser: ' . $software . ' * ' . $agent . ' * ' . $version, $logging['oldagents'] );
							status_header( 404 );
							echo 'Please use a modern webbrowser to access this website';
							exit();
						} else {
							if ( $blocked === '0' ) {
								wimbblock_counter( $table_name, 'block', $id );
							}
							wimbblock_counter( $table_name, 'robots', $id );
							$logging = wimbblock_get_option( 'wimbblock_log' );
							wimbblock_error_log( 'robots.txt old ' . $derivate . ' like forbidden: ' . $agent, $logging['robotsforbidden'] );
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
}

function wimbblock_old_agent( $table_name, $software, $version, $blocked, $id, $browser, $min_version, $robots ) {
	if ( $software !== '' ) {
		if ( strpos( $software, $browser ) !== false ) {
			if ( $version !== '' ) {
				if ( (int) $version < (int) $min_version ) {
					if ( $robots === false ) {
						wimbblock_counter( $table_name, 'block', $id );
						$logging = wimbblock_get_option( 'wimbblock_log' );
						wimbblock_error_log( 'Blocked - old browser: ' . $browser . ' * ' . $software . ' * ' . $version, $logging['oldagents'] );
						status_header( 404 );
						echo 'Please use a modern webbrowser to access this website';
						exit();
					} else {
						if ( $blocked === '0' ) {
							wimbblock_counter( $table_name, 'block', $id );
						}
						wimbblock_counter( $table_name, 'robots', $id );
						$logging = wimbblock_get_option( 'wimbblock_log' );
						wimbblock_error_log( 'robots.txt old browser forbidden: ' . $browser . ' * ' . $software . ' * ' . $version, $logging['robotsforbidden'] );
						header( 'Content-Type: text/plain; charset=UTF-8' );
						echo "User-agent: *\r\n" .
						'Disallow: /' . "\r\n";
						exit;
					}
				} else {
					if ( (int) $min_version !== 9999 ) {
						preg_match_all( '!\d+!', $software, $version );
						$is_version = isset( $version[0][0] ) ? $version[0][0] : 0;
					} else {
						$is_version = 0;
					}
					if ( (int) $is_version < (int) $min_version ) {
						if ( $robots === false ) {
							wimbblock_counter( $table_name, 'block', $id );
							$logging = wimbblock_get_option( 'wimbblock_log' );
							wimbblock_error_log( 'Blocked - old browser: ' . $browser . ' * ' . $software, $logging['oldagents'] );
							status_header( 404 );
							echo 'Please use a modern webbrowser to access this website';
							exit();
						} else {
							if ( $blocked === '0' ) {
								wimbblock_counter( $table_name, 'block', $id );
							}
							wimbblock_counter( $table_name, 'robots', $id );
							$logging = wimbblock_get_option( 'wimbblock_log' );
							wimbblock_error_log( 'robots.txt old browser forbidden: ' . $browser . ' * ' . $software, $logging['robotsforbidden'] );
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
}

function wimbblock_unknown_agent( $table_name, $agent, $software, $blocked, $id, $robots ) {
	if ( $software === '' ) {
		if ( $robots === false ) {
			wimbblock_counter( $table_name, 'block', $id );
			$logging = wimbblock_get_option( 'wimbblock_log' );
			wimbblock_error_log( 'Blocked - unknown software: ' . $agent, $logging['oldagents'] );
			status_header( 404 );
			echo 'Blocked - unknown software: ' . esc_html( $agent );
			exit();
		} else {
			if ( $blocked === '0' ) {
				wimbblock_counter( $table_name, 'block', $id );
			}
			wimbblock_counter( $table_name, 'robots', $id );
			$logging = wimbblock_get_option( 'wimbblock_log' );
			wimbblock_error_log( 'robots.txt unknown software forbidden: ' . $agent, $logging['robotsforbidden'] );
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
			wimbblock_error_log( 'Blocked - unknown webbrowser: ' . $agent . ' * ' . $software, $logging['oldagents'] );
			status_header( 404 );
			echo 'Blocked - unknown webbrowser';
			exit();
		} else {
			if ( $blocked === '0' ) {
				wimbblock_counter( $table_name, 'block', $id );
			}
			wimbblock_counter( $table_name, 'robots', $id );
			$logging = wimbblock_get_option( 'wimbblock_log' );
			wimbblock_error_log( 'robots.txt unknown webbrowser forbidden: ' . $agent . ' * ' . $software, $logging['robotsforbidden'] );
			header( 'Content-Type: text/plain; charset=UTF-8' );
			echo "User-agent: *\r\n" .
			'Disallow: /' . "\r\n";
			exit;
		}
	}
}

function wimbblock_old_system( $table_name, $system, $blocked, $id, $robots ) {
	if ( $system !== '' ) {
		$old_systems = array(
			'Vista',
			'Windows XP',
			'Windows 9',
			'Windows CE',
			'Windows NT',
			'Windows 7',
			'Windows 8',
			'Windows 2000',
			// https://en.wikipedia.org/wiki/MacOS_version_history
			'Sierra',
			'Mojave',
			'Big Sur',
			// https://de.wikipedia.org/wiki/Liste_von_Android-Versionen
			'Petit Four',
			'Cupcake',
			'Donut',
			'Eclair',
			'Froyo',
			'Gingerbread',
			'Honeycomb',
			'Ice Cream Sandwich',
			'Jelly Bean',
			'KitKat',
			'Lollipop',
		);
		foreach ( $old_systems as $old_system ) {
			if ( strpos( $system, $old_system ) !== false ) {
				if ( $robots === false ) {
					wimbblock_counter( $table_name, 'block', $id );
					$logging = wimbblock_get_option( 'wimbblock_log' );
					wimbblock_error_log( 'Blocked - old system: ' . $system, $logging['oldagents'] );
					status_header( 404 );
					echo 'Please use a modern operating system to access this website';
					exit();
				} else {
					if ( $blocked === '0' ) {
						wimbblock_counter( $table_name, 'block', $id );
					}
					wimbblock_counter( $table_name, 'robots', $id );
					$logging = wimbblock_get_option( 'wimbblock_log' );
					wimbblock_error_log( 'robots.txt old system forbidden: ' . $agent, $logging['robotsforbidden'] );
					header( 'Content-Type: text/plain; charset=UTF-8' );
					echo "User-agent: *\r\n" .
					'Disallow: /' . "\r\n";
					exit;
				}
			}
		}
	}
}
