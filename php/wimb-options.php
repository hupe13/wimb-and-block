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
		if ( is_plugin_active_for_network( WIMB_BASENAME ) ) {
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
		// https://en.wikipedia.org/wiki/Google_Chrome#Platforms
		'Chrome'            => 128,
		// https://de.wikipedia.org/wiki/Versionsgeschichte_von_Mozilla_Firefox
		'Firefox'           => 128,
		'Internet Explorer' => 9999,
		'Netscape'          => 9999,
		// https://en.wikipedia.org/wiki/History_of_the_Opera_web_browser
		'Opera'             => 83,
		// https://developer.apple.com/documentation/safari-release-notes
		'Safari'            => 17,
	);
	return $defaults;
}

function wimbblock_get_browsers_custom() {
	$defaults          = wimbblock_get_default_browsers();
	$wimbblock_options = wimbblock_get_option( 'wimbblock_browsers' );
	return $wimbblock_options;
}

function wimbblock_get_all_browsers() {
	$defaults = wimbblock_get_default_browsers();
	$customs  = wimbblock_get_browsers_custom();

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
