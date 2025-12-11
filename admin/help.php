<?php
/**
 * Functions help
 *
 * @package wimb-and-block
 */

//
function wimbblock_help() {
	require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	$plugin_info = plugins_api(
		'plugin_information',
		array(
			'slug'   => 'wimb-and-block',
			'fields' => array(
				'sections' => true,
			),
		)
	);
	if ( ! $plugin_info || is_wp_error( $plugin_info ) ) {
		return __( 'The information could not be retrieved.', 'wimb-and-block' );
	}
	$text = '';
	foreach ( $plugin_info as $key => $value ) {
		if ( $key === 'sections' ) {
			foreach ( $value as $section => $part ) {
				// ***description***installation***faq***changelog***screenshots***reviews
				switch ( $section ) {
					case 'description':
						if ( get_locale() === 'de_DE' ) {
							$text .= '<h3>Beschreibung</h3>';
						} else {
							$text .= '<h3>Description</h3>';
						}
						$text .= str_replace( '<li>', '<li class="adminli">', $part );
						break;
					default:
				}
			}
		}
	}
	return $text;
}

function wimbblock_callback_translate( $matches ) {
	if ( $matches[2] === 'Description' ) {
		return $matches[1] . ' Beschreibung ' . ( $matches[3] ?? '' );
	} else {
		$pattern     = array(
			'<br>',
			' & ',
			' < ',
		);
		$replacement = array(
			'<br />',
			' &amp; ',
			' &lt; ',
		);
		// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
		return $matches[1] . ' ' . __( str_replace( $pattern, $replacement, $matches[2] ), 'wimb-and-block-readme' ) . ' ' . ( $matches[3] ?? '' );
	}
}

function wimbblock_help_readme( $file ) {
	require_once ABSPATH . 'wp-content/plugins/' . WIMBBLOCK_NAME . '/pkg/parsedown/Parsedown.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
	$filesystem = new WP_Filesystem_Direct( true );
	$readme     = $filesystem->get_contents( ABSPATH . $file );
	$readme     = preg_replace( '/Contributors: [\s0-9a-zA-Z:.,]+GPLv2 or later/m', '', $readme );
	if ( get_locale() === 'de_DE' ) {
		$array = explode( "\n", $readme );
		// var_dump($array);
		$german = array();
		foreach ( $array as $line ) {
			if ( $line !== '' ) {
				// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				$translated = __( $line, 'wimb-and-block-readme' );
				if ( $line === $translated ) {
					$pattern    = array(
						'/(===) (.*) (===)/',
						'/(==) (.*) (==)/',
						'/(=) (.*) (=)/',
						'/^(\*) (.*)/',
						'/^(    - )(.*)/',
						'/^([#]+ )(.*)/',
						'/^([1-9]\. )(.*<br \/>)(.*)$/',
					);
					$translated = preg_replace_callback( $pattern, 'wimbblock_callback_translate', $line );
					$german[]   = $translated;
					// if ( $line === $translated ) {
					//  var_dump( $line );
					// }
				} else {
					$german[] = $translated;
				}
			} else {
				$german[] = $line;
			}
		}
		$readme = implode( "\n", $german );
	}
	$parsedown   = new Parsedown();
	$text        = $parsedown->text( $readme );
	$text        = str_replace( '.wordpress-org', get_site_url() . '/wp-content/plugins/' . WIMBBLOCK_NAME . '/.wordpress-org', $text );
	$text        = str_replace( '<li>', '<li class="adminli">', $text );
	$pattern     = array(
		'/=== (.*) ===/',
		'/== (.*) ==/',
		'/= (.*) =/',
	);
	$replacement = array(
		'<h2> ${1} </h2>',
		'<h3> ${1} </h3>',
		'<h4> ${1} </h4>',
	);
	$text        = preg_replace( $pattern, $replacement, $text );
	return $text;
}
