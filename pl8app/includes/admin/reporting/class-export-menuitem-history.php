<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * pl8app_Menuitem_History_Export Class
 *
 * @since  1.0.0
 */
class pl8app_Menuitem_History_Export extends pl8app_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since  1.0.0
	 */
	public $export_type = 'menuitem_history';


	/**
	 * Set the export headers
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function headers() {
		ignore_user_abort( true );

		if ( ! pl8app_is_func_disabled( 'set_time_limit' ) )
			set_time_limit( 0 );

		$month = isset( $_POST['month'] ) ? absint( $_POST['month'] ) : date( 'n' );
		$year  = isset( $_POST['year']  ) ? absint( $_POST['year']  ) : date( 'Y' );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'pl8app_menuitem_history_export_filename', 'pl8app-export-' . $this->export_type . '-' . $month . '-' . $year ) . '.csv' );
		header( "Expires: 0" );
	}


	/**
	 * Set the CSV columns
	 *
	 * @since  1.0.0
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'date'     => __( 'Date',   'pl8app' ),
			'user'     => __( 'Ordered by', 'pl8app' ),
			'ip'       => __( 'IP Address', 'pl8app' ),
			'menuitem' => __( 'Product', 'pl8app' ),
			'file'     => __( 'File', 'pl8app' )
		);
		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since  1.0.0
 	 * @global object $pl8app_logs pl8app Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $pl8app_logs;

		$data = array();

		$args = array(
			'nopaging' => true,
			'log_type' => 'file_menuitem',
			'monthnum' => isset( $_POST['month'] ) ? absint( $_POST['month'] ) : date( 'n' ),
			'year'     => isset( $_POST['year'] ) ? absint( $_POST['year'] ) : date( 'Y' )
		);

		$logs = $pl8app_logs->get_connected_logs( $args );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				$user_info = get_post_meta( $log->ID, '_pl8app_log_user_info', true );
				$user      = get_userdata( $user_info['id'] );
				$user      = $user ? $user->user_login : $user_info['email'];

				$data[]    = array(
					'date'     => $log->post_date,
					'user'     => $user,
					'ip'       => get_post_meta( $log->ID, '_pl8app_log_ip', true ),
					'menuitem' => get_the_title( $log->post_parent )
				);
			}
		}

		$data = apply_filters( 'pl8app_export_get_data', $data );
		$data = apply_filters( 'pl8app_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}
