<?php
/**
 * Table wimb entries
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_display_table( $table_name ) {

	$all_rows = filter_input(
		INPUT_GET,
		'all_rows',
		FILTER_VALIDATE_BOOL
	);
	$all_rows = isset( $all_rows ) && $all_rows === true ? true : false;

	echo '<form>';
	echo '<input type="hidden" name="page" value="wimb-and-block" />';
	echo '<input type="hidden" name="tab" value="table" />';
	echo '<input type="radio" name="all_rows" value="0" ';
	checked( ! ( $all_rows === true ) );
	echo '> ' . esc_html__( 'only entries from last 2 days', 'wimb-and-block' ) . ' &nbsp;&nbsp; ';
	echo '<input type="radio" name="all_rows" value="1" ';
	checked( $all_rows === true );
	echo '> ' . esc_html__( 'all entries', 'wimb-and-block' );
	wp_nonce_field( 'wimb-and-block', 'wimb_and_blockt_nonce' );
	submit_button( __( 'Change view', 'wimb-and-block' ), 'primary', 'changeview' );
	echo '</form>';

	global $wimb_datatable;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}

	$cycle = 'month';

	$tablehdr  = '<tr><th>&nbsp;</th>';
	$tablehdr .= '<th colspan=4>Browser Software</th>';
	$tablehdr .= '<th colspan=3>Time</th>';
	$tablehdr .= '<th colspan=2>this month</th>';
	$tablehdr .= '<th colspan=2>month before</th>';
	$tablehdr .= '<th colspan=2>2 months before</th>';
	$tablehdr .= '<th colspan=2>3 months before</th></tr>';
	$tablehdr .= '<tr><th>i</th><th>Type</th><th>Software</th><th>System</th><th>Version</th><th>time</th><th>yymm</th><th>wimbdate</th>';
	for ( $i = 1; $i <= 4; $i++ ) {
		$tablehdr .= '<th>count</th><th>blocked</th>';
	}
	$tablehdr .= '</tr>';
	$header    = '<thead>' . $tablehdr . '</thead>';

	if ( $all_rows === false ) {
		$datetime      = new DateTime( 'yesterday', new DateTimeZone( wp_timezone_string() ) );
		$selected_date = $datetime->format( 'Y-m-d 00:00:00' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$entries = $wimb_datatable->get_results(
			$wimb_datatable->prepare(
				'SELECT * FROM %i WHERE time >= %s ORDER BY time DESC',
				$table_name,
				$selected_date
			),
			ARRAY_A
		);
	} else {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$entries = $wimb_datatable->get_results(
			$wimb_datatable->prepare(
				'SELECT * FROM %i ORDER BY time DESC',
				$table_name
			),
			ARRAY_A
		);
	}

	if ( $wimb_datatable->last_error ) {
		return esc_html__( 'There was an error:', 'wimb-and-block' ) . ' ' . $wimb_datatable->last_error;
	}

	// Make the data rows
	$rows      = array();
	$alternate = true;
	$countrow  = 9;
	foreach ( $entries as $row ) {
		$row_vals = array();
		foreach ( $row as $key => $value ) {
			$row_vals[] = $value;
		}
		$class = '';
		// var_dump($row_vals); wp_die('tot');
		if ( $row_vals[ $countrow ] === '0' && $row_vals[ $countrow + 2 ] === '0' && $row_vals[ $countrow + 4 ] === '0' && $row_vals[ $countrow + 6 ] === '0' ) {
			if ( $alternate ) {
				$alternate = false;
				$class     = ' class="greenw04"';
			} else {
				$alternate = true;
				$class     = ' class="greenw02"';
			}
		} elseif ( $alternate ) {
				$alternate = false;
				$class     = ' class="orangew04"';
		} else {
			$alternate = true;
			$class     = ' class="orangew02"';
		}

		$table  = '<tr' . $class . '>
		<td style="text-align: center;">' . join( '</td><td style="text-align: center;">', $row_vals ) . '</td>';
		$table .= '</tr>';
		$rows[] = $table;
	}

	// Put the table together and output
	return '<table border=1>' . $header . '<tbody>' . join( $rows ) . '</tbody></table>';
}


function wimbblock_mgt_table() {
	$wpdb_options = wimbblock_get_options();
	$table_name   = $wpdb_options['table_name'];

	wp_enqueue_style(
		'wimbblock-css',
		plugins_url( dirname( WIMB_BASENAME ) . '/admin/admin.css' ),
		array(),
		1
	);

	$allowed_html = wp_kses_allowed_html( 'post' );
	echo wp_kses( wimbblock_display_table( $table_name ), $allowed_html );
}
