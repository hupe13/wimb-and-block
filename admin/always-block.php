<?php
/**
 *  Settings for wimb-and-block blocking browsers with string
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

// Database
function wimbblock_always_init() {
	add_settings_section( 'wimbblock_always', '', '', 'wimbblock_always' );
	add_settings_field( 'wimbblock_always', __( 'Always block browsers with this strings', 'wimb-and-block' ), 'wimbblock_always_form', 'wimbblock_always', 'wimbblock_always' );
	if ( get_option( 'wimbblock_always' ) === false ) {
		add_option( 'wimbblock_always', array() );
	}
	register_setting( 'wimbblock_always', 'wimbblock_always', 'wimbblock_always_validate' );
}
add_action( 'admin_init', 'wimbblock_always_init' );

// Baue Abfrage der Params
function wimbblock_always_form() {
	$all = wimbblock_get_always( 'wimbblock_always' );
	$i   = 0;
	foreach ( $all as $browser ) {
		echo '<p><input type="text" size="15" name="wimbblock_always[browser' . esc_html( $i ) . ']" value="' . esc_html( $browser ) . '" /> ' . "\n";
		++$i;
	}
	echo '<p><input type="text" size="15" name="wimbblock_always[browser' . esc_html( $i ) . ']" /> ' . "\n";
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wimbblock_always_validate( $params ) {
	// var_dump($params);wp_die();
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
			delete_option( 'wimbblock_always' );
		}
	}
	return false;
}

function wimbblock_always_help() {
	$text    = '<h3>' . __( 'Always block browsers with this strings', 'wimb-and-block' ) . '</h3>';
	$options = wimbblock_get_options_db();
	if ( $options['location'] === 'remote' ) {
		$text .= '<p><div class="notice notice-info">' . __( 'You must configure these settings on each of your websites that use this database!', 'wimb-and-block' ) . '</div></p>';
	}
	$text .= '<p>' . __( 'Sometimes bad bots have a changing version number, so you can configure a string to always block them.', 'wimb-and-block' ) . '</p>';
	echo wp_kses_post( $text );
}
