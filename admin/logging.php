<?php
/**
 * Log and Loglevel
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_log_init() {
	add_settings_section( 'wimbblock_log_settings', '', '', 'wimbblock_log_settings' );
	$fields = wimbblock_logging_levels();
	// var_dump($fields);
	foreach ( $fields as $field ) {
		add_settings_field(
			'wimbblock_log[' . $field['param'] . ']',
			$field['desc'],
			'wimbblock_form_logging',
			'wimbblock_log_settings',
			'wimbblock_log_settings',
			$field['param']
		);
	}
	register_setting( 'wimbblock_log_settings', 'wimbblock_log', 'wimbblock_validate_logging' );
}
add_action( 'admin_init', 'wimbblock_log_init' );

function wimbblock_form_logging( $field ) {
	$options = wimbblock_logging_levels_settings();
	if ( ! current_user_can( 'manage_options' ) ) {
		$disabled = ' disabled ';
	} else {
		$disabled = '';
	}
	$logging = wimbblock_logging_levels();
	foreach ( $logging as $log ) {
		if ( $log['param'] === $field ) {
			echo '<p>' . wp_kses_post( $log['help'] ) . '</p>';
			break;
		}
	}
	echo '<p><input ' . esc_attr( $disabled ) . ' type="radio" name="wimbblock_log[' . esc_attr( $field ) . ']" value="1" ';
	checked( $options[ $field ] === true );
	echo '> ' . esc_html__( 'yes', 'wimb-and-block' ) . ' &nbsp;&nbsp; ';
	echo '<input ' . esc_attr( $disabled ) . ' type="radio" name="wimbblock_log[' . esc_attr( $field ) . ']" value="0" ';
	checked( $options[ $field ] === false );
	echo '> ' . esc_html__( 'no', 'wimb-and-block' ) . '</p>';
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wimbblock_validate_logging( $options ) {
	$post = map_deep( wp_unslash( $_POST ), 'sanitize_text_field' );
	if ( ! empty( $post ) && check_admin_referer( 'wimbblock_log', 'wimbblock_log_nonce' ) ) {
		if ( isset( $post['submit'] ) ) {
			$settings = array();
			foreach ( $options as $key => $value ) {
				$settings[ $key ] = boolval( $value );
			}
			return $settings;
		}
		if ( isset( $post['delete'] ) ) {
			delete_option( 'wimbblock_log' );
		}
		return false;
	}
}

// Draw the menu page itself
function wimbblock_log_admin_page() {
	$options = wimbblock_get_options_db();
	if ( $options['location'] === 'remote' ) {
		echo '<p><div class="notice notice-info">' . wp_kses_post( __( 'You must configure these settings on each of your websites that use this database!', 'wimb-and-block' ) ) . '</div></p>';
	}
	if ( current_user_can( 'manage_options' ) ) {
		echo '<form method="post" action="options.php">';
	} else {
		echo '<form>';
	}
	settings_fields( 'wimbblock_settings_logfile' );
	do_settings_sections( 'wimbblock_settings_logfile' );

	if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMBBLOCK_BASENAME ) ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			wp_nonce_field( 'wimbblock_log', 'wimbblock_logfile_nonce' );
			submit_button();
			// submit_button( __( 'Reset', 'wimb-and-block' ), 'delete', 'delete', false );
		}
	}
	echo '</form>';

	echo '<hr class="adminhrule">';
	if ( current_user_can( 'manage_options' ) ) {
		echo '<form method="post" action="options.php">';
	} else {
		echo '<form>';
	}
	settings_fields( 'wimbblock_anon_settings' );
	do_settings_sections( 'wimbblock_anon_settings' );
	if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMBBLOCK_BASENAME ) ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			wp_nonce_field( 'wimbblock_anon', 'wimbblock_anon_nonce' );
			submit_button();
			// submit_button( __( 'Reset', 'wimb-and-block' ), 'delete', 'delete', false );
		}
	}
	echo '</form>';

	echo '<hr class="adminhrule">';
	echo wp_kses_post( wimbblock_what_log_help() );
	if ( current_user_can( 'manage_options' ) ) {
		echo '<form method="post" action="options.php">';
	} else {
		echo '<form>';
	}
	settings_fields( 'wimbblock_log_settings' );
	do_settings_sections( 'wimbblock_log_settings' );
	if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMBBLOCK_BASENAME ) ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			wp_nonce_field( 'wimbblock_log', 'wimbblock_log_nonce' );
			submit_button();
			submit_button( __( 'Reset', 'wimb-and-block' ), 'delete', 'delete', false );
		}
	}
	echo '</form>';
}

function wimbblock_what_log_help() {
	$text  = '<h3>' . __( 'This is always logged:', 'wimb-and-block' ) . '</h3>';
	$text .= '<ul><li class="adminli">';
	$text .= __( 'if the plugin creates its table, local or remote', 'wimb-and-block' );
	$text .= '</li><li class="adminli">';
	$text .= __( 'plugins cron jobs', 'wimb-and-block' );
	$text .= '</li><li class="adminli">';
	$text .= __( 'if an agent is inserted in table (on its first visit)', 'wimb-and-block' );
	$text .= '</li><li class="adminli">';
	$text .= __( 'if an agent is always blocked (on its first visit)', 'wimb-and-block' );
	$text .= '</li><li class="adminli">';
	$text .= __( 'if no agent is given', 'wimb-and-block' );
	// $text .= '</li><li class="adminli">';
	// $text .= __( 'if no IP ?', 'wimb-and-block' );
	$text .= '</li><li class="adminli">';
	$text .= __( 'if an IP uses an agent without authorization (for example an agent string from Google)', 'wimb-and-block' );
	$text .= '</li></ul>';
	return $text;
}
