<?php
/**
 * Functions What is my browser
 *
 * @package wimb-and-block
 */

//
function wimbblock_whatsmybrowser( $user_agent ) {
	if ( $user_agent !== '' ) {
		$options = wimbblock_get_options_db();

		# Where will the request be sent to
		$url = 'https://api.whatismybrowser.com/api/v2/user_agent_parse';
		# -- prepare data for the API request
		# This shows the `parse_options` key with some options you can choose to enable if you want
		# https://developers.whatismybrowser.com/api/docs/v2/integration-guide/#user-agent-parse-parse-options
		$post_data = array(
			'user_agent'    => $user_agent,
			'parse_options' => array(
				#"allow_servers_to_impersonate_devices" => True,
				#"return_metadata_for_useragent" => True,
				#"dont_sanitize" => True,
			),
		);

		$result = wp_remote_post(
			$url,
			array(
				'method'  => 'POST',
				'headers' => array(
					'X-API-KEY' => $options['wimb_api'],
				),
				'body'    => wp_json_encode( $post_data ),
			)
		);

		# -- Try to decode the api response as json
		$result_json = json_decode( $result['body'], true );

		$parse = $result_json['parse'];

		# Now you can do whatever you need to do with the parse result

		$result = array(
			'software' => is_null( $parse['simple_software_string'] ) ? '' : $parse['simple_software_string'],
			'system'   => is_null( $parse['operating_system'] ) ? '' : $parse['operating_system'],
			'version'  => is_null( $parse['software_version'] ) ? '' : $parse['software_version'],
		);
		return( $result );
	}
}

function wimbblock_check_wimb( $agent, $wimbblock_table ) {
	global $wimb_datatable;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}
	$table_name = $wimbblock_table;
	$yymm       = wp_date( 'ym' );

	$browser = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			"SELECT * FROM %i WHERE browser = '" . $agent . "' ORDER BY time DESC",
			$table_name
		),
		ARRAY_A
	);

	if ( count( $browser ) === 0 ) {

		$wimb     = wimbblock_whatsmybrowser( $agent );
		$software = $wimb['software'];
		$system   = $wimb['system'];
		$version  = $wimb['version'];

		$mgt_code = $wimb_datatable->query(
			$wimb_datatable->prepare(
				'INSERT INTO %i ( browser,software,system,version ) VALUES ( %s,%s,%s,%s ) ON DUPLICATE KEY UPDATE i=i ',
				$table_name,
				$agent,
				$software,
				$system,
				$version
			),
		);

		wimbblock_error_log( 'Inserted agent: ' . $agent . ' * ' . $mgt_code . ' * ' . $wimb_datatable->insert_id );

		$blocked = '0';
		$id      = $wimb_datatable->insert_id;

	} else {

		$software = $browser[0]['software'];
		$system   = $browser[0]['system'];
		$version  = $browser[0]['version'];
		$blocked  = $browser[0]['block'];
		$id       = $browser[0]['i'];

	}
	return array( $software, $system, $version, $blocked, $id );
}
