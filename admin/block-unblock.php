<?php
/**
 * Manage table wimb entries block / unblock
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_block_unblock_main() {
	$wimbblock_wpdb_options = wimbblock_get_options_db();
	$wimbblock_table_name   = $wimbblock_wpdb_options['table_name'];

	if ( $wimbblock_wpdb_options['error'] === '0' ) {
		$result = null;
		$nonce  = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) );
		if ( wp_verify_nonce( $nonce, 'name' ) ) {
			$name = sanitize_text_field( wp_unslash( $_GET['name'] ?? '' ) );
			if ( $name !== '' ) {
				$search = sanitize_text_field( wp_unslash( $_GET['search'] ?? '' ) );
				if ( $search !== '' ) {
					$result = wimbblock_handle_get( $search );
				}
			}
		}
		if ( is_null( $result ) ) {
			$result = wimbblock_handle_post();
		}
		if ( ! is_null( $result ) && is_array( $result ) && count( $result ) > 0 ) {
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

			echo '<form method="post" action="options-general.php?page=' . esc_html( WIMBBLOCK_NAME ) . '&tab=block">';
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

				echo wp_kses( wimbblock_block_unblock_table( $wimbblock_table_name, $result ), $allowed_html );
				wp_nonce_field( 'wimbblock_mgt', 'wimbblock_mgt_nonce' );
				submit_button( __( 'Unblock / block selected entries', 'wimb-and-block' ), 'primary', 'changeblock' );
			}
			echo '</form>';
		}
		echo '<div class="wimbbox">';

		$nonce = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) );
		if ( wp_verify_nonce( $nonce, 'link' ) ) {
			$type = sanitize_text_field( wp_unslash( $_GET['type'] ?? '' ) );
			switch ( $type ) {
				case 'C':
					wimbblock_derivates_table( 'C' );
					break;
				case 'F':
					wimbblock_derivates_table( 'F' );
					break;
				case 'S':
					wimbblock_searchengines_table();
					break;
				case 'O':
					wimbblock_other_table();
					break;
				default:
					wimbblock_derivates_table();
					wimbblock_other_table();
					wimbblock_searchengines_table();
			}
		}
		echo '</div>';
	}

	echo '<h3>' . esc_html__( 'Search entries', 'wimb-and-block' ) . '</h3>';
	echo esc_html__( 'You can search for entries here.', 'wimb-and-block' ) . ' ';
	echo esc_html__( 'Or you can click on a link from one of the tables.', 'wimb-and-block' ) . ' ';

	printf(
		wp_kses_post(
			/* translators: %1$s and %2$s is a link. */
			__( 'You can unblock all blocked entries. However, it is possible that they will be blocked again based on the settings for %1$sVersion Control%2$s.', 'wimb-and-block' )
		),
		'<a href="' . esc_url( '?page=' . WIMBBLOCK_NAME . '&tab=versions' ) . '">',
		'</a>'
	);
	echo '<br>';

	echo '<form method="post" action="options-general.php?page=' . esc_html( WIMBBLOCK_NAME ) . '&tab=block">';
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

	echo '<h3>' . esc_html__( 'Tables', 'wimb-and-block' ) . '</h3>';

	$liste  = '<ul><li class="adminli">';
	$liste .= '<a href="' .
	wp_nonce_url( '?page=' . WIMBBLOCK_NAME . '&tab=block&type=C', 'link' ) . '">Chrome ' .
	__( 'based browsers', 'wimb-and-block' ) . '</a>' . "\n";
	$liste .= '</li><li class="adminli">';
	$liste .= '<a href="' .
	wp_nonce_url( '?page=' . WIMBBLOCK_NAME . '&tab=block&type=F', 'link' ) . '">Firefox ' .
	__( 'based browsers', 'wimb-and-block' ) . '</a>' . "\n";
	$liste .= '</li><li class="adminli">';
	$liste .= '<a href="' .
	wp_nonce_url( '?page=' . WIMBBLOCK_NAME . '&tab=block&type=S', 'link' ) . '">' .
	__( 'Search Engines', 'wimb-and-block' ) . '</a> (' .
	__( 'which are verified by the plugin', 'wimb-and-block' ) . ')'
	. "\n";
	$liste .= '</li><li class="adminli">';
	$liste .= '<a href="' .
	wp_nonce_url( '?page=' . WIMBBLOCK_NAME . '&tab=block&type=O', 'link' ) . '">' .
	__( 'All other browsers', 'wimb-and-block' ) . '</a>' . "\n";
	$liste .= '</li><li class="adminli">';
	$liste .= '<a href="' .
	wp_nonce_url( '?page=' . WIMBBLOCK_NAME . '&tab=block&type=A', 'link' ) . '">' .
	__( 'All browsers', 'wimb-and-block' ) . '</a>' . "\n";
	$liste .= '</li></ul>';
	echo wp_kses_post( $liste );
}

function wimbblock_block_unblock_table( $wimbblock_table_name, $entries ) {
	// var_dump($entries);
	wp_enqueue_script(
		'sort_table_js',
		plugins_url(
			WIMBBLOCK_NAME . '/admin/sort-table.js'
		),
		array(),
		'1',
		false
	);

	$tablehdr = '<thead><tr><th>i</th>
	<th id="click" onclick="onColumnHeaderClickedChar(event)">browser</th>
	<th id="click" onclick="onColumnHeaderClickedChar(event)">software</th>
	<th id="click" onclick="onColumnHeaderClickedChar(event)">system</th>
	<th id="click" onclick="onColumnHeaderClickedChar(event)">time</th>
	<th>block</th>
	<th>unblock/block</th></tr></thead>';

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

function wimbblock_handle_post() {
	if ( ! empty( $_POST ) && check_admin_referer( 'wimbblock_mgt', 'wimbblock_mgt_nonce' ) ) {
		$options = wimbblock_get_options_db();
		global $wimb_datatable;
		if ( is_null( $wimb_datatable ) ) {
			wimbblock_open_wpdb();
		}
		if ( isset( $_POST['changeblock'] ) ) {
			$entries = $_POST;
			unset( $entries['wimbblock_mgt_nonce'] );
			unset( $entries['_wp_http_referer'] );
			unset( $entries['changeblock'] );
			// echo '<pre>';var_dump($entries);echo '</pre>';wp_die('tot');

			$command = array();
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
				$command[] = "i='" . $i . "'";
			}
			$query = implode( ' OR ', $command );

			$entries = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT i,browser,software,system,time,block FROM %i WHERE ' . $query,
					$options['table_name'],
				),
			);
			return $entries;
		}

		if ( isset( $_POST['search'] ) ) {
			$entries = $_POST;
			unset( $entries['wimbblock_mgt_nonce'] );
			unset( $entries['_wp_http_referer'] );
			unset( $entries['search'] );

			$command = array();
			foreach ( $entries as $key => $value ) {
				// escape: %d (integer), %f (float), %s (string), %i (identifier, e.g. table/field names)
				$to_escapes = array( 'd', 'f', 's', 'i' );
				foreach ( $to_escapes as $to_escape ) {
					if ( stripos( $value, '%' . $to_escape ) !== false ) {
						$value = str_ireplace( '%' . $to_escape, '%%' . $to_escape, $value );
					}
					if ( str_starts_with( strtolower( $value ), $to_escape ) ) {
						$value = '%' . $value;
					}
				}
				$command[] = $key . " LIKE '%" . $value . "%' ";
			}
			$query = implode( ' AND ', $command );
			// var_dump( $query );
			$results = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT i,browser,software,system,time,block FROM %i WHERE ' . $query . ' ORDER BY software, browser ASC',
					$options['table_name'],
				),
			);
			// var_dump( $results );
			return $results;
		}
	}
}

function wimbblock_handle_get( $search ) {
	$name = filter_input(
		INPUT_GET,
		'name',
		// FILTER_SANITIZE_SPECIAL_CHARS
		FILTER_DEFAULT
	);

	$options = wimbblock_get_options_db();
	global $wimb_datatable;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}
	$query = '%' . $name . '%';
	// var_dump( $search, $query );
	switch ( $search ) {
		case 'S':
			$searchengines = wimbblock_searchengines();
			$results       = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT i,browser,software,system,time,block FROM %i WHERE ( ' . $searchengines . ' ) AND software LIKE %s',
					$options['table_name'],
					$query,
				),
				ARRAY_A
			);
			break;
		case 'C':
			$searchengines = wimbblock_searchengines();
			$results       = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT i,browser,software,system,time,block FROM %i WHERE ( browser LIKE %s OR software LIKE %s ) AND NOT ( ' . $searchengines . ') AND software LIKE %s',
					$options['table_name'],
					'%Chrome/%',
					'%Chrome%',
					$query,
				),
				ARRAY_A
			);
			break;
		case 'F':
			$searchengines = wimbblock_searchengines();
			$results       = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT i,browser,software,system,time,block FROM %i WHERE ( browser LIKE %s OR software LIKE %s ) AND NOT ( ' . $searchengines . ') AND software LIKE %s',
					$options['table_name'],
					'%Firefox/%',
					'%Firefox%',
					$query,
				),
				ARRAY_A
			);
			break;
		case 'O':
			if ( $query === '%empty%' ) {
				$results = $wimb_datatable->get_results(
					$wimb_datatable->prepare(
						"SELECT i,browser,software,system,time,block FROM %i WHERE software = ''",
						$options['table_name'],
					),
				);
			} else {
				$derivates     = wimbblock_derivates();
				$searchengines = wimbblock_searchengines();
				$results       = $wimb_datatable->get_results(
					$wimb_datatable->prepare(
						'SELECT i,browser,software,system,time,block FROM %i WHERE NOT ( ' . $derivates . ' ) AND NOT ( ' . $searchengines . ') AND software LIKE %s',
						$options['table_name'],
						$query,
					),
					ARRAY_A
				);
			}
			break;
		default:
			wp_die( 'no search param' );
	}
	// var_dump( count( $results ) );
	return $results;
}

function wimbblock_display_type_table( $entries, $header, $search ) {
	wp_enqueue_script(
		'sort_table_js',
		plugins_url(
			WIMBBLOCK_NAME . '/admin/sort-table.js'
		),
		array(),
		'1',
		false
	);
	$head      = '<div><h3 align="center">' . $header . '</h3><figure class="wp-block-table aligncenter is-style-stripes">' . "\n";
	$head      = $head . '<table border=1>' . "\n";
	$head      = $head . '<thead><tr>
	<th id="click" onclick="onColumnHeaderClickedChar(event)">software</th>
	<th id="click" onclick="onColumnHeaderClickedNumbers(event)">count</th>
	<th id="click" onclick="onColumnHeaderClickedNumbers(event)">block</th>
	<th id="click" onclick="onColumnHeaderClickedNumbers(event)">%</th>
	</tr></thead><tbody>';
	$cellstyle = "style='border:1px solid #195b7a;'";
	$text      = $head;
	foreach ( $entries as $key => $value ) {
		// var_dump($key , $value); wp_die('tot');
		$text .= '<tr><td ' . $cellstyle . '><a href="' .
		wp_nonce_url( '?page=' . WIMBBLOCK_NAME . '&tab=block&name=' . trim( $key ) . '&search=' . $search, 'name' ) . '">' . $key . '</a></td>' . "\n";
		$text .= '<td ' . $cellstyle . ' align="center">' . $value ['count'] . '</td>' . "\n";
		$text .= '<td ' . $cellstyle . ' align="center">' . $value['block'] . '</td>';
		$text .= '<td ' . $cellstyle . ' align="center">' .
		wimbblock_prozent( $value['block'], $value ['count'] ) . '</td>';
		$text .= '</tr>' . "\n";
	}
	$text                 .= '</tbody></table></figure></div>';
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

	echo wp_kses( $text, $allowed_html );
	// echo wp_kses_post( $text );
}

function wimbblock_derivates_table( $type = '' ) {
	global $wimb_datatable;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}
	$wimbblock_wpdb_options = wimbblock_get_options_db();
	$wimbblock_table_name   = $wimbblock_wpdb_options['table_name'];

	switch ( $type ) {
		case 'C':
			$browsers = array( 'Chrome' );
			break;
		case 'F':
			$browsers = array( 'Firefox' );
			break;
		default:
			$browsers = array( 'Chrome', 'Firefox' );
	}
	$searchengines = wimbblock_searchengines();
	foreach ( $browsers as $browser ) {
		$agents = $wimb_datatable->get_results(
			$wimb_datatable->prepare(
				"SELECT DISTINCT substring_index(regexp_replace(software, '([0-9].*)', ''),' on ',1) as variants FROM %i " .
				' WHERE ( browser LIKE %s OR software LIKE %s ) AND NOT ( ' . $searchengines . ' )',
				$wimbblock_table_name,
				'%' . $browser . '/%',
				'%' . $browser . '%',
			),
			ARRAY_A
		);
		// var_dump(count($agents));
		$softwares = array_column( $agents, 'variants' );
		$variants  = wimbblock_browser_unique( $softwares );
		$result    = array();
		foreach ( $variants as $software ) {
			$wimbblock_entries            = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT COUNT(*) as count, SUM(CASE WHEN block > 0 THEN 1 ELSE 0 END ) as blocked FROM %i WHERE ( browser LIKE %s OR software LIKE %s ) AND software LIKE %s',
					$wimbblock_table_name,
					'%' . $browser . '/%',
					'%' . $browser . '%',
					$software . '%',
				),
				ARRAY_A
			);
			$result[ $software ]['count'] = (int) $wimbblock_entries[0]['count'];
			$result[ $software ]['block'] = (int) $wimbblock_entries[0]['blocked'];
		}
		arsort( $result );
		// ksort( $result );
		// var_dump(array_sum($result));
		$header = $browser . ' ' . __( 'based browsers', 'wimb-and-block' );
		$search = substr( $browser, 0, 1 );
		wimbblock_display_type_table( $result, $header, $search );
	}
}

function wimbblock_other_table() {
	global $wimb_datatable;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}
	$wimbblock_wpdb_options = wimbblock_get_options_db();
	$wimbblock_table_name   = $wimbblock_wpdb_options['table_name'];

	$derivates     = wimbblock_derivates();
	$searchengines = wimbblock_searchengines();
	// var_dump( $derivates, $searchengines );

	$agents = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			"SELECT DISTINCT substring_index(regexp_replace(software, '( [0-9].*)', ''),' on ',1) as variants FROM %i WHERE NOT "
			. '( ' . $derivates . ' ) AND NOT ( ' . $searchengines . ')',
			$wimbblock_table_name,
		),
		ARRAY_A
	);

	$softwares = array_column( $agents, 'variants' );
	$variants  = wimbblock_browser_unique( $softwares );
	$result    = array();
	foreach ( $variants as $software ) {
		$wimbblock_entries = $wimb_datatable->get_results(
			$wimb_datatable->prepare(
				'SELECT COUNT(*) as count, SUM(CASE WHEN block > 0 THEN 1 ELSE 0 END ) as blocked FROM %i WHERE NOT ( '
					. $derivates . ' ) AND NOT ( ' . $searchengines . ') AND software LIKE %s',
				$wimbblock_table_name,
				$software . '%',
			),
			ARRAY_A
		);
		// var_dump($wimbblock_entries); wp_die('tot');
		$result[ $software ]['count'] = (int) $wimbblock_entries[0]['count'];
		$result[ $software ]['block'] = (int) $wimbblock_entries[0]['blocked'];
	}
	$wimbblock_entries        = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			"SELECT COUNT(*) as count, SUM(CASE WHEN block > 0 THEN 1 ELSE 0 END ) as blocked FROM %i WHERE software=''",
			$wimbblock_table_name,
		),
		ARRAY_A
	);
	$result['empty']['count'] = (int) $wimbblock_entries[0]['count'];
	$result['empty']['block'] = (int) $wimbblock_entries[0]['blocked'];
	arsort( $result );
	// ksort( $result );
	// var_dump($result);
	$header = __( 'All other browsers', 'wimb-and-block' );
	wimbblock_display_type_table( $result, $header, 'O' );
}

function wimbblock_searchengines_table() {
	echo '<div class="wimbbox">';
	global $wimb_datatable;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}
	$wimbblock_wpdb_options = wimbblock_get_options_db();
	$wimbblock_table_name   = $wimbblock_wpdb_options['table_name'];

	$query = wimbblock_searchengines();

	$agents = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			"SELECT DISTINCT regexp_replace(software, '( [0-9].*)', '') as variants FROM %i WHERE " . $query,
			$wimbblock_table_name,
		),
		ARRAY_A
	);
	// var_dump( $agents );

	$softwares = array_column( $agents, 'variants' );
	$variants  = wimbblock_browser_unique( $softwares );
	$result    = array();
	foreach ( $variants as $software ) {
		$wimbblock_entries = $wimb_datatable->get_results(
			$wimb_datatable->prepare(
				'SELECT COUNT(*) as count, SUM(CASE WHEN block > 0 THEN 1 ELSE 0 END ) as blocked FROM %i WHERE (' . $query . ')
				AND software LIKE %s',
				$wimbblock_table_name,
				$software . '%',
			),
			ARRAY_A
		);
		// var_dump($wimbblock_entries); wp_die('tot');
		$result[ $software ]['count'] = (int) $wimbblock_entries[0]['count'];
		$result[ $software ]['block'] = (int) $wimbblock_entries[0]['blocked'];
	}
	arsort( $result );
	// ksort( $result );
	// var_dump($result);
	$header = __( 'Search Engines', 'wimb-and-block' );
	wimbblock_display_type_table( $result, $header, 'S' );
	echo '</div>';
}

function wimbblock_derivates() {
	// escape: %d (integer), %f (float), %s (string), %i (identifier, e.g. table/field names)
	$derivates = array(
		'Chrome/'   => 'browser',
		'%Firefox/' => 'browser',
		'Chrome'    => 'software',
		'%Firefox'  => 'software',
	);
	$command   = array();
	foreach ( $derivates as $derivate => $type ) {
		$command[] = ' ' . $type . " LIKE '%" . $derivate . "%' ";
	}
	$query = implode( ' OR ', $command );
	return $query;
}

function wimbblock_searchengines() {
	// ( $agent, 'Applebot' )
	// ( $agent, 'Baiduspider' )
	// ( $agent, 'googleother' )
	// ( $agent, 'http://yandex.com/bots' )
	// ( $agent, 'MojeekBot' )
	// ( $agent, 'SeznamBot' )
	// ( $agent, 'BingBot' )
	// escape: %d (integer), %f (float), %s (string), %i (identifier, e.g. table/field names)
	$searchengines = array(
		'Applebot'               => 'browser',
		'Baiduspider'            => 'browser',
		'http://yandex.com/bots' => 'browser',
		'MojeekBot'              => 'browser',
		'%SeznamBot'             => 'browser',
		'BingBot'                => 'browser',
		'Googlebot'              => 'browser',
		'GoogleOther'            => 'browser',
		'Google-CloudVertexBot'  => 'browser',
		'Google-Extended'        => 'browser',
		'Google-InspectionTool'  => 'browser',
		'Storebot-Google'        => 'browser',
	);
	$command       = array();
	foreach ( $searchengines as $searchengine => $type ) {
		$command[] = ' ' . $type . " LIKE '%" . $searchengine . "%' ";
	}
	$query = implode( ' OR ', $command );
	return $query;
}

function wimbblock_browser_unique( $softwares ) {
	$variants = array();
	foreach ( $softwares as $software ) {
		if ( $software === '' ) {
			continue;
		}
		$substr = false;
		foreach ( $softwares as $suche ) {
			if ( $suche === '' ) {
				continue;
			}
			if ( $software !== $suche ) {
				if ( strpos( $software, $suche ) !== false ) {
					$substr = true;
					// var_dump($software, $suche);
				}
			}
		}
		if ( ! $substr ) {
			$variants[] = $software;
		}
	}
	return $variants;
}
