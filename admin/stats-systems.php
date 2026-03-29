<?php
/**
 * Statistics about systems
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_stats_systems() {

	global $wimb_datatable;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}
	$wimbblock_wpdb_options = wimbblock_get_options_db();
	$wimbblock_table_name   = $wimbblock_wpdb_options['table_name'];

	$gesamt       = array( 'Sum', 0, 0, 0 );
	$tableentries = array();

	$allsystems = array(
		'Android',
		'Linux',
		'Windows',
		'Mac OS X',
		'macOS',
	);
	$systems    = array(
		'Android',
		'Linux',
		'Windows',
	);
	// Systems
	foreach ( $systems as $system ) {
		$wimbblock_entries = $wimb_datatable->get_results(
			$wimb_datatable->prepare(
				'SELECT COUNT(*) as anzahl FROM %i WHERE system LIKE %s',
				$wimbblock_table_name,
				$system . '%'
			),
			ARRAY_A
		);
		$blocked           = $wimb_datatable->get_results(
			$wimb_datatable->prepare(
				'SELECT COUNT(*) as anzahl FROM %i WHERE system LIKE %s AND block > 0',
				$wimbblock_table_name,
				$system . '%'
			),
			ARRAY_A
		);
		$tableentries[]    = array(
			$system,
			$wimbblock_entries[0]['anzahl'],
			$blocked[0]['anzahl'],
			wimbblock_prozent( $blocked[0]['anzahl'], $wimbblock_entries[0]['anzahl'] ),
		);
		$gesamt[1]        += $wimbblock_entries[0]['anzahl'];
		$gesamt[2]        += $blocked[0]['anzahl'];
	}

	// MacOS and Mac OS X
	$wimbblock_entries = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			'SELECT COUNT(*) as anzahl FROM %i WHERE system LIKE %s OR system LIKE %s',
			$wimbblock_table_name,
			'Mac OS X%',
			'macOS%'
		),
		ARRAY_A
	);
	$blocked           = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			'SELECT COUNT(*) as anzahl FROM %i WHERE ( system LIKE %s OR system LIKE %s ) AND block > 0',
			$wimbblock_table_name,
			'Mac OS X%',
			'macOS%'
		),
		ARRAY_A
	);

	$tableentries[] = array(
		'MacOS',
		$wimbblock_entries[0]['anzahl'],
		$blocked[0]['anzahl'],
		wimbblock_prozent( $blocked[0]['anzahl'], $wimbblock_entries[0]['anzahl'] ),
	);
	$gesamt[1]     += $wimbblock_entries[0]['anzahl'];
	$gesamt[2]     += $blocked[0]['anzahl'];
	$gesamt[3]      = wimbblock_prozent( $gesamt[2], $gesamt[1] );

	// all other systems
	$command = array();
	foreach ( $allsystems as $system ) {
		$command[] = ' system' . " NOT LIKE '" . $system . "%' ";
	}
	$query = implode( ' AND ', $command );

	$wimbblock_entries = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			'SELECT COUNT(*) as anzahl FROM %i WHERE ' . $query . "  AND system != ''",
			$wimbblock_table_name
		),
		ARRAY_A
	);
	$blocked           = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			'SELECT COUNT(*) as anzahl FROM %i WHERE ' . $query . "  AND system != ''" . ' AND block > 0',
			$wimbblock_table_name
		),
		ARRAY_A
	);

	$tableentries[] = array(
		'other',
		$wimbblock_entries[0]['anzahl'],
		$blocked[0]['anzahl'],
		wimbblock_prozent( $blocked[0]['anzahl'], $wimbblock_entries[0]['anzahl'] ),
	);
	$gesamt[1]     += $wimbblock_entries[0]['anzahl'];
	$gesamt[2]     += $blocked[0]['anzahl'];
	$gesamt[3]      = wimbblock_prozent( $gesamt[2], $gesamt[1] );

	// no system
	$wimbblock_entries = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			"SELECT COUNT(*) as anzahl FROM %i WHERE system = ''",
			$wimbblock_table_name
		),
		ARRAY_A
	);
	$blocked           = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			"SELECT COUNT(*) as anzahl FROM %i WHERE system = '' AND block > 0",
			$wimbblock_table_name
		),
		ARRAY_A
	);

	$tableentries[] = array(
		'no system',
		$wimbblock_entries[0]['anzahl'],
		$blocked[0]['anzahl'],
		wimbblock_prozent( $blocked[0]['anzahl'], $wimbblock_entries[0]['anzahl'] ),
	);

	$gesamt[1] += $wimbblock_entries[0]['anzahl'];
	$gesamt[2] += $blocked[0]['anzahl'];
	$gesamt[3]  = wimbblock_prozent( $gesamt[2], $gesamt[1] );

	// $tableentries[] = $gesamt;

	$entries  = array();
	$absolute = array();
	foreach ( $tableentries as $entry ) {
		array_splice( $entry, 2, 0, wimbblock_prozent( $entry[1], $gesamt[1] ) );
		$entries[]  = $entry;
		$absolute[] = $entry[1];
	}
	array_multisort( $absolute, SORT_NUMERIC, SORT_DESC, $entries );
	$header = array(
		__( 'System', 'wimb-and-block' ),
		__( 'count total', 'wimb-and-block' ),
		__( 'total share in %', 'wimb-and-block' ),
		__( 'blocked', 'wimb-and-block' ),
		__( 'blocked in %', 'wimb-and-block' ),
	);
	array_unshift( $entries, $header );
	return wimbblock_html_table( $entries );
}
