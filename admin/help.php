<?php
/**
 * Functions help
 *
 * @package wimb-and-block
 */

//
function wimbblock_help() {
	$text = '';
	require_once ABSPATH . 'wp-content/plugins/' . WIMBBLOCK_NAME . '/pkg/parsedown/Parsedown.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
	$filesystem = new WP_Filesystem_Direct( true );
	$readme     = $filesystem->get_contents( ABSPATH . '/wp-content/plugins/' . WIMBBLOCK_NAME . '/readme.md' );
	$parsedown  = new Parsedown();
	$parts      = explode( '## ', $readme );
	$text      .= $parsedown->text( '## ' . $parts[1] );
	$text      .= $parsedown->text( '## ' . $parts[2] );
	$text      .= $parsedown->text( '## ' . $parts[3] );
	$text      .= $parsedown->text( '## ' . $parts[4] );
	$text       = str_replace( '.wordpress-org', get_site_url() . '/wp-content/plugins/' . WIMBBLOCK_NAME . '/.wordpress-org', $text );
	$text       = str_replace( '<li>', '<li class="adminli">', $text );
	return $text;

	// $text = '<h3><a href="https://leafext.de/hp/wimb/">' . __( 'Readme / Documentation', 'wimb-and-block' ) . '</a></h3>';

	// require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	// $plugin_info = plugins_api(
	//  'plugin_information',
	//  array(
	//      'slug'   => 'wimb-and-block',
	//      'fields' => array(
	//          'sections' => true,
	//      ),
	//  )
	// );
	// if ( ! $plugin_info || is_wp_error( $plugin_info ) ) {
	//  return __( 'The information could not be retrieved.', 'wimb-and-block' );
	// }
	//
	// foreach ( $plugin_info as $key => $value ) {
	//  if ( $key === 'sections' ) {
	//      foreach ( $value as $section => $part ) {
	//          // ***description***installation***faq***changelog***screenshots***reviews
	//          switch ( $section ) {
	//              case 'description':
	//                  $text .=  $part;
	//                  break;
	//              // case 'screenshots':
	//              default:
	//                  // echo '*****'.$text.'*****';
	//          }
	//      }
	//  } elseif ( $key === 'screenshots' ) {
	//      $text .= '<h3>' . __( 'Screenshots', 'wimb-and-block' ) . '</h3>';
	//      foreach ( $value as $screenshot => $image ) {
	//          $caption = str_replace( '<br />', '', $image['caption'] );
	//          $text   .= '<h4>' . $caption . '</h4>';
	//          $text   .= '<a href="' . $image['src'] . '" alt="' . $caption . '"><img src="' . $image['src'] . '" alt="' . $caption . '"></a>';
	//      }
	//  }
	// }
	// return $text;
}
