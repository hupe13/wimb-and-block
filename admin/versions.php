<?php
/**
 *  Settings for wimb-and-block Blocking browsers
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

// Database
function wimbblock_browsers_init() {
	add_settings_section( 'wimbblock_browsers', '', '', 'wimbblock_browsers' );
	add_settings_field( 'wimbblock_browsers', __( 'Block browser versions smaller than', 'wimb-and-block' ), 'wimbblock_browsers_form', 'wimbblock_browsers', 'wimbblock_browsers' );
	if ( get_option( 'wimbblock_browsers' ) === false ) {
		add_option( 'wimbblock_browsers', array() );
	}
	register_setting( 'wimbblock_browsers', 'wimbblock_browsers', 'wimbblock_browsers_validate' );
}
add_action( 'admin_init', 'wimbblock_browsers_init' );

// Baue Abfrage der Params
function wimbblock_browsers_form() {
	$all = wimbblock_get_all_browsers();
	$i   = 0;
	foreach ( $all as $browser => $value ) {
		echo '<p><input type="text" size="15" name="wimbblock_browsers[browser' . esc_html( $i ) . ']" value="' . esc_html( $browser ) . '" /> ' . "\n";
		echo '<input type="number" size="8" name="wimbblock_browsers[count' . esc_html( $i ) . ']" value="' . esc_html( $value ) . '" /></p>' . "\n";
		++$i;
	}
	echo '<p><input type="text" size="15" name="wimbblock_browsers[browser' . esc_html( $i ) . ']" /> ' . "\n";
	echo '<input type="number" size="8" name="wimbblock_browsers[count' . esc_html( $i ) . ']" /></p>' . "\n";
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wimbblock_browsers_validate( $params ) {
	if ( ! empty( $_POST ) && check_admin_referer( 'wimbblock', 'wimbblock_nonce' ) ) {
		if ( isset( $_POST['submit'] ) ) {
			$newparams = array();
			$last      = count( $params ) / 2;
			for ( $i = 0; $i < $last; $i++ ) {
				if ( $params[ 'browser' . $i ] !== '' && (int) $params[ 'count' . $i ] > 0 ) {
					$newparams[ $params[ 'browser' . $i ] ] = $params[ 'count' . $i ];
				}
			}
			$defaults  = wimbblock_get_default_browsers();
			$newparams = array_diff_assoc( $newparams, $defaults );
			return $newparams;
		}
		if ( isset( $_POST['delete'] ) ) {
			delete_option( 'wimbblock_browsers' );
		}
	}
	return false;
}

function wimbblock_browsers_help() {
	$text    = '';
	$text   .= __(
		'The user agent string of every browser accessing your website the first time is send to WhatIsMyBrowser and some data will be stored in the table:',
		'wimb-and-block'
	);
	$text   .= '<p><table class="width450" border=1>
 	 <tr><td class="width280 center-text"><code>browser</code></td>
	 <td class="width85 center-text"><code>simple software string</code></td>
	 <td class="width85 center-text"><code>operating system</code></td></tr></table></p>';
	$text   .= '<p><img src="' . plugin_dir_url( __FILE__ ) . '../pict/good.jpg" alt="example entries" width="450" ></p>';
	$text   .= __( 'Browsers will be blocked, if the browser and/or the system is an old one:', 'wimb-and-block' );
	$text   .= '<p><img src="' . plugin_dir_url( __FILE__ ) . '../pict/old.jpg" alt="example entries" width="450" ></p>';
	$options = wimbblock_get_options_db();
	if ( $options['location'] === 'remote' ) {
		$text .= '<p><div class="notice notice-info">' . __( 'You must configure these settings on each of your websites that use this database!', 'wimb-and-block' ) . '</div></p>';
	}
	$text .= wp_sprintf(
		/* Translators: %s are browsers*/
		__( 'The versions of %1$s and %2$s also affect browsers with their code base, for example %3$s, %4$s, %5$s, %6$s, %7$s.', 'wimb-and-block' ),
		'Chrome',
		'Firefox',
		'Chromium',
		'Opera',
		'Brave',
		'Iceweasel',
		'Fennec'
	);
	return $text;
}
