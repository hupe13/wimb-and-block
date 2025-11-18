<?php
/**
 * Manage table wimb entries
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_display_mgt_table( $wimbblock_table_name, $result ) {
	wp_enqueue_script(
		'sort_table_js',
		plugins_url(
			WIMB_NAME . '/admin/sort-table.js'
		),
		array(),
		'1',
		false
	);
	global $wimb_datatable;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}
	$versions = wimbblock_get_all_browsers();
	$command  = array();

	foreach ( $versions as $key => $value ) {
		$command[] = "software NOT LIKE '%" . $key . "%'";
	}

	$where = implode( ' AND ', $command );
	//var_dump( $where );

	$crawlers = array(
		'googlebot',
		'googleother',
		'BingBot',
		'http://yandex.com/bots',
		'Applebot',
		'MojeekBot',
		'Baiduspider',
		'SeznamBot',
	);
	$command  = array();
	foreach ( $crawlers as $crawler ) {
		$command[] = " browser NOT LIKE '%" . $crawler . "%' ";
	}
	$crawlers = implode( ' AND ', $command );

	$derivates = " AND browser NOT like '%Chrome/%' AND browser NOT like '%Firefox/%' ";

	$tablehdr = '<thead><tr><th>i</th>
	<th id="click" onclick="onColumnHeaderClicked(event)">browser</th>
	<th id="click" onclick="onColumnHeaderClicked(event)">software</th>
	<th id="click" onclick="onColumnHeaderClicked(event)">system</th>
	<th id="click" onclick="onColumnHeaderClicked(event)">time</th>
	<th>block</th>
	<th>unblock/block</th></tr></thead>';

	if ( is_array( $result ) ) {

		$command = array();
		foreach ( $result as $key => $value ) {
			$command[] = $key . " LIKE '%" . $value . "%'";
		}
		$query = implode( ' AND ', $command );
	} else {
		$query = $where . ' AND ' . $crawlers . $derivates;
	}
	$entries = $wimb_datatable->get_results(
		'SELECT i,browser,software,system,time,block FROM ' . $wimbblock_table_name . ' WHERE ' . $query . ' ORDER BY software, browser ASC'
	);

	// Make the data rows
	$rows      = array();
	$alternate = true;
	foreach ( $entries as $row ) {
		$row_vals = array();
		foreach ( $row as $key => $value ) {
			$row_vals[] = $value;
		}
		$class = '';
		if ( $row_vals[5] === '0' ) {
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

		$table  = '<tr' . $class . '><td class="center-text">' . join( '</td>' . "\n\r" . '<td class="center-text">', $row_vals ) . '</td>';
		$table .= '<td class="center-text"><input type="checkbox" name="' . $row_vals[0] . '" value="' . $row_vals[5] . '"/></td>';
		$table .= '</tr>';
		$rows[] = $table;
	}

	$tablebegin = "\n\r" . '<fieldset><legend><h3>' . __( 'Select to unblock / block', 'wimb-and-block' ) . ':</h3></legend>' . "\n\r";
	$tableend   = '</fieldset>';

	// Put the table together and output
	return $tablebegin . '<table id="mgttable" border=1>' . $tablehdr . '<tbody>' . join( $rows ) . '</tbody></table>' . $tableend;
}

function wimbblock_selection_table() {
	$wimbblock_wpdb_options = wimbblock_get_options_db();
	$wimbblock_table_name   = $wimbblock_wpdb_options['table_name'];

	echo esc_html__( 'You can search for entries here.', 'wimb-and-block' ) . ' ';
	printf(
		wp_kses_post(
			/* translators: %1$s and %2$s is a link. */
			__( 'By default, all entries are displayed, except those browsers from %1$sVersion Control%2$s.', 'wimb-and-block' )
		),
		'<a href="' . esc_url( '?page=' . WIMB_NAME . '&tab=blocking' ) . '">',
		'</a>'
	);
	echo ' ' . esc_html( __( "You can't unblock these either.", 'wimb-and-block' ) ) . '<br>';

	echo '<form method="post" action="options-general.php?page=' . esc_html( WIMB_NAME ) . '&tab=mgt">';
	if ( current_user_can( 'manage_options' ) ) {
		echo '<table class="form-table" role="presentation">';
		echo '<tr><th scope="row">';
		echo 'browser';
		echo '</th><td>';
		echo '<input type="text" size="15" name="browser" />';
		echo '</td></tr>';
		echo '<tr><th scope="row">';
		echo 'software';
		echo '</th><td>';
		echo '<input type="text" size="15" name="software" />';
		echo '</td></tr>';
		echo '<tr><th scope="row">';
		echo 'system';
		echo '</th><td>';
		echo '<input type="text" size="15" name="system" />';
		echo '</td></tr>';
		echo '</table>';

		wp_nonce_field( 'wimbblock_mgt', 'wimbblock_mgt_nonce' );
		submit_button( __( 'Search', 'wimb-and-block' ), 'primary', 'search' );
	}
	echo '</form>';

	if ( $wimbblock_wpdb_options['error'] === '0' ) {
		$result = wimbblock_handle_form();
		// var_dump($result); //wp_die();

		printf(
			wp_kses_post(
				/* translators: %1$s, %2$s and %3$s are column names. */
				esc_html__( 'You can sort the columns %1$s, %2$s, %3$s and %4$s.', 'wimb-and-block' ),
			),
			'<code>browser</code>',
			'<code>software</code>',
			'<code>system</code>',
			'<code>time</code>'
		);

		echo '<form method="post" action="options-general.php?page=' . esc_html( WIMB_NAME ) . '&tab=mgt">';
		if ( current_user_can( 'manage_options' ) ) {
			$allowed_html          = wp_kses_allowed_html( 'post' );
			$allowed_html['input'] = array(
				'type'  => array(),
				'name'  => array(),
				'value' => array(),
			);
			$allowed_html['th']    = array(
				'onclick' => array(),
				'id'      => array(),
			);
			echo wp_kses( wimbblock_display_mgt_table( $wimbblock_table_name, $result ), $allowed_html );
			wp_nonce_field( 'wimbblock_mgt', 'wimbblock_mgt_nonce' );
			submit_button( __( 'Unblock / block selected entries', 'wimb-and-block' ), 'primary', 'changeblock' );
		}
		echo '</form>';
	}
}

function wimbblock_handle_form() {
	$text = '';
	if ( ! empty( $_POST ) && check_admin_referer( 'wimbblock_mgt', 'wimbblock_mgt_nonce' ) ) {
		if ( isset( $_POST['changeblock'] ) ) {

			$entries = $_POST;
			unset( $entries['wimbblock_mgt_nonce'] );
			unset( $entries['_wp_http_referer'] );
			unset( $entries['changeblock'] );
			// echo '<pre>';var_dump($entries);echo '</pre>';wp_die('tot');
			$options = wimbblock_get_options_db();
			global $wimb_datatable;
			if ( is_null( $wimb_datatable ) ) {
				wimbblock_open_wpdb();
			}
			foreach ( $entries as $i => $block ) {
				//echo '<pre>';var_dump($i,$block);echo '</pre>';
				if ( $block === '0' ) {
					// block the entry
					$entries = $wimb_datatable->get_results(
						$wimb_datatable->prepare(
							'UPDATE %i SET time=time, block=1 WHERE i = %s',
							$options['table_name'],
							$i
						),
					);
				} else {
					// unblock the entry
					$entries = $wimb_datatable->get_results(
						$wimb_datatable->prepare(
							"UPDATE %i SET time=time,software=IF(software LIKE '%unknown%' OR software='','*',software), block=0 WHERE i = %s",
							$options['table_name'],
							$i
						),
					);
				}
			}
		}
		// wp_die("tot");
		if ( isset( $_POST['search'] ) ) {
			$entries = $_POST;
			unset( $entries['wimbblock_mgt_nonce'] );
			unset( $entries['_wp_http_referer'] );
			unset( $entries['search'] );
			return $entries;
		}
	}
	return $text;
}
