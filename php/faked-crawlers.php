<?php
/**
 * Functions searchengines
 *
 * @package wimb-and-block
 */

//
function wimbblock_faked_crawler( $agent, $software, $ip, $robots ) {
	global $wimbblock_is_crawler;

	// https://developers.google.com/search/docs/crawling-indexing/overview-google-crawlers?hl=de
	if ( $software !== '' ) {
		if ( str_contains( strtolower( $software ), 'googlebot' ) || str_contains( strtolower( $agent ), 'googleother' ) ) {
			$hostname = gethostbyaddr( $ip );
			if ( ! ( str_contains( $hostname, 'googlebot.com' ) || str_contains( $hostname, 'google.com' ) ) ) {
				if ( $robots === false ) {
					wimbblock_error_log( 'Faked Googlebot: ' . $agent . ' * ' . $hostname );
					status_header( 404 );
					echo 'You are not from Google';
					exit();
				} else {
					wimbblock_error_log( 'robots.txt faked Googlebot forbidden: ' . $agent . ' * ' . $hostname );
					header( 'Content-Type: text/plain; charset=UTF-8' );
					echo "User-agent: *\r\n" .
					'Disallow: /' . "\r\n";
					exit;
				}
			}
			$wimbblock_is_crawler = 'Google';
		}

		//BingBot
		if ( str_contains( $software, 'BingBot' ) ) {
			$hostname = gethostbyaddr( $ip );
			if ( ! str_contains( $hostname, 'search.msn.com' ) ) {
				if ( $robots === false ) {
					wimbblock_error_log( 'Faked BingBot: ' . $agent . ' * ' . $hostname );
					status_header( 404 );
					echo 'You are not from Bing';
					exit();
				} else {
					wimbblock_error_log( 'robots.txt faked BingBot forbidden: ' . $agent . ' * ' . $hostname );
					header( 'Content-Type: text/plain; charset=UTF-8' );
					echo "User-agent: *\r\n" .
					'Disallow: /' . "\r\n";
					exit;
				}
			}
			$wimbblock_is_crawler = 'BingBot';
		}
	}

	//https://yandex.com/support/webmaster/robot-workings/check-yandex-robots.html
	//yandex.ru, yandex.net or yandex.com.
	if ( str_contains( $agent, 'http://yandex.com/bots' ) ) {
		$hostname = gethostbyaddr( $ip );
		if ( ! ( str_contains( $hostname, 'yandex.ru' ) || str_contains( $hostname, 'yandex.net' ) || str_contains( $hostname, 'yandex.com' ) ) ) {
			if ( $robots === false ) {
				wimbblock_error_log( 'Faked Yandex: ' . $agent . ' * ' . $hostname );
				status_header( 404 );
				echo 'You are not from Yandex';
				exit();
			} else {
				wimbblock_error_log( 'robots.txt faked Yandex forbidden: ' . $agent . ' * ' . $hostname );
				header( 'Content-Type: text/plain; charset=UTF-8' );
				echo "User-agent: *\r\n" .
				'Disallow: /' . "\r\n";
				exit;
			}
		}
		$wimbblock_is_crawler = 'Yandex';
	}

	//Applebot
	if ( str_contains( $agent, 'Applebot' ) ) {
		$hostname = gethostbyaddr( $ip );
		if ( ! str_contains( $hostname, 'applebot.apple.com' ) ) {
			if ( $robots === false ) {
				wimbblock_error_log( 'Faked Applebot: ' . $agent . ' * ' . $hostname );
				status_header( 404 );
				echo 'You are not from Apple';
				exit();
			} else {
				wimbblock_error_log( 'robots.txt faked Apple forbidden: ' . $agent . ' * ' . $hostname );
				header( 'Content-Type: text/plain; charset=UTF-8' );
				echo "User-agent: *\r\n" .
				'Disallow: /' . "\r\n";
				exit;
			}
		}
		$wimbblock_is_crawler = 'Applebot';
	}

	//https://www.mojeek.com/
	if ( str_contains( $agent, 'MojeekBot' ) ) {
		$hostname = gethostbyaddr( $ip );
		if ( ! str_contains( $hostname, 'mojeek.com' ) ) {
			if ( $robots === false ) {
				wimbblock_error_log( 'Faked MojeekBot: ' . $agent . ' * ' . $hostname );
				status_header( 404 );
				echo 'You are not from Mojeek';
				exit();
			} else {
				wimbblock_error_log( 'robots.txt faked Mojeek forbidden: ' . $agent . ' * ' . $hostname );
				header( 'Content-Type: text/plain; charset=UTF-8' );
				echo "User-agent: *\r\n" .
				'Disallow: /' . "\r\n";
				exit;
			}
		}
		$wimbblock_is_crawler = 'MojeekBot';
	}

	// https://help.baidu.com/question?prod_id=99&class=476&id=3001
	// The hostname of Baiduspider is *.baidu.com or *.baidu.jp. Others are fake hostnames.
	if ( str_contains( $agent, 'Baiduspider' ) ) {
		$hostname = gethostbyaddr( $ip );
		if ( ! ( str_contains( $hostname, 'baidu.com' ) || str_contains( $hostname, 'baidu.jp' ) ) ) {
			if ( $robots === false ) {
				wimbblock_error_log( 'Faked Baiduspider: ' . $agent . ' * ' . $hostname );
				status_header( 404 );
				echo 'You are not from baidu';
				exit();
			} else {
				wimbblock_error_log( 'robots.txt faked Baiduspider forbidden: ' . $agent . ' * ' . $hostname );
				header( 'Content-Type: text/plain; charset=UTF-8' );
				echo "User-agent: *\r\n" .
				'Disallow: /' . "\r\n";
				exit;
			}
		}
		$wimbblock_is_crawler = 'Baiduspider';
	}

	//seznam 240126
	//https://napoveda.seznam.cz/en/seznambot-crawler/
	if ( str_contains( $agent, 'SeznamBot' ) ) {
		$hostname = gethostbyaddr( $ip );
		if ( ! ( str_contains( $hostname, 'fulltextrobot' ) && str_contains( $hostname, 'seznam.cz' ) ) ) {
			if ( $robots === false ) {
				wimbblock_error_log( 'Faked SeznamBot: ' . $agent . ' * ' . $hostname );
				status_header( 404 );
				echo 'You are not from seznam';
				exit();
			} else {
				wimbblock_error_log( 'robots.txt faked SeznamBot forbidden: ' . $agent . ' * ' . $hostname );
				header( 'Content-Type: text/plain; charset=UTF-8' );
				echo "User-agent: *\r\n" .
				'Disallow: /' . "\r\n";
				exit;
			}
		}
		$wimbblock_is_crawler = 'SeznamBot';
	}
}
