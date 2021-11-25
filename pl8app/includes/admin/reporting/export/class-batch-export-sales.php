<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * pl8app_Batch_Sales_Export Class
 *
 * @since 1.0
 */
class pl8app_Batch_Sales_Export extends pl8app_Batch_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 1.0
	 */
	public $export_type = 'sales';

	/**
	 * Set the CSV columns
	 *
	 * @since 1.0
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'ID'          => __( 'Log ID', 'pl8app' ),
			'user_id'     => __( 'User', 'pl8app' ),
			'customer_id' => __( 'Customer ID', 'pl8app' ),
			'email'       => __( 'Email', 'pl8app' ),
			'first_name'  => __( 'First Name', 'pl8app' ),
			'last_name'   => __( 'Last Name', 'pl8app' ),
			'menuitem'    => pl8app_get_label_singular(),
			'amount'      => __( 'Item Amount', 'pl8app' ),
			'payment_id'  => __( 'Payment ID', 'pl8app' ),
			'price_id'    => __( 'Price ID', 'pl8app' ),
			'date'        => __( 'Date', 'pl8app' ),
		);

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since 1.0
 	 * @global object $pl8app_logs pl8app Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $pl8app_logs;

		$data = array();

		$args = array(
			'log_type'       => 'sale',
			'posts_per_page' => 30,
			'paged'          => $this->step,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		);

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
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
				$payment    = new pl8app_Payment( $payment_id );
				$menuitem    = new pl8app_Menuitem( $log->post_parent );

				if ( ! empty( $payment->ID ) ) {
					$customer   = new pl8app_Customer( $payment->customer_id );
					$cart_items = $payment->cart_details;
					$amount     = 0;

					if ( is_array( $cart_items ) ) {
						foreach ( $cart_items as $item ) {
							$log_price_id = null;
							if ( $item['id'] == $log->post_parent ) {
								if ( isset( $item['item_number']['options']['price_id'] ) ) {
									$log_price_id = get_post_meta( $log->ID, '_pl8app_log_price_id', true );

									if ( (int) $item['item_number']['options']['price_id'] !== (int) $log_price_id ) {
										continue;
									}
								}

								$amount = isset( $item['price'] ) ? $item['price'] : $item['item_price'];
								break;
							}
						}
					}
				}
				$data[] = array(
					'ID'          => $log->ID,
					'user_id'     => $customer->user_id,
					'customer_id' => $customer->id,
					'email'       => $payment->email,
					'first_name'  => $payment->first_name,
					'last_name'   => $payment->last_name,
					'menuitem'    => $menuitem->post_title,
					'amount'      => $amount,
					'payment_id'  => $payment->ID,
					'price_id'    => $log_price_id,
					'date'        => get_post_field( 'post_date', $payment_id ),
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
	 * @since 1.0
	 * @return int
	 */
	public function get_percentage_complete() {
		global $pl8app_logs;

		$args = array(
			'post_type'		   => 'pl8app_log',
			'posts_per_page'   => -1,
			'post_status'	   => 'publish',
			'fields'           => 'ids',
			'post_parent'      => $this->menuitem_id,
			'tax_query'        => array(
				array(
					'taxonomy' 	=> 'pl8app_log_type',
					'field'		=> 'slug',
					'terms'		=> 'sale'
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

		$logs       = new WP_Query( $args );
		$total      = (int) $logs->post_count;
		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	public function set_properties( $request ) {
		$this->start       = isset( $request['start'] ) ? sanitize_text_field( $request['start'] ) : '';
		$this->end         = isset( $request['end'] )   ? sanitize_text_field( $request['end'] ) . ' 23:59:59'  : '';
		$this->menuitem_id = isset( $request['menuitem_id'] )   ? absint( $request['menuitem_id'] )        : 0;
	}
}
