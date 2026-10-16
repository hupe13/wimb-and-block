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
		plugins_url( dirname( plugin_basename( __FILE__ ) ) . '/admin/admin.css' ),
		array(),
		'1'
	);
	wp_enqueue_script(
		'wimbblock-table-js',
		plugins_url( dirname( plugin_basename( __FILE__ ) ) . '/admin/table.js' ),
		array(),
		'1',
		true
	);
	echo '<h2>' . esc_html__( 'Block old browser versions and suspicious browsers', 'wimb-and-block' ) . '</h2>';
	echo '<h3>' . esc_html__( 'Help and Options', 'wimb-and-block' ) . '</h3>';

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$active_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ?? 'help' ) );

	echo '<div class="maxwidth1000">';
	wimbblock_admin_tabs();

	global $wimbblock_basename;
	if ( $active_tab === 'settings' ) {
		if ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( $wimbblock_basename ) ) {
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
		if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( $wimbblock_basename ) ) ) {
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
		if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( $wimbblock_basename ) ) ) {
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
			if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( $wimbblock_basename ) ) ) {
				if ( current_user_can( 'manage_options' ) ) {
					wp_nonce_field( 'wimbblock_deleting', 'wimbblock_deleting_nonce' );
					submit_button();
				}
			}
		}
		echo '</form>';
	} elseif ( strpos( $active_tab, 'versions' ) !== false ) {
		echo '<h3>' . esc_html( __( 'Versions Control - block outdated browsers', 'wimb-and-block' ) ) . '</h3>';
		if ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( $wimbblock_basename ) ) {
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
		if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( $wimbblock_basename ) ) ) {
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
		wimbblock_systems_help();
		if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( $wimbblock_basename ) ) ) {
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
	} elseif ( $active_tab === 'monthly' ) {
		require_once __DIR__ . '/admin/stats-systems.php';
		require_once __DIR__ . '/admin/monthly.php';
	} elseif ( $active_tab === 'ai' ) {
		if ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( $wimbblock_basename ) ) {
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
		$options = wimbblock_get_options_db();
		if ( $options['location'] === 'remote' ) {
			echo wp_kses_post( '<p><div class="notice notice-info">' . __( 'You must configure these settings on each of your websites that use this database!', 'wimb-and-block' ) . '</div></p>' );
		}
		wimbblock_no_ai_help();
		echo '<form method="post" action="options.php">';
		settings_fields( 'wimbblock_block_ai' );
		wp_nonce_field( 'wimbblock_block_ai', 'wimbblock_block_ai_nonce' );
		do_settings_sections( 'wimbblock_block_ai' );
		if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( $wimbblock_basename ) ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				submit_button();
			}
		}
		echo '</form>';
		wimbblock_ai_in_database();
	} elseif ( $active_tab === 'exclude' ) {
		if ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( $wimbblock_basename ) ) {
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
		$options = wimbblock_get_options_db();
		if ( $options['location'] === 'remote' ) {
			echo wp_kses_post( '<p><div class="notice notice-info">' . __( 'You must configure these settings on each of your websites that use this database!', 'wimb-and-block' ) . '</div></p>' );
		}
		wimbblock_exclude_help();
		echo '<form method="post" action="options.php">';
		settings_fields( 'wimbblock_exclude' );
		wp_nonce_field( 'wimbblock', 'wimbblock_nonce' );
		do_settings_sections( 'wimbblock_exclude' );
		if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( $wimbblock_basename ) ) ) {
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
		if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( $wimbblock_basename ) ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				submit_button();
				// submit_button( __( 'Reset', 'wimb-and-block' ), 'delete', 'delete', false );
			}
		}
		echo '</form>';
	} elseif ( $active_tab === 'crawlers' ) {
		$options = wimbblock_get_options_db();
		if ( $options['rotate'] === 'yes' && is_main_site() ) {
			wimbblock_crawler_help();
			echo '<form method="post" action="options.php">';
			settings_fields( 'wimbblock_searchengines' );
			wp_nonce_field( 'wimbblock', 'wimbblock_nonce' );
			do_settings_sections( 'wimbblock_searchengines' );
			if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( $wimbblock_basename ) ) ) {
				if ( current_user_can( 'manage_options' ) ) {
					submit_button();
					// submit_button( __( 'Reset', 'wimb-and-block' ), 'delete', 'delete', false );
				}
			}
			echo '</form>';
		} else {
			wimbblock_crawler_help_elsewhere();
		}
	} elseif ( $active_tab === 'headers' ) {
		require_once __DIR__ . '/admin/header-checks.php';
		wimbblock_header_help();
	} elseif ( $active_tab === 'robots' ) {
		require_once __DIR__ . '/admin/robots.php';
		wimbblock_robots_htaccess();
	} elseif ( $active_tab === 'logging' ) {
		echo '<h2>' . wp_kses_post( __( 'Logging', 'wimb-and-block' ) ) . '</h2>';
		if ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( $wimbblock_basename ) ) {
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
	} elseif ( $active_tab === 'testing' ) {
		require_once __DIR__ . '/admin/testing-rules.php';
		echo '<form action="" method="post">';
		wp_nonce_field( 'wimbblock_test_tables', 'wimbblock_test_tables_nonce' );
		echo '<p><select name="table" onchange="this.form.submit()">';
		echo '<option value=" ">' . wp_kses_post( __( 'Select info', 'wimb-and-block' ) ) . '</option>';
		echo '<option value="1">' . wp_kses_post( __( 'Not blocked so far, but will be blocked in the future', 'wimb-and-block' ) ) . '</option>';
		echo '<option value="2">' . wp_kses_post( __( 'Currently blocked', 'wimb-and-block' ) ) . '</option>';
		echo '<option value="3">' . wp_kses_post( __( 'Always blocked', 'wimb-and-block' ) ) . '</option>';
		echo '<option value="4">' . wp_kses_post( __( 'Blocked AI', 'wimb-and-block' ) ) . '</option>';
		echo '</select>';
		echo '</form></p>';
		if ( ! empty( $_POST ) && check_admin_referer( 'wimbblock_test_tables', 'wimbblock_test_tables_nonce' ) ) {
			if ( isset( $_POST['table'] ) ) {
				switch ( $_POST['table'] ) {
					case '1':
						wimbblock_test_rules( false );
						break;
					case '2':
						wimbblock_test_rules( true );
						break;
					case '4':
						wimbblock_ai_in_database();
						break;
					default:
						wimbblock_test_always_blocked();
				}
			}
		}
	} elseif ( $active_tab === 'readme' ) {
		require_once __DIR__ . '/admin/help.php';
		// echo wp_kses_post( wimbblock_help() );
		echo wp_kses_post( wimbblock_help_readme( '/wp-content/plugins/' . WIMBBLOCK_NAME . '/readme.txt' ) );
	} else {
		if ( function_exists( 'wimbblock_updates_from_github' ) ) {
			wimbblock_updates_from_github();
		}
		require_once __DIR__ . '/admin/help.php';
		echo wp_kses_post( wimbblock_help_config() );
	}
	echo '</div>';
}

function wimbblock_admin_tabs() {
	$tabs = array();

	$tabs['help'] = array(
		'section' => 'help',
		'title'   => __( 'Help', 'wimb-and-block' ),
	);

	$tabs['readme'] = array(
		'section' => 'help',
		'title'   => __( 'README', 'wimb-and-block' ),
	);

	$tabs['settings'] = array(
		'section' => 'config',
		'title'   => __( 'Settings WIMB', 'wimb-and-block' ),
	);

	$tabs['robots'] = array(
		'section' => 'config',
		'title'   => __( 'robots.txt', 'wimb-and-block' ),
	);

	$tabs['logging'] = array(
		'section' => 'config',
		'title'   => __( 'Logging', 'wimb-and-block' ),
	);

	$tabs['crawlers'] = array(
		'section' => 'config',
		'title'   => __( 'Search Engines', 'wimb-and-block' ),
	);

	$tabs['ai'] = array(
		'section' => 'config',
		'title'   => __( 'AI crawlers', 'wimb-and-block' ),
	);

	$tabs['last24'] = array(
		'section' => 'check',
		'title'   => __( 'WIMB last 24 hours', 'wimb-and-block' ),
	);

	$tabs['monthly'] = array(
		'section' => 'check',
		'title'   => __( 'Overview and maintenance', 'wimb-and-block' ),
	);

	$tabs['versions'] = array(
		'section' => 'check',
		'title'   => __( 'Versions Control', 'wimb-and-block' ),
	);

	// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- is not a WP_Query
	$tabs['exclude'] = array(
		'section' => 'check',
		'title'   => __( 'Exclude / always block', 'wimb-and-block' ),
	);

	$tabs['block'] = array(
		'section' => 'check',
		'title'   => __( 'Search / block / unblock entries', 'wimb-and-block' ),
	);

	$tabs['testing'] = array(
		'section' => 'advanced',
		'title'   => __( 'Block rules results', 'wimb-and-block' ),
	);

	$tabs['headers'] = array(
		'section' => 'advanced',
		'title'   => __( 'Header Checks', 'wimb-and-block' ),
	);

	$sections = array(
		'help'     => __( 'Help', 'wimb-and-block' ),
		'config'   => __( 'Configuration', 'wimb-and-block' ),
		'check'    => __( 'Settings, maintenance and checks', 'wimb-and-block' ),
		'advanced' => __( 'Advanced information', 'wimb-and-block' ),
	);

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$active_tab     = sanitize_text_field( wp_unslash( $_GET['tab'] ?? 'help' ) );
	$active_section = $tabs[ $active_tab ]['section'];
	$tab_keys       = array_keys( $tabs );

	echo '<h3 class="nav-tab-wrapper">';
	foreach ( $sections as $section => $title ) {
		$key    = array_search( $section, array_column( $tabs, 'section' ), true );
		$tab    = $tab_keys[ $key ];
		$active = ( $section === $active_section ) ? ' nav-tab-active' : '';
		echo '<a href="' . esc_url( '?page=' . WIMBBLOCK_NAME . '&tab=' . $tab ) . '" class="nav-tab';
		echo esc_attr( $active );
		echo '">' . esc_html( $title ) . '</a>' . "\n";
	}
	echo '</h3>';
	echo '<h4 class="nav-tab-wrapper">';
	foreach ( $tabs as $tab => $cont ) {
		if ( $cont['section'] === $active_section ) {
			$active = ( $active_tab === $tab ) ? ' nav-tab-active' : '';
			echo '<a href="' . esc_url( '?page=' . WIMBBLOCK_NAME . '&tab=' . $tab ) . '" class="nav-tab';
			echo esc_attr( $active );
			echo '">' . esc_html( $cont['title'] ) . '</a>' . "\n";
		}
	}
	echo '</h4>';
}

// Display array as table
function wimbblock_html_table( $data = array(), $id = 'table' ) {
	$rows = array();
	foreach ( $data as $row ) {
		$cells = array();
		foreach ( $row as $cell ) {
			$cells[] = '<td align="center">' . "{$cell}</td>";
		}
		$rows[] = '<tr>' . implode( '', $cells ) . '</tr>' . "\n";
	}
	$head = '<div style="width:80%;">';
	$head = $head . '<figure class="wp-block-table aligncenter"><table border=1 class="alternate" id="' . $id . '">';
	return $head . implode( '', $rows ) . '</table></figure></div>';
}

function wimbblock_prozent( $teil, $gesamt ) {
	return $gesamt > 0 ? number_format( $teil * 100 / $gesamt, 0, '', '' ) . '%' : '';
}

function wimbblock_percent( $teil, $gesamt ) {
	return $gesamt > 0 ? number_format( $teil * 100 / $gesamt, 0, '', '' ) : '';
}
