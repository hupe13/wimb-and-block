<?php
/**
 *  Settings for block ai
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

// Init settings fuer block ai
function wimbblock_block_ai_init() {
	add_settings_section( 'wimbblock_block_ai', '', '__return_empty_string', 'wimbblock_block_ai' );
	add_settings_field( 'wimbblock_block_ai', __( 'Block AI crawlers', 'wimb-and-block' ), 'wimbblock_form_block_ai', 'wimbblock_block_ai', 'wimbblock_block_ai' );
	register_setting(
		'wimbblock_block_ai',
		'wimbblock_block_ai',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'wimbblock_validate_block_ai',
			'default'           => '0',
		)
	);
}
add_action( 'admin_init', 'wimbblock_block_ai_init' );

// Baue Abfrage der Params
function wimbblock_form_block_ai() {
	$setting = wimbblock_get_option( 'wimbblock_block_ai' );
	if ( $setting === false ) {
		$setting = '0';
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		$disabled = ' disabled ';
	} else {
		$disabled = '';
	}
	echo '<input ' . esc_attr( $disabled ) . ' type="radio" name="wimbblock_block_ai" value="0" ';
	checked( $setting === '0' );
	echo '> ' . esc_html__( 'No', 'wimb-and-block' ) . ' &nbsp;&nbsp; ';
	echo '<input ' . esc_attr( $disabled ) . ' type="radio" name="wimbblock_block_ai" value="1" ';
	checked( $setting === '1' );
	echo '> ' . esc_html__( 'Yes', 'wimb-and-block' );
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wimbblock_validate_block_ai( $input ) {
	if ( ! empty( $_POST ) && check_admin_referer( 'wimbblock_block_ai', 'wimbblock_block_ai_nonce' ) ) {
		if ( isset( $_POST['submit'] ) ) {
			delete_transient( 'wimbblock_block_ai_stop' );
			return $input;
		}
	}
}

function wimbblock_no_ai_help() {
	global $wimbblock_ai_agents;
	$text  = '<h3>' . __( 'AI crawlers', 'wimb-and-block' ) . '</h3>';
	$text .= '<p>' . wp_sprintf(
		/* translators: %s is a link. */
		__( 'The plugin uses the data from Github repository %s to block AI crawlers.', 'wimb-and-block' ),
		'<a href="https://github.com/ai-robots-txt/ai.robots.txt">ai.robots.txt</a>'
	);
	$text .= ' ' . wp_sprintf(
		/* translators: %s is a link. */
		__( 'Take care about its %1$slicense%2$s.', 'wimb-and-block' ),
		'<a href="https://github.com/ai-robots-txt/ai.robots.txt/blob/main/LICENSE">',
		'</a>'
	);
	$text .= ' ' . __( 'After each update to this repository, I will release a new version of the plugin.', 'wimb-and-block' ) . '</p>';
	echo wp_kses_post( $text );

	echo '<details class="primer" style="display: inline-block; width: 100%;">
	<summary title="AI">' . wp_kses_post( __( 'AI crawlers', 'wimb-and-block' ) ) .
	'</summary>';

	$search  = array(
		'%',
		'|',
		'\\',
	);
	$replace = array(
		'',
		' &#9679; ',
		'',
	);
	echo wp_kses_post( str_replace( $search, $replace, $wimbblock_ai_agents ) );
	echo '</details><!--/.primer-->';
}

function wimbblock_ai_always() {
	global $wimb_datatable;
	global $wimbblock_test_to_block;
	global $wimbblock_ai_agents;

	$search    = array(
		'%',
		// '|',
		'\\',
	);
	$replace   = array(
		'',
		// ' &#9679; ',
		'',
	);
	$ai_agents = explode( '|', str_replace( $search, $replace, $wimbblock_ai_agents ) );
	// var_dump($ai_agents);

	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}
	$wimbblock_wpdb_options = wimbblock_get_options_db();
	$wimbblock_table_name   = $wimbblock_wpdb_options['table_name'];
	$table_name             = $wimbblock_table_name;

	$to_delete = array();
	$no_delete = array();
	$alwayses  = wimbblock_get_option( 'wimbblock_always' );

	if ( $alwayses !== false && count( $alwayses ) > 0 ) {
		foreach ( $alwayses as $always ) {
			$wimbblock_entries = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT * FROM %i WHERE browser LIKE %s ORDER BY time DESC',
					$table_name,
					'%' . $always . '%',
				),
				ARRAY_A
			);

			foreach ( $wimbblock_entries as $entry ) {
				$agent = $entry['browser'];
				if ( preg_match( $wimbblock_ai_agents, $agent, $match ) === 1 ) {
					$to_delete[] = $always;
					continue;
				}
				if ( stripos( $wimbblock_ai_agents, $always ) !== false ) {
					$no_delete[] = $always;
				}
			}
		}

		$to_delete = array_unique( array_diff( $to_delete, $no_delete ) );
		// var_dump($to_delete);
		return $to_delete;
	}
}

function wimbblock_ai_in_database() {
	global $wimb_datatable;
	global $wimbblock_ai_agents;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}
	$wimbblock_wpdb_options = wimbblock_get_options_db();
	$wimbblock_table_name   = $wimbblock_wpdb_options['table_name'];
	$table_name             = $wimbblock_table_name;
	$search                 = array(
		'%',
		// '|',
		'\\',
	);
	$replace                = array(
		'',
		// ' &#9679; ',
		'',
	);
	$ai_agents              = explode( '|', str_replace( $search, $replace, $wimbblock_ai_agents ) );
	$entries                = array();
	foreach ( $ai_agents as $ai_agent ) {
		$wimbblock_entries = $wimb_datatable->get_results(
			$wimb_datatable->prepare(
				'SELECT browser,software,system,time,block FROM %i WHERE browser LIKE %s ORDER BY time DESC',
				$table_name,
				'%' . $ai_agent . '%',
			),
			ARRAY_A
		);
		foreach ( $wimbblock_entries as $wimbblock_entry ) {
			$exist = array_search( $wimbblock_entry['browser'], array_column( $entries, 'browser' ), true );
			if ( $exist === false ) {
				$wimbblock_entry['ai'] = $ai_agent;
				$entries[]             = $wimbblock_entry;
			}
		}
	}
	echo wp_kses_post(
		wp_sprintf(
			/* translators: %s are numbers */
			__( 'So far, %1$s out of %2$s AI crawlers have not been blocked.', 'wimb-and-block' ),
			array_count_values( array_column( $entries, 'block' ) )['0'],
			count( $entries )
		)
	);

	echo '<h4>' . wp_kses_post(
		'<a href="' .
		wp_nonce_url( '?page=' . WIMBBLOCK_NAME . '&tab=ai', 'table' )
		. '">' .
		__( 'Table AI', 'wimb-and-block' ) . '</a>'
	) . '</h4>';
	$nonce = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) );
	if ( wp_verify_nonce( $nonce, 'table' ) ) {
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
		echo wp_kses( wimbblock_ai_table( $entries ), $allowed_html );
	}
}

// Display array as table
function wimbblock_ai_table( $data = array() ) {

	wp_enqueue_script(
		'sort_table_js',
		plugins_url(
			WIMBBLOCK_NAME . '/admin/sort-table.js'
		),
		array(),
		'1',
		false
	);

	$tablehdr = '<thead><tr>
	<th id="click" onclick="onColumnHeaderClickedChar(event)">browser</th>
	<th id="click" onclick="onColumnHeaderClickedChar(event)">software</th>
	<th id="click" onclick="onColumnHeaderClickedChar(event)">system</th>
	<th id="click" onclick="onColumnHeaderClickedChar(event)">time</th>
	<th id="click" onclick="onColumnHeaderClickedNumbers(event)">block</th>
	<th id="click" onclick="onColumnHeaderClickedChar(event)">AI</th>
	</tr></thead>';

	$rows      = array();
	$alternate = true;
	foreach ( $data as $row ) {
		if ( (int) $row['block'] === 0 ) {
			if ( $alternate ) {
				$alternate = false;
				$class     = ' class="greenw04"';
			} else {
				$alternate = true;
				$class     = ' class="greenw02"';
			}
		} elseif ( $alternate ) {
			$alternate = false;
			$class     = ' class="red04"';
		} else {
			$alternate = true;
			$class     = ' class="red02"';
		}
		$cells = array();
		foreach ( $row as $cell ) {
			$cells[] = '<td align="center">' . "{$cell}</td>";
		}
		$rows[] = '<tr' . $class . '>' . implode( '', $cells ) . '</tr>' . "\n";
	}
	$head = '<div style="width:80%;">';
	$head = $head . '<figure class="wp-block-table aligncenter"><table border=1>' . $tablehdr;
	return "\n\n" . $head . '<tbody>' . implode( '', $rows ) . '<tbody></table></figure></div>';
}
