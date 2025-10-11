<?php
/**
 * Functions for unknown and empty softwar strings
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

// Init settings fuer unknown_empty
function wimbblock_unknown_empty_init() {
	add_settings_section( 'wimbblock_unknown_empty', '', 'wimbblock_unknown_empty_help', 'wimbblock_unknown_empty' );
	add_settings_field( 'wimbblock_unknown_empty[unknown]', __( 'Block browser with "unknown" in the software string', 'wimb-and-block' ), 'wimbblock_form_unknown_empty', 'wimbblock_unknown_empty', 'wimbblock_unknown_empty', 'unknown' );
	add_settings_field( 'wimbblock_unknown_empty[empty]', __( 'Block browser with empty software string', 'wimb-and-block' ), 'wimbblock_form_unknown_empty', 'wimbblock_unknown_empty', 'wimbblock_unknown_empty', 'empty' );
	register_setting( 'wimbblock_unknown_empty', 'wimbblock_unknown_empty', 'wimbblock_validate_unknown_empty' );
}
add_action( 'admin_init', 'wimbblock_unknown_empty_init' );

// Baue Abfrage der Params
function wimbblock_form_unknown_empty( $field ) {
	// var_dump($field);
	$setting = get_option(
		'wimbblock_unknown_empty',
		array(
			'unknown' => '1',
			'empty'   => '1',
		)
	);
	// var_dump($setting);
	if ( ! current_user_can( 'manage_options' ) ) {
		$disabled = ' disabled ';
	} else {
		$disabled = '';
	}

	echo '<input ' . esc_attr( $disabled ) . ' type="radio" name="wimbblock_unknown_empty[' . esc_attr( $field ) . ']" value="1" ';
	checked( ! ( isset( $setting[ $field ] ) && $setting[ $field ] === '0' ) );
	echo '> ' . esc_html__( 'yes', 'wimb-and-block' ) . ' &nbsp;&nbsp; ';
	echo '<input ' . esc_attr( $disabled ) . ' type="radio" name="wimbblock_unknown_empty[' . esc_attr( $field ) . ']" value="0" ';
	checked( isset( $setting[ $field ] ) && $setting[ $field ] === '0' );
	echo '> ' . esc_html__( 'no', 'wimb-and-block' );
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wimbblock_validate_unknown_empty( $input ) {
	if ( ! empty( $_POST ) && check_admin_referer( 'wimbblock', 'wimbblock_nonce' ) ) {
		if ( isset( $_POST['submit'] ) ) {
			return $input;
		}
		if ( isset( $_POST['delete'] ) ) {
			delete_option( 'wimbblock_unknown_empty' );
		}
	}
	return false;
}

function wimbblock_unknown_empty_help() {
	wp_enqueue_style(
		'wimbblock-css',
		plugins_url( dirname( WIMB_BASENAME ) . '/admin/admin.css' ),
		array(),
		1
	);
	$text  = '<p>';
	$text .= __(
		'The user agent string of every browser accessing your website the first time is send to WhatIsMyBrowser and some data will be stored in this table.',
		'wimb-and-block'
	);
	$text .= '<p>';

	$text .= __( 'These data are: ', 'wimb-and-block' );
	$text .= '<ul><li>' . __( 'the user agent string, ', 'wimb-and-block' ) . '</li>';
	$text .= '<li>' . __( 'a simple software string, ', 'wimb-and-block' ) . '</li>';
	$text .= '<li>' . __( 'and the operating system, ', 'wimb-and-block' ) . '</li></ul>';
	$text .= __( 'For example:', 'wimb-and-block' );
	$text .= '<ul><li>Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36</li>';
	$text .= '<li>Chrome 140 on Windows 10</li>';
	$text .= '<li>Windows 10</li></ul>';

	$text .= __( 'It will be blocked, if the "simple software string" contains "unknown" or is empty.', 'wimb-and-block' );
	$text .= '</p>';
	$text .= '<p>' . __( 'Sometimes there are false positive. Then you can stop the blocking.', 'wimb-and-block' ) . '</p>';
	$text .= '<p>' . __( 'Use phpmyadmin to change these entries: Set the software column to any string without "unknown" and set the column block to 0!', 'wimb-and-block' ) . '</p>';
	$text .= '<p>' . __( 'After that, the blocking can continue.', 'wimb-and-block' ) . '</p>';
	echo wp_kses_post( $text );
}
