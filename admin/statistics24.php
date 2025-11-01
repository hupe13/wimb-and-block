<?php
/**
 *  Statistics for wimb-and-block 24 hours
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
$wpdb_options  = wimbblock_get_options_db();
$table_name    = $wpdb_options['table_name'];
$datetime      = new DateTime( '-24 hours', new DateTimeZone( wp_timezone_string() ) );
$selected_date = $datetime->format( 'Y-m-d H:i:s' );
$wimbdate      = $datetime->format( 'ymd' );

$entries = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as alle FROM %i WHERE time >= %s',
		$table_name,
		$selected_date
	),
	ARRAY_A
);
$alle    = $entries[0]['alle'];

$entries = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as blocked FROM %i WHERE time >= %s AND block > 0',
		$table_name,
		$selected_date
	),
	ARRAY_A
);
$blocked = $entries[0]['blocked'];

$entries = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as robot FROM %i WHERE time >= %s AND robots > 0',
		$table_name,
		$selected_date
	),
	ARRAY_A
);
$robot   = $entries[0]['robot'];

$entries    = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as blockrobot FROM %i WHERE time >= %s AND robots > 0 and block > 0',
		$table_name,
		$selected_date
	),
	ARRAY_A
);
$blockrobot = $entries[0]['blockrobot'];

$entries = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as new FROM %i WHERE time >= %s AND ( wimbdate = %s OR wimbdate > %s )',
		$table_name,
		$selected_date,
		$wimbdate,
		$wimbdate
	),
	ARRAY_A
);
$new     = $entries[0]['new'];

$entries    = $wimb_datatable->get_results(
	$wimb_datatable->prepare(
		'SELECT COUNT(*) as newblocked FROM %i WHERE time >= %s AND ( wimbdate = %s OR wimbdate > %s) AND block > 0',
		$table_name,
		$selected_date,
		$wimbdate,
		$wimbdate
	),
	ARRAY_A
);
$newblocked = $entries[0]['newblocked'];

echo '<p>' . wp_kses_post(
	wp_sprintf(
		/* translators:%s are numbers */
		__( 'In the last 24 hours, there were %1$s different browsers, of which %2$s were blocked.', 'wimb-and-block' ),
		$alle,
		$blocked . ( $alle > 0 ? ' ( ' . number_format( $blocked * 100 / $alle, 0, '', '' ) . '% )' : '' ),
	)
) . '<br>';

echo wp_kses_post(
	wp_sprintf(
		/* translators:%s are numbers */
		__( '%1$s browsers accessed the website(s) for the first time, of which %2$s were blocked.', 'wimb-and-block' ),
		$new,
		$newblocked . ( $new > 0 ? ' ( ' . number_format( $newblocked * 100 / $new, 0, '', '' ) . '% )' : '' ),
	)
) . '<br>';

echo wp_kses_post(
	wp_sprintf(
		/* translators:%s are numbers */
		__( '%s browsers accessed robots.txt at some point, so not necessarily within the last 24 hours.', 'wimb-and-block' ),
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
