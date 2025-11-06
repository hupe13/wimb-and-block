<?php
/**
 *  Settings for robots
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

wp_enqueue_style(
	'prism-css',
	plugins_url( dirname( WIMB_BASENAME ) . '/pkg/prism/prism.css' ),
	array(),
	1
);
wp_enqueue_script(
	'prism-js',
	plugins_url( dirname( WIMB_BASENAME ) . '/pkg/prism/prism.js' ),
	array(),
	'1',
	true
);

function wimbblock_robots_htaccess() {
	wimbblock_hint_multisite();
	$site       = wp_parse_url( get_home_url() );
	$serverroot = $site['host'];
	echo '<h3>robots.txt - ' . wp_kses_post( $serverroot ) . '</h3>';
	if ( ! isset( $site['path'] ) ) {
		global $wp_filesystem;
		if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		WP_Filesystem();
		if ( $wp_filesystem->exists( ABSPATH . '.htaccess' ) && $wp_filesystem->is_writable( ABSPATH . '.htaccess' ) ) {
			if ( ! $wp_filesystem->exists( ABSPATH . 'robots.txt' ) ) {
				echo '<p>' .
				wp_kses_post(
					__( 'You do not seem to have your own robots.txt file, so WordPress provides it. Then you can test the configuration without any changes of the .htaccess.', 'wimb-and-block' )
				) . '</p>';
				wimbblock_htaccess_display_config_form();
				wimbblock_htaccess_handle_config_form();
			} else {
				echo wp_kses_post(
					__( 'You can configure some rewrite rules, to provide a robots.txt to enable or to disable crawling for a browser. If crawling is disabled, access to your website will be blocked for that browser.', 'wimb-and-block' )
				);
				echo wp_kses_post(
					'<h4>' .
					__( 'Form to handle the entry in .htaccess', 'wimb-and-block' )
					. '</h4>'
				);
				echo '<p>' .
				wp_kses_post(
					__(
						'The plugin can modify the .htaccess file.',
						'wimb-and-block'
					)
				) . '</p>';

				wimbblock_display_htaccess_form();
				wimbblock_handle_htaccess_form();
				wimbblock_edit_rules_htaccess( true );
				wimbblock_htaccess_display_config_form();
				wimbblock_htaccess_handle_config_form();
			}
		} else {
			echo '<p>' .
			wp_kses_post(
				__(
					'The plugin cannot modify the .htaccess file because it is read-only.',
					'wimb-and-block'
				)
			) . '</p>';
			wimbblock_edit_rules_htaccess( false );
			wimbblock_htaccess_display_config_form();
			wimbblock_htaccess_handle_config_form();
		}
	} else {
		$test = wimbblock_goto_robots_site();
		if ( ! $test ) {
			wimbblock_test_subdir();
			wimbblock_htaccess_subdir_help();
		}
	}
}

function wimbblock_htaccess_subdir_help() {
	$site      = wp_parse_url( get_home_url() );
	$codestyle = ' class="language-coffeescript"';
	$text      = '<p>';
	$text     .= wp_sprintf(
	/* translators: %1$s and %2$s is a link. */
		__( 'Write in your .htaccess in the root directory of %s:', 'wimb-and-block' ),
		$site['host']
	);
	$text .= '</p>';
	$text .= '<pre' . $codestyle . '><code' . $codestyle . '>RewriteCond %{HTTP_USER_AGENT} !WordPress [NC]
RewriteRule ^robots.txt$ ' . $site['path'] . '/robots-check/ [flags]</code></pre>';
	$text .= '<p>';
	$text .= wp_sprintf(
	/* translators: %1$s and %2$s is a link. */
		__( 'Please check, if you need some flags like %1$s or %2$s or other or nothing.', 'wimb-and-block' ),
		'<code ' . $codestyle . '">[R]</code>',
		'<code ' . $codestyle . '">[R,L]</code>'
	);
	$text .= '</p>';
	echo wp_kses_post( $text );
	wimbblock_htaccess_display_config_form();
	wimbblock_htaccess_handle_config_form();
}

function wimbblock_edit_rules_htaccess( $form ) {
	$site      = wp_parse_url( get_home_url() );
	$codestyle = ' class="language-coffeescript"';
	$text      =
		'<h4>' .
		__( 'Edit .htaccess', 'wimb-and-block' )
		. '</h4>';
	if ( $form ) {
		$text .= '<p>';
		$text .= wp_sprintf(
		/* translators: is a host. */
			__( 'If you do not want to use the form write in your .htaccess in the root directory of  %s', 'wimb-and-block' ),
			$site['host']
		);
		$text .= ':</p>';
	}
	$text .= '<p><pre' . $codestyle . '><code' . $codestyle . '>RewriteCond %{HTTP_USER_AGENT} !WordPress [NC]
RewriteRule ^robots.txt$ /robots-check/</code></pre></p>';
	echo wp_kses_post( $text );
}

function wimbblock_test_subdir() {
	$site       = wp_parse_url( get_home_url() );
	$serverroot = $site['host'];
	if ( isset( $site['path'] ) ) {
		echo '<p>' .
		wp_kses_post(
			__(
				"The plugin cannot modify the .htaccess file in the server's root directory because the WordPress installation is located in a subdirectory.",
				'wimb-and-block'
			)
		) . '</p>';
	}
}

function wimbblock_hint_multisite() {
	if ( is_multisite() ) {
		$domains = array();
		foreach ( get_sites() as $site ) {
			$domains[ $site->blog_id ] = $site->domain;
		}
		$domains = array_unique( $domains );
		$text    = '';
		if ( count( $domains ) > 1 ) {
			$text .= '<p><div class="notice notice-info">';
			$text .= __( 'You must configure these settings on each of your domains!', 'wimb-and-block' );
			$text .= '<ul>';
			foreach ( $domains as $blog_id => $domain ) {
				$text .= '<li class="adminli"><a href="' . get_site_url( $blog_id ) . '/wp-admin/admin.php?page=' . WIMB_NAME . '&tab=robots">' . $domain . '</a></li>';
			}
			$text .= '</ul></div></p>';
		}
		echo wp_kses_post( $text );
	}
}

function wimbblock_goto_robots_site() {
	if ( is_multisite() ) {
		$site = wp_parse_url( get_home_url() );
		foreach ( get_sites() as $multisite ) {
			if ( $multisite->path === '/' && $multisite->domain === $site['host'] ) {
				echo '<p>';
				echo wp_kses_post(
					wp_sprintf(
						/* translators: %1$s and %2$s is a link. */
						__( 'You can do it on %1$sthis site%2$s.', 'wimb-and-block' ),
						'<a href="' . get_site_url( $multisite->blog_id ) . '/wp-admin/admin.php?page=' . WIMB_NAME . '&tab=robots">',
						'</a>'
					)
				);
				echo '</p>';
				return true;
			}
		}
	}
	return false;
}

function wimbblock_display_htaccess_form() {
	echo '<form method="post" action="options-general.php?page=' . esc_html( WIMB_NAME ) . '&tab=robots">';
	if ( current_user_can( 'manage_options' ) ) {
		wp_nonce_field( 'wimbblock_robots', 'wimbblock_robots_nonce' );
		submit_button( __( 'Write .htaccess file', 'wimb-and-block' ), 'primary', 'htaccess' );
		echo wp_kses_post(
			__(
				'There is no WordPress function to delete the rules completely. This means that the comments remain.',
				'wimb-and-block'
			)
		);
		submit_button( __( 'Delete the rules', 'wimb-and-block' ), 'primary', 'delete' );
	}
	echo '</form>';
}

function wimbblock_handle_htaccess_form() {
	if ( ! empty( $_POST ) && check_admin_referer( 'wimbblock_robots', 'wimbblock_robots_nonce' ) ) {
		if ( isset( $_POST['htaccess'] ) ) {
			$lines   = array();
			$lines[] = 'RewriteCond %{HTTP_USER_AGENT} !WordPress [NC]';
			$lines[] = 'RewriteRule ^robots.txt$ /robots-check/';
			insert_with_markers( ABSPATH . '.htaccess', 'wimb-and-block', implode( "\n", $lines ) );
		}
		if ( isset( $_POST['delete'] ) ) {
			echo 'delete';
			insert_with_markers( ABSPATH . '.htaccess', 'wimb-and-block', '' );
		}
	}
}

function wimbblock_htaccess_display_config_form() {
	echo wp_kses_post(
		'<h4>' .
		__( 'Test your configuration', 'wimb-and-block' )
		. '</h4>'
	);
	echo '<form method="post" action="options-general.php?page=' . esc_html( WIMB_NAME ) . '&tab=robots">';
	if ( current_user_can( 'manage_options' ) ) {
		wp_nonce_field( 'wimbblock_robots', 'wimbblock_robots_nonce' );
		submit_button( __( 'Test', 'wimb-and-block' ), 'primary', 'test' );
	}
	echo '</form>';
}

function wimbblock_htaccess_handle_config_form() {
	if ( ! empty( $_POST ) && check_admin_referer( 'wimbblock_robots', 'wimbblock_robots_nonce' ) ) {
		if ( isset( $_POST['test'] ) ) {
			$site       = wp_parse_url( get_home_url() );
			$serverroot = $site['host'];
			$path       = isset( $site['path'] ) ? $site['path'] : '';
			$urls       = array( 'https://' . $serverroot . '/robots.txt', 'https://' . $serverroot . $path . '/robots-check/' );
			$this_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
			$agents     = array( $this_agent, 'wimb-and-block test agent' );
			foreach ( $agents as $agent ) {
				if ( $agent !== 'wimb-and-block test agent' ) {
					echo '<h3>' . wp_kses_post( __( 'Crawling is allowed:', 'wimb-and-block' ) ) . '</h3>';
				} else {
					echo '<h3>' . wp_kses_post( __( 'Crawling is disabled:', 'wimb-and-block' ) ) . '</h3>';
				}
				foreach ( $urls as $url ) {
					// var_dump( $url, $agent );
					echo '<h4>' . wp_kses_post( $url ) . '</h4>';
					$response = wp_remote_get( $url, array( 'user-agent' => $agent ) );
					if ( is_array( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
						echo '<pre>' . esc_html( $response['body'] ) . '</pre>'; // use the content
						if ( $agent === 'wimb-and-block test agent' ) {
							$expected = "User-agent: *\r\n" . 'Disallow: /' . "\r\n";
							if ( $response['body'] === $expected ) {
								echo wp_kses_post( __( 'This is the expected output.', 'wimb-and-block' ) );
							} else {
								echo wp_kses_post( __( 'Something is going wrong.', 'wimb-and-block' ) );
							}
						}
					} else {
						echo '<p>';
						echo wp_kses_post( wp_remote_retrieve_response_code( $response ) );
						echo ' - ';
						echo wp_kses_post( __( 'Something is going wrong. Check your rewrite rules.', 'wimb-and-block' ) );
						echo '</p>';
					}
				}
				$url = trailingslashit( get_home_url() );
				echo '<h4>' . wp_kses_post( $url ) . '</h4>';
				$response = wp_remote_get( $url, array( 'user-agent' => $agent ) );
				if ( is_array( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
					echo '<p>';
					echo wp_kses_post( wp_remote_retrieve_response_code( $response ) );
					echo ' - ';
					echo wp_kses_post( __( 'Access is allowed - this is correct.', 'wimb-and-block' ) );
					echo '</p>';
				} elseif ( is_array( $response ) && wp_remote_retrieve_response_code( $response ) === 404 ) {
					echo '<p>';
					echo wp_kses_post( wp_remote_retrieve_response_code( $response ) );
					echo ' - ';
					echo wp_kses_post( __( 'Access is forbidden - this is correct.', 'wimb-and-block' ) );
					echo '</p>';
				} else {
					echo '<p>';
					echo wp_kses_post( wp_remote_retrieve_response_code( $response ) );
					echo ' - ';
					echo wp_kses_post( __( 'Something is going wrong.', 'wimb-and-block' ) );
					echo '</p>';
				}
			}
		}
	}
}
