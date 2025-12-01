<?php
/**
 * Functions help
 *
 * @package wimb-and-block
 */

//
function wimbblock_help() {
	// $text = '<h3>' .
	// __( 'Found an issue? Do you have a question?', 'wimb-and-block' ) . '</h3>
	// <p>' .
	// __( 'Post it to the support forum', 'wimb-and-block' ) .
	// ': <a href="https://wordpress.org/support/plugin/wimb-and-block/" target="_blank">Block old browser versions and suspicious browsers</a></p>';

	$text = '<h3>' .
		'<a href="https://leafext.de/hp/wimb/">' . __( 'Readme / Documentation', 'wimb-and-block' ) . '</a>';
	$text = $text . '</h3></p>';
	return $text;
}
