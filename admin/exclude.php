<?php
/**
 *  Settings for wimb-and-block Blocking browsers
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

// Database
function wimbblock_exclude_init() {
	add_settings_section( 'wimbblock_exclude', '', '', 'wimbblock_exclude' );
	add_settings_field( 'wimbblock_exclude', __( 'Exclude these browsers from checking', 'wimb-and-block' ), 'wimbblock_exclude_form', 'wimbblock_exclude', 'wimbblock_exclude' );
	if ( get_option( 'wimbblock_exclude' ) === false ) {
		add_option( 'wimbblock_exclude', array() );
	}
	register_setting( 'wimbblock_exclude', 'wimbblock_exclude', 'wimbblock_exclude_validate' );
}
add_action( 'admin_init', 'wimbblock_exclude_init' );

// Baue Abfrage der Params
function wimbblock_exclude_form() {
	$all = wimbblock_get_exclude( 'wimbblock_exclude' );
	$i   = 0;
	foreach ( $all as $browser ) {
		echo '<p><input type="text" size="15" name="wimbblock_exclude[browser' . esc_html( $i ) . ']" value="' . esc_html( $browser ) . '" /> ' . "\n";
		++$i;
	}
	if ( $i === 0 ) {
		$placeholder = ' placeholder="Mastodon" ';
	} else {
		$placeholder = '';
	}
	echo '<p><input type="text" size="15" name="wimbblock_exclude[browser' . esc_html( $i ) . ']" ' . wp_kses_post( $placeholder ) . ' /> ' . "\n";
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wimbblock_exclude_validate( $params ) {
	//var_dump($params);wp_die();
	if ( ! empty( $_POST ) && check_admin_referer( 'wimbblock', 'wimbblock_nonce' ) ) {
		if ( isset( $_POST['submit'] ) ) {
			$newparams = array();
			$last      = count( $params );
			for ( $i = 0; $i < $last; $i++ ) {
				if ( $params[ 'browser' . $i ] !== '' ) {
					$newparams[] = $params[ 'browser' . $i ];
				}
			}
			return $newparams;
		}
		if ( isset( $_POST['delete'] ) ) {
			delete_option( 'wimbblock_exclude' );
		}
	}
	return false;
}
