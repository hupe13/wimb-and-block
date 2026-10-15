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
	add_settings_section( 'wimbblock_always', '', '__return_empty_string', 'wimbblock_always' );
	add_settings_field( 'wimbblock_always', __( 'Always block browsers with this strings', 'wimb-and-block' ), 'wimbblock_always_form', 'wimbblock_always', 'wimbblock_always' );
	if ( get_option( 'wimbblock_always' ) === false ) {
		add_option( 'wimbblock_always', array() );
	}
	register_setting(
		'wimbblock_always',
		'wimbblock_always',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'wimbblock_always_validate',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'wimbblock_always_init' );

// Baue Abfrage der Params
function wimbblock_always_form() {
	$all       = wimbblock_get_always();
	$to_delete = wimbblock_ai_always();
	$i         = 0;
	foreach ( $all as $browser ) {
		if ( in_array( $browser, $to_delete, true ) ) {
			$style = ' class="grey" ';
		} else {
			$style = '';
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<p><input ' . $style . ' type="text" size="15" name="wimbblock_always[' . $i . ']" value="' . esc_html( $browser ) . '" /> ' . "\n";
		++$i;
	}
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '<p><input type="text" size="15" name="wimbblock_always[' . $i . ']" /> ' . "\n";
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wimbblock_always_validate( $params ) {
	// var_dump($params);wp_die();
	if ( ! empty( $_POST ) && check_admin_referer( 'wimbblock', 'wimbblock_nonce' ) ) {
		delete_transient( 'wimbblock_always' );
		if ( isset( $_POST['submit'] ) ) {
			$newparams = array();
			$last      = count( $params );
			for ( $i = 0; $i < $last; $i++ ) {
				if ( $params[ $i ] !== '' ) {
					$newparams[] = $params[ $i ];
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
	$text  = '<h3>' . __( 'Always block browsers with this strings', 'wimb-and-block' ) . '</h3>';
	$text .= '<p>' . __( 'Sometimes bad bots have a changing version number, so you can configure a string to always block them.', 'wimb-and-block' ) . '</p>';
	$ai    = wimbblock_get_option( 'wimbblock_block_ai' ); // string 1 or 0
	// var_dump( $ai );
	if ( $ai === '1' ) {
		$text .= '<p>' . __( 'You can delete the grey values, since they are detected by the AI crawler check.', 'wimb-and-block' );
	} else {
		$text .= '<p>' . __( 'If you enable to block AI crawlers, you can delete the grey values, since they are detected by the AI crawler check.', 'wimb-and-block' );
	}
	echo wp_kses_post( $text );
}
