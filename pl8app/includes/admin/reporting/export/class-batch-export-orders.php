<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * pl8app_Batch_File_pl8app_Export Class
 *
 * @since 2.4
 */
class pl8app_Batch_File_Orders_Export extends pl8app_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 2.4
	 */
	public $export_type = 'file_menuitems';

	/**
	 * Set the CSV columns
	 *
	 * @since 2.4
	 * @return array $cols All the columns
	 */
	public function csv_cols() {

		$cols = array(
			'date'     => __( 'Date',   'pl8app' ),
			'user'     => __( 'Ordered by', 'pl8app' ),
			'menuitem' => __( 'Menu Item', 'pl8app' )
		);

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since 2.4
 	 * @global object $pl8app_logs pl8app Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		global $pl8app_logs;

		$data = array();

		$args = array(
			'log_type'       => 'file_download',
			'posts_per_page' => 30,
			'paged'          => $this->step
		);

		if( ! empty( $this->start ) || ! empty( $this->end ) ) {

			$args['date_query'] = array(
				array(
					'after'     => date( 'Y-n-d H:i:s', strtotime( $this->start ) ),
					'before'    => date( 'Y-n-d H:i:s', strtotime( $this->end ) ),
					'inclusive' => true
				)
			);
		}

		if ( 0 !== $this->menuitem_id ) {
			$args['post_parent'] = $this->menuitem_id;
		}

		$logs = $pl8app_logs->get_connected_logs( $args );

		if ( $logs ) {
			foreach ( $logs as $log ) {

				$payment_id = get_post_meta( $log->ID, '_pl8app_log_payment_id', true );
				$user_email = get_post_meta( $payment_id, '_pl8app_payment_user_email', true );

				$data[]    = array(
					'date'     => $log->post_date,
					'user'     => $user_email,
					'menuitem' => get_the_title( $log->post_parent ),
				);
			}

			$data = apply_filters( 'pl8app_export_get_data', $data );
			$data = apply_filters( 'pl8app_export_get_data_' . $this->export_type, $data );

			return $data;
		}

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.4
	 * @return int
	 */
	public function get_percentage_complete() {

		global $pl8app_logs;

		$args = array(
			'post_type'		   => 'pl8app_log',
			'posts_per_page'   => -1,
			'post_status'	   => 'publish',
			'fields'           => 'ids',
			'tax_query'        => array(
				array(
					'taxonomy' 	=> 'pl8app_log_type',
					'field'		=> 'slug',
					'terms'		=> 'file_download'
				)
			),
			'date_query'        => array(
				array(
					'after'     => date( 'Y-n-d H:i:s', strtotime( $this->start ) ),
					'before'    => date( 'Y-n-d H:i:s', strtotime( $this->end ) ),
					'inclusive' => true
				)
			)
		);

		if ( 0 !== $this->menuitem_id ) {
			$args['post_parent'] = $this->menuitem_id;
		}

		$logs       = new WP_Query( $args );
		$total      = (int) $logs->post_count;
		$percentage = 100;

		if( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	public function set_properties( $request ) {
		$this->start       = isset( $request['start'] )         ? sanitize_text_field( $request['start'] ) : '';
		$this->end         = isset( $request['end']  )          ? sanitize_text_field( $request['end']  )  : '';
		$this->menuitem_id = isset( $request['menuitem_id'] )   ? absint( $request['menuitem_id'] )        : 0;
	}
}
