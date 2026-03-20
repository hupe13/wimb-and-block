<?php
/**
 * Creation and updates for wimb tables
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

if ( is_main_site() ) {

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
		wimbblock_error_log( 'Created / updated wimb_table: ' . $table_name );
	}

	function wimbblock_rotate_table() {
		$wpdb_options = wimbblock_get_options_db();
		$yymm         = wp_date( 'ym' );
		$yymm_last    = wp_date( 'ym', strtotime( 'first day of previous month' ) );
		global $wimb_datatable;
		if ( is_null( $wimb_datatable ) ) {
			wimbblock_open_wpdb();
		}
		$entries = $wimb_datatable->get_results(
			$wimb_datatable->prepare(
				'UPDATE %i SET time=time, ' .
				'count_3=count_2, block_3=block_2, ' .
				'count_2=count_1, block_2=block_1, ' .
				'count_1=count, block_1=block, ' .
				'count=0, block=IF(block > 0, 1, 0), ' .
				'robots=IF(robots > 0, 1, 0), ' .
				'yymm=%s WHERE yymm=%s',
				$wpdb_options['table_name'],
				$yymm,
				$yymm_last
			)
		);
		wimbblock_error_log( 'Rotated - wimbblock_rotate_table' );
	}

	$wimbblock_options = wimbblock_get_options_db();
	if ( $wimbblock_options['rotate'] === 'yes' ) {
		add_action( 'wimbblock_rotate_hook', 'wimbblock_rotate_table' );
		if ( ! wp_next_scheduled( 'wimbblock_rotate_hook' ) ) {
			$wimbblock_datetime = new DateTime( 'tomorrow 00.05.00', new DateTimeZone( wp_timezone_string() ) );
			wp_schedule_event( $wimbblock_datetime->getTimestamp(), 'daily', 'wimbblock_rotate_hook' );
		}
	}

	function wimbblock_crawler_table_install( $table_name ) {
		$wimbblock_options = wimbblock_get_options_db();
		if ( $wimbblock_options['location'] === 'local' ) {
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();
		} else {
			global $wimb_datatable;
			wimbblock_open_wpdb();
			$charset_collate = $wimb_datatable->get_charset_collate();
		}

		$table_name_create = $table_name . '_crawler';
		$wimb_sql          = "CREATE TABLE {$table_name_create} (
		  crawler varchar(20) NOT NULL,
		  begin varchar(15) NOT NULL,
			int_begin int(11) UNSIGNED NOT NULL,
		  end varchar(15) NOT NULL,
			int_end int(11) UNSIGNED NOT NULL,
			PRIMARY KEY (int_begin),
		  UNIQUE KEY int_end (int_end),
		  KEY crawler (crawler)
		) $charset_collate;";

		if ( $wimbblock_options['location'] === 'local' ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$status = dbDelta( $wimb_sql );
		} else {
			$status = wimbblock_dbDelta( $wimb_sql );
		}
		wimbblock_update_crawlers();
		wimbblock_error_log( 'Created / updated wimb_table crawler: ' . $table_name_create );
	}

	function wimbblock_update_crawlers() {
		// wimbblock_error_log( 'wimbblock_update_crawlers aufgerufen' );
		$wpdb_options = wimbblock_get_options_db();
		global $wimb_datatable;
		if ( is_null( $wimb_datatable ) ) {
			wimbblock_open_wpdb();
		}
		$wimbblock_crawlers = wimbblock_get_option( 'wimbblock_crawlers' );
		$crawlers           = wimbblock_get_jsons();
		$searchengines      = wimbblock_get_option( 'wimbblock_searchengines' );

		foreach ( $searchengines as $crawler => $value ) {
			if ( $value === '0' ) {
				$mgt_code = $wimb_datatable->query(
					$wimb_datatable->prepare(
						'DELETE FROM %i WHERE crawler = %s',
						$wpdb_options['table_name'] . '_crawler',
						$crawler
					),
				);
			}
		}

		foreach ( $crawlers as $crawler => $url ) {
			if ( $searchengines[ $crawler ] === '1' ) {
				$exist = $wimb_datatable->get_row(
					$wimb_datatable->prepare(
						'SELECT crawler FROM %i WHERE %s = crawler;',
						$wpdb_options['table_name'] . '_crawler',
						$crawler
					),
					ARRAY_A
				);
				if ( ! is_null( $exist ) ) {
					$last = $wimbblock_crawlers[ $crawler ] ?? '0000-00-00T12:00:00.000000';
				} else {
					$last = '0000-00-00T12:00:00.000000';
				}
				$response = wp_remote_get( $url );
				if ( ! is_wp_error( $response ) ) {
					$json = json_decode( wp_remote_retrieve_body( $response ) );
					// var_dump($json->creationTime, $wimbblock_crawlers[$crawler]);
					// phpcs:ignore  WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					if ( $json->creationTime > $last ) {
						$mgt_code = $wimb_datatable->query(
							$wimb_datatable->prepare(
								'DELETE FROM %i WHERE crawler = %s',
								$wpdb_options['table_name'] . '_crawler',
								$crawler
							),
						);
						foreach ( $json->prefixes as $prefix ) {
							// phpcs:ignore  WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
							if ( isset( $prefix->ipv4Prefix ) ) {
								// https://stackoverflow.com/questions/4931721/getting-list-ips-from-cidr-notation-in-php
								// phpcs:ignore  WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
								list($network, $mask) = explode( '/', $prefix->ipv4Prefix );
								$begin                = long2ip( ( ip2long( $network ) ) & ( ( -1 << ( 32 - (int) $mask ) ) ) );
								$end                  = long2ip( ( ip2long( $network ) ) + pow( 2, ( 32 - (int) $mask ) ) - 1 );
								$mgt_code             = $wimb_datatable->query(
									$wimb_datatable->prepare(
										'INSERT INTO %i ( crawler, begin, int_begin, end, int_end ) VALUES ( %s,%s,%s,%s,%s )',
										$wpdb_options['table_name'] . '_crawler',
										$crawler,
										$begin,
										ip2long( $begin ),
										$end,
										ip2long( $end ),
									),
								);
							}
						}
						// phpcs:ignore  WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						$wimbblock_crawlers[ $crawler ] = $json->creationTime;
						update_option( 'wimbblock_crawlers', $wimbblock_crawlers );
						wimbblock_error_log( 'Crawler updated - ' . $crawler . ' * ' . $wimbblock_crawlers[ $crawler ] );
					}
				}
			}
		}
	}

	if ( $wimbblock_options['rotate'] === 'yes' ) {
		add_action( 'wimbblock_update_crawler_hook', 'wimbblock_update_crawlers' );
		if ( ! wp_next_scheduled( 'wimbblock_update_crawler_hook' ) ) {
			$wimbblock_datetime = new DateTime( 'tomorrow 00.15.00', new DateTimeZone( wp_timezone_string() ) );
			wp_schedule_event( $wimbblock_datetime->getTimestamp(), 'daily', 'wimbblock_update_crawler_hook' );
		}
	}

	// Set the initial version of the database schema
	function wimbblock_activate() {
		add_option( 'wimbblock_db_version', '260300' );
	}
	register_activation_hook( __FILE__, 'wimbblock_activate' );

	function wimbblock_update() {
		$options = wimbblock_get_options_db();
		if ( $options['error'] === '0' ) {
			$current_version = get_option( 'wimbblock_db_version', '260300' );
			$new_version     = '260311'; // Update this to your new version
			if ( version_compare( $current_version, $new_version, '<' ) ) {
				$options = wimbblock_get_options_db();
				wimbblock_table_install( $options['table_name'] ); // Call the migration function
				wimbblock_crawler_table_install( $options['table_name'] );
				update_option( 'wimbblock_db_version', $new_version ); // Update the version
			}
		}
	}
	add_action( 'plugins_loaded', 'wimbblock_update' );

}
