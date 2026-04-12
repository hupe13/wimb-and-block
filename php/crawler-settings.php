<?php
/**
 *  Settings for wimb-and-block Blocking browsers
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

// Crawlers
function wimbblock_crawlers_init() {
	add_settings_section( 'wimbblock_crawlers', '', '__return_empty_string', 'wimbblock_crawlers' );
	add_settings_field( 'wimbblock_crawlers', '', '__return_empty_string', 'wimbblock_crawlers', 'wimbblock_crawlers' );
	if ( get_option( 'wimbblock_crawlers' ) === false ) {
		add_option( 'wimbblock_crawlers', array() );
	}
	register_setting(
		'wimbblock_crawlers',
		'wimbblock_crawlers',
		array(
			'type'    => 'array',
			'default' => array(),
		)
	);
}
add_action( 'admin_init', 'wimbblock_crawlers_init' );

function wimbblock_crawlers_params() {
	$params = array(

		// https://developers.google.com/search/docs/crawling-indexing/overview-google-crawlers?hl=de
		// Common crawlers:
		// Google-CloudVertexBot
		// Google-Extended
		// Google-InspectionTool
		// Googlebot(-Images, Video, News)
		// GoogleOther(-Images, Video)
		// Storebot-Google
		// https://developers.google.com/crawling/docs/crawlers-fetchers/verify-google-requests?hl=de
		// Common crawlers:
		// crawl-***-***-***-***.googlebot.com oder geo-crawl-***-***-***-***.geo.googlebot.com

		'Googlebot'   => array(
			'allowed'     => '0',
			'agents'      => array(
				'Googlebot', // (-Images, Video, News)
				'GoogleOther', // (-Images, Video)
				'Google-CloudVertexBot',
				'Google-Extended',
				'Google-InspectionTool',
				'Storebot-Google',
			),
			'json'        => 'https://developers.google.com/static/crawling/ipranges/common-crawlers.json',
			'json-notice' => __( 'The file also contains IPv6 addresses. However, these are verified via DNS.', 'wimb-and-block' ),
			'names'       => array(
				'/crawl-.*.googlebot.com/',
				'/geo-crawl-.*.geo.googlebot.com/',
			),
		),

		// https://yandex.com/support/webmaster/robot-workings/check-yandex-robots.html
		// yandex.ru, yandex.net or yandex.com.
		'Yandex'      => array(
			'allowed'     => '0',
			'agents'      => array(
				'http://yandex.com/bots',
			),
			'json'        => '',
			'json-notice' => '',
			'names'       => array(
				'/yandex.ru/',
				'/yandex.net/',
				'/yandex.com/',
			),
		),

		// https://help.baidu.com/question?prod_id=99&class=476&id=3001
		//  *.baidu.com or *.baidu.jp.
		'Baiduspider' => array(
			'allowed'     => '0',
			'agents'      => array(
				'Baiduspider',
			),
			'json'        => '',
			'json-notice' => '',
			'names'       => array(
				'/baidu.com/',
				'/baidu.jp/',
			),
		),

		// https://o-seznam.cz/napoveda/vyhledavani/en/seznambot-crawler/
		// fulltextrobot-77-75-77-xxx.seznam.cz
		'SeznamBot'   => array(
			'allowed'     => '0',
			'agents'      => array(
				'SeznamBot',
			),
			'json'        => '',
			'json-notice' => '',
			'names'       => array(
				'/fulltextrobot-.*.seznam.cz/',
			),
		),

		// https://www.bing.com/webmasters/help/which-crawlers-does-bing-use-8c184ec0
		// end with search.msn.com
		'Bingbot'     => array(
			'allowed'     => '0',
			'agents'      => array(
				'Bingbot',
			),
			'json'        => 'https://www.bing.com/toolbox/bingbot.json',
			'json-notice' => '',
			'names'       => array(
				'/search.msn.com/',
			),
		),

		// http://www.apple.com/go/applebot
		// applebot.apple.com
		'Applebot'    => array(
			'allowed'     => '0',
			'agents'      => array(
				'Applebot',
			),
			'json'        => 'https://search.developer.apple.com/applebot.json',
			'json-notice' => '',
			'names'       => array(
				'/applebot.apple.com/',
			),
		),

		// https://www.mojeek.com/bot.html
		// crawl-5-102-173-71.mojeek.com.
		'MojeekBot'   => array(
			'allowed'     => '0',
			'agents'      => array(
				'MojeekBot',
			),
			'json'        => 'https://www.mojeek.com/mojeekbot.json',
			'json-notice' => '',
			'names'       => array(
				'/crawl-.*.mojeek.com/',
			),
		),

		// https://duckduckgo.com/duckduckgo-help-pages/results/duckduckbot
		'DuckDuckBot' => array(
			'allowed'     => '0',
			'agents'      => array(
				'DuckDuckBot',
			),
			'json'        => 'https://duckduckgo.com/duckduckbot.json',
			'json-notice' => '',
			'names'       => array(),
		),
		// https://help.yahoo.com/kb/search-for-desktop/slurp-crawling-page-sln22600.html
		// Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)
		'Yahoo'       => array(
			'allowed'     => '0',
			'agents'      => array(
				'Slurp',
			),
			'json'        => '',
			'json-notice' => '',
			'names'       => array(
				'/crawl.yahoo.net/',
			),
		),
	);
	return $params;
}

function wimbblock_get_search_engine_strings() {
	$params  = wimbblock_crawlers_params();
	$strings = array();
	// escape: %d (integer), %f (float), %s (string), %i (identifier, e.g. table/field names)
	$to_escape = array( 'd', 'f', 's', 'i' );
	foreach ( $params as $crawler => $value ) {
		foreach ( $value['agents'] as $agent ) {
			foreach ( $to_escape as $escape ) {
				if ( str_starts_with( strtolower( $agent ), $escape ) ) {
					$agent = '%' . $agent;
				}
			}
			$strings[] = $agent;
		}
	}
	return $strings;
}

function wimbblock_set_transients_crawlers_in_table( $table_name_crawler = '' ) {
	global $wimb_datatable;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}
	if ( $table_name_crawler == '' ) {
		$wpdb_options       = wimbblock_get_options_db();
		$table_name_crawler = $wpdb_options['table_name'] . '_crawler';
	}
	$results  = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			'SELECT DISTINCT(crawler) FROM %i WHERE 1;',
			$table_name_crawler
		),
		ARRAY_A
	);
	$crawlers = array();
	$in_table = array_keys( wimbblock_crawlers_params() );
	foreach ( $results as $result ) {
		if ( in_array( $result['crawler'], $in_table, true ) ) {
			$crawlers[] = $result['crawler'];
		}
	}
	set_transient( 'wimbblock_crawlers', $crawlers, HOUR_IN_SECONDS );
	// wimbblock_error_log( 'Set transient wimbblock_crawlers' );
	return $crawlers;
}
