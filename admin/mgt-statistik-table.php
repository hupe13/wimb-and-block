<?php
/**
 * Manage table wimb entries
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

echo '<h3>' . esc_html( __( 'WIMB Table Statistics', 'wimb-and-block' ) ) . '</h3>';

require_once __DIR__ . '/statistics-all.php';
// require_once __DIR__ . '/statistics24.php';
wimbblock_stistic_table();

function wimbblock_stistic_table() {
	global $wimb_datatable;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}
	$wimbblock_wpdb_options = wimbblock_get_options_db();
	$wimbblock_table_name   = $wimbblock_wpdb_options['table_name'];

	$wimbblock_entries = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			'SELECT time FROM %i ORDER BY time ASC limit 1',
			$wimbblock_table_name
		),
		ARRAY_A
	);

	if ( count( $wimbblock_entries ) > 0 ) {
		$date1 = date_create( wp_date( 'Y-m-01' ) );
		$date2 = date_create( wp_date( 'Y-m-01', strtotime( $wimbblock_entries[0]['time'] ) ) );
		$diff  = date_diff( $date1, $date2 );

		$thismonth   = wp_date( 'Y-m' );
		$search_date = wp_date( 'Y-m-d H:i:s', strtotime( '- 24 hours' ) );
		$months      = range( 0, $diff->format( '%m' ) );
		$entries     = array();

		foreach ( $months as $month ) {
			$entries[ $month ]['month'] = wp_date( 'F', strtotime( $thismonth . ' - ' . $month . ' month' ) );
			$search_date                = wp_date( 'Y-m-', strtotime( $thismonth . ' - ' . $month . ' month' ) );
			$wimbblock_entries          = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT COUNT(*) as count FROM %i WHERE time LIKE %s',
					$wimbblock_table_name,
					$search_date . '%'
				),
				ARRAY_A
			);
			$entries[ $month ]['count'] = $wimbblock_entries[0]['count'];
			//
			$wimbblock_entries            = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT COUNT(*) as blocked FROM %i WHERE time LIKE %s AND block > 0',
					$wimbblock_table_name,
					$search_date . '%'
				),
				ARRAY_A
			);
			$entries[ $month ]['blocked'] = $wimbblock_entries[0]['blocked'];

			$entries[ $month ]['blocked in %'] =
			wimbblock_prozent( $entries[ $month ]['blocked'], $entries[ $month ]['count'] );

			$wimbblock_entries           = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT COUNT(*) as robots FROM %i WHERE time LIKE %s AND robots > 0',
					$wimbblock_table_name,
					$search_date . '%'
				),
				ARRAY_A
			);
			$entries[ $month ]['robots'] = $wimbblock_entries[0]['robots'];

			$wimbblock_entries               = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT COUNT(*) as blockrobot FROM %i WHERE time LIKE %s AND robots > 0 AND block > 0',
					$wimbblock_table_name,
					$search_date . '%'
				),
				ARRAY_A
			);
			$entries[ $month ]['blockrobot'] = $wimbblock_entries[0]['blockrobot'];

			$entries[ $month ]['robots blocked in %'] =
			wimbblock_prozent( $entries[ $month ]['blockrobot'], $entries[ $month ]['robots'] );

		}

		$header = array_fill_keys( array_keys( $entries[0] ), '' );
		foreach ( $header as $key => $value ) {
			$header[ $key ] = $key;
		}
		$header['month']      = __( 'month', 'wimb-and-block' );
		$header['count']      = __( 'count', 'wimb-and-block' );
		$header['blockrobot'] = __( 'robots blocked', 'wimb-and-block' );

		array_unshift( $entries, $header );
		echo wp_kses_post( wimbblock_html_table( $entries ) );
	}
}

// Display array as table
function wimbblock_html_table( $data = array() ) {
	$rows      = array();
	$cellstyle = ( is_singular() || is_archive() ) ? "style='border:1px solid #195b7a;'" : '';
	foreach ( $data as $row ) {
		$cells = array();
		foreach ( $row as $cell ) {
			$cells[] = '<td ' . $cellstyle . ' align="center">' . "{$cell}</td>";
		}
		$rows[] = '<tr>' . implode( '', $cells ) . '</tr>' . "\n";
	}
	$head = '<div style="width:' . ( ( is_singular() || is_archive() ) ? '100' : '80' ) . '%;">';
	$head = $head . '<figure class="wp-block-table aligncenter is-style-stripes"><table border=1>';
	return $head . implode( '', $rows ) . '</table></figure></div>';
}

function wimbblock_prozent( $teil, $gesamt ) {
	return $gesamt > 0 ? number_format( $teil * 100 / $gesamt, 0, '', '' ) . '%' : '';
}
