<?php
/**
 * Functions for delete all settings
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

// Init settings fuer deleting
function wimbblock_deleting_init() {
	add_settings_section( 'deleting_settings', '', '', 'wimbblock_settings_deleting' );
	add_settings_field( 'wimbblock_deleting', __( 'Delete all plugin settings when deleting the plugin?', 'wimb-and-block' ), 'wimbblock_form_deleting', 'wimbblock_settings_deleting', 'deleting_settings' );
	register_setting( 'wimbblock_settings_deleting', 'wimbblock_deleting', 'wimbblock_validate_deleting' );
}
add_action( 'admin_init', 'wimbblock_deleting_init' );

// Baue Abfrage der Params
function wimbblock_form_deleting() {
	$should_delete = get_option( 'wimbblock_deleting' );
	if ( ! current_user_can( 'manage_options' ) ) {
		$disabled = ' disabled ';
	} else {
		$disabled = '';
	}

	echo '<p>' . wp_kses_post( __( 'If the database is local, the table will also be deleted.', 'wimb-and-block' ) ) . '</p>';
	echo '<input ' . esc_attr( $disabled ) . ' type="radio" name="wimbblock_deleting[on]" value="1" ';
	checked( ! ( isset( $should_delete['on'] ) && $should_delete['on'] === '0' ) );
	echo '> ' . esc_html__( 'yes', 'wimb-and-block' ) . ' &nbsp;&nbsp; ';
	echo '<input ' . esc_attr( $disabled ) . ' type="radio" name="wimbblock_deleting[on]" value="0" ';
	checked( ( isset( $should_delete['on'] ) && $should_delete['on'] === '0' ) || $should_delete === false );
	echo '> ' . esc_html__( 'no', 'wimb-and-block' );
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wimbblock_validate_deleting( $input ) {
	if ( ! empty( $_POST ) && check_admin_referer( 'wimbblock_deleting', 'wimbblock_deleting_nonce' ) ) {
		if ( isset( $_POST['submit'] ) ) {
			return $input;
		}
	}
}
