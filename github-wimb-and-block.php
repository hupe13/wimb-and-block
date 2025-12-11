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
	load_plugin_textdomain( 'wimb-and-block', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
	load_plugin_textdomain( 'wimb-and-block-readme', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}
add_action( 'init', 'wimbblock_textdomain' );

// Updates from Github
function wimbblock_updates_from_github() {
	$name             = 'Updates created by hupe13 hosted on GitHub';
	$ghu_url          = 'https://github.com/hupe13/ghu-update-puc';
	$ghu_php_old      = 'leafext-update-github.php';
	$ghu_settings_old = 'admin.php?page=github-settings">Github settings</a>';
	$ghu_php          = 'ghu-update-puc.php';
	$ghu_settings     = 'options-general.php?page=ghu-update-puc">Github Update PUC</a>';
	$settings_page    = '';
	echo '<h2>' . wp_kses_post( 'Updates in WordPress way' ) . '</h2>';
	if ( is_multisite() ) {
		if ( strpos(
			implode(
				',',
				array_keys(
					get_site_option( 'active_sitewide_plugins', array() )
				)
			),
			$ghu_php_old
		) !== false ) {
			$settings_page = $ghu_settings_old;
		} elseif ( strpos(
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
			$ghu_php_old
		) !== false ) {
			$settings_page = $ghu_settings_old;
		} elseif ( strpos(
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
