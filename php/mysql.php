<?php
/**
 * Functions for database
 *
 * @package wimb-and-block
 */

//
function wimbblock_table_install( $table_name ) {

	$wimbblock_options = wimbblock_get_options_db();
	if ( $wimbblock_options['location'] === 'local' ) {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
	} else {
		global $wimb_datatable;
		wimbblock_open_wpdb();
		$charset_collate = $wimb_datatable->get_charset_collate();
	}

	$wimb_sql = "CREATE TABLE {$table_name} (
		i bigint NOT NULL auto_increment,
		browser varchar(300) NOT NULL,
		software varchar(70) NOT NULL,
		system varchar(50) NOT NULL,
		version varchar(6) NOT NULL,
		time timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
		yymm char(4) NOT NULL DEFAULT date_format(current_timestamp(),'%y%m'),
		wimbdate char(6) NOT NULL DEFAULT date_format(current_timestamp(),'%y%m%d'),
		count int(11) NOT NULL DEFAULT 0,
		block int(11) NOT NULL DEFAULT 0,
		robots int(11) NOT NULL DEFAULT 0,
		count_1 int(11) NOT NULL DEFAULT 0,
		block_1 int(11) NOT NULL DEFAULT 0,
		count_2 int(11) NOT NULL DEFAULT 0,
		block_2 int(11) NOT NULL DEFAULT 0,
		count_3 int(11) NOT NULL DEFAULT 0,
		block_3 int(11) NOT NULL DEFAULT 0,
		PRIMARY KEY i (i),
		UNIQUE KEY browser (browser)
	) $charset_collate;";

	if ( $wimbblock_options['location'] === 'local' ) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$status = dbDelta( $wimb_sql );
	} else {
		$status = wimbblock_dbDelta( $wimb_sql );
	}
	wimbblock_error_log( 'Created wimb_table: ' . $table_name );
}

function wimbblock_close_mysqlstat() {
	global $wimb_datatable;
	if ( isset( $wimb_datatable ) ) {
		$wimb_datatable->close();
		unset( $wimb_datatable );
	}
}
// add_action( 'shutdown', 'wimbblock_close_mysqlstat', 10, 1 );

function wimbblock_open_wpdb() {
	global $wimb_datatable;
	$wimbblock_options = wimbblock_get_options_db();
	if ( $wimbblock_options['location'] === 'local' ) {
		global $wpdb;
		$wimb_datatable = $wpdb;
	} else {
		$wimb_datatable = new wpdb(
			$wimbblock_options['db_user'],
			$wimbblock_options['db_password'],
			$wimbblock_options['db_name'],
			$wimbblock_options['db_host']
		);
	}
}

function wimbblock_counter( $table_name, $counter, $id ) {
	global $wimb_datatable;
	$entry = $wimb_datatable->query(
		$wimb_datatable->prepare(
			'UPDATE %i SET ' . $counter . ' = ' . $counter . " + 1 WHERE i = '" . $id . "'",
			$table_name
		)
	);
}

function wimbblock_error_log( $reason ) {
	$logfile = get_transient( 'wimbblock_logfile' );
	if ( false === $logfile ) {
		$options = wimbblock_get_options_db();
		if ( isset( $options['logfile'] ) && $options['logfile'] !== '' && $options['logfile'] !== false ) {
			$logfile = $options['logfile'];
			if ( $logfile === '/dev/null' ) {
				$logfile = '';
			}
		} elseif ( true === WP_DEBUG && WP_DEBUG_LOG === true ) {
			$logfile = WP_CONTENT_DIR . '/debug.log';
		} elseif ( true === WP_DEBUG && WP_DEBUG_LOG !== false ) {
			global $wp_filesystem;
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			WP_Filesystem();
			if ( $wp_filesystem->exists( WP_DEBUG_LOG ) && $wp_filesystem->is_writable( WP_DEBUG_LOG ) ) {
				$logfile = WP_DEBUG_LOG;
			} else {
				$logfile = '';
			}
		} else {
			$logfile = '';
		}
		set_transient( 'wimbblock_logfile', $logfile, DAY_IN_SECONDS );
	}
	if ( $logfile !== '' ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( '[' . current_time( 'mysql' ) . '] ' . get_site_url() . ' - wimb - ' . $ip . ': ' . $reason . "\r\n", 3, $logfile );
	}
}
