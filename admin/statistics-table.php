<?php
/**
 * Manage table wimb entries
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

require_once __DIR__ . '/statistics-all.php';
require_once __DIR__ . '/statistics-month.php';

wimbblock_delete_month();

echo '<h3>' . esc_html( __( 'WIMB Table Statistics', 'wimb-and-block' ) ) . '</h3>';

echo '<h4>' . wp_kses_post(
	__( 'Monthly statistics - last access', 'wimb-and-block' ),
) . '</h4>';

echo '<p>' . wp_kses_post(
	__( 'The browsers whose last access occurred during this month are counted for the month.', 'wimb-and-block' ),
) . '</p>';

echo wp_kses_post( wimbblock_statistic_month() );

echo '<form method="post" action="options-general.php?page=' . esc_html( WIMBBLOCK_NAME ) . '&tab=wimbstat">';
if ( current_user_can( 'manage_options' ) ) {
	global $wimb_datatable;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}
	$wimbblock_wpdb_options = wimbblock_get_options_db();
	$wimbblock_table_name   = $wimbblock_wpdb_options['table_name'];

	$wimbblock_entries = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			'SELECT time FROM %i ORDER BY time ASC limit 1',
			$wimbblock_table_name
		),
		ARRAY_A
	);

	if ( count( $wimbblock_entries ) > 0 ) {
		$wimbblock_oldest_month = wp_date( 'F', strtotime( $wimbblock_entries[0]['time'] ) );
		wp_nonce_field( 'wimbblock_month', 'wimbblock_month_nonce' );
		submit_button( __( 'Delete entries for', 'wimb-and-block' ) . ' ' . $wimbblock_oldest_month, 'primary', 'changeblock' );
	}
}
echo '</form>';

echo '<h4>' . wp_kses_post(
	__( 'Monthly statistics - first access', 'wimb-and-block' ),
) . '</h4>';

echo '<p>' . wp_kses_post(
	__( 'The browsers whose first access occurred during this month are counted for the month.', 'wimb-and-block' ),
) . '</p>';

echo wp_kses_post( wimbblock_statistic_new_month() );
