<?php
/**
 * Functions help
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_help_config() {
	$text = "\n" . '<h2>' . __( 'Configuration and Settings', 'wimb-and-block' ) . '</h2>';

	$text .= '<ul>';
	$text .= '<li class="adminli">';
	$text .= '<h4>' . __( 'Configuration', 'wimb-and-block' ) . '</h4>' . "\n";

	$text .= '<ul>';
	$text .= '<li class="adminli">';
	$text .= wp_sprintf(
		/* translators: %s are hrefs */
		__( 'First configure the %1$sWIMB settings%2$s', 'wimb-and-block' ),
		'<a href="' . esc_url( '?page=' . WIMBBLOCK_NAME . '&tab=settings' ) . '">',
		'</a>'
	);
	$text .= ' ' . wimbblock_has_key() . '</li>' . "\n";

	$text .= '<li class="adminli">';
	$text .= ' ' . wp_sprintf(
		/* translators: %s are hrefs */
		__( '%1$sCheck settings for robots.txt%2$s', 'wimb-and-block' ),
		'<a href="' . esc_url( '?page=' . WIMBBLOCK_NAME . '&tab=robots' ) . '">',
		'</a>'
	);
	$text .= ' ' . wimbblock_default_robots_txt() . '</li>' . "\n";

	$text .= '<li class="adminli">';
	$text .= ' ' . wp_sprintf(
		/* translators: %s are hrefs */
		__( 'If you would like to get logs, %1$sconfigure%2$s it.', 'wimb-and-block' ),
		'<a href="' . esc_url( '?page=' . WIMBBLOCK_NAME . '&tab=logging' ) . '">',
		'</a>'
	);
	$text .= ' ' . wimbblock_logfile_status() . '</li>' . "\n";

	$text .= '<li class="adminli">';
	$text .= wp_sprintf(
		/* translators: %s are hrefs */
		__( 'Optionally, configure the %1$ssearch engines%2$s settings.', 'wimb-and-block' ),
		'<a href="' . esc_url( '?page=' . WIMBBLOCK_NAME . '&tab=crawlers' ) . '">',
		'</a>'
	);
	$text .= '</li>' . "\n";

	$text .= '<li class="adminli">';
	$text .= ' ' . wp_sprintf(
		/* translators: %s are hrefs */
		__( "If you don't like AI bots, just %1\$sblock%2\$s them.", 'wimb-and-block' ),
		'<a href="' . esc_url( '?page=' . WIMBBLOCK_NAME . '&tab=ai' ) . '">',
		'</a>'
	);
	$text .= '</li>' . "\n";

	$text .= '</ul>' . "\n";

	### Statistik

	$text .= '<li class="adminli">';
	$text .= '<h4>' . __( 'You should check that out every now and then', 'wimb-and-block' ) . '</h4>';

	$text .= '<ul>';

	$text .= '<li class="adminli">';
	$text .= ' ' . wp_sprintf(
		/* translators: %s are hrefs */
		__( 'Statistics about %1$slast 24 hours%2$s', 'wimb-and-block' ),
		'<a href="' . esc_url( '?page=' . WIMBBLOCK_NAME . '&tab=last24' ) . '">',
		'</a>'
	);
	$text .= '</li>' . "\n";

	$text .= '<li class="adminli">';
	$text .= ' ' . wp_sprintf(
		/* translators: %s are hrefs */
		__( 'Monthly %1$sstatistics%2$s and maintenace', 'wimb-and-block' ),
		'<a href="' . esc_url( '?page=' . WIMBBLOCK_NAME . '&tab=monthly' ) . '">',
		'</a>'
	);
	$text .= '</li>' . "\n";

	$text .= '<li class="adminli">';
	$text .= ' ' . wp_sprintf(
		/* translators: %s are hrefs */
		__( "If you do not want to use the plugin's default settings, set up blocking of browsers with %1\$soutdated versions%2\$s.", 'wimb-and-block' ),
		'<a href="' . esc_url( '?page=' . WIMBBLOCK_NAME . '&tab=versions' ) . '">',
		'</a>'
	);
	$text .= '</li>' . "\n";

	$text .= '<li class="adminli">';
	$text .= ' ' . wp_sprintf(
		/* translators: %s are hrefs */
		__( '%1$sExclude%2$s browsers from checking / %1$salways block%2$s some browsers', 'wimb-and-block' ),
		'<a href="' . esc_url( '?page=' . WIMBBLOCK_NAME . '&tab=exclude' ) . '">',
		'</a>'
	);
	$text .= '</li>' . "\n";

	$text .= '<li class="adminli">';
	$text .= ' ' . wp_sprintf(
		/* translators: %s are hrefs */
		__( '%1$sSearch / block / unblock%2$s entries and get some statistics about types of agents', 'wimb-and-block' ),
		'<a href="' . esc_url( '?page=' . WIMBBLOCK_NAME . '&tab=block' ) . '">',
		'</a>'
	);
	$text .= '</li>' . "\n";

	$text .= '</ul>' . "\n";

	### Doku

	$text .= '<li class="adminli">';
	$text .= '<h4>' . __( 'For advanced users', 'wimb-and-block' ) . '</h4>';

	$text .= '<ul>';

	$text .= '<li class="adminli">';
	$text .= ' ' . wp_sprintf(
		/* translators: %s are hrefs */
		__( '%1$sEffect%2$s of the rules', 'wimb-and-block' ),
		'<a href="' . esc_url( '?page=' . WIMBBLOCK_NAME . '&tab=testing' ) . '">',
		'</a>'
	);
	$text .= '</li>' . "\n";

	$text .= '<li class="adminli">';
	$text .= ' ' . wp_sprintf(
		/* translators: %s are hrefs */
		__( '%1$sDocumentation%2$s about header checks', 'wimb-and-block' ),
		'<a href="' . esc_url( '?page=' . WIMBBLOCK_NAME . '&tab=headers' ) . '">',
		'</a>'
	);
	$text .= '</li>' . "\n";

	###

	$text .= '</ul>' . "\n";
	$text .= '</ul>' . "\n";
	return $text;
}

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
	$header     = array( '/Contributors:.*/', '/Tags:.*/', '/Tested up to:.*/', '/Stable tag:.*/', '/Requires at least:.*/', '/Requires PHP:.*/', '/License:.*/' );
	$readme     = preg_replace( $header, '', $readme );
	if ( get_locale() === 'de_DE' ) {
		$array  = explode( "\n", $readme );
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
					// var_dump( $line );
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

function wimbblock_has_key() {
	$wpdb_options = wimbblock_get_options_db();
	if ( ! ( $wpdb_options['error'] !== '0' || $wpdb_options['wimb_api'] === '' ) ) {
		return '&#10004;';
	}
	return '';
}

function wimbblock_default_robots_txt() {
	$site         = wp_parse_url( get_home_url() );
	$serverroot   = $site['host'];
	$wpdb_options = wimbblock_get_options_db();
	if ( ! isset( $site['path'] ) && get_home_path() === ABSPATH ) {
		global $wp_filesystem;
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		WP_Filesystem();
		if ( $wp_filesystem->exists( ABSPATH . '.htaccess' ) && $wp_filesystem->is_writable( ABSPATH . '.htaccess' ) ) {
			if ( ! $wp_filesystem->exists( ABSPATH . 'robots.txt' ) ) {
				return '&#10004;';
			}
		}
	}
	return ' -- ' . __( 'You should configure this. Ignore this warning if you have already done so.', 'wimb-and-block' );
}

function wimbblock_logfile_status() {

		$logfile           = '';
		$wimbblock_logfile = wimbblock_get_option( 'wimbblock_logfile' );
	if ( isset( $wimbblock_logfile ) && $wimbblock_logfile !== '' && $wimbblock_logfile !== false ) {
		$logfile = $wimbblock_logfile;
	}
	if ( $logfile === '' ) {
		if ( true === WP_DEBUG ) {
			if ( WP_DEBUG_LOG === true ) {
				$logfile = WP_CONTENT_DIR . '/debug.log';
			} elseif ( WP_DEBUG_LOG !== false ) {
				global $wp_filesystem;
				if ( ! function_exists( 'WP_Filesystem' ) ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}
				WP_Filesystem();
				if ( $wp_filesystem->is_writable( dirname( constant( 'WP_DEBUG_LOG' ) ) ) ) {
					$logfile = constant( 'WP_DEBUG_LOG' );
				} else {
					$logfile = '';
				}
			}
		}
	}
	if ( $logfile === '/dev/null' ) {
		$logfile = '';
	}

	if ( $logfile === '' ) {
		return esc_html( ' -- ' . __( 'Nothing is logged.', 'wimb-and-block' ) );
	} else {
		return esc_html( ' -- ' . __( 'Logging is performed according to your settings.', 'wimb-and-block' ) );
	}
}
