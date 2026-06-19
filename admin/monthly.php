<?php
/**
 * Manage monthly wimb table entries
 *
 * @package wimb-and-block
 **/

// Direktzugriff auf diese Datei verhindern.
defined( 'ABSPATH' ) || die();

echo '<h3>' . esc_html( __( 'Overview and maintenance', 'wimb-and-block' ) ) . '</h3>';

wimbblock_counter_all();
wimbblock_delete_month();

echo '<h4>' . wp_kses_post(
	__( 'Monthly statistics - last access', 'wimb-and-block' ),
) . '</h4>';

echo '<p>' . wp_kses_post(
	__( 'The browsers whose last access occurred during this month are counted for the month.', 'wimb-and-block' ),
) . '</p>';

echo wp_kses_post( wimbblock_statistic_month() );

global $wimbblock_basename;
if ( ! ( is_multisite() && ! is_main_site() && is_plugin_active_for_network( $wimbblock_basename ) ) ) {

	echo '<form method="post" action="options-general.php?page=' . esc_html( WIMBBLOCK_NAME ) . '&tab=monthly">';
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
} else {
	echo '<p>';
			echo wp_kses_post(
				wp_sprintf(
				/* translators: %1$s and %2$s is a link. */
					__( 'You can delete oldest month on the %1$smain site%2$s.', 'wimb-and-block' ),
					'<a href="' . get_site_url( get_main_site_id() ) . '/wp-admin/admin.php?page=' . WIMBBLOCK_NAME . '&tab=monthly">',
					'</a>'
				)
			);
			echo '</p>';
}

// echo '<h4>' . wp_kses_post(
//  __( 'Monthly statistics - first access', 'wimb-and-block' ),
// ) . '</h4>';
//
// echo '<p>' . wp_kses_post(
//  __( 'The browsers whose first access occurred during this month are counted for the month.', 'wimb-and-block' ),
// ) . '</p>';
//
// echo wp_kses_post( wimbblock_statistic_new_month() );

echo '<h4>' . wp_kses_post(
	__( 'Statistics about systems in total', 'wimb-and-block' ),
) . '</h4>';

echo '<p>' . wp_kses_post(
	__( 'The number of different browsers per system is counted.', 'wimb-and-block' ),
) . '</p>';

echo wp_kses_post( wimbblock_stats_systems() );

echo '<h4>' . wp_kses_post(
	__( 'Statistics about first and last monthly access', 'wimb-and-block' ),
) . '</h4>';

wimbblock_statistic_first_last();

echo '<h4>' . wp_kses_post(
	'<a href="' .
	wp_nonce_url( '?page=' . WIMBBLOCK_NAME . '&tab=block&type=A', 'link' )
	. '">' .
	__( 'Even more statistics', 'wimb-and-block' ) . '</a>'
) . '</h4>';

function wimbblock_counter_all() {
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

	echo '<h4>' . wp_kses_post(
		__( 'All entries', 'wimb-and-block' ),
	) . '</h4>';

	$entries = array(
		array(
			'month'               => __( 'total', 'wimb-and-block' ),
			'count'               => $wimbblock_alle,
			'blocked'             => $wimbblock_blocked,
			'blocked in %'        => wimbblock_prozent( $wimbblock_blocked, $wimbblock_alle ),
			'robots'              => $wimbblock_robot,
			'blockrobot'          => $wimbblock_blockrobot,
			'robots blocked in %' => wimbblock_prozent( $wimbblock_blockrobot, $wimbblock_robot ),
		),
	);

	$header                        = array();
	$header['month']               = '';
	$header['count']               = __( 'count', 'wimb-and-block' );
	$header['blocked']             = __( 'blocked', 'wimb-and-block' );
	$header['blocked in %']        = __( 'blocked in %', 'wimb-and-block' );
	$header['robots']              = __( 'robots', 'wimb-and-block' );
	$header['robots blocked in %'] = __( 'robots blocked in %', 'wimb-and-block' );
	$header['blockrobot']          = __( 'robots blocked', 'wimb-and-block' );

	array_unshift( $entries, $header );
	echo '<p>';
	echo wp_kses_post( wimbblock_html_table( $entries ) );
	echo '</p>';
}

function wimbblock_statistic_month() {
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
		$date1     = date_create( wp_date( 'Y-m-01' ) );
		$date2     = date_create( wp_date( 'Y-m-01', strtotime( $wimbblock_entries[0]['time'] ) ) );
		$diff      = date_diff( $date1, $date2 );
		$thismonth = wp_date( 'Y-m' );
		$months    = range( 0, $diff->format( '%m' ) );
		$entries   = array();

		foreach ( $months as $month ) {
			$entries[ $month ]['month']        = wp_date( 'F', strtotime( $thismonth . ' - ' . $month . ' month' ) );
			$search_date                       = wp_date( 'Y-m-', strtotime( $thismonth . ' - ' . $month . ' month' ) );
			$wimbblock_entries                 = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT COUNT(*) as count FROM %i WHERE time LIKE %s',
					$wimbblock_table_name,
					$search_date . '%'
				),
				ARRAY_A
			);
			$entries[ $month ]['count']        = $wimbblock_entries[0]['count'];
			$wimbblock_entries                 = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT COUNT(*) as blocked FROM %i WHERE time LIKE %s AND block > 0',
					$wimbblock_table_name,
					$search_date . '%'
				),
				ARRAY_A
			);
			$entries[ $month ]['blocked']      = $wimbblock_entries[0]['blocked'];
			$entries[ $month ]['blocked in %'] =
			wimbblock_prozent( $entries[ $month ]['blocked'], $entries[ $month ]['count'] );

			$wimbblock_entries           = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT COUNT(*) as robots FROM %i WHERE time LIKE %s AND robots > 0',
					$wimbblock_table_name,
					$search_date . '%'
				),
				ARRAY_A
			);
			$entries[ $month ]['robots'] = $wimbblock_entries[0]['robots'];

			$wimbblock_entries               = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT COUNT(*) as blockrobot FROM %i WHERE time LIKE %s AND robots > 0 AND block > 0',
					$wimbblock_table_name,
					$search_date . '%'
				),
				ARRAY_A
			);
			$entries[ $month ]['blockrobot'] = $wimbblock_entries[0]['blockrobot'];

			$entries[ $month ]['robots blocked in %'] =
			wimbblock_prozent( $entries[ $month ]['blockrobot'], $entries[ $month ]['robots'] );
		}

		$header = array_fill_keys( array_keys( $entries[0] ), '' );
		foreach ( $header as $key => $value ) {
			$header[ $key ] = $key;
		}
		$header['month']               = __( 'month', 'wimb-and-block' );
		$header['count']               = __( 'count', 'wimb-and-block' );
		$header['blocked']             = __( 'blocked', 'wimb-and-block' );
		$header['blocked in %']        = __( 'blocked in %', 'wimb-and-block' );
		$header['robots']              = __( 'robots', 'wimb-and-block' );
		$header['robots blocked in %'] = __( 'robots blocked in %', 'wimb-and-block' );
		$header['blockrobot']          = __( 'robots blocked', 'wimb-and-block' );

		array_unshift( $entries, $header );
		return wimbblock_html_table( $entries );
	} else {
		return '';
	}
}

function wimbblock_statistic_new_month() {
	global $wimb_datatable;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}
	$wimbblock_wpdb_options = wimbblock_get_options_db();
	$wimbblock_table_name   = $wimbblock_wpdb_options['table_name'];

	$wimbblock_entries = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			"SELECT wimbdate FROM %i WHERE wimbdate != '' ORDER BY wimbdate ASC limit 1",
			$wimbblock_table_name
		),
		ARRAY_A
	);

	if ( count( $wimbblock_entries ) > 0 ) {
		$date1 = date_create( wp_date( 'Y-m-01' ) );
		$date2 = date_create( wp_date( 'Y-m-01', strtotime( '01.' . substr( $wimbblock_entries[0]['wimbdate'], 2, 2 ) . '.20' . substr( $wimbblock_entries[0]['wimbdate'], 0, 2 ) ) ) );
		$diff  = date_diff( $date1, $date2 );

		$thismonth = wp_date( 'Y-m' );
		$months    = range( 0, $diff->format( '%m' ) );
		$entries   = array();

		foreach ( $months as $month ) {
			$entries[ $month ]['month']   = wp_date( 'F', strtotime( $thismonth . ' - ' . $month . ' month' ) );
			$search_date                  = wp_date( 'ym', strtotime( $thismonth . ' - ' . $month . ' month' ) );
			$wimbblock_entries            = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT COUNT(*) as count FROM %i WHERE wimbdate LIKE %s',
					$wimbblock_table_name,
					$search_date . '%'
				),
				ARRAY_A
			);
			$entries[ $month ]['count']   = $wimbblock_entries[0]['count'];
			$wimbblock_entries            = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT COUNT(*) as blocked FROM %i WHERE wimbdate LIKE %s AND block > 0',
					$wimbblock_table_name,
					$search_date . '%'
				),
				ARRAY_A
			);
			$entries[ $month ]['blocked'] = $wimbblock_entries[0]['blocked'];

			$entries[ $month ]['blocked in %'] =
			wimbblock_prozent( $entries[ $month ]['blocked'], $entries[ $month ]['count'] );

			$wimbblock_entries           = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT COUNT(*) as robots FROM %i WHERE wimbdate LIKE %s AND robots > 0',
					$wimbblock_table_name,
					$search_date . '%'
				),
				ARRAY_A
			);
			$entries[ $month ]['robots'] = $wimbblock_entries[0]['robots'];

			$wimbblock_entries               = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'SELECT COUNT(*) as blockrobot FROM %i WHERE wimbdate LIKE %s AND robots > 0 AND block > 0',
					$wimbblock_table_name,
					$search_date . '%'
				),
				ARRAY_A
			);
			$entries[ $month ]['blockrobot'] = $wimbblock_entries[0]['blockrobot'];

			$entries[ $month ]['robots blocked in %'] =
			wimbblock_prozent( $entries[ $month ]['blockrobot'], $entries[ $month ]['robots'] );
		}

		$header = array_fill_keys( array_keys( $entries[0] ), '' );
		foreach ( $header as $key => $value ) {
			$header[ $key ] = $key;
		}
		$header['month']               = __( 'month', 'wimb-and-block' );
		$header['count']               = __( 'count', 'wimb-and-block' );
		$header['blocked']             = __( 'blocked', 'wimb-and-block' );
		$header['blocked in %']        = __( 'blocked in %', 'wimb-and-block' );
		$header['robots']              = __( 'robots', 'wimb-and-block' );
		$header['robots blocked in %'] = __( 'robots blocked in %', 'wimb-and-block' );
		$header['blockrobot']          = __( 'robots blocked', 'wimb-and-block' );

		array_unshift( $entries, $header );
		return wimbblock_html_table( $entries );
	} else {
		return '';
	}
}

function wimbblock_delete_month() {
	if ( ! empty( $_POST ) && check_admin_referer( 'wimbblock_month', 'wimbblock_month_nonce' ) ) {
		global $wimb_datatable;
		if ( is_null( $wimb_datatable ) ) {
			wimbblock_open_wpdb();
		}
		$wimbblock_wpdb_options = wimbblock_get_options_db();
		$wimbblock_table_name   = $wimbblock_wpdb_options['table_name'];
		$wimbblock_entries      = $wimb_datatable->get_results(
			$wimb_datatable->prepare(
				'SELECT time FROM %i ORDER BY time ASC limit 1',
				$wimbblock_table_name
			),
			ARRAY_A
		);

		if ( count( $wimbblock_entries ) > 0 ) {
			$search_date       = substr( $wimbblock_entries[0]['time'], 0, 8 );
			$wimbblock_entries = $wimb_datatable->get_results(
				$wimb_datatable->prepare(
					'DELETE FROM %i WHERE time LIKE %s',
					$wimbblock_table_name,
					$search_date . '%'
				),
				ARRAY_A
			);
		}
	}
}

function wimbblock_statistic_first_last() {
	global $wimb_datatable;
	if ( is_null( $wimb_datatable ) ) {
		wimbblock_open_wpdb();
	}
	$wimbblock_wpdb_options = wimbblock_get_options_db();
	$wimbblock_table_name   = $wimbblock_wpdb_options['table_name'];

	$oldest_wimbdate_entries = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			'SELECT wimbdate FROM %i ORDER BY wimbdate ASC limit 1',
			$wimbblock_table_name
		),
		ARRAY_A
	);

	$oldest_entries = $wimb_datatable->get_results(
		$wimb_datatable->prepare(
			'SELECT time FROM %i ORDER BY time ASC limit 1',
			$wimbblock_table_name
		),
		ARRAY_A
	);

	$date1      = date_create( wp_date( 'Y-m-01' ) );
	$wimbmonths = 0;
	$oldmonths  = 0;

	if ( count( $oldest_wimbdate_entries ) > 0 ) {
		$date2      = date_create( wp_date( 'Y-m-01', strtotime( '01.' . substr( $oldest_wimbdate_entries[0]['wimbdate'], 2, 2 ) . '.20' . substr( $oldest_wimbdate_entries[0]['wimbdate'], 0, 2 ) ) ) );
		$diff       = date_diff( $date1, $date2 );
		$thismonth  = wp_date( 'Y-m' );
		$wimbmonths = range( 0, $diff->format( '%m' ) );
	}

	if ( count( $oldest_entries ) > 0 ) {
		$date2     = date_create( wp_date( 'Y-m-01', strtotime( $oldest_entries[0]['time'] ) ) );
		$diff      = date_diff( $date1, $date2 );
		$oldmonths = range( 0, $diff->format( '%m' ) );
	}

	if ( $wimbmonths !== 0 && $oldmonths !== 0 ) {
		$entries   = array();
		$percents  = array();
		$oldmonths = array_reverse( $oldmonths );
		foreach ( $wimbmonths as $wimbmonth ) {
			$entries[ $wimbmonth ]['month']  = wp_date( 'F', strtotime( $thismonth . ' - ' . $wimbmonth . ' month' ) );
			$percents[ $wimbmonth ]['month'] = $entries[ $wimbmonth ]['month'];

			$wimb_search = wp_date( 'ym', strtotime( $thismonth . ' - ' . $wimbmonth . ' month' ) );

			foreach ( $oldmonths as $oldmonth ) {
				$time_search = wp_date( 'Y-m-', strtotime( $thismonth . ' - ' . $oldmonth . ' month' ) );

				$wimbblock_entries                  = $wimb_datatable->get_results(
					$wimb_datatable->prepare(
						'SELECT COUNT(*) as count FROM %i WHERE time LIKE %s AND wimbdate LIKE %s',
						$wimbblock_table_name,
						$time_search . '%',
						$wimb_search . '%'
					),
					ARRAY_A
				);
				$entries[ $wimbmonth ][ $oldmonth ] = $wimbblock_entries[0]['count'] > 0 ? $wimbblock_entries[0]['count'] : '';

				$wimbblock_entries = $wimb_datatable->get_results(
					$wimb_datatable->prepare(
						'SELECT COUNT(*) as count FROM %i WHERE time LIKE %s AND wimbdate LIKE %s AND block > 0',
						$wimbblock_table_name,
						$time_search . '%',
						$wimb_search . '%'
					),
					ARRAY_A
				);
				if ( $wimbblock_entries[0]['count'] > 0 ) {
					$percents[ $wimbmonth ][ $oldmonth ] = wimbblock_percent( $wimbblock_entries[0]['count'], $entries[ $wimbmonth ][ $oldmonth ] );
				} else {
					$percents[ $wimbmonth ][ $oldmonth ] = '';
				}
			}
		}
		$header = array_fill_keys( array_keys( $entries[0] ), '' );
		foreach ( $header as $key => $value ) {
			$header[ $key ] = wp_date( 'F', strtotime( $thismonth . ' - ' . $key . ' month' ) );
		}
		$header['month'] = '<table><tr><td>' . __( 'last', 'wimb-and-block' ) . ' &rarr;</td></tr>' .
		'<tr><td>' . __( 'first', 'wimb-and-block' ) . ' &darr;</td></tr></table>';
		array_unshift( $entries, $header );
		array_unshift( $percents, $header );
		echo wp_kses_post( wimbblock_html_table( $entries ) );
		echo wp_kses_post( wimbblock_html_table( $percents, 'statistics' ) );

	}
}
