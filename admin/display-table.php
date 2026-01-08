<?php
/**
 * Table wimb entries
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_display_table( $wimbblock_table_name ) {
	require_once __DIR__ . '/statistics24.php';

	$all_rows = filter_input(
		INPUT_GET,
		'all_rows',
		FILTER_VALIDATE_BOOL
	);
	$all_rows = isset( $all_rows ) && $all_rows === true ? true : false;

	echo '<form>';
	echo '<input type="hidden" name="page" value="' . esc_html( WIMBBLOCK_NAME ) . '" />';
	echo '<input type="hidden" name="tab" value="table" />';
	echo '<input type="radio" name="all_rows" value="0" ';
	checked( ! ( $all_rows === true ) );
	echo '> ' . esc_html__( 'entries from the last 24 hours', 'wimb-and-block' ) . ' &nbsp;&nbsp; ';
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

	if ( $all_rows === false ) {
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
	} else {
		$entries = $wimb_datatable->get_results(
			$wimb_datatable->prepare(
				'SELECT * FROM %i ORDER BY time DESC',
				$wimbblock_table_name
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

function wimbblock_mgt_table() {
	$wimbblock_wpdb_options = wimbblock_get_options_db();
	$wimbblock_table_name   = $wimbblock_wpdb_options['table_name'];
	if ( $wimbblock_wpdb_options['error'] === '0' ) {

		wp_enqueue_style(
			'wimbblock-css',
			plugins_url( dirname( WIMBBLOCK_BASENAME ) . '/admin/admin.css' ),
			array(),
			1
		);

		$allowed_html = wp_kses_allowed_html( 'post' );
		echo wp_kses( wimbblock_display_table( $wimbblock_table_name ), $allowed_html );
	}
}
