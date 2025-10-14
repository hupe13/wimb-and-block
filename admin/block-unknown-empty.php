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
	add_settings_section( 'wimbblock_unknown_empty', '', '', 'wimbblock_unknown_empty' );
	add_settings_field( 'wimbblock_unknown_empty[unknown]', __( 'Block browser with "unknown" in the software string', 'wimb-and-block' ), 'wimbblock_form_unknown_empty', 'wimbblock_unknown_empty', 'wimbblock_unknown_empty', 'unknown' );
	add_settings_field( 'wimbblock_unknown_empty[empty]', __( 'Block browsers with an empty entry in the software field', 'wimb-and-block' ), 'wimbblock_form_unknown_empty', 'wimbblock_unknown_empty', 'wimbblock_unknown_empty', 'empty' );
	register_setting( 'wimbblock_unknown_empty', 'wimbblock_unknown_empty', 'wimbblock_validate_unknown_empty' );
}
add_action( 'admin_init', 'wimbblock_unknown_empty_init' );

// Baue Abfrage der Params
function wimbblock_form_unknown_empty( $field ) {
	// var_dump($field);
	$setting = wimbblock_get_option( 'wimbblock_unknown_empty' );
	if ( $setting === false ) {
		$setting = array(
			'unknown' => '1',
			'empty'   => '1',
		);
	}
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
	$text .= __( 'You can also stop such blocking as follows:', 'wimb-and-block' ) . ' ';
	$text .= '<ul><li>' . __( 'Use phpmyadmin to change these entries.', 'wimb-and-block' ) . '</li>';
	$text .= '<li>' . __( 'Set the software column to any string without "unknown" and set the column block to 0!', 'wimb-and-block' ) . '</li>';
	$text .= '<li>' . __( 'After that, the blocking can continue.', 'wimb-and-block' ) . '</li></ul></p>';
	echo wp_kses_post( $text );
}

function wimbblock_exlude_help() {
	wp_enqueue_style(
		'wimbblock-css',
		plugins_url( dirname( WIMB_BASENAME ) . '/admin/admin.css' ),
		array(),
		1
	);
	$text  = '<h3>' . __( 'Unknown browsers and empty software strings', 'wimb-and-block' ) . '</h3><p>';
	$text .= __( 'Browsers will be blocked, if the "simple software string" contains "unknown" or is empty:', 'wimb-and-block' ) . '</p>';
	$text .= '<p><img src="' . plugin_dir_url( __FILE__ ) . '../pict/suspect.jpg" alt="example entries" width="450" ></p>';
	$text .= '<p>' . __( 'Sometimes there are false positive, for example if the browser is from Mastodon. Then you can exclude these.', 'wimb-and-block' ) . '</p>';
	echo wp_kses_post( $text );
}
