<?php
/**
 *  Settings for wimb-and-block Blocking
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_blocking_tab( $active_tab ) {

	if ( $active_tab === 'blocking' ) {
		echo '<h3>' . wp_kses_post( wimbblock_blocking_tabs() ) . '</h3>';
		echo '<h3>' . esc_html( __( 'Blog browsers, bots and crawlers', 'wimb-and-block' ) ) . '</h3>';

		if ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMB_BASENAME ) ) {
			echo '<p>';
			echo wp_kses_post(
				wp_sprintf(
				/* translators: %1$s and %2$s is a link. */
					__( 'You can change this setting on the %1$smain site%2$s.', 'wimb-and-block' ),
					'<a href="' . get_site_url( get_main_site_id() ) . '/wp-admin/admin.php?page=' . WIMB_NAME . '&tab=' . $active_tab . '">',
					'</a>'
				)
			);
			echo '</p>';
		}
		echo wp_kses_post( wimbblock_browsers_help() );
		echo '<form method="post" action="options.php">';
		settings_fields( 'wimbblock_browsers' );
		wp_nonce_field( 'wimbblock', 'wimbblock_nonce' );
		do_settings_sections( 'wimbblock_browsers' );
		if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMB_BASENAME ) ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				submit_button();
				submit_button( __( 'Reset', 'wimb-and-block' ), 'delete', 'delete', false );
			}
		}
		echo '</form>';
	} elseif ( $active_tab === 'blockingunknownempty' ) {
		echo '<h3>' . wp_kses_post( wimbblock_blocking_tabs() ) . '</h3>';
		if ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMB_BASENAME ) ) {
			echo '<p>';
			echo wp_kses_post(
				wp_sprintf(
				/* translators: %1$s and %2$s is a link. */
					__( 'You can change this setting on the %1$smain site%2$s.', 'wimb-and-block' ),
					'<a href="' . get_site_url( get_main_site_id() ) . '/wp-admin/admin.php?page=' . WIMB_NAME . '&tab=' . $active_tab . '">',
					'</a>'
				)
			);
			echo '</p>';
		}

				wimbblock_exlude_help();
				echo '<form method="post" action="options.php">';
				settings_fields( 'wimbblock_exclude' );
				wp_nonce_field( 'wimbblock', 'wimbblock_nonce' );
				do_settings_sections( 'wimbblock_exclude' );
		if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMB_BASENAME ) ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				submit_button();
				// submit_button( __( 'Reset', 'wimb-and-block' ), 'delete', 'delete', false );
			}
		}
				echo '</form>';

		wimbblock_unknown_empty_help();
		echo '<form method="post" action="options.php">';
		settings_fields( 'wimbblock_unknown_empty' );
		wp_nonce_field( 'wimbblock', 'wimbblock_nonce' );
		do_settings_sections( 'wimbblock_unknown_empty' );
		if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMB_BASENAME ) ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				submit_button();
				// submit_button( __( 'Reset', 'wimb-and-block' ), 'delete', 'delete', false );
			}
		}
		echo '</form>';
	}
}

function wimbblock_blocking_tabs() {
	$tabs = array(
		array(
			'tab'   => 'blocking',
			'title' => __( 'versions control', 'wimb-and-block' ),
		),
		array(
			'tab'   => 'blockingunknownempty',
			'title' => __( 'unknown and empty', 'wimb-and-block' ),
		),
	);

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$active_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ?? 'blocking' ) );
	$textheader = '<div class="nav-tab-wrapper">';

	foreach ( $tabs as $tab ) {
		$textheader = $textheader . '<a href="?page=' . WIMB_NAME . '&tab=' . $tab['tab'] . '" class="nav-tab';
		$active     = ( $active_tab === $tab['tab'] ) ? ' nav-tab-active' : '';
		$textheader = $textheader . $active;
		$textheader = $textheader . '">' . $tab['title'] . '</a>' . "\n";
	}

	$textheader = $textheader . '</div>';
	return $textheader;
}
