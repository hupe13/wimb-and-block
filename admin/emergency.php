<?php
/**
 * Functions for emergency button
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

// Init settings fuer emergency
function wimbblock_emergency_init() {
	add_settings_section( 'wimbblock_emergency', '', '', 'wimbblock_emergency' );
	add_settings_field( 'wimbblock_emergency', __( 'Emergency - start / stop', 'wimb-and-block' ), 'wimbblock_form_emergency', 'wimbblock_emergency', 'wimbblock_emergency' );
	register_setting( 'wimbblock_emergency', 'wimbblock_emergency', 'wimbblock_validate_emergency' );
}
add_action( 'admin_init', 'wimbblock_emergency_init' );

// Baue Abfrage der Params
function wimbblock_form_emergency() {
	$setting = wimbblock_get_option( 'wimbblock_emergency' );
	if ( $setting === false ) {
		$setting = '1';
	}

	// var_dump($setting);
	if ( ! current_user_can( 'manage_options' ) ) {
		$disabled = ' disabled ';
	} else {
		$disabled = '';
	}

	$options = wimbblock_get_options_db();
	if ( $options['error'] !== '0' ) {
		$setting = '0';
	}
	echo '<input ' . esc_attr( $disabled ) . ' type="radio" name="wimbblock_emergency" value="1" ';
	checked( $setting !== '0' );
	echo '> ' . esc_html__( 'It is working.', 'wimb-and-block' ) . ' &nbsp;&nbsp; ';
	echo '<input ' . esc_attr( $disabled ) . ' type="radio" name="wimbblock_emergency" value="0" ';
	checked( $setting === '0' );
	echo '> ' . esc_html__( 'STOP!', 'wimb-and-block' );
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wimbblock_validate_emergency( $input ) {
	if ( ! empty( $_POST ) && check_admin_referer( 'wimbblock_emergency', 'wimbblock_emergency_nonce' ) ) {
		if ( isset( $_POST['submit'] ) ) {
			return $input;
		}
	}
}

function wimbblock_emergency_help() {
	esc_html_e( 'The plugin begins its work if the WIMB API Key is set and if the database is configured and running.', 'wimb-and-block' );
	echo ' ';
	esc_html_e( 'If something does not work as expected, you can stop it here.', 'wimb-and-block' );
}
