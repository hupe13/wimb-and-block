<?php
/**
 * Documentation HELP
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

require_once __DIR__ . '/../pkg/parsedown/Parsedown.php';

require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
$filesystem = new WP_Filesystem_Direct( true );
$text       = $filesystem->get_contents( __DIR__ . '/../readme.md' );

$parsedown    = new Parsedown();
$allowed_html = wp_kses_allowed_html( 'post' );
echo wp_kses( $parsedown->text( $text ), $allowed_html );
