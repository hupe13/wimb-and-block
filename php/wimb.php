<?php
/**
 * Functions What is my browser
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_whatsmybrowser( $user_agent, $api_key = '' ) {
	if ( $user_agent !== '' ) {
		if ( $api_key === '' ) {
			$options = wimbblock_get_options_db();
			$api_key = $options['wimb_api'];
		}

		// -- https://developers.whatismybrowser.com/api/docs/v3/integration-guide/detect/requests/
		$url = 'https://api.whatismybrowser.com/api/v3/detect';

		$headers_list = array();
		array_push(
			$headers_list,
			array(
				'name'  => 'User-Agent',
				'value' => $user_agent,
			)
		);

		// -- Prepare data for the API request
		$post_data = array(
			'headers' => $headers_list,
		);
		$result    = wp_remote_post(
			$url,
			array(
				'method'  => 'POST',
				'headers' => array(
					'X-API-KEY' => $api_key,
				),
				'body'    => (string) wp_json_encode( $post_data ),
			)
		);
		$wimberror = 'something went wrong';
		if ( is_wp_error( $result ) ) {
			$wimberror = $result->get_error_message();
		} else {
			$result_json = json_decode( wp_remote_retrieve_body( $result ), true );
			if ( isset( $result_json['detection'] ) ) {
				$parse = $result_json['detection'];
				// Now you can do whatever you need to do with the parse result
				$result = array(
					'software' => is_null( $parse['simple_software_string'] ) ? '' : $parse['simple_software_string'],
					'system'   => is_null( $parse['operating_system'] ) ? '' : $parse['operating_system'],
					'version'  => is_null( $parse['software_version'] ) ? '' : $parse['software_version'],
				);
				return( $result );
			} elseif ( isset( $result_json['result'] ) ) {
				$wimberror = $result_json['result']['message_code'];
			}

			wimbblock_error_log( 'Could not get wimb data: ' . $wimberror . ' * ' . $user_agent, true );
			$result = array(
				'software' => 'none',
				'system'   => '',
				'version'  => '',
			);
			return( $result );
		}
	}
}

function wimbblock_check_wimb( $agent, $table_name ) {
	global $wimb_datatable;
	$yymm = wp_date( 'ym' );

	$browser = $wimb_datatable->get_row(
		$wimb_datatable->prepare(
			'SELECT * FROM %i WHERE browser = %s',
			$table_name,
			$agent
		),
		ARRAY_A
	);

	if ( is_null( $browser ) ) {
		$wimb     = wimbblock_whatsmybrowser( $agent );
		$software = $wimb['software'] ?? '';
		$system   = $wimb['system'] ?? '';
		$version  = $wimb['version'] ?? '';
		$blocked  = '0';
		$mgt_code = $wimb_datatable->query(
			$wimb_datatable->prepare(
				'INSERT INTO %i ( browser,software,system,version ) VALUES ( %s,%s,%s,%s )
				ON DUPLICATE KEY UPDATE i=LAST_INSERT_ID(i)',
				$table_name,
				$agent,
				$software,
				$system,
				$version
			),
		);
		$id       = $wimb_datatable->insert_id;
		wimbblock_error_log( 'Inserted agent: ' . $agent . ' * ' . $mgt_code . ' * ' . $id );
		if ( $id === '0' ) {
			wimbblock_error_log( 'Too many requests - died: ' . $agent );
			wp_die(
				'<h1>Too many requests</h1><p>Try again later ...</p>',
				'',
				array( 'response' => 503 )
			);
		}
	} else {
		$software = $browser['software'];
		$system   = $browser['system'];
		$version  = $browser['version'];
		$blocked  = $browser['block'];
		$id       = $browser['i'];
	}
	return array( $software, $system, $version, $blocked, $id );
}
