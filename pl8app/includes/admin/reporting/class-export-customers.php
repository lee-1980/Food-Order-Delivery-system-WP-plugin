<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * pl8app_Customers_Export Class
 *
 * @since  1.0.0
 */
class pl8app_Customers_Export extends pl8app_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since  1.0.0
	 */
	public $export_type = 'customers';

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

		$extra = '';

		if ( ! empty( $_POST['pl8app_export_menuitem'] ) ) {
			$extra = sanitize_title( get_the_title( absint( $_POST['pl8app_export_menuitem'] ) ) ) . '-';
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'pl8app_customers_export_filename', 'pl8app-export-' . $extra . $this->export_type . '-' . date( 'm-d-Y' ) ) . '.csv' );
		header( "Expires: 0" );
	}

	/**
	 * Set the CSV columns
	 *
	 * @since  1.0.0
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		if ( ! empty( $_POST['pl8app_export_menuitem'] ) ) {
			$cols = array(
				'first_name' => __( 'First Name',   'pl8app' ),
				'last_name'  => __( 'Last Name',   'pl8app' ),
				'email'      => __( 'Email', 'pl8app' ),
				'date'       => __( 'Date Purchased', 'pl8app' )
			);
		} else {

			$cols = array();

			if( 'emails' != $_POST['pl8app_export_option'] ) {
				$cols['name'] = __( 'Name',   'pl8app' );
			}

			$cols['email'] = __( 'Email',   'pl8app' );

			if( 'full' == $_POST['pl8app_export_option'] ) {
				$cols['purchases'] = __( 'Total Purchases',   'pl8app' );
				$cols['amount']    = __( 'Total Purchased', 'pl8app' ) . ' (' . html_entity_decode( pl8app_currency_filter( '' ) ) . ')';
			}

		}

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since  1.0.0
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @global object $pl8app_logs pl8app Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $wpdb;

		$data = array();

		if ( ! empty( $_POST['pl8app_export_menuitem'] ) ) {

			// Export customers of a specific product
			global $pl8app_logs;

			$args = array(
				'post_parent' => absint( $_POST['pl8app_export_menuitem'] ),
				'log_type'    => 'sale',
				'nopaging'    => true
			);

			if( isset( $_POST['pl8app_price_option'] ) ) {
				$args['meta_query'] = array(
					array(
						'key'   => '_pl8app_log_price_id',
						'value' => (int) $_POST['pl8app_price_option']
					)
				);
			}

			$logs = $pl8app_logs->get_connected_logs( $args );

			if ( $logs ) {
				foreach ( $logs as $log ) {
					$payment_id = get_post_meta( $log->ID, '_pl8app_log_payment_id', true );
					$user_info  = pl8app_get_payment_meta_user_info( $payment_id );
					$data[] = array(
						'first_name' => $user_info['first_name'],
						'last_name'  => $user_info['last_name'],
						'email'      => $user_info['email'],
						'date'       => $log->post_date
					);
				}
			}

		} else {

			// Export all customers
			$customers = PL8PRESS()->customers->get_customers( array( 'number' => -1 ) );

			$i = 0;

			foreach ( $customers as $customer ) {

				if( 'emails' != $_POST['pl8app_export_option'] ) {
					$data[$i]['name'] = $customer->name;
				}

				$data[$i]['email'] = $customer->email;

				if( 'full' == $_POST['pl8app_export_option'] ) {

					$data[$i]['purchases'] = $customer->purchase_count;
					$data[$i]['amount']    = pl8app_format_amount( $customer->purchase_value );

				}
				$i++;
			}
		}

		$data = apply_filters( 'pl8app_export_get_data', $data );
		$data = apply_filters( 'pl8app_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}
