<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * pl8app_Batch_Payments_Export Class
 *
 * @since 2.4
 */
class pl8app_Batch_Payments_Export extends pl8app_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 2.4
	 */
	public $export_type = 'payments';

	/**
	 * Set the CSV columns
	 *
	 * @since 2.4
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'id'           => __( 'Payment ID',   'pl8app' ), // unaltered payment ID (use for querying)
			'seq_id'       => __( 'Payment Number',   'pl8app' ), // sequential payment ID
			'email'        => __( 'Email', 'pl8app' ),
			'customer_id'  => __( 'Customer ID', 'pl8app' ),
			'first'        => __( 'First Name', 'pl8app' ),
			'last'         => __( 'Last Name', 'pl8app' ),
			'address1'     => __( 'Address', 'pl8app' ),
			'address2'     => __( 'Address (Line 2)', 'pl8app' ),
			'city'         => __( 'City', 'pl8app' ),
			'state'        => __( 'State', 'pl8app' ),
			'country'      => __( 'Country', 'pl8app' ),
			'zip'          => __( 'Zip / Postal Code', 'pl8app' ),
			'products'     => __( 'Products (Verbose)', 'pl8app' ),
			'products_raw' => __( 'Products (Raw)', 'pl8app' ),
			'skus'         => __( 'SKUs', 'pl8app' ),
			'amount'       => __( 'Amount', 'pl8app' ) . ' (' . html_entity_decode( pl8app_currency_filter( '' ) ) . ')',
			'tax'          => __( 'Tax', 'pl8app' ) . ' (' . html_entity_decode( pl8app_currency_filter( '' ) ) . ')',
			'discount'     => __( 'Discount Code', 'pl8app' ),
			'gateway'      => __( 'Payment Method', 'pl8app' ),
			'trans_id'     => __( 'Transaction ID', 'pl8app' ),
			'key'          => __( 'Purchase Key', 'pl8app' ),
			'date'         => __( 'Date', 'pl8app' ),
			'user'         => __( 'User', 'pl8app' ),
			'currency'     => __( 'Currency', 'pl8app' ),
			'ip'           => __( 'IP Address', 'pl8app' ),
			'mode'         => __( 'Mode (Live|Test)', 'pl8app' ),
			'status'       => __( 'Status', 'pl8app' ),
			'country_name' => __( 'Country Name', 'pl8app' ),
		);

		if( ! pl8app_use_skus() ){
			unset( $cols['skus'] );
		}
		if ( ! pl8app_get_option( 'enable_sequential' ) ) {
			unset( $cols['seq_id'] );
		}

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since 2.4
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $wpdb;

		$data = array();

		$args = array(
			'number'   => 30,
			'page'     => $this->step,
			'status'   => $this->status,
			'order'    => 'ASC',
			'orderby'  => 'date'
		);

		if( ! empty( $this->start ) || ! empty( $this->end ) ) {

			$args['date_query'] = array(
				array(
					'after'     => date( 'Y-n-d 00:00:00', strtotime( $this->start ) ),
					'before'    => date( 'Y-n-d 23:59:59', strtotime( $this->end ) ),
					'inclusive' => true
				)
			);

		}

		$payments = pl8app_get_payments( $args );

		if( $payments ) {

			foreach ( $payments as $payment ) {
				$payment = new pl8app_Payment( $payment->ID );
				$payment_meta   = $payment->payment_meta;
				$user_info      = $payment->user_info;
				$menuitems      = $payment->cart_details;
				$total          = $payment->total;
				$user_id        = isset( $user_info['id'] ) && $user_info['id'] != -1 ? $user_info['id'] : $user_info['email'];
				$products       = '';
				$products_raw   = '';
				$skus           = '';

				if ( $menuitems ) {
					foreach ( $menuitems as $key => $menuitem ) {

						$id  = isset( $payment_meta['cart_details'] ) ? $menuitem['id'] : $menuitem;
						$qty = isset( $menuitem['quantity'] ) ? $menuitem['quantity'] : 1;

						if ( isset( $menuitem['price'] ) ) {
							$price = $menuitem['price'];
						} else {
							// If the menuitem has variable prices, override the default price
							$price_override = isset( $payment_meta['cart_details'] ) ? $menuitem['price'] : null;
							$price = pl8app_get_menuitem_final_price( $id, $user_info, $price_override );
						}

						$menuitem_tax      = isset( $menuitem['tax'] ) ? $menuitem['tax'] : 0;
						$menuitem_price_id = isset( $menuitem['item_number']['options']['price_id'] ) ? absint( $menuitem['item_number']['options']['price_id'] ) : false;

						/* Set up verbose product column */

						$products .= html_entity_decode( get_the_title( $id ) );

						if ( $qty > 1 ) {
							$products .= html_entity_decode( ' (' . $qty . ')' );
						}

						$products .= ' - ';

						if ( pl8app_use_skus() ) {
							$sku = pl8app_get_menuitem_sku( $id );

							if ( ! empty( $sku ) ) {
								$skus .= $sku;
							}
						}

						if ( isset( $menuitems[ $key ]['item_number'] ) && isset( $menuitems[ $key ]['item_number']['options'] ) ) {
							$price_options = $menuitems[ $key ]['item_number']['options'];

							if ( isset( $price_options['price_id'] ) && ! is_null( $price_options['price_id'] ) ) {
								$products .= html_entity_decode( pl8app_get_price_option_name( $id, $price_options['price_id'], $payment->ID ) ) . ' - ';
							}
						}

						$products .= html_entity_decode( pl8app_currency_filter( pl8app_format_amount( $price ) ) );

						if ( $key != ( count( $menuitems ) -1 ) ) {

							$products .= ' / ';

							if( pl8app_use_skus() ) {
								$skus .= ' / ';
							}
						}

						/* Set up raw products column - Nothing but product names */
						$products_raw .= html_entity_decode( get_the_title( $id ) ) . '|' . $price . '{' . $menuitem_tax . '}';

						// if we have a Price ID, include it.
						if ( false !== $menuitem_price_id ) {
							$products_raw .= '{' . $menuitem_price_id . '}';
						}

						if ( $key != ( count( $menuitems ) -1 ) ) {

							$products_raw .= ' / ';

						}
					}
				}

				if ( is_numeric( $user_id ) ) {
					$user = get_userdata( $user_id );
				} else {
					$user = false;
				}

				$data[] = array(
					'id'           => $payment->ID,
					'seq_id'       => $payment->number,
					'email'        => $payment_meta['email'],
					'customer_id'  => $payment->customer_id,
					'first'        => $user_info['first_name'],
					'last'         => $user_info['last_name'],
					'address1'     => isset( $user_info['address']['line1'] )   ? $user_info['address']['line1']   : '',
					'address2'     => isset( $user_info['address']['line2'] )   ? $user_info['address']['line2']   : '',
					'city'         => isset( $user_info['address']['city'] )    ? $user_info['address']['city']    : '',
					'state'        => isset( $user_info['address']['state'] )   ? $user_info['address']['state']   : '',
					'country'      => isset( $user_info['address']['country'] ) ? $user_info['address']['country'] : '',
					'zip'          => isset( $user_info['address']['zip'] )     ? $user_info['address']['zip']     : '',
					'products'     => $products,
					'products_raw' => $products_raw,
					'skus'         => $skus,
					'amount'       => html_entity_decode( pl8app_format_amount( $total ) ), // The non-discounted item price
					'tax'          => html_entity_decode( pl8app_format_amount( pl8app_get_payment_tax( $payment->ID, $payment_meta ) ) ),
					'discount'     => isset( $user_info['discount'] ) && $user_info['discount'] != 'none' ? $user_info['discount'] : __( 'none', 'pl8app' ),
					'gateway'      => pl8app_get_gateway_admin_label( pl8app_get_payment_meta( $payment->ID, '_pl8app_payment_gateway', true ) ),
					'trans_id'     => $payment->transaction_id,
					'key'          => $payment_meta['key'],
					'date'         => $payment->date,
					'user'         => $user ? $user->display_name : __( 'guest', 'pl8app' ),
					'currency'     => $payment->currency,
					'ip'           => $payment->ip,
					'mode'         => $payment->get_meta( '_pl8app_payment_mode', true ),
					'status'       => ( 'publish' === $payment->status ) ? 'complete' : $payment->status,
					'country_name' => isset( $user_info['address']['country'] ) ? pl8app_get_country_name( $user_info['address']['country'] ) : '',
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

		$status = $this->status;
		$args   = array(
			'start-date' => date( 'n/d/Y', strtotime( $this->start ) ),
			'end-date'   => date( 'n/d/Y', strtotime( $this->end ) ),
		);

		if( 'any' == $status ) {

			$total = array_sum( (array) pl8app_count_payments( $args ) );

		} else {

			$total = pl8app_count_payments( $args )->$status;

		}

		$percentage = 100;

		if( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the payments export
	 *
	 * @since 2.4.2
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {
		$this->start  = isset( $request['start'] )  ? sanitize_text_field( $request['start'] )  : '';
		$this->end    = isset( $request['end']  )   ? sanitize_text_field( $request['end']  )   : '';
		$this->status = isset( $request['status'] ) ? sanitize_text_field( $request['status'] ) : 'complete';
	}
}
