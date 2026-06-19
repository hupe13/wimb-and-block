<?php
/**
 * Functions for transients
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_get_emergency_transient() {
	$emergency = get_transient( 'wimbblock_emergency_stop' );
	if ( false === $emergency ) {
		$stop      = wimbblock_get_option( 'wimbblock_emergency' );
		$emergency = false;
		if ( $stop !== false ) {
			if ( $stop === '0' ) {
				$emergency = true;
			}
		}
		set_transient( 'wimbblock_emergency_stop', $emergency, HOUR_IN_SECONDS );
	}
	return $emergency;
}
