<?php
/**
 * Manage table wimb entries
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_statistic_month() {
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
		$date1     = date_create( wp_date( 'Y-m-01' ) );
		$date2     = date_create( wp_date( 'Y-m-01', strtotime( $wimbblock_entries[0]['time'] ) ) );
		$diff      = date_diff( $date1, $date2 );
		$thismonth = wp_date( 'Y-m' );
		$months    = range( 0, $diff->format( '%m' ) );
		$entries   = array();

		foreach ( $months as $month ) {
			$entries[ $month ]['month']        = wp_date( 'F', strtotime( $thismonth . ' - ' . $month . ' month' ) );
			$search_date                       = wp_date( 'Y-m-', strtotime( $thismonth . ' - ' . $month . ' month' ) );
			$wimbblock_entries                 = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT COUNT(*) as count FROM %i WHERE time LIKE %s',
					$wimbblock_table_name,
					$search_date . '%'
				),
				ARRAY_A
			);
			$entries[ $month ]['count']        = $wimbblock_entries[0]['count'];
			$wimbblock_entries                 = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT COUNT(*) as blocked FROM %i WHERE time LIKE %s AND block > 0',
					$wimbblock_table_name,
					$search_date . '%'
				),
				ARRAY_A
			);
			$entries[ $month ]['blocked']      = $wimbblock_entries[0]['blocked'];
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
		$header['month']               = __( 'month', 'wimb-and-block' );
		$header['count']               = __( 'count', 'wimb-and-block' );
		$header['blocked']             = __( 'blocked', 'wimb-and-block' );
		$header['blocked in %']        = __( 'blocked in %', 'wimb-and-block' );
		$header['robots']              = __( 'robots', 'wimb-and-block' );
		$header['robots blocked in %'] = __( 'robots blocked in %', 'wimb-and-block' );
		$header['blockrobot']          = __( 'robots blocked', 'wimb-and-block' );

		array_unshift( $entries, $header );
		return wimbblock_html_table( $entries );
	} else {
		return '';
	}
}

function wimbblock_statistic_new_month() {
	global $wimb_datatable;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}
	$wimbblock_wpdb_options = wimbblock_get_options_db();
	$wimbblock_table_name   = $wimbblock_wpdb_options['table_name'];

	$wimbblock_entries = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			"SELECT wimbdate FROM %i WHERE wimbdate != '' ORDER BY wimbdate ASC limit 1",
			$wimbblock_table_name
		),
		ARRAY_A
	);

	if ( count( $wimbblock_entries ) > 0 ) {
		$date1 = date_create( wp_date( 'Y-m-01' ) );
		$date2 = date_create( wp_date( 'Y-m-01', strtotime( '01.' . substr( $wimbblock_entries[0]['wimbdate'], 2, 2 ) . '.20' . substr( $wimbblock_entries[0]['wimbdate'], 0, 2 ) ) ) );
		$diff  = date_diff( $date1, $date2 );

		$thismonth = wp_date( 'Y-m' );
		$months    = range( 0, $diff->format( '%m' ) );
		$entries   = array();

		foreach ( $months as $month ) {
			$entries[ $month ]['month']   = wp_date( 'F', strtotime( $thismonth . ' - ' . $month . ' month' ) );
			$search_date                  = wp_date( 'ym', strtotime( $thismonth . ' - ' . $month . ' month' ) );
			$wimbblock_entries            = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT COUNT(*) as count FROM %i WHERE wimbdate LIKE %s',
					$wimbblock_table_name,
					$search_date . '%'
				),
				ARRAY_A
			);
			$entries[ $month ]['count']   = $wimbblock_entries[0]['count'];
			$wimbblock_entries            = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT COUNT(*) as blocked FROM %i WHERE wimbdate LIKE %s AND block > 0',
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
					'SELECT COUNT(*) as robots FROM %i WHERE wimbdate LIKE %s AND robots > 0',
					$wimbblock_table_name,
					$search_date . '%'
				),
				ARRAY_A
			);
			$entries[ $month ]['robots'] = $wimbblock_entries[0]['robots'];

			$wimbblock_entries               = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT COUNT(*) as blockrobot FROM %i WHERE wimbdate LIKE %s AND robots > 0 AND block > 0',
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
		$header['month']               = __( 'month', 'wimb-and-block' );
		$header['count']               = __( 'count', 'wimb-and-block' );
		$header['blocked']             = __( 'blocked', 'wimb-and-block' );
		$header['blocked in %']        = __( 'blocked in %', 'wimb-and-block' );
		$header['robots']              = __( 'robots', 'wimb-and-block' );
		$header['robots blocked in %'] = __( 'robots blocked in %', 'wimb-and-block' );
		$header['blockrobot']          = __( 'robots blocked', 'wimb-and-block' );

		array_unshift( $entries, $header );
		return wimbblock_html_table( $entries );
	} else {
		return '';
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
	$head = '<div style="width:80%;">';
	$head = $head . '<figure class="wp-block-table aligncenter is-style-stripes"><table border=1>';
	return $head . implode( '', $rows ) . '</table></figure></div>';
}

function wimbblock_prozent( $teil, $gesamt ) {
	return $gesamt > 0 ? number_format( $teil * 100 / $gesamt, 0, '', '' ) . '%' : '';
}

function wimbblock_delete_month() {
	if ( ! empty( $_POST ) && check_admin_referer( 'wimbblock_month', 'wimbblock_month_nonce' ) ) {
		global $wimb_datatable;
		if ( is_null( $wimb_datatable ) ) {
			wimbblock_open_wpdb();
		}
		$wimbblock_wpdb_options = wimbblock_get_options_db();
		$wimbblock_table_name   = $wimbblock_wpdb_options['table_name'];
		$wimbblock_entries      = $wimb_datatable->get_results(
			$wimb_datatable->prepare(
				'SELECT time FROM %i ORDER BY time ASC limit 1',
				$wimbblock_table_name
			),
			ARRAY_A
		);

		if ( count( $wimbblock_entries ) > 0 ) {
			$search_date       = substr( $wimbblock_entries[0]['time'], 0, 8 );
			$wimbblock_entries = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'DELETE FROM %i WHERE time LIKE %s',
					$wimbblock_table_name,
					$search_date . '%'
				),
				ARRAY_A
			);
		}
	}
}
