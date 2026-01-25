<?php
/**
 * Functions searchengines
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_google_crawlers( $agent ) {
	// https://developers.google.com/search/docs/crawling-indexing/overview-google-crawlers?hl=de
	// Common crawlers:
	// Google-CloudVertexBot
	// Google-Extended
	// Google-InspectionTool
	// Googlebot(-Images, Video, News)
	// GoogleOther(-Images, Video)
	// Storebot-Google
	$googlecrawlers = array(
		'Googlebot', // (-Images, Video, News)
		'GoogleOther', // (-Images, Video)
		'Google-CloudVertexBot',
		'Google-Extended',
		'Google-InspectionTool',
		'Storebot-Google',
	);
	foreach ( $googlecrawlers as $googlecrawler ) {
		if ( stripos( $agent, $googlecrawler ) !== false ) {
			return true;
		}
	}
	return false;
}

function wimbblock_faked_crawler( $agent, $ip, $robots ) {
	global $wimbblock_is_crawler;
	// https://developers.google.com/crawling/docs/crawlers-fetchers/verify-google-requests?hl=de
	// Common crawlers:
	// crawl-***-***-***-***.googlebot.com oder geo-crawl-***-***-***-***.geo.googlebot.com
	if ( wimbblock_google_crawlers( $agent ) ) {
		$hostname = gethostbyaddr( $ip );
		// wimbblock_error_log( 'Test Googlebot: ' . $agent . ' * ' . $hostname . ' * ' . $ip );
		if ( $hostname !== $ip && $hostname !== false ) {
			if ( !
			( preg_match( '/crawl-.*.googlebot.com/', strtolower( $hostname ) )
			|| preg_match( '/geo-crawl-.*.geo.googlebot.com/', strtolower( $hostname ) ) ) ) {
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
		} else {
			wimbblock_error_log( 'Could not check Googlebot: ' . $agent );
		}
		$wimbblock_is_crawler = 'Google';
	}

	// https://www.bing.com/webmasters/help/which-crawlers-does-bing-use-8c184ec0
	// end with search.msn.com
	if ( stripos( $agent, 'bingbot' ) !== false ) {
		$hostname = gethostbyaddr( $ip );
		// wimbblock_error_log( 'Test BingBot: ' . $agent . ' * ' . $hostname . ' * ' . $ip );
		if ( $hostname !== $ip && $hostname !== false ) {
			if ( stripos( $hostname, 'search.msn.com' ) === false ) {
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
		} else {
			wimbblock_error_log( 'Could not check BingBot: ' . $agent );
		}
		$wimbblock_is_crawler = 'BingBot';
	}

	// https://yandex.com/support/webmaster/robot-workings/check-yandex-robots.html
	// yandex.ru, yandex.net or yandex.com.
	if ( stripos( $agent, 'http://yandex.com/bots' ) !== false ) {
		$hostname = gethostbyaddr( $ip );
		// wimbblock_error_log( 'Test YandexBot: ' . $agent . ' * ' . $hostname . ' * ' . $ip );
		if ( $hostname !== $ip && $hostname !== false ) {
			if ( stripos( $hostname, 'yandex.ru' ) === false && stripos( $hostname, 'yandex.net' ) === false && stripos( $hostname, 'yandex.com' ) === false ) {
				if ( $robots === false ) {
					wimbblock_error_log( 'Faked YandexBot: ' . $agent . ' * ' . $hostname );
					status_header( 404 );
					echo 'You are not from Yandex';
					exit();
				} else {
					wimbblock_error_log( 'robots.txt faked YandexBot forbidden: ' . $agent . ' * ' . $hostname );
					header( 'Content-Type: text/plain; charset=UTF-8' );
					echo "User-agent: *\r\n" .
					'Disallow: /' . "\r\n";
					exit;
				}
			}
		} else {
			wimbblock_error_log( 'Could not check YandexBot: ' . $agent );
		}
		$wimbblock_is_crawler = 'Yandex';
	}

	// http://www.apple.com/go/applebot
	// applebot.apple.com
	if ( stripos( $agent, 'Applebot' ) !== false ) {
		$hostname = gethostbyaddr( $ip );
		// wimbblock_error_log( 'Test Applebot: ' . $agent . ' * ' . $hostname . ' * ' . $ip );
		if ( $hostname !== $ip && $hostname !== false ) {
			if ( stripos( $hostname, 'applebot.apple.com' ) === false ) {
				if ( $robots === false ) {
					wimbblock_error_log( 'Faked Applebot: ' . $agent . ' * ' . $hostname );
					status_header( 404 );
					echo 'You are not from Apple';
					exit();
				} else {
					wimbblock_error_log( 'robots.txt faked Applebot forbidden: ' . $agent . ' * ' . $hostname );
					header( 'Content-Type: text/plain; charset=UTF-8' );
					echo "User-agent: *\r\n" .
					'Disallow: /' . "\r\n";
					exit;
				}
			}
		} else {
			wimbblock_error_log( 'Could not check Applebot: ' . $agent );
		}
		$wimbblock_is_crawler = 'Applebot';
	}

	// https://www.mojeek.com/bot.html
	// crawl-5-102-173-71.mojeek.com.
	if ( stripos( $agent, 'MojeekBot' ) !== false ) {
		$hostname = gethostbyaddr( $ip );
		// wimbblock_error_log( 'Test MojeekBot: ' . $agent . ' * ' . $hostname . ' * ' . $ip );
		if ( $hostname !== $ip && $hostname !== false ) {
			if ( ! preg_match( '/crawl-.*.mojeek.com/', strtolower( $hostname ) ) ) {
				if ( $robots === false ) {
					wimbblock_error_log( 'Faked MojeekBot: ' . $agent . ' * ' . $hostname );
					status_header( 404 );
					echo 'You are not from Mojeek';
					exit();
				} else {
					wimbblock_error_log( 'robots.txt faked MojeekBot forbidden: ' . $agent . ' * ' . $hostname );
					header( 'Content-Type: text/plain; charset=UTF-8' );
					echo "User-agent: *\r\n" .
					'Disallow: /' . "\r\n";
					exit;
				}
			}
		} else {
			wimbblock_error_log( 'Could not check MojeekBot: ' . $agent );
		}
		$wimbblock_is_crawler = 'MojeekBot';
	}

	// https://help.baidu.com/question?prod_id=99&class=476&id=3001
	// The hostname of Baiduspider is *.baidu.com or *.baidu.jp. Others are fake hostnames.
	if ( stripos( $agent, 'Baiduspider' ) !== false ) {
		$hostname = gethostbyaddr( $ip );
		// wimbblock_error_log( 'Test Baiduspider: ' . $agent . ' * ' . $hostname . ' * ' . $ip );
		if ( $hostname !== $ip && $hostname !== false ) {
			if ( stripos( $hostname, 'baidu.com' ) === false && stripos( $hostname, 'baidu.jp' ) === false ) {
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
		} else {
			wimbblock_error_log( 'Could not check Baiduspider: ' . $agent );
		}
		$wimbblock_is_crawler = 'Baiduspider';
	}

	// https://o-seznam.cz/napoveda/vyhledavani/en/seznambot-crawler/
	// fulltextrobot-77-75-77-xxx.seznam.cz
	if ( stripos( $agent, 'SeznamBot' ) !== false ) {
		$hostname = gethostbyaddr( $ip );
		// wimbblock_error_log( 'Test SeznamBot: ' . $agent . ' * ' . $hostname . ' * ' . $ip );
		if ( $hostname !== $ip && $hostname !== false ) {
			if ( ! preg_match( '/fulltextrobot-.*.seznam.cz/', strtolower( $hostname ) ) ) {
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
		} else {
			wimbblock_error_log( 'Could not check SeznamBot: ' . $agent );
		}
		$wimbblock_is_crawler = 'SeznamBot';
	}
}
