<?php
/**
 * Functions searchengines
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_check_crawler_address_in_table( $table_name_crawler, $crawler, $agent, $ip, $robots ) {
	global $wimb_datatable;

	$valid = $wimb_datatable->get_row(
		$wimb_datatable->prepare(
			'SELECT crawler FROM %i WHERE %s = crawler AND INET_ATON(%s) >= int_begin AND INET_ATON(%s) <= int_end;',
			$table_name_crawler,
			$crawler,
			$ip,
			$ip
		),
		ARRAY_A
	);
	if ( is_null( $valid ) ) {
		$exist = $wimb_datatable->get_row(
			$wimb_datatable->prepare(
				'SELECT crawler FROM %i WHERE %s = crawler;',
				$table_name_crawler,
				$crawler
			),
			ARRAY_A
		);
		if ( ! is_null( $exist ) ) {
			if ( $robots === false ) {
				wimbblock_error_log( 'Faked ' . $crawler . ': ' . $agent );
				status_header( 404 );
				echo 'You are not a ' . esc_html( $crawler );
				exit();
			} else {
				wimbblock_error_log( 'robots.txt faked ' . $crawler . ' forbidden: ' . $agent );
				header( 'Content-Type: text/plain; charset=UTF-8' );
				echo "User-agent: *\r\n" .
				'Disallow: /' . "\r\n";
				exit;
			}
		} else {
			return false;
		}
	} else {
		// wimbblock_error_log( 'wimbblock_check_crawler_address_in_table Crawler okay ' . $crawler . ' * ' . $agent );
		return true;
	}
}

function wimbblock_check_crawler_ip_hostname( $agent, $ip, $robots ) {
	global $wimbblock_is_crawler;
	$params = wimbblock_crawlers_params();
	foreach ( $params as $crawler => $value ) {
		foreach ( $value['agents'] as $brand ) {
			if ( stripos( $agent, $brand ) !== false ) {
				$hostname = gethostbyaddr( $ip );
				if ( $hostname !== false ) {
					foreach ( $value['names'] as $name ) {
						if ( preg_match( $name, strtolower( $hostname ) ) ) {
							$wimbblock_is_crawler = $crawler;
							// wimbblock_error_log( 'Crawler is valid ' . $crawler );
							return $crawler;
						}
					}
					if ( $robots === false ) {
						wimbblock_error_log( 'Faked ' . $crawler . ': ' . $agent . ' * ' . $hostname );
						status_header( 404 );
						echo 'You are not ' . esc_html( $crawler );
						exit();
					} else {
						wimbblock_error_log( 'robots.txt faked ' . $crawler . ' forbidden: ' . $agent . ' * ' . $hostname );
						header( 'Content-Type: text/plain; charset=UTF-8' );
						echo "User-agent: *\r\n" .
						'Disallow: /' . "\r\n";
						exit;
					}
				} else {
					wimbblock_error_log( 'Could not check if it is a Crawler: ' . $agent );
				}
			}
		}
	}
	return '';
}

function wimbblock_is_crawler_in_table( $table_name_crawler, $agent, $ip, $robots ) {
	global $wimbblock_is_crawler;

	$params   = wimbblock_crawlers_params();
	$crawlers = get_transient( 'wimbblock_crawlers' );
	if ( $crawlers === false ) {
		$crawlers = wimbblock_set_transients_crawlers_in_table( $table_name_crawler );
	}
	foreach ( $crawlers as $crawler ) {
		foreach ( $params[ $crawler ]['agents'] as $brand ) {
			if ( stripos( $agent, $brand ) !== false ) {
				$test = wimbblock_check_crawler_address_in_table( $table_name_crawler, $crawler, $agent, $ip, $robots );
				if ( $test ) {
					// wimbblock_error_log( 'Crawler is okay ' . $crawler );
					$wimbblock_is_crawler = $crawler;
				}
				break;
			}
		}
	}
}

function wimbblock_faked_crawler( $table_name, $agent, $ip, $robots ) {
	global $wimbblock_is_crawler;
	$table_name_crawler = $table_name . '_crawler';
	if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
		wimbblock_is_crawler_in_table( $table_name_crawler, $agent, $ip, $robots );
	}
	if ( $wimbblock_is_crawler === false ) {
		wimbblock_check_crawler_ip_hostname( $agent, $ip, $robots );
	}
}
