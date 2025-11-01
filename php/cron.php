<?php
/**
 * Functions cron
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

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
add_action( 'wimbblock_rotate_hook', 'wimbblock_rotate_table' );

if ( ! wp_next_scheduled( 'wimbblock_rotate_hook' ) ) {
	wp_schedule_event( time(), 'daily', 'wimbblock_rotate_hook' );
}
