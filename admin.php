<?php
/**
 *  Admin interface
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

// linkes Menu
function wimbblock_add_sub_page() {
	add_submenu_page(
		'options-general.php',
		__( 'WIMB and Block', 'wimb-and-block' ),
		__( 'WIMB and Block', 'wimb-and-block' ),
		'manage_options',
		WIMBBLOCK_NAME,
		'wimbblock_admin',
	);
}
add_action( 'admin_menu', 'wimbblock_add_sub_page' );

// Admin page for the plugin
function wimbblock_admin() {
	wp_enqueue_style(
		'wimbblock-css',
		plugins_url( dirname( WIMBBLOCK_BASENAME ) . '/admin/admin.css' ),
		array(),
		1
	);
	echo '<h2>' . esc_html__( 'Block old browser versions and suspicious browsers', 'wimb-and-block' ) . '</h2>';
	echo '<h3>' . esc_html__( 'Help and Options', 'wimb-and-block' ) . '</h3>';

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$active_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ?? 'help' ) );

	echo '<div class="maxwidth1000">';
	wimbblock_admin_tabs();

	if ( $active_tab === 'settings' ) {
		if ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMBBLOCK_BASENAME ) ) {
			echo '<p>';
			echo wp_kses_post(
				wp_sprintf(
					/* translators: %1$s and %2$s is a link. */
					__( 'You can change this setting on the %1$smain site%2$s.', 'wimb-and-block' ),
					'<a href="' . get_site_url( get_main_site_id() ) . '/wp-admin/admin.php?page=' . WIMBBLOCK_NAME . '&tab=' . $active_tab . '">',
					'</a>'
				)
			);
			echo '</p>';
		}
		$wimbblock_options = wimbblock_get_options_db();
		if ( $wimbblock_options['error'] === 1 ) {
			echo '<p><b>' . esc_html__( 'There is an error in your settings.', 'wimb-and-block' ) . '</b></p>';
		}
		echo '<h3>' . esc_html( __( 'Emergency button', 'wimb-and-block' ) ) . '</h3>';
		wimbblock_emergency_help();
		echo '<form method="post" action="options.php">';
		settings_fields( 'wimbblock_emergency' );
		wp_nonce_field( 'wimbblock_emergency', 'wimbblock_emergency_nonce' );
		do_settings_sections( 'wimbblock_emergency' );
		if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMBBLOCK_BASENAME ) ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				submit_button();
			}
		}
		echo '</form>';
		echo '<hr class="adminhrule">';

		echo '<h3>' . esc_html( __( 'Settings WIMB', 'wimb-and-block' ) ) . '</h3>';
		echo '<form method="post" action="options.php">';
		settings_fields( 'wimbblock_settings' );
		wp_nonce_field( 'wimbblock', 'wimbblock_nonce' );
		do_settings_sections( 'wimbblock_settings' );
		$wimbblock_options = wimbblock_get_option( 'wimbblock_settings' );
		if ( $wimbblock_options === false || count( $wimbblock_options ) === 0 ) {
			echo wp_kses_post( __( 'Please submit the form twice if you are configuring a remote database and it does not yet exist.', 'wimb-and-block' ) );
		}
		if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMBBLOCK_BASENAME ) ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				submit_button();
				echo '<p>' . esc_html( __( 'To change the settings, first reset them all.', 'wimb-and-block' ) ) . '</p>';
				submit_button( __( 'Reset', 'wimb-and-block' ), 'delete', 'delete', false );
			}
		}
		echo '</form>';
		echo '<hr class="adminhrule">';
		echo '<form method="post" action="options.php">';
		settings_fields( 'wimbblock_settings_deleting' );
		do_settings_sections( 'wimbblock_settings_deleting' );
		$wimbblock_options = wimbblock_get_options_db();
		if ( $wimbblock_options['error'] === '0' ) {
			if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMBBLOCK_BASENAME ) ) ) {
				if ( current_user_can( 'manage_options' ) ) {
					wp_nonce_field( 'wimbblock_deleting', 'wimbblock_deleting_nonce' );
					submit_button();
				}
			}
		}
		echo '</form>';
	} elseif ( strpos( $active_tab, 'versions' ) !== false ) {
		echo '<h3>' . esc_html( __( 'Versions Control', 'wimb-and-block' ) ) . '</h3>';
		if ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMBBLOCK_BASENAME ) ) {
			echo '<p>';
			echo wp_kses_post(
				wp_sprintf(
				/* translators: %1$s and %2$s is a link. */
					__( 'You can change this setting on the %1$smain site%2$s.', 'wimb-and-block' ),
					'<a href="' . get_site_url( get_main_site_id() ) . '/wp-admin/admin.php?page=' . WIMBBLOCK_NAME . '&tab=' . $active_tab . '">',
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
		if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMBBLOCK_BASENAME ) ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				submit_button();
				submit_button( __( 'Reset', 'wimb-and-block' ), 'delete', 'delete', false );
			}
		}
		echo '</form>';
		echo '<form method="post" action="options.php">';
		settings_fields( 'wimbblock_systems' );
		wp_nonce_field( 'wimbblock', 'wimbblock_nonce' );
		do_settings_sections( 'wimbblock_systems' );
		if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMBBLOCK_BASENAME ) ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				submit_button();
				submit_button( __( 'Reset', 'wimb-and-block' ), 'delete', 'delete', false );
			}
		}
		echo '</form>';
	} elseif ( $active_tab === 'last24' ) {
		require_once __DIR__ . '/admin/last24.php';
		echo '<h3>' . esc_html( __( 'WIMB last 24 hours', 'wimb-and-block' ) ) . '</h3>';
		wimbblock_daily_table();
	} elseif ( $active_tab === 'block' ) {
		require_once __DIR__ . '/admin/block-unblock.php';
		echo '<h3>' . esc_html( __( 'Search / block / unblock entries', 'wimb-and-block' ) ) . '</h3>';
		wimbblock_block_unblock_main();
	} elseif ( $active_tab === 'montly' ) {
		require_once __DIR__ . '/admin/monthly.php';
	} elseif ( $active_tab === 'exclude' ) {
		if ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMBBLOCK_BASENAME ) ) {
			echo '<p>';
			echo wp_kses_post(
				wp_sprintf(
				/* translators: %1$s and %2$s is a link. */
					__( 'You can change this setting on the %1$smain site%2$s.', 'wimb-and-block' ),
					'<a href="' . get_site_url( get_main_site_id() ) . '/wp-admin/admin.php?page=' . WIMBBLOCK_NAME . '&tab=' . $active_tab . '">',
					'</a>'
				)
			);
			echo '</p>';
		}
		wimbblock_exclude_help();
		echo '<form method="post" action="options.php">';
		settings_fields( 'wimbblock_exclude' );
		wp_nonce_field( 'wimbblock', 'wimbblock_nonce' );
		do_settings_sections( 'wimbblock_exclude' );
		if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMBBLOCK_BASENAME ) ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				submit_button();
				// submit_button( __( 'Reset', 'wimb-and-block' ), 'delete', 'delete', false );
			}
		}
		echo '</form>';
		echo '<hr class="adminhrule">';
		wimbblock_always_help();
		echo '<form method="post" action="options.php">';
		settings_fields( 'wimbblock_always' );
		wp_nonce_field( 'wimbblock', 'wimbblock_nonce' );
		do_settings_sections( 'wimbblock_always' );
		if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMBBLOCK_BASENAME ) ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				submit_button();
				// submit_button( __( 'Reset', 'wimb-and-block' ), 'delete', 'delete', false );
			}
		}
		echo '</form>';

	} elseif ( $active_tab === 'robots' ) {
		require_once __DIR__ . '/admin/robots.php';
		wimbblock_robots_htaccess();
	} elseif ( $active_tab === 'logging' ) {
			echo '<h2>' . wp_kses_post( __( 'Logging', 'wimb-and-block' ) ) . '</h2>';
		if ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( WIMBBLOCK_BASENAME ) ) {
			echo '<p>';
			echo wp_kses_post(
				wp_sprintf(
				/* translators: %1$s and %2$s is a link. */
					__( 'You can change this setting on the %1$smain site%2$s.', 'wimb-and-block' ),
					'<a href="' . get_site_url( get_main_site_id() ) . '/wp-admin/admin.php?page=' . WIMBBLOCK_NAME . '&tab=' . $active_tab . '">',
					'</a>'
				)
			);
			echo '</p>';
		}
		wimbblock_log_admin_page();
	} else {
		if ( function_exists( 'wimbblock_updates_from_github' ) ) {
			wimbblock_updates_from_github();
		}
		require_once __DIR__ . '/admin/help.php';
		// echo wp_kses_post( wimbblock_help() );
		echo wp_kses_post( wimbblock_help_readme( '/wp-content/plugins/' . WIMBBLOCK_NAME . '/readme.txt' ) );
	}
	echo '</div>';
}

function wimbblock_admin_tabs() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$active_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ?? 'help' ) );

	echo '<h3 class="nav-tab-wrapper">';
	echo '<a href="' . esc_url( '?page=' . WIMBBLOCK_NAME . '&tab=help' ) . '" class="nav-tab';
	echo $active_tab === 'help' ? ' nav-tab-active' : '';
	echo '">' . esc_html__( 'Help', 'wimb-and-block' ) . '</a>' . "\n";

	$tabs = array();

	$wimbblock_wpdb_options = wimbblock_get_options_db();
	if ( $wimbblock_wpdb_options['error'] === '0' ) {
		$tabs[] = array(
			'tab'   => 'last24',
			'title' => __( 'WIMB last 24 hours', 'wimb-and-block' ),
		);
		$tabs[] = array(
			'tab'   => 'montly',
			'title' => __( 'Overview and maintenance', 'wimb-and-block' ),
		);
		$tabs[] = array(
			'tab'   => 'block',
			'title' => __( 'Search / block / unblock entries', 'wimb-and-block' ),
		);
		$tabs[] = array(
			'tab'   => 'exclude',
			'title' => __( 'Exclude / always block', 'wimb-and-block' ),
		);
		$tabs[] = array(
			'tab'   => 'versions',
			'title' => __( 'Versions Control', 'wimb-and-block' ),
		);
		$tabs[] = array(
			'tab'   => 'settings',
			'title' => __( 'Settings WIMB', 'wimb-and-block' ),
		);
		$tabs[] = array(
			'tab'   => 'logging',
			'title' => __( 'Logging', 'wimb-and-block' ),
		);
		$tabs[] = array(
			'tab'   => 'robots',
			'title' => __( 'robots.txt', 'wimb-and-block' ),
		);
	} else {
		$tabs[] = array(
			'tab'   => 'settings',
			'title' => __( 'Settings WIMB', 'wimb-and-block' ),
		);
		$tabs[] = array(
			'tab'   => 'logging',
			'title' => __( 'Logging', 'wimb-and-block' ),
		);
		$tabs[] = array(
			'tab'   => 'exclude',
			'title' => __( 'Exclude / always block', 'wimb-and-block' ),
		);
		$tabs[] = array(
			'tab'   => 'versions',
			'title' => __( 'Versions Control', 'wimb-and-block' ),
		);
		$tabs[] = array(
			'tab'   => 'robots',
			'title' => __( 'robots.txt', 'wimb-and-block' ),
		);
	}

	foreach ( $tabs as $tab ) {
		echo '<a href="' . esc_url( '?page=' . WIMBBLOCK_NAME . '&tab=' . $tab['tab'] ) . '" class="nav-tab';
		$active = ( $active_tab === $tab['tab'] ) ? ' nav-tab-active' : '';
		if ( isset( $tab['strpos'] ) ) {
			if ( strpos( $active_tab, $tab['strpos'] ) !== false ) {
				$active = ' nav-tab-active';
			}
		}
		echo esc_attr( $active );
		echo '">' . esc_html( $tab['title'] ) . '</a>' . "\n";
	}
	echo '</h3>';
}

// Display array as table
function wimbblock_html_table( $data = array() ) {
	$rows = array();
	foreach ( $data as $row ) {
		$cells = array();
		foreach ( $row as $cell ) {
			$cells[] = '<td align="center">' . "{$cell}</td>";
		}
		$rows[] = '<tr>' . implode( '', $cells ) . '</tr>' . "\n";
	}
	$head = '<div style="width:80%;">';
	$head = $head . '<figure class="wp-block-table aligncenter is-style-stripes"><table border=1>';
	return $head . implode( '', $rows ) . '</table></figure></div>';
}

function wimbblock_prozent( $teil, $gesamt ) {
	return $gesamt > 0 ? number_format( $teil * 100 / $gesamt, 0, '', '' ) . '%' : '';
}
