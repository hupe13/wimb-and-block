<?php
/**
 *  Statistics for wimb-and-block
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

//
global $wimb_datatable;
if ( is_null( $wimb_datatable ) ) {
	wimbblock_open_wpdb();
}
$wimbblock_wpdb_options = wimbblock_get_options_db();
$wimbblock_table_name   = $wimbblock_wpdb_options['table_name'];

$wimbblock_entries = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as alle FROM %i WHERE 1',
		$wimbblock_table_name,
	),
	ARRAY_A
);
$wimbblock_alle    = $wimbblock_entries[0]['alle'];

$wimbblock_entries = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as blocked FROM %i WHERE block > 0',
		$wimbblock_table_name,
	),
	ARRAY_A
);
$wimbblock_blocked = $wimbblock_entries[0]['blocked'];

$wimbblock_entries = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as robot FROM %i WHERE robots > 0',
		$wimbblock_table_name,
	),
	ARRAY_A
);
$wimbblock_robot   = $wimbblock_entries[0]['robot'];

$wimbblock_entries    = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as blockrobot FROM %i WHERE robots > 0 AND block > 0 ',
		$wimbblock_table_name,
	),
	ARRAY_A
);
$wimbblock_blockrobot = $wimbblock_entries[0]['blockrobot'];

echo '<p>' . wp_kses_post(
	wp_sprintf(
		/* translators:%s are numbers */
		__( 'There were %1$s different browsers, of which %2$s were blocked.', 'wimb-and-block' ),
		$wimbblock_alle,
		$wimbblock_blocked . ( $wimbblock_alle > 0 ? ' ( ' . number_format( $wimbblock_blocked * 100 / $wimbblock_alle, 0, '', '' ) . '% )' : '' ),
	)
) . '<br>';

echo wp_kses_post(
	wp_sprintf(
		/* translators:%s are numbers */
		__( '%s browsers accessed robots.txt.', 'wimb-and-block' ),
		$wimbblock_robot,
	)
) . '<br>';

echo wp_kses_post(
	wp_sprintf(
		/* translators:%s are numbers */
		__( '%s are not allowed to crawl.', 'wimb-and-block' ),
		$wimbblock_blockrobot . ( $wimbblock_robot > 0 ? ' ( ' . number_format( $wimbblock_blockrobot * 100 / $wimbblock_robot, 0, '', '' ) . '% )' : '' ),
	)
) . '</p>';
