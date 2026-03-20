<?php
/**
 * Functions for database crawler
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_searchengines_init() {
	add_settings_section( 'wimbblock_searchengines', '', '__return_empty_string', 'wimbblock_searchengines' );
	add_settings_field( 'wimbblock_searchengines', '', 'wimbblock_crawler_form', 'wimbblock_searchengines', 'wimbblock_searchengines' );
	if ( get_option( 'wimbblock_searchengines' ) === false ) {
		add_option( 'wimbblock_searchengines', array() );
	}
	register_setting(
		'wimbblock_searchengines',
		'wimbblock_searchengines',
		array(
			'type'    => 'array',
			'default' => array(),
		)
	);
}
add_action( 'admin_init', 'wimbblock_searchengines_init' );

// Baue Abfrage der Params
function wimbblock_crawler_form() {
	if ( ! current_user_can( 'manage_options' ) ) {
		$disabled = ' disabled ';
	} else {
		$disabled = '';
	}

	$jsons  = wimbblock_get_allowed_jsons();
	$params = wimbblock_crawlers_params();

	foreach ( $params as $crawler => $value ) {
		// var_dump( $value );
		// echo '<p>';
		if ( $value['json'] !== '' ) {
			echo '<h4>' . esc_html( $crawler ) . '</h4>';

			echo esc_html( __( 'Load file from', 'wimb-and-block' ) ) . ' <i>' . esc_html( $value['json'] ) . '</i>.';
			if ( count( $value['names'] ) === 0 ) {
				echo '<br>' . esc_html(
					wp_sprintf(
						/* translators: %s is a crawler. */
						__( 'If you do not enable this option, IP addresses will not be checked to see if they originate from %s.', 'wimb-and-block' ),
						$crawler
					)
				);
			}
			if ( $value['json-notice'] !== '' ) {
				echo '<br>' . esc_html( $value['json-notice'] );
			}
			echo '<p><input ' . esc_attr( $disabled ) . ' type="radio" name="wimbblock_searchengines[' . esc_attr( $crawler ) . ']" value=1 ';
			checked( $jsons[ $crawler ] === '1' );
			echo '> ' . esc_html__( 'yes', 'wimb-and-block' ) . ' &nbsp;&nbsp; ';
			echo '<input ' . esc_attr( $disabled ) . ' type="radio" name="wimbblock_searchengines[' . esc_attr( $crawler ) . ']" value=0 ';
			checked( $jsons[ $crawler ] === '0' );
			echo '> ' . esc_html__( 'no', 'wimb-and-block' ) . '</p>';
		}
	}
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wimbblock_crawler_validate( $params ) {
	// var_dump($params);wp_die();
	if ( ! empty( $_POST ) && check_admin_referer( 'wimbblock', 'wimbblock_nonce' ) ) {
		if ( isset( $_POST['submit'] ) ) {
			return $params;
		}
		if ( isset( $_POST['delete'] ) ) {
			delete_option( 'wimbblock_searchengines' );
		}
	}
	return false;
}

add_action(
	'update_option_wimbblock_searchengines',
	function ( $old_settings, $new_settings ) {
		if ( ! $new_settings ) {
			wimbblock_error_log( 'update_option_wimbblock_searchengines did not run' );
			return;
		}
		wimbblock_update_crawlers();
		wimbblock_set_transients_crawlers_in_table();
	},
	1,
	2
);

function wimbblock_crawler_help() {
	$text = '<h3>' . __( 'Settings Search Engines', 'wimb-and-block' ) . '</h3>';

	$text .= __( 'Each search engine identifies itself with the user agent string.', 'wimb-and-block' ) . ' ';
	$text .= __( 'This string is easy to fake.', 'wimb-and-block' ) . ' ';
	$text .= __( 'Therefore, the plugin checks whether the IP address really comes from the search engine.', 'wimb-and-block' ) . ' ';
	$text .= __( 'By default, all IP addresses are checked via DNS.', 'wimb-and-block' ) . ' ';
	$text .= __( 'Alternatively you can enable the loading of a JSON file with IP ranges to do this.', 'wimb-and-block' ) . ' ';
	$text .= __( 'Only IPv4 addresses are validated via JSON files, IPv6 is validated via DNS.', 'wimb-and-block' ) . ' ';

	$jsons    = wimbblock_get_allowed_jsons();
	$params   = wimbblock_crawlers_params();
	$dns_only = array();

	foreach ( $params as $crawler => $value ) {
		if ( $value['json'] === '' ) {
			$dns_only[] = $crawler;
		}
	}

	$text .= '<p>' . implode( ', ', $dns_only ) . ' ' . __( 'only offer verification via DNS.', 'wimb-and-block' ) . '</p>';
	// $text .= '<p>' . __( '', 'wimb-and-block' ) . '</p>';

	echo wp_kses_post( $text );
}

function wimbblock_crawler_help_elsewhere() {
	global $wimbblock_basename;
	$text    = '<h3>' . __( 'Settings Search Engines', 'wimb-and-block' ) . '</h3>';
	$options = wimbblock_get_options_db();
	if ( $options['location'] === 'remote' && $options['rotate'] !== 'yes' ) {
		$text .= '<p><div class="notice notice-info">' .
			wp_sprintf(
				/* Translators: %s is an option */
				__( 'The settings are the same as on the website configured with "%s" = yes.', 'wimb-and-block' ),
				__( 'Rotate the table on this site', 'wimb-and-block' )
			) .
			'</div></p>';
	} elseif ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( $wimbblock_basename ) ) {
			$text .= '<p>';
			$text .=
				wp_sprintf(
				/* translators: %1$s and %2$s is a link. */
					__( 'You can change this setting on the %1$smain site%2$s.', 'wimb-and-block' ),
					'<a href="' . get_site_url( get_main_site_id() ) . '/wp-admin/admin.php?page=' . WIMBBLOCK_NAME . '&tab=crawlers">',
					'</a>'
				);
			$text .= '</p>';
	}

	$jsons        = wimbblock_get_allowed_jsons();
	$jsons_true   = wimbblock_set_transients_crawlers_in_table();
	$params       = wimbblock_crawlers_params();
	$dns_only     = array();
	$json_checked = array();
	$json_dns     = array();

	foreach ( $params as $crawler => $value ) {
		if ( $value['json'] === '' ) {
			$dns_only[] = $crawler;
		} else {
			$json_dns[] = $crawler;
		}
	}

	$text .= '<ul><li class="adminli">' .
	implode( ', ', $dns_only ) . ' ' .
	__( 'only offer verification via DNS.', 'wimb-and-block' ) . '</li>';
	$text .= '<li class="adminli">' .
	wp_sprintf(
	/* translators: %1$s are crawlers. */
		__( 'The IP addresses of %1$s are validated via DNS.', 'wimb-and-block' ),
		implode( ', ', $json_dns )
	) . '</li>';
	$text .= '<li class="adminli">' .
	wp_sprintf(
	/* translators: %1$s are crawlers. */
		__( 'The IP addresses of %1$s are validated via JSON files.', 'wimb-and-block' ),
		implode( ', ', $jsons_true )
	) . '</li></ul>';

	echo wp_kses_post( $text );
}
