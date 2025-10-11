<?php
/**
 * Functions searchengines
 *
 * @package wimb-and-block
 */

//
function wimbblock_faked_crawler( $agent, $software, $ip ) {
	global $is_crawler;

	// https://developers.google.com/search/docs/crawling-indexing/overview-google-crawlers?hl=de
	if ( $software !== '' ) {
		if ( str_contains( strtolower( $software ), 'googlebot' ) || str_contains( strtolower( $agent ), 'googleother' ) ) {
			$hostname = gethostbyaddr( $ip );
			if ( ! ( str_contains( $hostname, 'googlebot.com' ) || str_contains( $hostname, 'google.com' ) ) ) {
				wimbblock_error_log( 'Faked Googlebot: ' . $ip . ' - ' . $hostname );
				status_header( 404, 'You are not from Google' );
				exit();
			}
			$is_crawler = 'Google';
		}

		//BingBot
		if ( str_contains( $software, 'BingBot' ) ) {
			$hostname = gethostbyaddr( $ip );
			if ( ! str_contains( $hostname, 'search.msn.com' ) ) {
				wimbblock_error_log( 'Faked BingBot: ' . $ip . ' - ' . $hostname );
				status_header( 404, 'You are not from Bing' );
				exit();
			}
			$is_crawler = 'BingBot';
		}
	}

	//https://yandex.com/support/webmaster/robot-workings/check-yandex-robots.html
	//yandex.ru, yandex.net or yandex.com.
	if ( str_contains( $agent, 'http://yandex.com/bots' ) ) {
		$hostname = gethostbyaddr( $ip );
		if ( ! ( str_contains( $hostname, 'yandex.ru' ) || str_contains( $hostname, 'yandex.net' ) || str_contains( $hostname, 'yandex.com' ) ) ) {
			wimbblock_error_log( 'Faked Yandex: ' . $hostname );
			status_header( 404, 'You are not from Yandex' );
			exit();
		}
		$is_crawler = 'Yandex';
	}

	//Applebot
	if ( str_contains( $agent, 'Applebot' ) ) {
		$hostname = gethostbyaddr( $ip );
		if ( ! str_contains( $hostname, 'applebot.apple.com' ) ) {
			wimbblock_error_log( 'Faked Applebot: ' . $hostname );
			status_header( 404, 'You are not from Apple' );
			exit();
		}
		$is_crawler = 'Applebot';
	}

	//https://www.mojeek.com/
	if ( str_contains( $agent, 'MojeekBot' ) ) {
		$hostname = gethostbyaddr( $ip );
		if ( ! str_contains( $hostname, 'mojeek.com' ) ) {
			wimbblock_error_log( 'Faked MojeekBot: ' . $hostname );
			status_header( 404, 'You are not from Mojeek' );
			exit();
		}
		$is_crawler = 'MojeekBot';
	}

	// https://help.baidu.com/question?prod_id=99&class=476&id=3001
	// The hostname of Baiduspider is *.baidu.com or *.baidu.jp. Others are fake hostnames.
	if ( str_contains( $agent, 'Baiduspider' ) ) {
		$hostname = gethostbyaddr( $ip );
		if ( ! ( str_contains( $hostname, 'baidu.com' ) || str_contains( $hostname, 'baidu.jp' ) ) ) {
			wimbblock_error_log( 'Faked Baiduspider: ' . $hostname );
			status_header( 404, 'You are not from baidu' );
			exit();
		}
		$is_crawler = 'Baiduspider';
	}

	//seznam 240126
	//https://napoveda.seznam.cz/en/seznambot-crawler/
	if ( str_contains( $agent, 'SeznamBot' ) ) {
		$hostname = gethostbyaddr( $ip );
		if ( ! ( str_contains( $hostname, 'fulltextrobot' ) && str_contains( $hostname, 'seznam.cz' ) ) ) {
			wimbblock_error_log( 'Faked SeznamBot: ' . $hostname );
			status_header( 404, 'You are not from seznam' );
			exit();
		}
		$is_crawler = 'SeznamBot';
	}
}
