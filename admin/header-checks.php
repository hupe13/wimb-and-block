<?php
/**
 * Header checks
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_header_help() {
	//$text .= 'https://caniuse.com/?search=Sec-Fetch-'

	$text  = '<h3>' . __( 'Header Checks', 'wimb-and-block' ) . '</h3>';
	$text .= '<h4>Sec-Fetch Header</h4>';
	$text .= '<p>' . __( 'In addition to the User Agent string, there are certain headers that some browsers also send.', 'wimb-and-block' ) . '</p>';
	$text .= '<ul>';
	$text .= '<li class="adminli"><a href="https://developer.mozilla.org/de/docs/Web/HTTP/Reference/Headers/Sec-Fetch-Site">Sec-Fetch-Site</a></li>';
	$text .= '<li class="adminli"><a href="https://developer.mozilla.org/de/docs/Web/HTTP/Reference/Headers/Sec-Fetch-Mode">Sec-Fetch-Mode</a></li>';
	$text .= '<li class="adminli"><a href="https://developer.mozilla.org/de/docs/Web/HTTP/Reference/Headers/Sec-Fetch-Dest">Sec-Fetch-Dest</a></li>';
	$text .= '</ul>';

	$text .= '<p>' .
	wp_sprintf(
	/* translators: %1$s are browser names. */
		__( 'All of these browsers %1$s should send these headers.', 'wimb-and-block' ),
		" - 'Chrome',
    'Edge',
    'Safari',
    'Firefox',
    'Opera',
    'Samsung Internet',
    'UC Browser',
    'QQ Browser' and
    'KaiOS Browser' - "
	) . '</br>';
	$text .= __( 'If one is not present, the browser will be blocked.', 'wimb-and-block' ) . '</p>';

	$text .= '<h4><a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Sec-CH-UA">Sec-CH-UA</a></h4>';

	$text .= '<p>' . __( 'The following Browsers have this header:', 'wimb-and-block' );
	$text .= " 'Chrome', 'Edge', 'Opera', 'Samsung Internet', 'WebView Android'.";
	$text .= '</p>';

	$text .= '<p>' . __( 'Just like with the user agent string, you can determine the browser version from this header.', 'wimb-and-block' );
	$text .= ' ' . __( 'If the two do not match, the browser is blocked.', 'wimb-and-block' ) . '</p>';

	$text .= '<h4><a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Sec-CH-UA-Platform">Sec-CH-UA-Platform</a></h4>';

	$text .= '<p>' . __( 'The following Browsers have this header:', 'wimb-and-block' );
	$text .= " 'Chrome', 'Edge', 'Opera', 'Samsung Internet', 'WebView Android'.";
	$text .= '</p>';

	$text .= '<p>' . __( 'This header sometimes does not match the system detected from the user agent string.', 'wimb-and-block' );
	$text .= ' ' . __( 'For example, the user-agent string indicates "Windows", but this header shows "Linux".', 'wimb-and-block' );
	$text .= ' ' . __( 'In this case, the browser is blocked.', 'wimb-and-block' ) . '</p>';
	echo wp_kses_post( $text );
}
