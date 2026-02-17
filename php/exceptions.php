<?php
/**
 * Functions Exceptions prove the rule
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

// Samsung Internet 29: (Android 10; K) SamsungBrowser/29.0 Chrome/136
// Firefox 144 Android Oreo

function wimbblock_exceptions( $table_name, $agent, $blocked, $id ) {
	$software = 'SamsungBrowser';
	if ( strpos( $agent, $software ) !== false ) {
		$version  = preg_replace( '%.*' . $software . ' ([0-9]+)[^0-9].*%', '${1}', $software );
		$checking = wimbblock_get_all_browsers();
		wimbblock_old_agent( $table_name, $software, '', '0', $id, $agent, $checking[ $software ], false );
		if ( $blocked > 0 ) {
			wimbblock_unblock( $table_name, $agent, $id );
		}
		return '0';
	}
	return $blocked;
}
