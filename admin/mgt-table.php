<?php
/**
 * Manage table wimb entries
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_display_mgt_table( $table_name, $result ) {
	global $wimb_datatable;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}
	$checking = wimbblock_get_all_browsers();
	$command  = array();

	foreach ( $checking as $key => $value ) {
		$command[] = "software NOT LIKE '%" . $key . "%'";
	}

	$where = implode( ' AND ', $command );
	//var_dump( $where );

	$tablehdr = '<thead><tr><th>i</th><th>browser</th><th>software</th><th>block</th><th>unblock/block</th></tr></thead>';

	if ( is_array( $result ) ) {
		$command = array();
		foreach ( $result as $key => $value ) {
			$command[] = $key . " LIKE '%" . $value . "%'";
		}
		$query = implode( ' AND ', $command );
	} else {
		$query = $where . ' AND ( system = ' . "''" . ' AND block > 0 ) OR software = "*" ';
	}
	$entries = $wimb_datatable->get_results(
		'SELECT i,browser,software,block FROM ' . $table_name . ' WHERE ' . $query . ' ORDER BY time DESC'
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
		if ( $row_vals[3] === '0' ) {
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

		$table  = '<tr' . $class . '><td class="center-text">' . join( '</td><td class="center-text">', $row_vals ) . '</td>';
		$table .= '<td class="center-text"><input type="checkbox" name="' . $row_vals[0] . '" value="' . $row_vals[3] . '"/></td>';
		$table .= '</tr>';
		$rows[] = $table;
	}

	$tablebegin = '<fieldset><legend><h3>' . __( 'Select to unblock / block', 'wimb-and-block' ) . ':</h3></legend>';
	$tableend   = '</fieldset>';

	// Put the table together and output
	return $tablebegin . '<table border=1>' . $tablehdr . '<tbody>' . join( $rows ) . '</tbody></table>' . $tableend;
}

function wimbblock_selection_table() {
	$wpdb_options = wimbblock_get_options_db();
	$table_name   = $wpdb_options['table_name'];

	echo esc_html__( 'You can search for entries here.', 'wimb-and-block' ) . ' ';
	printf(
		/* translators: %1$s is "unknown" and %2$s is "simple software string". */
		wp_kses_post( __( 'By default, all blocked entries with an empty or %1$s in the %2$s (software) are displayed.', 'wimb-and-block' ) ),
		'"unknown"',
		'<code>simple software string</code>'
	);

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
		echo '</table>';

		wp_nonce_field( 'wimbblock_mgt', 'wimbblock_mgt_nonce' );
		submit_button( __( 'Search', 'wimb-and-block' ), 'primary', 'search' );
	}
	echo '</form>';

	$result = wimbblock_handle_form();

	// var_dump($result); //wp_die();
	echo '<form method="post" action="options-general.php?page=' . esc_html( WIMB_NAME ) . '&tab=mgt">';
	if ( current_user_can( 'manage_options' ) ) {
		// echo $text;
		$allowed_html          = wp_kses_allowed_html( 'post' );
		$allowed_html['input'] = array(
			'type'  => array(),
			'name'  => array(),
			'value' => array(),
		);
		echo wp_kses( wimbblock_display_mgt_table( $table_name, $result ), $allowed_html );
		wp_nonce_field( 'wimbblock_mgt', 'wimbblock_mgt_nonce' );
		submit_button( __( 'Unblock / block selected entries', 'wimb-and-block' ), 'primary', 'changeblock' );
	}
	echo '</form>';
}

function wimbblock_handle_form() {
	$text = '';
	if ( ! empty( $_POST ) && check_admin_referer( 'wimbblock_mgt', 'wimbblock_mgt_nonce' ) ) {
		if ( isset( $_POST['changeblock'] ) ) {
			$entries = $_POST;
			unset( $entries['wimbblock_mgt_nonce'] );
			unset( $entries['_wp_http_referer'] );
			unset( $entries['changeblock'] );

			$options = wimbblock_get_options_db();
			global $wimb_datatable;
			if ( is_null( $wimb_datatable ) ) {
				wimbblock_open_wpdb();
			}
			foreach ( $entries as $i => $block ) {
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
