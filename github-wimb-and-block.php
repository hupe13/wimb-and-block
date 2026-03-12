<?php
/**
 *  Github
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

/**
 * For translating
 */
function wimbblock_textdomain() {
	load_plugin_textdomain( 'wimb-and-block', false, WIMBBLOCK_NAME . '/lang' );
	load_plugin_textdomain( 'wimb-and-block-readme', false, WIMBBLOCK_NAME . '/lang' );
}
add_action( 'init', 'wimbblock_textdomain', 11 );

/**
 * For translating if both plugins (WP and Github) exist.
 */
function wimbblock_extra_textdomain( $mofile, $domain ) {
	if ( 'wimb-and-block' === $domain ) {
		if ( file_exists( dirname( __DIR__ ) . '/' . WIMBBLOCK_NAME . '/lang/wimb-and-block-' . get_locale() . '.mo' ) ) {
			$mofile = dirname( __DIR__ ) . '/' . WIMBBLOCK_NAME . '/lang/wimb-and-block-' . get_locale() . '.mo';
		}
	}
	return $mofile;
}
add_filter( 'load_textdomain_mofile', 'wimbblock_extra_textdomain', 10, 2 );

// Updates from Github
function wimbblock_updates_from_github() {
	$name          = 'Updates by hupe13 hosted on GitHub';
	$ghu_url       = 'https://github.com/hupe13/ghu-update-puc';
	$ghu_php       = 'ghu-update-puc.php';
	$ghu_settings  = 'options-general.php?page=ghu-update-puc">Github Update PUC</a>';
	$settings_page = '';
	echo '<h2>' . wp_kses_post( 'Updates in WordPress way' ) . '</h2>';
	if ( is_multisite() ) {
		if ( strpos(
			implode(
				',',
				array_keys(
					get_site_option( 'active_sitewide_plugins', array() )
				)
			),
			$ghu_php
		) !== false
		) {
			$settings_page = $ghu_settings;
		}
		if ( $settings_page !== '' ) {
			echo wp_kses_post(
				'To manage and receive updates, open <a href="' .
				get_site_url( get_main_site_id() ) .
				'/wp-admin/' . $settings_page . '.'
			);
		} else {
			echo wp_kses_post(
				'To receive updates, go to the <a href="' .
				esc_url( network_admin_url() ) .
				'plugins.php">network dashboard</a> and install and network activate ' .
				'<a href=' . $ghu_url . '>' . $name . '</a>.'
			);
		}
	} else {
		// Single site
		if ( strpos(
			implode(
				',',
				get_option( 'active_plugins', array() )
			),
			$ghu_php
		) !== false ) {
			$settings_page = $ghu_settings;
		}
		if ( $settings_page !== '' ) {
			echo wp_kses_post(
				'To manage and receive updates, open <a href="' .
				esc_url( admin_url() ) .
				$settings_page . '.'
			);
		} else {
			echo wp_kses_post(
				'To receive updates, go to the <a href="' .
				esc_url( admin_url() ) .
				'plugins.php">dashboard</a> and install and activate ' .
				'<a href=' . $ghu_url . '>' . $name . '</a>.'
			);
		}
	}
}
