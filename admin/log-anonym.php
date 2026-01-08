<?php
/**
 * Log anonymous IP
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

//
function wimbblock_anon_init() {
	add_settings_section( 'wimbblock_anon_settings', '', '', 'wimbblock_anon_settings' );
	add_settings_field( 'wimbblock_anon', __( 'IP anonymization', 'wimb-and-block' ), 'wimbblock_form_anon', 'wimbblock_anon_settings', 'wimbblock_anon_settings' );
	register_setting( 'wimbblock_anon_settings', 'wimbblock_anon', 'wimbblock_validate_anon' );
}
add_action( 'admin_init', 'wimbblock_anon_init' );

function wimbblock_form_anon() {
	if ( ! current_user_can( 'manage_options' ) ) {
		$disabled = ' disabled ';
	} else {
		$disabled = '';
	}
	$anon    = wimbblock_anon_log();
	$setting = wimbblock_anon_settings();
	echo '<p>' . wp_kses_post( wimbblock_anon_help() ) . '</p>';
	echo '<p><fieldset>';
	foreach ( $anon as $iplog ) {
		echo '<div><input ' . esc_attr( $disabled ) . ' type="radio" name="wimbblock_anon" value="' . esc_attr( $iplog['log'] ) . '" ';
		checked( $iplog['log'] === $setting );
		echo '> ' . esc_attr( $iplog['help'] ) . '</div>';
	}
	echo '</fieldset></p>';
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wimbblock_validate_anon( $options ) {
	$post = map_deep( wp_unslash( $_POST ), 'sanitize_text_field' );
	if ( ! empty( $post ) && check_admin_referer( 'wimbblock_anon', 'wimbblock_anon_nonce' ) ) {
		if ( isset( $post['submit'] ) ) {
			return esc_html( $options );
		}
		if ( isset( $post['delete'] ) ) {
			delete_option( 'wimbblock_anon_' );
		}
		return false;
	}
}

function wimbblock_anon_help() {
	$text  = __( 'When logging, the IP address is also logged.', 'wimb-and-block' );
	$text .= ' ' . __(
		'This could potentially be a problem in the context of the GDPR.',
		'wimb-and-block'
	);
	$text .= ' ' . __( 'However, logging only occurs when an agent is detected that meets the criteria listed below or when the agent accesses your website for the first time.', 'wimb-and-block' );
	return $text;
}
