<?php
/**
 * Functions for testing
 *
 * @package wimb-and-block
 */

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

function wimbblock_test_rules( $query ) {
	global $wimb_datatable;
	global $wimbblock_test_to_block;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}
	$wimbblock_wpdb_options = wimbblock_get_options_db();
	$wimbblock_table_name   = $wimbblock_wpdb_options['table_name'];
	$table_name             = $wimbblock_table_name;

	if ( $query ) {
		$wimbblock_entries = $wimb_datatable->get_results(
			$wimb_datatable->prepare(
				'SELECT * FROM %i WHERE block > 0 ORDER BY time DESC',
				$table_name
			),
			ARRAY_A
		);
	} else {
		$wimbblock_entries = $wimb_datatable->get_results(
			$wimb_datatable->prepare(
				'SELECT * FROM %i WHERE block = 0 ORDER BY time DESC',
				$table_name
			),
			ARRAY_A
		);
	}
	$results = array();

	foreach ( $wimbblock_entries as $entry ) {
		$agent             = $entry['browser'];
		$software          = $entry['software'];
		$system            = $entry['system'];
		$version           = $entry['version'];
		$blocked           = $entry['block'];
		$last_access       = $entry['time'];
		$id                = $entry['i'];
		$wimbblock_test_to_block = '';
		$is_crawler        = false;
		$params            = wimbblock_crawlers_params();
		foreach ( $params as $crawler => $value ) {
			foreach ( $value['agents'] as $brand ) {
				if ( stripos( $agent, $brand ) !== false ) {
					$is_crawler = true;
				}
			}
		}
		$alwayses    = wimbblock_get_option( 'wimbblock_always' );
		$alwaysblock = false;
		if ( $alwayses !== false ) {
			foreach ( $alwayses as $always ) {
				if ( stripos( $agent, $always ) !== false ) {
					$alwaysblock = true;
				}
			}
		}
		$excludes  = wimbblock_get_option( 'wimbblock_exclude' );
		$toexclude = false;
		if ( $excludes !== false ) {
			foreach ( $excludes as $exclude ) {
				if ( stripos( $agent, $exclude ) !== false ) {
					$toexclude = true;
				}
			}
		}
		if ( ! $is_crawler && ! $alwaysblock && ! $toexclude ) {
			wimbblock_unknown_agent( $table_name, $agent, $software, $blocked, $id, 'testing' );
			if ( $wimbblock_test_to_block === '' ) {
				if ( $version === '' && $software !== '' ) {
					$version = preg_replace( '%.* ([0-9]+)[^0-9]?.* on .*%', '${1}', $software );
				}
				wimbblock_check_modern_browser( $table_name, $agent, $software, $version, $system, $blocked, $id, 'testing' );
			}
			if ( $wimbblock_test_to_block === '' ) {
				wimbblock_old_system( $table_name, $agent, $system, $blocked, $id, 'testing' );
			}
			if ( $wimbblock_test_to_block !== '' && (int) $blocked === 0 ) {
				$results[] = array( $agent, $software, $system, $wimbblock_test_to_block, $last_access );
			} elseif ( $wimbblock_test_to_block === '' && (int) $blocked > 0 ) {
				$results[] = array( $agent, $software, $system, $blocked );
			}
		}
	}

	if ( $query ) {
		echo '<h2>' . wp_kses_post( __( 'Currently blocked', 'wimb-and-block' ) ) . '</h2>';
		echo '<p>' . wp_kses_post( __( 'You have probably set it up to block that.', 'wimb-and-block' ) ) . '</p>';
		array_unshift( $results, array( '<strong>browser</strong>', '<strong>software</strong>', '<strong>system</strong>', '<strong>blocked</strong>' ) );
	} else {
		echo '<p>' . wp_kses_post( __( 'Everything changes - new agents appear almost every day, while others no longer meet current requirements.', 'wimb-and-block' ) ) . '</p>';
		echo '<h2>' . wp_kses_post( __( 'Not blocked so far, but will be blocked in the future', 'wimb-and-block' ) ) . '</h2>';
		array_unshift( $results, array( '<strong>browser</strong>', '<strong>software</strong>', '<strong>system</strong>', '<strong>reason</strong>', '<strong>time</strong>' ) );
	}
	echo '<p>';
	echo wp_kses_post( wimbblock_html_table( $results ) );
	echo '</p>';
}
