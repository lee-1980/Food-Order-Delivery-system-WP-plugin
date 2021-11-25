<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * pl8app_Payments_Export Class
 *
 * @since  1.0.0
 */
class pl8app_Payments_Export extends pl8app_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since  1.0.0
	 */
	public $export_type = 'payments';

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
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'pl8app_payments_export_filename', 'pl8app-export-' . $this->export_type . '-' . $month . '-' . $year ) . '.csv' );
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
			'id'       => __( 'ID',   'pl8app' ), // unaltered payment ID (use for querying)
			'seq_id'   => __( 'Payment Number',   'pl8app' ), // sequential payment ID
			'email'    => __( 'Email', 'pl8app' ),
			'first'    => __( 'First Name', 'pl8app' ),
			'last'     => __( 'Last Name', 'pl8app' ),
			'address1' => __( 'Address', 'pl8app' ),
			'address2' => __( 'Address (Line 2)', 'pl8app' ),
			'city'     => __( 'City', 'pl8app' ),
			'state'    => __( 'State', 'pl8app' ),
			'country'  => __( 'Country', 'pl8app' ),
			'zip'      => __( 'Zip / Postal Code', 'pl8app' ),
			'products' => __( 'Products', 'pl8app' ),
			'skus'     => __( 'SKUs', 'pl8app' ),
			'amount'   => __( 'Amount', 'pl8app' ) . ' (' . html_entity_decode( pl8app_currency_filter( '' ) ) . ')',
			'tax'      => __( 'Tax', 'pl8app' ) . ' (' . html_entity_decode( pl8app_currency_filter( '' ) ) . ')',
			'discount' => __( 'Discount Code', 'pl8app' ),
			'gateway'  => __( 'Payment Method', 'pl8app' ),
			'trans_id' => __( 'Transaction ID', 'pl8app' ),
			'key'      => __( 'Purchase Key', 'pl8app' ),
			'date'     => __( 'Date', 'pl8app' ),
			'user'     => __( 'User', 'pl8app' ),
			'status'   => __( 'Status', 'pl8app' )
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
	 * @since  1.0.0
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $wpdb;

		$data = array();

		$payments = pl8app_get_payments( array(
			'offset' => 0,
			'number' => -1,
			'mode'   => pl8app_is_test_mode() ? 'test' : 'live',
			'status' => isset( $_POST['pl8app_export_payment_status'] ) ? $_POST['pl8app_export_payment_status'] : 'any',
			'month'  => isset( $_POST['month'] ) ? absint( $_POST['month'] ) : date( 'n' ),
			'year'   => isset( $_POST['year'] ) ? absint( $_POST['year'] ) : date( 'Y' )
		) );

		foreach ( $payments as $payment ) {
			$payment_meta   = pl8app_get_payment_meta( $payment->ID );
			$user_info      = pl8app_get_payment_meta_user_info( $payment->ID );
			$menuitems      = pl8app_get_payment_meta_cart_details( $payment->ID );
			$total          = pl8app_get_payment_amount( $payment->ID );
			$user_id        = isset( $user_info['id'] ) && $user_info['id'] != -1 ? $user_info['id'] : $user_info['email'];
			$products       = '';
			$skus           = '';

			if ( $menuitems ) {
				foreach ( $menuitems as $key => $menuitem ) {
					// Menu Item ID
					$id = isset( $payment_meta['cart_details'] ) ? $menuitem['id'] : $menuitem;

					// If the menuitem has variable prices, override the default price
					$price_override = isset( $payment_meta['cart_details'] ) ? $menuitem['price'] : null;

					$price = pl8app_get_menuitem_final_price( $id, $user_info, $price_override );

					// Display the Downoad Name
					$products .= get_the_title( $id ) . ' - ';

					if ( pl8app_use_skus() ) {
						$sku = pl8app_get_menuitem_sku( $id );

						if ( ! empty( $sku ) )
							$skus .= $sku;
					}

					if ( isset( $menuitems[ $key ]['item_number'] ) && isset( $menuitems[ $key ]['item_number']['options'] ) ) {
						$price_options = $menuitems[ $key ]['item_number']['options'];

						if ( isset( $price_options['price_id'] ) ) {
							$products .= pl8app_get_price_option_name( $id, $price_options['price_id'], $payment->ID ) . ' - ';
						}
					}
					$products .= html_entity_decode( pl8app_currency_filter( $price ) );

					if ( $key != ( count( $menuitems ) -1 ) ) {
						$products .= ' / ';

						if( pl8app_use_skus() )
							$skus .= ' / ';
					}
				}
			}

			if ( is_numeric( $user_id ) ) {
				$user = get_userdata( $user_id );
			} else {
				$user = false;
			}

			$data[] = array(
				'id'       => $payment->ID,
				'seq_id'   => pl8app_get_payment_number( $payment->ID ),
				'email'    => $payment_meta['email'],
				'first'    => $user_info['first_name'],
				'last'     => $user_info['last_name'],
				'address1' => isset( $user_info['address']['line1'] )   ? $user_info['address']['line1']   : '',
				'address2' => isset( $user_info['address']['line2'] )   ? $user_info['address']['line2']   : '',
				'city'     => isset( $user_info['address']['city'] )    ? $user_info['address']['city']    : '',
				'state'    => isset( $user_info['address']['state'] )   ? $user_info['address']['state']   : '',
				'country'  => isset( $user_info['address']['country'] ) ? $user_info['address']['country'] : '',
				'zip'      => isset( $user_info['address']['zip'] )     ? $user_info['address']['zip']     : '',
				'products' => $products,
				'skus'     => $skus,
				'amount'   => html_entity_decode( pl8app_format_amount( $total ) ),
				'tax'      => html_entity_decode( pl8app_format_amount( pl8app_get_payment_tax( $payment->ID, $payment_meta ) ) ),
				'discount' => isset( $user_info['discount'] ) && $user_info['discount'] != 'none' ? $user_info['discount'] : __( 'none', 'pl8app' ),
				'gateway'  => pl8app_get_gateway_admin_label( pl8app_get_payment_meta( $payment->ID, '_pl8app_payment_gateway', true ) ),
				'trans_id' => pl8app_get_payment_transaction_id( $payment->ID ),
				'key'      => $payment_meta['key'],
				'date'     => $payment->post_date,
				'user'     => $user ? $user->display_name : __( 'guest', 'pl8app' ),
				'status'   => pl8app_get_payment_status( $payment, true )
			);

		}

		$data = apply_filters( 'pl8app_export_get_data', $data );
		$data = apply_filters( 'pl8app_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}
