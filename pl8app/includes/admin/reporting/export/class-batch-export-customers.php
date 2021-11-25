<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * pl8app_Batch_Customers_Export Class
 *
 * @since 2.4
 */
class pl8app_Batch_Customers_Export extends pl8app_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 2.4
	 */
	public $export_type = 'customers';

	/**
	 * Set the CSV columns
	 *
	 * @since 2.4
	 * @return array $cols All the columns
	 */
	public function csv_cols() {

		$cols = array(
			'id'        => __( 'ID',   'pl8app' ),
			'name'      => __( 'Name',   'pl8app' ),
			'email'     => __( 'Email', 'pl8app' ),
			'purchases' => __( 'Number of Purchases', 'pl8app' ),
			'amount'    => __( 'Customer Value', 'pl8app' )
		);

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since 2.4
	 *   Database API
	 * @global object $pl8app_logs pl8app Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		$data = array();

		if ( ! empty( $this->menuitem ) ) {

			// Export customers of a specific product
			global $pl8app_logs;

			$args = array(
				'post_parent'    => absint( $this->menuitem ),
				'log_type'       => 'sale',
				'posts_per_page' => 30,
				'paged'          => $this->step
			);

			if( null !== $this->price_id ) {
				$args['meta_query'] = array(
					array(
						'key'   => '_pl8app_log_price_id',
						'value' => (int) $this->price_id
					)
				);
			}

			$logs = $pl8app_logs->get_connected_logs( $args );

			if ( $logs ) {
				foreach ( $logs as $log ) {

					$payment_id  = get_post_meta( $log->ID, '_pl8app_log_payment_id', true );
					$customer_id = pl8app_get_payment_customer_id( $payment_id );
					$customer    = new pl8app_Customer( $customer_id );

					$data[] = array(
						'id'        => $customer->id,
						'name'      => $customer->name,
						'email'     => $customer->email,
						'purchases' => $customer->purchase_count,
						'amount'    => pl8app_format_amount( $customer->purchase_value ),
					);
				}
			}

		} else {

			// Export all customers
			$offset    = 30 * ( $this->step - 1 );
			$customers = PL8PRESS()->customers->get_customers( array( 'number' => 30, 'offset' => $offset ) );

			$i = 0;

			foreach ( $customers as $customer ) {

				$data[$i]['id']        = $customer->id;
				$data[$i]['name']      = $customer->name;
				$data[$i]['email']     = $customer->email;
				$data[$i]['purchases'] = $customer->purchase_count;
				$data[$i]['amount']    = pl8app_format_amount( $customer->purchase_value );

				$i++;
			}
		}

		$data = apply_filters( 'pl8app_export_get_data', $data );
		$data = apply_filters( 'pl8app_export_get_data_' . $this->export_type, $data );

		return $data;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.4
	 * @return int
	 */
	public function get_percentage_complete() {

		$percentage = 0;

		// We can't count the number when getting them for a specific menuitem
		if( empty( $this->menuitem ) ) {

			$total = PL8PRESS()->customers->count();

			if( $total > 0 ) {

				$percentage = ( ( 30 * $this->step ) / $total ) * 100;

			}

		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the Customers export
	 *
	 * @since 2.4.2
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {
		$this->start    = isset( $request['start'] )            ? sanitize_text_field( $request['start'] ) : '';
		$this->end      = isset( $request['end']  )             ? sanitize_text_field( $request['end']  )  : '';
		$this->menuitem = isset( $request['menuitem']         ) ? absint( $request['menuitem']         )   : null;
		$this->price_id = ! empty( $request['pl8app_price_option'] ) && 0 !== $request['pl8app_price_option'] ? absint( $request['pl8app_price_option'] )   : null;
	}
}
