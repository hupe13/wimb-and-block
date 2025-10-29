<?php
/**
 * Functions block always browsers with strings
 *
 * @package wimb-and-block
 */

//
function wimbblock_always( $table_name, $agent, $blocked, $id, $robots ) {
	$alwayses = wimbblock_get_option( 'wimbblock_always' );
	if ( $alwayses !== false ) {
		foreach ( $alwayses as $always ) {
			if ( stripos( $agent, $always ) !== false ) {
				if ( $robots === false ) {
					wimbblock_counter( $table_name, 'block', $id );
					wimbblock_error_log( 'always blocked: ' . $agent . ' * ' . $always );
					status_header( 404 );
					echo 'Your browser has been blocked.';
					exit();
				} else {
					if ( $blocked === '0' ) {
						wimbblock_counter( $table_name, 'block', $id );
					}
					wimbblock_counter( $table_name, 'robots', $id );
					wimbblock_error_log( 'robots.txt forbidden: ' . $agent );
					header( 'Content-Type: text/plain; charset=UTF-8' );
					echo "User-agent: *\r\n" .
					'Disallow: /' . "\r\n";
					exit;
				}
			}
		}
	}
}
