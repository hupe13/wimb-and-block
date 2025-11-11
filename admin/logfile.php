<?php
/**
 *  Settings for wimb-and-block Settings for logfile
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

// Logfile
function wimbblock_logfile_init() {
	add_settings_section( 'logfile_settings', '', '', 'wimbblock_settings_logfile' );
	add_settings_field( 'wimbblock_logfile', __( 'Path of log file', 'wimb-and-block' ), 'wimbblock_logfile_form', 'wimbblock_settings_logfile', 'logfile_settings' );
	if ( get_option( 'wimbblock_logfile' ) === false ) {
		$wimbblock_wpdb_options = wimbblock_get_options_db();
		if ( isset( $wimbblock_wpdb_options['logfile'] ) && $wimbblock_wpdb_options['logfile'] !== '' && $wimbblock_wpdb_options['logfile'] !== false ) {
			$logfile = $wimbblock_wpdb_options['logfile'];
			add_option( 'wimbblock_logfile', $logfile );
		}
	}
	register_setting( 'wimbblock_settings_logfile', 'wimbblock_logfile', 'wimbblock_validate_logfile' );
}
add_action( 'admin_init', 'wimbblock_logfile_init' );

// Baue Abfrage der Params
function wimbblock_logfile_form() {
	$setting = '';
	$logfile = wimbblock_get_option( 'wimbblock_logfile' );
	// var_dump( 'logfile', $logfile );
	if ( isset( $logfile ) && $logfile !== '' && $logfile !== false ) {
		$setting = $logfile;
	}
	if ( $setting !== '' ) {
		$value = ' value="' . sanitize_text_field( $logfile ) . '" ';
	} else {
		$value = '';
		if ( true === WP_DEBUG && WP_DEBUG_LOG === true ) {
			$setting = 'WP_CONTENT_DIR/debug.log';
		} elseif ( true === WP_DEBUG && WP_DEBUG_LOG !== false ) {
			global $wp_filesystem;
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			WP_Filesystem();
			if ( $wp_filesystem->exists( WP_DEBUG_LOG ) && $wp_filesystem->is_writable( WP_DEBUG_LOG ) ) {
				$setting = WP_DEBUG_LOG;
			} else {
				$setting = '';
			}
		} else {
			$setting = 'WP_DEBUG is false.';
		}
	}

	echo wp_kses_post( __( "The logging is very verbose. If you have WP_DEBUG set to true, but don't want the plugin to log anything, set it to /dev/null.", 'wimb-and-block' ) );
	echo '<br>' . wp_kses_post( __( 'You should make sure that the log file does not become too large. It is best to set up a cron job that rotates the log file every day.', 'wimb-and-block' ) );
	echo '<p><b>' . esc_html( __( 'Setting:', 'wimb-and-block' ) ) . '</b> ' . esc_html( $setting ) . '</p>';
	echo '<input type="text" size="80" name="wimbblock_logfile" ';
	if ( $setting === $logfile ) {
		echo 'value="' . esc_html( $setting ) . '" />';
	} else {
		echo 'placeholder="/path/to/logfile" />';
	}
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wimbblock_validate_logfile( $filename ) {
	if ( ! empty( $_POST ) && check_admin_referer( 'wimbblock_log', 'wimbblock_logfile_nonce' ) ) {
		// wimbblock_error_log( 'Sanitize and validate' );
		if ( isset( $_POST['submit'] ) ) {
			delete_transient( 'wimbblock_logfile' );
			if ( $filename !== '' ) {
				if ( ! file_exists( dirname( $filename ) ) ) {
					$filename = '';
				}
			}
			return $filename;
		}
		if ( isset( $_POST['delete'] ) ) {
			delete_option( 'wimbblock_logfile' );
			delete_transient( 'wimbblock_logfile' );
		}
	}
	return false;
}
