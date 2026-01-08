<?php
/**
 *  Settings for wimb-and-block Blocking systems
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

// Database
function wimbblock_systems_init() {
	add_settings_section( 'wimbblock_systems', '', '', 'wimbblock_systems' );
	add_settings_field( 'wimbblock_systems', __( 'Block operating system versions smaller than', 'wimb-and-block' ), 'wimbblock_systems_form', 'wimbblock_systems', 'wimbblock_systems' );
	if ( get_option( 'wimbblock_systems' ) === false ) {
		add_option( 'wimbblock_systems', array() );
	}
	register_setting( 'wimbblock_systems', 'wimbblock_systems', 'wimbblock_systems_validate' );
}
add_action( 'admin_init', 'wimbblock_systems_init' );

// Baue Abfrage der Params
function wimbblock_systems_form() {
	$all = wimbblock_get_all_systems();
	$i   = 0;
	foreach ( $all as $system => $value ) {
		echo '<p><input type="text" size="15" name="wimbblock_systems[system' . esc_html( $i ) . ']" value="' . esc_html( $system ) . '" /> ' . "\n";
		echo '<input type="number" size="8" name="wimbblock_systems[count' . esc_html( $i ) . ']" value="' . esc_html( $value ) . '" /></p>' . "\n";
		++$i;
	}
	// echo '<p><input type="text" size="15" name="wimbblock_systems[system' . esc_html( $i ) . ']" /> ' . "\n";
	// echo '<input type="number" size="8" name="wimbblock_systems[count' . esc_html( $i ) . ']" /></p>' . "\n";
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wimbblock_systems_validate( $params ) {
	if ( ! empty( $_POST ) && check_admin_referer( 'wimbblock', 'wimbblock_nonce' ) ) {
		if ( isset( $_POST['submit'] ) ) {
			$newparams = array();
			$last      = count( $params ) / 2;
			for ( $i = 0; $i < $last; $i++ ) {
				if ( $params[ 'system' . $i ] !== '' && (int) $params[ 'count' . $i ] > 0 ) {
					$newparams[ $params[ 'system' . $i ] ] = $params[ 'count' . $i ];
				}
			}
			$defaults  = wimbblock_get_default_systems();
			$newparams = array_diff_assoc( $newparams, $defaults );
			return $newparams;
		}
		if ( isset( $_POST['delete'] ) ) {
			delete_option( 'wimbblock_systems' );
		}
	}
	return false;
}
