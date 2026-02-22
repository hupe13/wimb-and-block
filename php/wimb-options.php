<?php
/**
 *  Options
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_get_option( $option ) {
	$wimbblock_option = get_option( $option );
	if ( is_multisite() && ! is_main_site() ) {
		if ( is_plugin_active_for_network( WIMBBLOCK_BASENAME ) ) {
			$wimbblock_option = get_blog_option( get_main_site_id(), $option );
		}
	}
	return $wimbblock_option;
}

// tab=settings
function wimbblock_get_options_db() {
	global $wpdb;
	$defaults          = array(
		'error'       => '3',
		'wimb_api'    => '',
		'table_name'  => $wpdb->prefix . 'wimb_table',
		'location'    => 'local',
		'db_user'     => '',
		'db_password' => '',
		'db_name'     => '',
		'db_host'     => '',
		'rotate'      => 'no',
		'logfile'     => '',
	);
	$wimbblock_options = wimbblock_get_option( 'wimbblock_settings' );
	if ( $wimbblock_options === false || count( $wimbblock_options ) === 0 ) {
		$wimbblock_options = $defaults;
	}
	return $wimbblock_options;
}

// tab=blocking
function wimbblock_get_default_browsers() {
	$defaults = array(
		// https://developer.chrome.com/release-notes/
		'Chrome'                   => 139,
		// https://en.wikipedia.org/wiki/Microsoft_Edge#New_Edge_release_history
		// https://www.cvedetails.com/version-list/26/32367/1/Microsoft-Edge.html?order=0
		// Edge = Chrome
		'Edge'                     => 139,
		// https://de.wikipedia.org/wiki/Versionsgeschichte_von_Mozilla_Firefox
		// ESR  115.32.0  140.7.0
		'Firefox'                  => 140,
		// https://developer.apple.com/documentation/safari-release-notes
		// https://theapplewiki.com/wiki/Safari
		'Safari'                   => 18,
		// https://caniuse.com/usage-table
		// SamsungBrowser/29.0 Chrome/136
		// SamsungBrowser/28.0 Chrome/130
		// https://en.wikipedia.org/wiki/Samsung_Internet
		'Samsung Internet Browser' => 28,
	);
	return $defaults;
}

function wimbblock_get_all_browsers() {
	$defaults = wimbblock_get_default_browsers();
	$customs  = wimbblock_get_option( 'wimbblock_browsers' );

	$out = array();
	if ( $customs !== false && count( $customs ) > 0 ) {
		foreach ( $defaults as $name => $default ) {
			if ( array_key_exists( $name, $customs ) ) {
				$out[ $name ] = $customs[ $name ];
			} else {
				$out[ $name ] = $default;
			}
		}
		foreach ( $customs as $name => $option ) {
			if ( ! array_key_exists( $name, $out ) ) {
				$out[ $name ] = $customs[ $name ];
			}
		}
	} else {
		$out = $defaults;
	}
	return $out;
}

// systems
function wimbblock_get_default_systems() {
	$defaults = array(
		// https://support.google.com/chrome/thread/352616098/sunsetting-chrome-support-for-android-8-0-oreo-and-android-9-0-pie?hl=en
		// https://blog.mozilla.org/futurereleases/2025/09/15/raising-the-minimum-android-version-for-firefox/  - oreo
		'Android' => 10,
		// https://de.wikipedia.org/wiki/Versionsgeschichte_von_iOS#iPhone,_iPad_&_iPod_touch
		'iOS'     => 15,
	);
	return $defaults;
}

function wimbblock_get_all_systems() {
	$defaults = wimbblock_get_default_systems();
	$customs  = wimbblock_get_option( 'wimbblock_systems' );

	$out = array();
	if ( $customs !== false && count( $customs ) > 0 ) {
		foreach ( $defaults as $name => $default ) {
			if ( array_key_exists( $name, $customs ) ) {
				$out[ $name ] = $customs[ $name ];
			} else {
				$out[ $name ] = $default;
			}
		}
		foreach ( $customs as $name => $option ) {
			if ( ! array_key_exists( $name, $out ) ) {
				$out[ $name ] = $customs[ $name ];
			}
		}
	} else {
		$out = $defaults;
	}
	return $out;
}

function wimbblock_get_exclude() {
	$wimbblock_exclude = wimbblock_get_option( 'wimbblock_exclude' );
	return $wimbblock_exclude;
}

function wimbblock_get_always() {
	$wimbblock_always = wimbblock_get_option( 'wimbblock_always' );
	return $wimbblock_always;
}

function wimbblock_logging_levels() {
	$params = array(
		array(
			'param'   => 'blockagain',
			'desc'    => 'Blocked again',
			'help'    => __( 'Once it has been detected that the browser is being blocked, it will be blocked later without any explanation.', 'wimb-and-block' ),
			'default' => true,
		),
		array(
			'param'   => 'excluded',
			'desc'    => 'Excluded',
			'help'    => __( 'Log when the browser is excluded from checking.', 'wimb-and-block' ),
			'default' => true,
		),
		array(
			'param'   => 'robotsokay',
			'desc'    => 'robots.txt okay',
			'help'    => __( 'Log when the browser gets a robots.txt to allow crawling.', 'wimb-and-block' ),
			'default' => true,
		),
		array(
			'param'   => 'robotsforbidden',
			'desc'    => 'robots.txt forbidden',
			'help'    => __( 'Log when the browser gets a robots.txt to disable crawling.', 'wimb-and-block' ),
			'default' => true,
		),
		array(
			'param'   => 'oldagents',
			'desc'    => 'Old and unknown agents',
			'help'    => __( 'Log when the browser accesses your website for the first time and an old version is detected or it is unknown or suspicious.', 'wimb-and-block' ),
			'default' => true,
		),
	);
	return $params;
}

function wimbblock_logging_levels_settings() {
	$params   = wimbblock_logging_levels();
	$defaults = array();
	foreach ( $params as $param ) {
		$defaults[ $param['param'] ] = $param['default'];
	}
	$settings = wimbblock_get_option( 'wimbblock_log' );
	$options  = array();
	foreach ( $defaults as $key => $default ) {
		if ( isset( $settings[ $key ] ) ) {
			$options[ $key ] = $settings[ $key ];
		} else {
			$options[ $key ] = $default;
		}
	}
	return $options;
}

function wimbblock_anon_log() {
	$logip = array(
		array(
			'log'  => 'nolog',
			'help' => __( 'The IP address is not logged.', 'wimb-and-block' ),
		),
		array(
			'log'  => 'two',
			'help' => __( 'The last two digits are removed, IP 11.22.33.44 becomes 11.22.0.0', 'wimb-and-block' ),
		),
		array(
			'log'  => 'all',
			'help' => __( 'The full IP is logged. (default)', 'wimb-and-block' ),
		),
	);
	return $logip;
}

function wimbblock_anon_settings() {
	// $logips   = wimbblock_anon_log();
	$setting = wimbblock_get_option( 'wimbblock_anon' );
	if ( $setting === false ) {
		$setting = 'all';
	}
	return $setting;
}
