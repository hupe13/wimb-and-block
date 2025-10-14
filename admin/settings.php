<?php
/**
 *  Settings for wimb-and-block Settings for database
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

// Database
function wimbblock_init() {
	add_settings_section( 'wimbblock_settings', '', '', 'wimbblock_settings' );
	add_settings_field( 'wimbblock_settings[error]', '', 'wimbblock_form', 'wimbblock_settings', 'wimbblock_settings', 'error' );
	add_settings_field( 'wimbblock_settings[wimb_api]', __( 'WIMB API Key', 'wimb-and-block' ), 'wimbblock_form', 'wimbblock_settings', 'wimbblock_settings', 'wimb_api' );
	add_settings_field( 'wimbblock_settings[location]', __( 'Local WP database or remote database', 'wimb-and-block' ), 'wimbblock_form', 'wimbblock_settings', 'wimbblock_settings', 'location' );
	add_settings_field( 'wimbblock_settings[table_name]', __( 'WIMB table name', 'wimb-and-block' ), 'wimbblock_form', 'wimbblock_settings', 'wimbblock_settings', 'table_name' );
	add_settings_field( 'wimbblock_settings[db_user]', __( 'Remote database username', 'wimb-and-block' ), 'wimbblock_form', 'wimbblock_settings', 'wimbblock_settings', 'db_user' );
	add_settings_field( 'wimbblock_settings[db_password]', __( 'Remote database password', 'wimb-and-block' ), 'wimbblock_form', 'wimbblock_settings', 'wimbblock_settings', 'db_password' );
	add_settings_field( 'wimbblock_settings[db_name]', __( 'Remote database name', 'wimb-and-block' ), 'wimbblock_form', 'wimbblock_settings', 'wimbblock_settings', 'db_name' );
	add_settings_field( 'wimbblock_settings[db_host]', __( 'Remote database hostname', 'wimb-and-block' ), 'wimbblock_form', 'wimbblock_settings', 'wimbblock_settings', 'db_host' );
	add_settings_field( 'wimbblock_settings[rotate]', __( 'Rotate the table on this site', 'wimb-and-block' ), 'wimbblock_form', 'wimbblock_settings', 'wimbblock_settings', 'rotate' );
	add_settings_field( 'wimbblock_settings[logfile]', __( 'Path of log file', 'wimb-and-block' ), 'wimbblock_form', 'wimbblock_settings', 'wimbblock_settings', 'logfile' );
	if ( get_option( 'wimbblock_settings' ) === false ) {
		add_option( 'wimbblock_settings', array() );
	}
	register_setting( 'wimbblock_settings', 'wimbblock_settings', 'wimbblock_validate' );
}
add_action( 'admin_init', 'wimbblock_init' );

// Baue Abfrage der Params
function wimbblock_form( $field ) {
	$options = wimbblock_get_options_db();
	if ( $field === 'error' ) {
		echo '<input type="hidden" name="wimbblock_settings[' . esc_attr( $field ) . ']" value="' . esc_attr( $options[ $field ] ) . '" />';
		if ( $options['error'] === '2' && $options['location'] === 'remote' ) {
			echo '<div class="error notice">';
			echo esc_html( __( 'Access to the remote database seems to be working fine. Please resubmit the form.', 'wimb-and-block' ) );
			echo '</div>';
		}
	} elseif ( $field === 'location' ) {

		echo '<p>';
		esc_html_e( 'You need a table in a database. This can be a table in the default WordPress database (local) or in a remote database.', 'wimb-and-block' );
		echo ' ';
		esc_html_e( 'The latter is recommended if you have multiple WordPress instances on the same server.', 'wimb-and-block' );
		echo '</p>';

		$locations   = array();
		$locations[] = 'local';
		$locations[] = 'remote';

		echo '<select name="wimbblock_settings[' . esc_attr( $field ) . ']">' . "\r\n";
		foreach ( $locations as $location ) {
			if ( $location === $options['location'] ) {
				echo '<option selected ';
			} else {
				echo '<option ';
			}
			echo 'value="' . esc_attr( $location ) . '">' . esc_attr( $location ) . '</option>' . "\r\n";
		}
		echo '</select>' . "\r\n";
	} elseif ( $field === 'logfile' ) {
		// var_dump($options);
		if ( isset( $options['logfile'] ) && $options['logfile'] !== '' && $options['logfile'] !== false ) {
			$value   = ' value="' . sanitize_text_field( $options['logfile'] ) . '" ';
			$setting = $options['logfile'];
		} else {
			$value = '';
			if ( true === WP_DEBUG && WP_DEBUG_LOG === true ) {
				$setting = 'WP_CONTENT_DIR/wp-content/debug.log';
			} elseif ( true === WP_DEBUG ) {
				$setting = WP_DEBUG_LOG . ' (WP_DEBUG_LOG)';
			} else {
				$setting = 'WP_DEBUG is false.';
			}
		}
		echo '<p><b>' . esc_html( __( 'Setting:', 'wimb-and-block' ) ) . '</b> ' . esc_html( $setting ) . '</p>';
		echo '<input type="text" size="80" name="wimbblock_settings[logfile]" ';
		echo 'placeholder="/path/to/logfile" />';

	} elseif ( $field === 'rotate' ) {
		echo '<p>';
		esc_html_e( 'If the database is local, it is automatically set to "yes". For remote databases, set it to "yes" on exactly one WP instance.', 'wimb-and-block' );
		echo '</p>';
		if ( ! isset( $options['rotate'] ) ) {
			$options['rotate'] = 'none';
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			$select_disabled = ' disabled ';
		} else {
			$select_disabled = '';
		}
		$times = array( 'yes', 'no' );
		echo '<select ' . esc_attr( $select_disabled ) . ' id="rotate" name="wimbblock_settings[rotate]">' . "\r\n";
		foreach ( $times as $time ) {
			if ( $time === $options['rotate'] ) {
				echo '<option selected ';
			} else {
				echo '<option ';
			}
			echo 'value="' . esc_attr( $time ) . '">' . esc_attr( $time ) . '</option>' . "\r\n";
		}
		echo '</select>' . "\r\n";
	} else {
		if ( $field === 'wimb_api' ) {
			echo '<p>';
			echo wp_kses_post( __( 'Get an API key for a <a href="https://developers.whatismybrowser.com/api/">Basic Application Plan</a>.', 'wimb-and-block' ) );
			echo '</p>';
		}
		echo '<input type="text" size="20" name="wimbblock_settings[' . esc_attr( $field ) . ']" ';
		echo ' value="' . esc_attr( $options[ $field ] ) . '" />';
	}
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wimbblock_validate( $options ) {
	// wimbblock_error_log( 'Options ' . print_r( $options, true ) );
	if ( ! empty( $_POST ) && check_admin_referer( 'wimbblock', 'wimbblock_nonce' ) ) {
		// wimbblock_error_log( 'Sanitize and validate' );
		if ( isset( $_POST['submit'] ) ) {

			if ( $options['error'] === '1' ) {
				$options['error'] = '3';
			}

			if ( $options['logfile'] !== '' ) {
				if ( ! file_exists( dirname( $options['logfile'] ) ) ) {
					$options['logfile'] = '';
				}
			}
			delete_transient( 'wimbblock_logfile' );

			if ( $options['wimb_api'] === '' ) {
				add_settings_error(
					'wimbblock_settings',
					'invalid',
					'WIMB API key needed.',
					'error'
				);
				$options['error'] = '1';
				return $options;
			}

			if ( $options['location'] === 'local' ) {
				$options = array(
					'wimb_api'    => $options['wimb_api'],
					'table_name'  => $options['table_name'],
					'location'    => 'local',
					'db_user'     => '',
					'db_password' => '',
					'db_name'     => '',
					'db_host'     => '',
					'logfile'     => $options['logfile'],
					'rotate'      => 'yes',
					'error'       => '0',
				);
				wimbblock_error_log( 'Local Table ' . $options['table_name'] );
				wimbblock_table_install( $options['table_name'] );
			} else {
				// remote
				settings_errors( 'wimbblock_settings' );
				if ( $options['table_name'] === '' || $options['db_user'] === '' || $options['db_password'] === '' || $options['db_host'] === '' ) {
					add_settings_error(
						'wimbblock_settings',
						'invalid',
						'Please fill out all fields.',
						'error'
					);
					$options['error'] = '1';
					return $options;
				}
				if ( ! filter_var( $options['db_host'], FILTER_VALIDATE_IP ) ) {
					if ( ! filter_var( $options['db_host'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME ) ) {
						add_settings_error(
							'wimbblock_settings',
							'invalid',
							'Invalid IP or hostname',
							'error'
						);
						$options['error'] = '1';
						return $options;
					}
				}

				switch ( $options['error'] ) {
					case '2':
						wimbblock_error_log( 'Remote Table ' . $options['table_name'] );
						wimbblock_table_install( $options['table_name'] );
						// $wimb_test_datatable->close();
						$options['error'] = '0';
						break;

					case '3':
						$wimb_test_datatable = new wpdb(
							$options['db_user'],
							$options['db_password'],
							$options['db_name'],
							$options['db_host']
						);

						if ( $wimb_test_datatable->error !== null ) {
							add_settings_error(
								'wimbblock_settings',
								'invalid',
								'No connection to database - try again!',
								'error'
							);
							$options['error'] = '1';
							return $options;
						} else {
							$query = $wimb_test_datatable->prepare( 'SHOW TABLES LIKE %s', $wimb_test_datatable->esc_like( $options['table_name'] ) );
							if ( $wimb_test_datatable->get_var( $query ) !== $options['table_name'] ) {
								// table does not exists
								$options['error'] = '2';
							} else {
								// all okay
								$options['error'] = '0';
							}
						}
						break;
				}
			}
			//var_dump($options); wp_die("tot");
			return $options;
		}
		if ( isset( $_POST['delete'] ) ) {
			delete_option( 'wimbblock_settings' );
			delete_transient( 'wimbblock_logfile' );
		}
	}
	return false;
}
