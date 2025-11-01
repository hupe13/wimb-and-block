<?php
/**
 * Functions help
 *
 * @package wimb-and-block
 */

//
function wimbblock_help() {
	$text  = '';
	$text .= '<h2>' . __( 'Description', 'wimb-and-block' ) . '</h2>' . "\n\r";
	$text .= '<p>' .
	sprintf(
	/* translators: %s is a name of a website */
		__( 'The plugin uses %s to get informations about the browser. It detects old and suspicious browsers and denies them access to your website.', 'wimb-and-block' ),
		'WhatIsMyBrowser.com'
	) . '</p>' . "\n\r";

	$text .= '<ul>' . "\n\r";
	$text .= '<li>' . sprintf(
	/* translators: %1$s is What is my browser?. %2$s is Basic Application Plan */
		__( 'Get an API key from %1$s for a %2$s.', 'wimb-and-block' ),
		'<a href="https://developers.whatismybrowser.com/api/">What is my browser?</a>',
		'Basic Application Plan'
	) . '</li>' . "\n\r";
	$text .= '<li>' . sprintf(
	/* translators: %s is 'Parsing User Agent' */
		__( 'You have a limit of 5000 hits / month for %s. Thats why the plugin manages a database table.', 'wimb-and-block' ),
		'Parsing User Agent'
	)
	. '</li>' . "\n\r";
	$text .= '<li>' . __( 'The user agent string of every browser accessing your website the first time is send to this service and some data will be stored in this table:', 'wimb-and-block' ) . "\n\r";

	$text .= '<p><table class="width450" border=1>
 	 <tr><td class="width280 center-text"><code>browser</code></td>
	 <td class="width85 center-text"><code>simple software string</code></td>
	 <td class="width85 center-text"><code>operating system</code></td></tr></table></p>';
	$text .= '<p><img src="' . plugin_dir_url( __FILE__ ) . '../pict/good.jpg" alt="example entries" width="450" ></p>';
	$text .= '<p>' . __( 'Browsers will be blocked, if the browser and/or the system is an old one:', 'wimb-and-block' ) . '<br>';
	$text .= __( 'Default: Chrome and Chrome based browsers &lt; 128, Firefox &lt; 128, Internet Explorer, Netscape (!), Opera &lt; 83, Safari &lt; 17', 'wimb-and-block' ) . '<br>' . "\n\r";
	$text .= __( 'Old systems are all Windows versions before Windows 10, some MacOS and Android versions.', 'wimb-and-block' ) . '</p>' . "\n\r";

	$text .= '<p><img src="' . plugin_dir_url( __FILE__ ) . '../pict/old.jpg" alt="example entries" width="450" ></p>';

	$text .= '<p>' . sprintf(
	/* translators: %1$s is "simple software string", %2$s is "unknown" */
		__( 'It will be blocked also if the %1$s contains %2$s or is empty.', 'wimb-and-block' ),
		'"simple software string"',
		'"unknown"'
	) . '</p>' . "\n\r";
	$text .= '<p><img src="' . plugin_dir_url( __FILE__ ) . '../pict/suspect.jpg" alt="example entries" width="450" ></p>';

	$text .= '</li>';
	$text .= '<li>' . __( 'You can configure other browsers too.', 'wimb-and-block' ) . '</li>' . "\n\r";

	$text .= '<li>' . __( 'Sometimes there are false positive, for example if the browser is from Mastodon. Then you can exclude these from checking.', 'wimb-and-block' ) . '</li>' . "\n\r";
	$text .= '<li>' . __( 'The plugin checks, if the crawlers are really from Google, Bing, Yandex, Apple, Mojeek, Baidu, Seznam.', 'wimb-and-block' ) . '</li>' . "\n\r";
	$text .= '</ul>' . "\n\r";

	$text .= '<h3>' . __( 'About robots.txt', 'wimb-and-block' ) . '</h3>';
	$text .= '<ul><li>';
	$text .= __( 'You can configure some rewrite rules, to provide a robots.txt to enable or to disable crawling for a browser. If crawling is disabled, access to your website will be blocked for that browser.', 'wimb-and-block' );
	$text .= '</li></ul>';
	return $text;
}
