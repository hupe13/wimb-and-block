<?php
/**
 * Table wimb entries last 24 hours
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_daily_table() {
	echo '<h4>' . esc_html( __( 'Overview last 24 hours', 'wimb-and-block' ) ) . '</h4>';
	wimbblock_statistic24();
	echo '<h4>' . esc_html( __( 'Entries last 24 hours', 'wimb-and-block' ) ) . '</h4>';
	$wimbblock_wpdb_options = wimbblock_get_options_db();
	$wimbblock_table_name   = $wimbblock_wpdb_options['table_name'];
	if ( $wimbblock_wpdb_options['error'] === '0' ) {
		$allowed_html = wp_kses_allowed_html( 'post' );
		echo wp_kses( wimbblock_display_table( $wimbblock_table_name ), $allowed_html );
	}
}

function wimbblock_statistic24() {
	global $wimb_datatable;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}
	$wimbblock_wpdb_options  = wimbblock_get_options_db();
	$wimbblock_table_name    = $wimbblock_wpdb_options['table_name'];
	$wimbblock_datetime      = new DateTime( '-24 hours', new DateTimeZone( wp_timezone_string() ) );
	$wimbblock_selected_date = $wimbblock_datetime->format( 'Y-m-d H:i:s' );
	$wimbblock_wimbdate      = $wimbblock_datetime->format( 'ymd' );

	$wimbblock_entries = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			'SELECT COUNT(*) as alle FROM %i WHERE time >= %s',
			$wimbblock_table_name,
			$wimbblock_selected_date
		),
		ARRAY_A
	);
	$wimbblock_alle    = $wimbblock_entries[0]['alle'];

	$wimbblock_entries = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			'SELECT COUNT(*) as blocked FROM %i WHERE time >= %s AND block > 0',
			$wimbblock_table_name,
			$wimbblock_selected_date
		),
		ARRAY_A
	);
	$wimbblock_blocked = $wimbblock_entries[0]['blocked'];

	$wimbblock_entries = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			'SELECT COUNT(*) as robot FROM %i WHERE time >= %s AND robots > 0',
			$wimbblock_table_name,
			$wimbblock_selected_date
		),
		ARRAY_A
	);
	$wimbblock_robot   = $wimbblock_entries[0]['robot'];

	$wimbblock_entries    = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			'SELECT COUNT(*) as blockrobot FROM %i WHERE time >= %s AND robots > 0 and block > 0',
			$wimbblock_table_name,
			$wimbblock_selected_date
		),
		ARRAY_A
	);
	$wimbblock_blockrobot = $wimbblock_entries[0]['blockrobot'];

	$wimbblock_entries = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			'SELECT COUNT(*) as new FROM %i WHERE time >= %s AND ( wimbdate = %s OR wimbdate > %s )',
			$wimbblock_table_name,
			$wimbblock_selected_date,
			$wimbblock_wimbdate,
			$wimbblock_wimbdate
		),
		ARRAY_A
	);
	$wimbblock_new     = $wimbblock_entries[0]['new'];

	$wimbblock_entries    = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			'SELECT COUNT(*) as newblocked FROM %i WHERE time >= %s AND ( wimbdate = %s OR wimbdate > %s) AND block > 0',
			$wimbblock_table_name,
			$wimbblock_selected_date,
			$wimbblock_wimbdate,
			$wimbblock_wimbdate
		),
		ARRAY_A
	);
	$wimbblock_newblocked = $wimbblock_entries[0]['newblocked'];

	$entries = array(
		array(
			'month'               => __( 'last 24 hours', 'wimb-and-block' ),
			'count'               => $wimbblock_alle,
			'blocked'             => $wimbblock_blocked,
			'blocked in %'        => wimbblock_prozent( $wimbblock_blocked, $wimbblock_alle ),
			'robots'              => $wimbblock_robot,
			'blockrobot'          => $wimbblock_blockrobot,
			'robots blocked in %' => wimbblock_prozent( $wimbblock_blockrobot, $wimbblock_robot ),
			'todaycount'          => $wimbblock_new,
			'todayblocked'        => $wimbblock_newblocked,
			'todayblocked in %'   => wimbblock_prozent( $wimbblock_newblocked, $wimbblock_new ),
		),
	);

	$header                        = array();
	$header['month']               = '';
	$header['count']               = __( 'count', 'wimb-and-block' );
	$header['blocked']             = __( 'blocked', 'wimb-and-block' );
	$header['blocked in %']        = __( 'in %', 'wimb-and-block' );
	$header['robots']              = __( 'robots', 'wimb-and-block' );
	$header['blockrobot']          = __( 'blocked', 'wimb-and-block' );
	$header['robots blocked in %'] = __( 'in %', 'wimb-and-block' );
	$header['todaycount']          = __( 'new browsers', 'wimb-and-block' );
	$header['todayblocked']        = __( 'blocked', 'wimb-and-block' );
	$header['todayblocked in %']   = __( 'in %', 'wimb-and-block' );

	array_unshift( $entries, $header );
	echo '<p>';
	echo wp_kses_post( wimbblock_html_table( $entries ) );
	echo '</p>';
}

function wimbblock_display_table( $wimbblock_table_name ) {
	global $wimb_datatable;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}

	$colgroups = '
	<colgroup>
		<col /><col /><col /><col /><col /><col /><col /><col class="grey"/><col /><col  class="grey"/><col /><col  class="grey"/><col /><col  class="grey"/><col /><col  class="grey"/>
  </colgroup>';

	$tablehdr  = '<tr><th>&nbsp;</th>';
	$tablehdr .= '<th colspan=4>Browser Software</th>';
	$tablehdr .= '<th colspan=2>Time</th>';
	$tablehdr .= '<th colspan=1>&nbsp;</th>';
	$thismonth = wp_date( 'Y-m' );
	$tablehdr .= '<th colspan=2>' . wp_date( 'F', strtotime( $thismonth ) ) . '</th>';
	$tablehdr .= '<th colspan=2>' . wp_date( 'F', strtotime( $thismonth . ' - 1 month' ) ) . '</th>';
	$tablehdr .= '<th colspan=2>' . wp_date( 'F', strtotime( $thismonth . ' - 2 month' ) ) . '</th>';
	$tablehdr .= '<th colspan=2>' . wp_date( 'F', strtotime( $thismonth . ' - 3 month' ) ) . '</th></tr>';
	$tablehdr .= $colgroups;
	$tablehdr .= '<tr><th>i</th><th>Type</th><th>Software</th><th>System</th><th>Version</th><th>time</th><th>wimbdate</th><th>robots</th>';
	for ( $i = 1; $i <= 4; $i++ ) {
		$tablehdr .= '<th>count</th><th>blocked</th>';
	}
	$tablehdr .= '</tr>';
	$header    = '<thead>' . $tablehdr . '</thead>';

		$datetime      = new DateTime( '-24 hours', new DateTimeZone( wp_timezone_string() ) );
		$selected_date = $datetime->format( 'Y-m-d H:i:s' );
		// var_dump($selected_date);

		$entries = $wimb_datatable->get_results(
			$wimb_datatable->prepare(
				'SELECT * FROM %i WHERE time >= %s ORDER BY time DESC',
				$wimbblock_table_name,
				$selected_date
			),
			ARRAY_A
		);

	if ( $wimb_datatable->last_error ) {
		return esc_html__( 'There was an error:', 'wimb-and-block' ) . ' ' . $wimb_datatable->last_error;
	}

	// Make the data rows
	$rows      = array();
	$alternate = true;

	foreach ( $entries as $entry ) {
		$line = array();

		$line[] = $entry['i'];
		$line[] = $entry['browser'];
		$line[] = $entry['software'];
		$line[] = $entry['system'];
		$line[] = $entry['version'];
		$line[] = $entry['time'];
		$line[] = $entry['wimbdate'];
		$line[] = $entry['robots'];
		$line[] = $entry['count'];
		$line[] = $entry['block'];
		$line[] = $entry['count_1'];
		$line[] = $entry['block_1'];
		$line[] = $entry['count_2'];
		$line[] = $entry['block_2'];
		$line[] = $entry['count_3'];
		$line[] = $entry['block_3'];

		$class = '';
		// var_dump($row_vals); wp_die('tot');
		if ( $entry['block'] === '0' && $entry['block_1'] === '0' && $entry['block_2'] === '0' && $entry['block_3'] === '0' ) {
			if ( $alternate ) {
				$alternate = false;
				$class     = ' class="greenw04"';
			} else {
				$alternate = true;
				$class     = ' class="greenw02"';
			}
		} elseif ( $entry['robots'] > 0 && ( $entry['block'] > 1 || $entry['block_1'] > 1 || $entry['block_2'] > 1 || $entry['block_3'] > 1 ) ) {
			if ( $alternate ) {
				$alternate = false;
				$class     = ' class="red04"';
			} else {
				$alternate = true;
				$class     = ' class="red02"';
			}
		} elseif ( $alternate ) {
				$alternate = false;
				$class     = ' class="orangew04"';
		} else {
			$alternate = true;
			$class     = ' class="orangew02"';
		}

		$table  = '<tr' . $class . '>
		<td class="center-text">' . join( '</td><td class="center-text">', $line ) . '</td>';
		$table .= '</tr>';
		$rows[] = $table;
	}

	// Put the table together and output
	return '<table border=1>' . $header . '<tbody>' . join( $rows ) . '</tbody></table>';
}
