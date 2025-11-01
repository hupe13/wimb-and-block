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
$wpdb_options = wimbblock_get_options_db();
$table_name   = $wpdb_options['table_name'];

$entries = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as alle FROM %i WHERE 1',
		$table_name,
	),
	ARRAY_A
);
$alle    = $entries[0]['alle'];

$entries = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as blocked FROM %i WHERE block > 0',
		$table_name,
	),
	ARRAY_A
);
$blocked = $entries[0]['blocked'];

$entries = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as robot FROM %i WHERE robots > 0',
		$table_name,
	),
	ARRAY_A
);
$robot   = $entries[0]['robot'];

$entries    = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as blockrobot FROM %i WHERE robots > 0 AND block > 0 ',
		$table_name,
	),
	ARRAY_A
);
$blockrobot = $entries[0]['blockrobot'];

echo '<p>' . wp_kses_post(
	wp_sprintf(
		/* translators:%s are numbers */
		__( 'There were %1$s different browsers, of which %2$s were blocked.', 'wimb-and-block' ),
		$alle,
		$blocked . ( $alle > 0 ? ' ( ' . number_format( $blocked * 100 / $alle, 0, '', '' ) . '% )' : '' ),
	)
) . '<br>';

echo wp_kses_post(
	wp_sprintf(
		/* translators:%s are numbers */
		__( '%s browsers accessed robots.txt.', 'wimb-and-block' ),
		$robot,
	)
) . '<br>';

echo wp_kses_post(
	wp_sprintf(
		/* translators:%s are numbers */
		__( '%s are not allowed to crawl.', 'wimb-and-block' ),
		$blockrobot . ( $robot > 0 ? ' ( ' . number_format( $blockrobot * 100 / $robot, 0, '', '' ) . '% )' : '' ),
	)
) . '</p>';
