<?php
/**
 *  Statistics for wimb-and-block 24 hours
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

global $wimb_datatable;
if ( is_null( $wimb_datatable ) ) {
	wimbblock_open_wpdb();
}
$wimbblock_wpdb_options  = wimbblock_get_options_db();
$wimbblock_table_name    = $wimbblock_wpdb_options['table_name'];
$wimbblock_datetime      = new DateTime( '-24 hours', new DateTimeZone( wp_timezone_string() ) );
$wimbblock_selected_date = $wimbblock_datetime->format( 'Y-m-d H:i:s' );
$wimbblock_wimbdate      = $wimbblock_datetime->format( 'ymd' );

$wimbblock_entries = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as alle FROM %i WHERE time >= %s',
		$wimbblock_table_name,
		$wimbblock_selected_date
	),
	ARRAY_A
);
$wimbblock_alle    = $wimbblock_entries[0]['alle'];

$wimbblock_entries = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as blocked FROM %i WHERE time >= %s AND block > 0',
		$wimbblock_table_name,
		$wimbblock_selected_date
	),
	ARRAY_A
);
$wimbblock_blocked = $wimbblock_entries[0]['blocked'];

$wimbblock_entries = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as robot FROM %i WHERE time >= %s AND robots > 0',
		$wimbblock_table_name,
		$wimbblock_selected_date
	),
	ARRAY_A
);
$wimbblock_robot   = $wimbblock_entries[0]['robot'];

$wimbblock_entries    = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as blockrobot FROM %i WHERE time >= %s AND robots > 0 and block > 0',
		$wimbblock_table_name,
		$wimbblock_selected_date
	),
	ARRAY_A
);
$wimbblock_blockrobot = $wimbblock_entries[0]['blockrobot'];

$wimbblock_entries = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as new FROM %i WHERE time >= %s AND ( wimbdate = %s OR wimbdate > %s )',
		$wimbblock_table_name,
		$wimbblock_selected_date,
		$wimbblock_wimbdate,
		$wimbblock_wimbdate
	),
	ARRAY_A
);
$wimbblock_new     = $wimbblock_entries[0]['new'];

$wimbblock_entries    = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as newblocked FROM %i WHERE time >= %s AND ( wimbdate = %s OR wimbdate > %s) AND block > 0',
		$wimbblock_table_name,
		$wimbblock_selected_date,
		$wimbblock_wimbdate,
		$wimbblock_wimbdate
	),
	ARRAY_A
);
$wimbblock_newblocked = $wimbblock_entries[0]['newblocked'];

echo '<p>' . wp_kses_post(
	wp_sprintf(
		/* translators:%s are numbers */
		__( 'In the last 24 hours, there were %1$s different browsers, of which %2$s were blocked.', 'wimb-and-block' ),
		$wimbblock_alle,
		$wimbblock_blocked . ( $wimbblock_alle > 0 ? ' ( ' . number_format( $wimbblock_blocked * 100 / $wimbblock_alle, 0, '', '' ) . '% )' : '' ),
	)
) . '<br>';

echo wp_kses_post(
	wp_sprintf(
		/* translators:%s are numbers */
		__( '%1$s browsers accessed the website(s) for the first time, of which %2$s were blocked.', 'wimb-and-block' ),
		$wimbblock_new,
		$wimbblock_newblocked . ( $wimbblock_new > 0 ? ' ( ' . number_format( $wimbblock_newblocked * 100 / $wimbblock_new, 0, '', '' ) . '% )' : '' ),
	)
) . '<br>';

echo wp_kses_post(
	wp_sprintf(
		/* translators:%s are numbers */
		__( '%s browsers accessed robots.txt at some point, so not necessarily within the last 24 hours.', 'wimb-and-block' ),
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
