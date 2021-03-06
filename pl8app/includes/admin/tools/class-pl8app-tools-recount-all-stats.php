<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * pl8app_Tools_Recount_All_Stats Class
 *
 * @since  1.0.0
 */
class pl8app_Tools_Recount_All_Stats extends pl8app_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since  1.0.0
	 */
	public $export_type = '';

	/**
	 * Allows for a non-menuitem batch processing to be run.
	 * @since  1.0.0
	 * @var boolean
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step
	 * @since  1.0.0
	 * @var integer
	 */
	public $per_step = 30;

	/**
	 * Get the Export Data
	 *
	 * @since  1.0.0
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $pl8app_logs, $wpdb;

		$totals             = $this->get_stored_data( 'pl8app_temp_recount_all_stats'  );
		$payment_items      = $this->get_stored_data( 'pl8app_temp_payment_items'      );
		$processed_payments = $this->get_stored_data( 'pl8app_temp_processed_payments' );
		$accepted_statuses  = apply_filters( 'pl8app_recount_accepted_statuses', array( 'publish', 'revoked' ) );

		if ( false === $totals ) {
			$totals = array();
		}

		if ( false === $payment_items ) {
			$payment_items = array();
		}

		if ( false === $processed_payments ) {
			$processed_payments = array();
		}

		$all_menuitems = $this->get_stored_data( 'pl8app_temp_menuitem_ids' );

		$args = apply_filters( 'pl8app_recount_menuitem_stats_args', array(
			'post_parent__in' => $all_menuitems,
			'post_type'       => 'pl8app_log',
			'posts_per_page'  => $this->per_step,
			'post_status'     => 'publish',
			'paged'           => $this->step,
			'log_type'        => 'sale',
			'fields'          => 'ids',
		) );

		$log_ids = $pl8app_logs->get_connected_logs( $args, 'sale' );

		if ( $log_ids ) {
			$log_ids     = implode( ',', $log_ids );
			$payment_ids = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_pl8app_log_payment_id' AND post_id IN ($log_ids)" );
			unset( $log_ids );

			$payment_ids = implode( ',', $payment_ids );
			$payments = $wpdb->get_results( "SELECT ID, post_status FROM $wpdb->posts WHERE ID IN (" . $payment_ids . ")" );
			unset( $payment_ids );

			foreach ( $payments as $payment ) {

				// Prevent payments that have all ready been retrieved from a previous sales log from counting again.
				if ( in_array( $payment->ID, $processed_payments ) ) {
					continue;
				}

				if ( ! in_array( $payment->post_status, $accepted_statuses ) ) {
					$processed_payments[] = $payment->ID;
					continue;
				}

				$items = $payment_items[ $payment->ID ];

				foreach ( $items as $item ) {
					$menuitem_id = $item['id'];

					if ( ! in_array( $menuitem_id, $all_menuitems ) ) {
						continue;
					}

					if ( ! array_key_exists( $menuitem_id, $totals ) ) {
						$totals[ $menuitem_id ] = array(
							'sales'    => (int) 0,
							'earnings' => (float) 0,
						);
					}

					$amount = $item['price'];
					if ( ! empty( $item['fees'] ) ) {
						foreach ( $item['fees'] as $fee ) {
							// Only let negative fees affect earnings
							if ( $fee['amount'] > 0  ) {
								continue;
							}

							$amount += $fee['amount'];
						}
					}

					$totals[ $menuitem_id ]['sales']++;
					$totals[ $menuitem_id ]['earnings'] += $amount;

				}

				$processed_payments[] = $payment->ID;
			}

			$this->store_data( 'pl8app_temp_processed_payments', $processed_payments );
			$this->store_data( 'pl8app_temp_recount_all_stats', $totals );

			return true;
		}

		foreach ( $totals as $key => $stats ) {
			update_post_meta( $key, '_pl8app_menuitem_sales'   , $stats['sales'] );
			update_post_meta( $key, '_pl8app_menuitem_earnings', $stats['earnings'] );
		}

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since  1.0.0
	 * @return int
	 */
	public function get_percentage_complete() {

		$total = $this->get_stored_data( 'pl8app_recount_all_total', false );

		if ( false === $total ) {
			$this->pre_fetch();
			$total = $this->get_stored_data( 'pl8app_recount_all_total', 0 );
		}

		$percentage = 100;

		if( $total > 0 ) {
			$percentage = ( ( $this->per_step * $this->step ) / $total ) * 100;
		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the payments export
	 *
	 * @since  1.0.0
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {
		$this->menuitem_id = isset( $request['menuitem_id'] ) ? sanitize_text_field( $request['menuitem_id'] ) : false;
	}

	/**
	 * Process a step
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die( __( 'You do not have permission to export data.', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if( $had_data ) {
			$this->done = false;
			return true;
		} else {
			$this->delete_data( 'pl8app_recount_all_total' );
			$this->delete_data( 'pl8app_temp_recount_all_stats' );
			$this->delete_data( 'pl8app_temp_payment_items' );
			$this->delete_data( 'pl8app_temp_menuitem_ids' );
			$this->delete_data( 'pl8app_temp_processed_payments' );
			$this->done    = true;
			$this->message = __( 'Earnings and sales stats successfully recounted.', 'pl8app' );
			return false;
		}
	}

	public function headers() {
		ignore_user_abort( true );

		if ( ! pl8app_is_func_disabled( 'set_time_limit' ) ) {
			set_time_limit( 0 );
		}
	}

	/**
	 * Perform the export
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function export() {

		// Set headers
		$this->headers();

		pl8app_die();
	}

	public function pre_fetch() {
		global $pl8app_logs, $wpdb;

		if ( $this->step == 1 ) {
			$this->delete_data( 'pl8app_temp_recount_all_total' );
			$this->delete_data( 'pl8app_temp_recount_all_stats' );
			$this->delete_data( 'pl8app_temp_payment_items' );
			$this->delete_data( 'pl8app_temp_processed_payments' );
		}

		$accepted_statuses = apply_filters( 'pl8app_recount_accepted_statuses', array( 'publish', 'revoked' ) );
		$total             = $this->get_stored_data( 'pl8app_temp_recount_all_total' );

		if ( false === $total ) {
			$total         = 0;
			$payment_items = $this->get_stored_data( 'pl8app_temp_payment_items' );

			if ( false === $payment_items ) {
				$payment_items = array();
				$this->store_data( 'pl8app_temp_payment_items', $payment_items );
			}

			$all_menuitems = $this->get_stored_data( 'pl8app_temp_menuitem_ids' );

			if ( false === $all_menuitems ) {
				$args = array(
					'post_status'    => 'any',
					'post_type'      => 'menuitem',
					'posts_per_page' => -1,
					'fields'         => 'ids',
				);

				$all_menuitems = get_posts( $args );
				$this->store_data( 'pl8app_temp_menuitem_ids', $all_menuitems );

				if ( $this->step == 1 ) {
					foreach ( $all_menuitems as $menuitem ) {
						update_post_meta( $menuitem, '_pl8app_menuitem_sales'   , 0 );
						update_post_meta( $menuitem, '_pl8app_menuitem_earnings', 0 );
					}
				}
			}

			$args  = apply_filters( 'pl8app_recount_menuitem_stats_total_args', array(
				'post_parent__in' => $all_menuitems,
				'post_type'       => 'pl8app_log',
				'post_status'     => 'publish',
				'log_type'        => 'sale',
				'fields'          => 'ids',
				'nopaging'        => true,
			) );

			$all_logs = $pl8app_logs->get_connected_logs( $args, 'sale' );

			if ( $all_logs ) {
				$log_ids     = implode( ',', $all_logs );
				$payment_ids = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_pl8app_log_payment_id' AND post_id IN ($log_ids)" );
				unset( $log_ids );

				$payment_ids = implode( ',', $payment_ids );
				$payments = $wpdb->get_results( "SELECT ID, post_status FROM $wpdb->posts WHERE ID IN (" . $payment_ids . ")" );
				unset( $payment_ids );

				foreach ( $payments as $payment ) {
					if ( ! in_array( $payment->post_status, $accepted_statuses ) ) {
						continue;
					}

					if ( ! array_key_exists( $payment->ID, $payment_items ) ) {

						$items = pl8app_get_payment_meta_cart_details( $payment->ID );
						$payment_items[ $payment->ID ] = $items;

					}

				}

				$total = count( $all_logs );
			}

			$this->store_data( 'pl8app_temp_payment_items', $payment_items );
			$this->store_data( 'pl8app_recount_all_total' , $total );
		}

	}

	/**
	 * Given a key, get the information from the Database Directly
	 *
	 * @since  1.0.0
	 * @param  string $key The option_name
	 * @return mixed       Returns the data from the database
	 */
	private function get_stored_data( $key ) {
		global $wpdb;
		$value = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = '%s'", $key ) );

		if ( empty( $value ) ) {
			return false;
		}

		$maybe_json = json_decode( $value );
		if ( ! is_null( $maybe_json ) ) {
			$value = json_decode( $value, true );
		}

		return $value;
	}

	/**
	 * Give a key, store the value
	 *
	 * @since  1.0.0
	 * @param  string $key   The option_name
	 * @param  mixed  $value  The value to store
	 * @return void
	 */
	private function store_data( $key, $value ) {
		global $wpdb;

		$value = is_array( $value ) ? wp_json_encode( $value ) : esc_attr( $value );

		$data = array(
			'option_name'  => $key,
			'option_value' => $value,
			'autoload'     => 'no',
		);

		$formats = array(
			'%s', '%s', '%s',
		);

		$wpdb->replace( $wpdb->options, $data, $formats );
	}

	/**
	 * Delete an option
	 *
	 * @since  1.0.0
	 * @param  string $key The option_name to delete
	 * @return void
	 */
	private function delete_data( $key ) {
		global $wpdb;
		$wpdb->delete( $wpdb->options, array( 'option_name' => $key ) );
	}

}
