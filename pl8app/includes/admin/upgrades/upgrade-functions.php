<?php
/**
 * Upgrade Functions
 *
 * @package     pl8app
 * @subpackage  Admin/Upgrades
 * @copyright
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0.0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



/**
 * Triggers all upgrade functions
 *
 * This function is usually triggered via AJAX
 *
 * @since  1.0.0
 * @return void
*/
function pl8app_trigger_upgrades() {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	$pl8app_version = get_option( 'pl8app_version' );

	if ( ! $pl8app_version ) {
		// 1.3 is the first version to use this option so we must add it
		$pl8app_version = '1.3';
		add_option( 'pl8app_version', $pl8app_version );
	}

	if ( version_compare( PL8_VERSION, $pl8app_version, '>' ) ) {
		pl8app_v131_upgrades();
	}

	if ( version_compare( $pl8app_version, '1.3.4', '<' ) ) {
		pl8app_v134_upgrades();
	}

	if ( version_compare( $pl8app_version, '1.4', '<' ) ) {
		pl8app_v14_upgrades();
	}

	if ( version_compare( $pl8app_version, '1.5', '<' ) ) {
		pl8app_v15_upgrades();
	}

	if ( version_compare( $pl8app_version, '2.0', '<' ) ) {
		pl8app_v20_upgrades();
	}

	update_option( 'pl8app_version', PL8_VERSION );

	if ( DOING_AJAX )
		die( 'complete' ); // Let AJAX know that the upgrade is complete
}
add_action( 'wp_ajax_pl8app_trigger_upgrades', 'pl8app_trigger_upgrades' );

/**
 * For use when doing 'stepped' upgrade routines, to see if we need to start somewhere in the middle
 * @since  1.0.0.6
 * @return mixed   When nothing to resume returns false, otherwise starts the upgrade where it left off
 */
function pl8app_maybe_resume_upgrade() {

	$doing_upgrade = get_option( 'pl8app_doing_upgrade', false );

	if ( empty( $doing_upgrade ) ) {
		return false;
	}

	return $doing_upgrade;

}

/**
 * Adds an upgrade action to the completed upgrades array
 *
 * @since  1.0.0
 * @param  string $upgrade_action The action to add to the copmleted upgrades array
 * @return bool                   If the function was successfully added
 */
function pl8app_set_upgrade_complete( $upgrade_action = '' ) {

	if ( empty( $upgrade_action ) ) {
		return false;
	}

	$completed_upgrades   = pl8app_get_completed_upgrades();
	$completed_upgrades[] = $upgrade_action;

	// Remove any blanks, and only show uniques
	$completed_upgrades = array_unique( array_values( $completed_upgrades ) );

	return update_option( 'pl8app_completed_upgrades', $completed_upgrades );
}

/**
 * Converts old sale and file menuitem logs to new logging system
 *
 * @since  1.0.0
 * @uses WP_Query
 * @uses pl8app_Logging
 * @return void
 */
function pl8app_v131_upgrades() {
	if ( get_option( 'pl8app_logs_upgraded' ) )
		return;

	if ( version_compare( get_option( 'pl8app_version' ), '1.3', '>=' ) )
		return;

	ignore_user_abort( true );

	if ( ! pl8app_is_func_disabled( 'set_time_limit' ) )
		set_time_limit( 0 );

	$args = array(
		'post_type' 		=> 'menuitem',
		'posts_per_page' 	=> -1,
		'post_status' 		=> 'publish'
	);

	$query = new WP_Query( $args );
	$menuitems = $query->get_posts();

	if ( $menuitems ) {
		$pl8app_log = new pl8app_Logging();
		foreach ( $menuitems as $menuitem ) {
			// Convert sale logs
			$sale_logs = pl8app_get_menuitem_sales_log( $menuitem->ID, false );

			if ( $sale_logs ) {
				foreach ( $sale_logs['sales'] as $sale ) {
					$log_data = array(
						'post_parent'	=> $menuitem->ID,
						'post_date'		=> $sale['date'],
						'log_type'		=> 'sale'
					);

					$log_meta = array(
						'payment_id'=> $sale['payment_id']
					);

					$log = $pl8app_log->insert_log( $log_data, $log_meta );
				}
			}

			// Convert file menuitem logs
			$file_logs = pl8app_get_file_menuitem_log( $menuitem->ID, false );

			if ( $file_logs ) {
				foreach ( $file_logs['menuitems'] as $log ) {
					$log_data = array(
						'post_parent'	=> $menuitem->ID,
						'post_date'		=> $log['date'],
						'log_type'		=> 'file_menuitem'

					);

					$log_meta = array(
						'user_info'	=> $log['user_info'],
						'file_id'	=> $log['file_id'],
						'ip'		=> $log['ip']
					);

					$log = $pl8app_log->insert_log( $log_data, $log_meta );
				}
			}
		}
	}
	add_option( 'pl8app_logs_upgraded', '1' );
}

/**
 * Upgrade routine for v1.3.4
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_v134_upgrades() {
	$general_options = get_option( 'pl8app_settings_general' );

	if ( isset( $general_options['failure_page'] ) )
		return; // Settings already updated

	// Failed Purchase Page
	$failed = wp_insert_post(
		array(
			'post_title'     => __( 'Transaction Failed', 'pl8app' ),
			'post_content'   => __( 'Your transaction failed, please try again or contact site support.', 'pl8app' ),
			'post_status'    => 'publish',
			'post_author'    => 1,
			'post_type'      => 'page',
			'post_parent'    => $general_options['purchase_page'],
			'comment_status' => 'closed'
		)
	);

	$general_options['failure_page'] = $failed;

	update_option( 'pl8app_settings_general', $general_options );
}

/**
 * Upgrade routine for v1.4
 *
 * @since  1.0.0
 * @global $pl8app_options Array of all the pl8app Options
 * @return void
 */
function pl8app_v14_upgrades() {
	global $pl8app_options;

	/** Add [pl8app_receipt] to success page **/
	$success_page = get_post( pl8app_get_option( 'success_page' ) );

	// Check for the [pl8app_receipt] shortcode and add it if not present
	if( strpos( $success_page->post_content, '[pl8app_receipt' ) === false ) {
		$page_content = $success_page->post_content .= "\n[pl8app_receipt]";
		wp_update_post( array( 'ID' => pl8app_get_option( 'success_page' ), 'post_content' => $page_content ) );
	}

	/** Convert Discounts to new Custom Post Type **/
	$discounts = get_option( 'pl8app_discounts' );

	if ( $discounts ) {
		foreach ( $discounts as $discount_key => $discount ) {

			$discount_id = wp_insert_post( array(
				'post_type'   => 'pl8app_discount',
				'post_title'  => isset( $discount['name'] ) ? $discount['name'] : '',
				'post_status' => 'active'
			) );

			$meta = array(
				'code'        => isset( $discount['code'] ) ? $discount['code'] : '',
				'uses'        => isset( $discount['uses'] ) ? $discount['uses'] : '',
				'max_uses'    => isset( $discount['max'] ) ? $discount['max'] : '',
				'amount'      => isset( $discount['amount'] ) ? $discount['amount'] : '',
				'start'       => isset( $discount['start'] ) ? $discount['start'] : '',
				'expiration'  => isset( $discount['expiration'] ) ? $discount['expiration'] : '',
				'type'        => isset( $discount['type'] ) ? $discount['type'] : '',
				'min_price'   => isset( $discount['min_price'] ) ? $discount['min_price'] : ''
			);

			foreach ( $meta as $meta_key => $value ) {
				update_post_meta( $discount_id, '_pl8app_discount_' . $meta_key, $value );
			}
		}

		// Remove old discounts from database
		delete_option( 'pl8app_discounts' );
	}
}


/**
 * Upgrade routine for v1.5
 *
 * @since 1.0
 * @return void
 */
function pl8app_v15_upgrades() {
	// Update options for missing tax settings
	$tax_options = get_option( 'pl8app_settings_taxes' );

	// Set include tax on checkout to off
	$tax_options['checkout_include_tax'] = 'no';

	// Check if prices are displayed with taxes
	if( isset( $tax_options['taxes_on_prices'] ) ) {
		$tax_options['prices_include_tax'] = 'yes';
	} else {
		$tax_options['prices_include_tax'] = 'no';
	}

	update_option( 'pl8app_settings_taxes', $tax_options );

	// Flush the rewrite rules for the new /pl8app-api/ end point
	flush_rewrite_rules( false );
}

/**
 * Upgrades for pl8app v2.0
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_v20_upgrades() {

	global $pl8app_options, $wpdb;

	ignore_user_abort( true );

	if ( ! pl8app_is_func_disabled( 'set_time_limit' ) ) {
		set_time_limit( 0 );
	}

	// Upgrade for the anti-behavior fix - #2188
	if( ! empty( $pl8app_options['disable_ajax_cart'] ) ) {
		unset( $pl8app_options['enable_ajax_cart'] );
	} else {
		$pl8app_options['enable_ajax_cart'] = '1';
	}

	// Upgrade for the anti-behavior fix - #2188
	if( ! empty( $pl8app_options['disable_cart_saving'] ) ) {
		unset( $pl8app_options['enable_cart_saving'] );
	} else {
		$pl8app_options['enable_cart_saving'] = '1';
	}

	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_wp_session_expires_%' AND option_value+0 < 2789308218" );

	update_option( 'pl8app_settings', $pl8app_options );

}

/**
 * Upgrades for pl8app v2.0 and sequential payment numbers
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_v20_upgrade_sequential_payment_numbers() {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	if ( ! pl8app_is_func_disabled( 'set_time_limit' ) ) {
		set_time_limit( 0 );
	}

	$step   = isset( $_GET['step'] )  ? absint( $_GET['step'] )  : 1;
	$total  = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;

	if( empty( $total ) || $total <= 1 ) {
		$payments = pl8app_count_payments();
		foreach( $payments as $status ) {
			$total += $status;
		}
	}

	$args   = array(
		'number' => 100,
		'page'   => $step,
		'status' => 'any',
		'order'  => 'ASC'
	);

	$payments = new pl8app_Payments_Query( $args );
	$payments = $payments->get_payments();

	if( $payments ) {

		$prefix  = pl8app_get_option( 'sequential_prefix' );
		$postfix = pl8app_get_option( 'sequential_postfix' );
		$number  = ! empty( $_GET['custom'] ) ? absint( $_GET['custom'] ) : intval( pl8app_get_option( 'sequential_start', 1 ) );

		foreach( $payments as $payment ) {

			// Re-add the prefix and postfix
			$payment_number = $prefix . $number . $postfix;

			pl8app_update_payment_meta( $payment->ID, '_pl8app_payment_number', $payment_number );

			// Increment the payment number
			$number++;

		}

		// Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'pl8app-upgrades',
			'pl8app-upgrade' => 'upgrade_sequential_payment_numbers',
			'step'        => $step,
			'custom'      => $number,
			'total'       => $total
		), admin_url( 'index.php' ) );
		wp_redirect( $redirect ); exit;

	} else {


		// No more payments found, finish up
		PL8PRESS()->session->set( 'upgrade_sequential', null );
		delete_option( 'pl8app_doing_upgrade' );

		wp_redirect( admin_url() ); exit;
	}

}
add_action( 'pl8app_upgrade_sequential_payment_numbers', 'pl8app_v20_upgrade_sequential_payment_numbers' );

/**
 * Upgrades for pl8app v2.1 and the new customers database
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_v21_upgrade_customers_db() {

	global $wpdb;

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	if ( ! pl8app_is_func_disabled( 'set_time_limit' ) ) {
		@set_time_limit(0);
	}

	if( ! get_option( 'pl8app_upgrade_customers_db_version' ) ) {
		// Create the customers database on the first run
		@PL8PRESS()->customers->create_table();
	}

	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$number = 20;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;

	$emails = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = '_pl8app_payment_user_email' LIMIT %d,%d;", $offset, $number ) );

	if( $emails ) {

		foreach( $emails as $email ) {

			if( PL8PRESS()->customers->exists( $email ) ) {
				continue; // Allow the upgrade routine to be safely re-run in the case of failure
			}

			$args = array(
				'user'    => $email,
				'order'   => 'ASC',
				'orderby' => 'ID',
				'number'  => -1,
				'page'    => $step
			);

			$payments = new pl8app_Payments_Query( $args );
			$payments = $payments->get_payments();

			if( $payments ) {

				$total_value = 0.00;
				$total_count = 0;

				foreach( $payments as $payment ) {

					$status = get_post_status( $payment->ID );
					if( 'revoked' == $status || 'publish' == $status ) {

						$total_value += $payment->total;
						$total_count += 1;

					}

				}

				$ids  = wp_list_pluck( $payments, 'ID' );

				$user = get_user_by( 'email', $email );

				$args = array(
					'email'          => $email,
					'user_id'        => $user ? $user->ID : 0,
					'name'           => $user ? $user->display_name : '',
					'purchase_count' => $total_count,
					'purchase_value' => round( $total_value, 2 ),
					'payment_ids'    => implode( ',', array_map( 'absint', $ids ) ),
					'date_created'   => $payments[0]->date
				);

				$customer_id = PL8PRESS()->customers->add( $args );

				foreach( $ids as $id ) {
					update_post_meta( $id, '_pl8app_payment_customer_id', $customer_id );
				}

			}

		}

		// Customers found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'pl8app-upgrades',
			'pl8app-upgrade' => 'upgrade_customers_db',
			'step'        => $step
		), admin_url( 'index.php' ) );
		wp_redirect( $redirect ); exit;

	} else {

		// No more customers found, finish up

		update_option( 'pl8app_version', preg_replace( '/[^0-9.].*/', '', PL8_VERSION ) );
		delete_option( 'pl8app_doing_upgrade' );

		wp_redirect( admin_url() ); exit;
	}

}
add_action( 'pl8app_upgrade_customers_db', 'pl8app_v21_upgrade_customers_db' );

/**
 * Fixes the pl8app_log meta for 2.2.6
 *
 * @since  1.0.0.6
 * @return void
 */
function pl8app_v226_upgrade_payments_price_logs_db() {
	global $wpdb;
	if( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}
	ignore_user_abort( true );
	if ( ! pl8app_is_func_disabled( 'set_time_limit' ) ) {
		@set_time_limit(0);
	}
	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$number = 25;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;
	if ( 1 === $step ) {
		// Check if we have any variable price products on the first step
		$sql = "SELECT ID FROM $wpdb->posts p LEFT JOIN $wpdb->postmeta m ON p.ID = m.post_id WHERE m.meta_key = '_variable_pricing' AND m.meta_value = 1 LIMIT 1";
		$has_variable = $wpdb->get_col( $sql );
		if( empty( $has_variable ) ) {
			// We had no variable priced products, so go ahead and just complete
			update_option( 'pl8app_version', preg_replace( '/[^0-9.].*/', '', PL8_VERSION ) );
			delete_option( 'pl8app_doing_upgrade' );
			wp_redirect( admin_url() ); exit;
		}
	}
	$payment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'pl8app_payment' ORDER BY post_date DESC LIMIT %d,%d;", $offset, $number ) );
	if( ! empty( $payment_ids ) ) {
		foreach( $payment_ids as $payment_id ) {
			$payment_menuitems  = pl8app_get_payment_meta_menuitems( $payment_id );
			$variable_menuitems = array();
			if ( ! is_array( $payment_menuitems ) ) {
				continue; // May not be an array due to some very old payments, move along
			}
			foreach ( $payment_menuitems as $menuitem ) {
				// Don't care if the menuitem is a single price id
				if ( ! isset( $menuitem['options']['price_id'] ) ) {
					continue;
				}
				$variable_menuitems[] = array( 'id' => $menuitem['id'], 'price_id' => $menuitem['options']['price_id'] );
			}
			$variable_menuitem_ids = array_unique( wp_list_pluck( $variable_menuitems, 'id' ) );
			$unique_menuitem_ids   = implode( ',', $variable_menuitem_ids );
			if ( empty( $unique_menuitem_ids ) ) {
				continue; // If there were no menu addons, just fees, move along
			}
			// Get all Log Ids where the post parent is in the set of menuitem IDs we found in the cart meta
			$logs = $wpdb->get_results( "SELECT m.post_id AS log_id, p.post_parent AS menuitem_id FROM $wpdb->postmeta m LEFT JOIN $wpdb->posts p ON m.post_id = p.ID WHERE meta_key = '_pl8app_log_payment_id' AND meta_value = $payment_id AND p.post_parent IN ($unique_menuitem_ids)", ARRAY_A );
			$mapped_logs = array();
			// Go through each cart item
			foreach( $variable_menuitems as $cart_item ) {
				// Itterate through the logs we found attached to this payment
				foreach ( $logs as $key => $log ) {
					// If this Log ID is associated with this menuitem ID give it the price_id
					if ( (int) $log['menuitem_id'] === (int) $cart_item['id'] ) {
						$mapped_logs[$log['log_id']] = $cart_item['price_id'];
						// Remove this Download/Log ID from the list, for multipurchase compatibility
						unset( $logs[$key] );
						// These aren't the logs we're looking for. Move Along, Move Along.
						break;
					}
				}
			}
			if ( ! empty( $mapped_logs ) ) {
				$update  = "UPDATE $wpdb->postmeta SET meta_value = ";
				$case    = "CASE post_id ";
				foreach ( $mapped_logs as $post_id => $value ) {
					$case .= "WHEN $post_id THEN $value ";
				}
				$case   .= "END ";
				$log_ids = implode( ',', array_keys( $mapped_logs ) );
				$where   = "WHERE post_id IN ($log_ids) AND meta_key = '_pl8app_log_price_id'";
				$sql     = $update . $case . $where;
				// Execute our query to update this payment
				$wpdb->query( $sql );
			}
		}
		// More Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'pl8app-upgrades',
			'pl8app-upgrade' => 'upgrade_payments_price_logs_db',
			'step'        => $step
		), admin_url( 'index.php' ) );
		wp_redirect( $redirect ); exit;
	} else {
		// No more payments found, finish up
		update_option( 'pl8app_version', preg_replace( '/[^0-9.].*/', '', PL8_VERSION ) );
		delete_option( 'pl8app_doing_upgrade' );
		wp_redirect( admin_url() ); exit;
	}
}
add_action( 'pl8app_upgrade_payments_price_logs_db', 'pl8app_v226_upgrade_payments_price_logs_db' );

/**
 * Upgrades payment taxes for 2.3
 *
 * @since 1.0
 * @return void
 */
function pl8app_v23_upgrade_payment_taxes() {
	global $wpdb;
	if( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}
	ignore_user_abort( true );
	if ( ! pl8app_is_func_disabled( 'set_time_limit' ) ) {
		@set_time_limit(0);
	}

	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$number = 50;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;

	if ( $step < 2 ) {
		// Check if we have any payments before moving on
		$sql = "SELECT ID FROM $wpdb->posts WHERE post_type = 'pl8app_payment' LIMIT 1";
		$has_payments = $wpdb->get_col( $sql );

		if( empty( $has_payments ) ) {
			// We had no payments, just complete
			update_option( 'pl8app_version', preg_replace( '/[^0-9.].*/', '', PL8_VERSION ) );
			pl8app_set_upgrade_complete( 'upgrade_payment_taxes' );
			delete_option( 'pl8app_doing_upgrade' );
			wp_redirect( admin_url() ); exit;
		}
	}

	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;
	if ( empty( $total ) || $total <= 1 ) {
		$total_sql = "SELECT COUNT(ID) as total_payments FROM $wpdb->posts WHERE post_type = 'pl8app_payment'";
		$results   = $wpdb->get_row( $total_sql, 0 );

		$total     = $results->total_payments;
	}

	$payment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'pl8app_payment' ORDER BY post_date DESC LIMIT %d,%d;", $offset, $number ) );

	if( $payment_ids ) {
		foreach( $payment_ids as $payment_id ) {

			// Add the new _pl8app_payment_meta item
			$payment_tax = pl8app_get_payment_tax( $payment_id );
			pl8app_update_payment_meta( $payment_id, '_pl8app_payment_tax', $payment_tax );

		}

		// Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'pl8app-upgrades',
			'pl8app-upgrade' => 'upgrade_payment_taxes',
			'step'        => $step,
			'number'      => $number,
			'total'       => $total
		), admin_url( 'index.php' ) );
		wp_redirect( $redirect ); exit;
	} else {
		// No more payments found, finish up
		update_option( 'pl8app_version', preg_replace( '/[^0-9.].*/', '', PL8_VERSION ) );
		pl8app_set_upgrade_complete( 'upgrade_payment_taxes' );
		delete_option( 'pl8app_doing_upgrade' );
		wp_redirect( admin_url() ); exit;
	}
}
add_action( 'pl8app_upgrade_payment_taxes', 'pl8app_v23_upgrade_payment_taxes' );

/**
 * Run the upgrade for the customers to find all payment attachments
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_v23_upgrade_customer_purchases() {
	global $wpdb;

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	if ( ! pl8app_is_func_disabled( 'set_time_limit' ) ) {
		@set_time_limit(0);
	}

	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$number = 50;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;

	if ( $step < 2 ) {
		// Check if we have any payments before moving on
		$sql = "SELECT ID FROM $wpdb->posts WHERE post_type = 'pl8app_payment' LIMIT 1";
		$has_payments = $wpdb->get_col( $sql );

		if( empty( $has_payments ) ) {
			// We had no payments, just complete
			update_option( 'pl8app_version', preg_replace( '/[^0-9.].*/', '', PL8_VERSION ) );
			pl8app_set_upgrade_complete( 'upgrade_customer_payments_association' );
			delete_option( 'pl8app_doing_upgrade' );
			wp_redirect( admin_url() ); exit;
		}
	}

	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;

	if ( empty( $total ) || $total <= 1 ) {
		$total = PL8PRESS()->customers->count();
	}

	$customers = PL8PRESS()->customers->get_customers( array( 'number' => $number, 'offset' => $offset ) );

	if( ! empty( $customers ) ) {

		foreach( $customers as $customer ) {

			// Get payments by email and user ID
			$select = "SELECT ID FROM $wpdb->posts p ";
			$join   = "LEFT JOIN $wpdb->postmeta m ON p.ID = m.post_id ";
			$where  = "WHERE p.post_type = 'pl8app_payment' ";

			if ( ! empty( $customer->user_id ) && intval( $customer->user_id ) > 0 ) {
				$where .= "AND ( ( m.meta_key = '_pl8app_payment_user_email' AND m.meta_value = '$customer->email' ) OR ( m.meta_key = '_pl8app_payment_customer_id' AND m.meta_value = '$customer->id' ) OR ( m.meta_key = '_pl8app_payment_user_id' AND m.meta_value = '$customer->user_id' ) )";
			} else {
				$where .= "AND ( ( m.meta_key = '_pl8app_payment_user_email' AND m.meta_value = '$customer->email' ) OR ( m.meta_key = '_pl8app_payment_customer_id' AND m.meta_value = '$customer->id' ) ) ";
			}

			$sql            = $select . $join . $where;
			$found_payments = $wpdb->get_col( $sql );

			$unique_payment_ids  = array_unique( array_filter( $found_payments ) );

			if ( ! empty( $unique_payment_ids ) ) {

				$unique_ids_string = implode( ',', $unique_payment_ids );

				$customer_data = array( 'payment_ids' => $unique_ids_string );

				$purchase_value_sql = "SELECT SUM( m.meta_value ) FROM $wpdb->postmeta m LEFT JOIN $wpdb->posts p ON m.post_id = p.ID WHERE m.post_id IN ( $unique_ids_string ) AND p.post_status IN ( 'publish', 'revoked' ) AND m.meta_key = '_pl8app_payment_total'";
				$purchase_value     = $wpdb->get_col( $purchase_value_sql );

				$purchase_count_sql = "SELECT COUNT( m.post_id ) FROM $wpdb->postmeta m LEFT JOIN $wpdb->posts p ON m.post_id = p.ID WHERE m.post_id IN ( $unique_ids_string ) AND p.post_status IN ( 'publish', 'revoked' ) AND m.meta_key = '_pl8app_payment_total'";
				$purchase_count     = $wpdb->get_col( $purchase_count_sql );

				if ( ! empty( $purchase_value ) && ! empty( $purchase_count ) ) {

					$purchase_value = $purchase_value[0];
					$purchase_count = $purchase_count[0];

					$customer_data['purchase_count'] = $purchase_count;
					$customer_data['purchase_value'] = $purchase_value;

				}

			} else {

				$customer_data['purchase_count'] = 0;
				$customer_data['purchase_value'] = 0;
				$customer_data['payment_ids']    = '';

			}


			if ( ! empty( $customer_data ) ) {

				$customer = new pl8app_Customer( $customer->id );
				$customer->update( $customer_data );

			}

		}

		// More Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'pl8app-upgrades',
			'pl8app-upgrade' => 'upgrade_customer_payments_association',
			'step'        => $step,
			'number'      => $number,
			'total'       => $total
		), admin_url( 'index.php' ) );
		wp_redirect( $redirect ); exit;
	} else {

		// No more customers found, finish up

		update_option( 'pl8app_version', preg_replace( '/[^0-9.].*/', '', PL8_VERSION ) );
		pl8app_set_upgrade_complete( 'upgrade_customer_payments_association' );
		delete_option( 'pl8app_doing_upgrade' );

		wp_redirect( admin_url() ); exit;
	}
}
add_action( 'pl8app_upgrade_customer_payments_association', 'pl8app_v23_upgrade_customer_purchases' );

/**
 * Upgrade the Usermeta API Key storage to swap keys/values for performance
 *
 * @since 1.0
 * @return void
 */
function pl8app_upgrade_user_api_keys() {
	global $wpdb;

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	if ( ! pl8app_is_func_disabled( 'set_time_limit' ) ) {
		@set_time_limit(0);
	}

	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$number = 10;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;

	if ( $step < 2 ) {
		// Check if we have any users with API Keys before moving on
		$sql     = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'pl8app_user_public_key' LIMIT 1";
		$has_key = $wpdb->get_col( $sql );

		if( empty( $has_key ) ) {
			// We had no key, just complete
			update_option( 'pl8app_version', preg_replace( '/[^0-9.].*/', '', PL8_VERSION ) );
			pl8app_set_upgrade_complete( 'upgrade_user_api_keys' );
			delete_option( 'pl8app_doing_upgrade' );
			wp_redirect( admin_url() ); exit;
		}
	}

	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;

	if ( empty( $total ) || $total <= 1 ) {
		$total = $wpdb->get_var( "SELECT count(user_id) FROM $wpdb->usermeta WHERE meta_key = 'pl8app_user_public_key'" );
	}

	$keys_sql   = $wpdb->prepare( "SELECT user_id, meta_key, meta_value FROM $wpdb->usermeta WHERE meta_key = 'pl8app_user_public_key' OR meta_key = 'pl8app_user_secret_key' ORDER BY user_id ASC LIMIT %d,%d;", $offset, $number );
	$found_keys = $wpdb->get_results( $keys_sql );

	if( ! empty( $found_keys ) ) {


		foreach( $found_keys as $key ) {
			$user_id    = $key->user_id;
			$meta_key   = $key->meta_key;
			$meta_value = $key->meta_value;

			// Generate a new entry
			update_user_meta( $user_id, $meta_value, $meta_key );

			// Delete the old one
			delete_user_meta( $user_id, $meta_key );

		}

		// More Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'pl8app-upgrades',
			'pl8app-upgrade' => 'upgrade_user_api_keys',
			'step'        => $step,
			'number'      => $number,
			'total'       => $total
		), admin_url( 'index.php' ) );
		wp_redirect( $redirect ); exit;
	} else {

		// No more customers found, finish up

		update_option( 'pl8app_version', preg_replace( '/[^0-9.].*/', '', PL8_VERSION ) );
		pl8app_set_upgrade_complete( 'upgrade_user_api_keys' );
		delete_option( 'pl8app_doing_upgrade' );

		wp_redirect( admin_url() ); exit;
	}
}
add_action( 'pl8app_upgrade_user_api_keys', 'pl8app_upgrade_user_api_keys' );

/**
 * Remove sale logs from refunded orders
 *
 * @since 1.0.4
 * @return void
 */
function pl8app_remove_refunded_sale_logs() {
	global $wpdb, $pl8app_logs;

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	if ( ! pl8app_is_func_disabled( 'set_time_limit' ) ) {
		@set_time_limit(0);
	}

	$step    = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$total   = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : pl8app_count_payments()->refunded;
	$refunds = pl8app_get_payments( array( 'status' => 'refunded', 'number' => 20, 'page' => $step ) );

	if( ! empty( $refunds ) ) {

		// Refunded Payments found so process them

		foreach( $refunds as $refund ) {

			if( 'refunded' !== $refund->post_status ) {
				continue; // Just to be safe
			}

			// Remove related sale log entries
			$pl8app_logs->delete_logs(
				null,
				'sale',
				array(
					array(
						'key'   => '_pl8app_log_payment_id',
						'value' => $refund->ID
					)
				)
			);
		}

		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'pl8app-upgrades',
			'pl8app-upgrade' => 'remove_refunded_sale_logs',
			'step'        => $step,
			'total'       => $total
		), admin_url( 'index.php' ) );
		wp_redirect( $redirect ); exit;

	} else {

		// No more refunded payments found, finish up

		update_option( 'pl8app_version', preg_replace( '/[^0-9.].*/', '', PL8_VERSION ) );
		pl8app_set_upgrade_complete( 'remove_refunded_sale_logs' );
		delete_option( 'pl8app_doing_upgrade' );

		wp_redirect( admin_url() ); exit;
	}
}
add_action( 'pl8app_remove_refunded_sale_logs', 'pl8app_remove_refunded_sale_logs' );

/**
 * 2.6 Upgrade routine to create the customer meta table
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_v26_upgrades() {
	@PL8PRESS()->customers->create_table();
	@PL8PRESS()->customer_meta->create_table();
}


function pl8app_upgrade_render_update_file_menuitem_log_data() {
	$migration_complete = pl8app_has_upgrade_completed( 'update_file_menuitem_log_data' );

	if ( $migration_complete ) : ?>
		<div id="pl8app-sl-migration-complete" class="notice notice-success">
			<p>
				<?php _e( '<strong>Migration complete:</strong> You have already completed the update to the file menuitem logs.', 'pl8app' ); ?>
			</p>
		</div>
		<?php return; ?>
	<?php endif; ?>

	<div id="pl8app-migration-ready" class="notice notice-success" style="display: none;">
		<p>
			<?php _e( '<strong>Database Upgrade Complete:</strong> All database upgrades have been completed.', 'pl8app' ); ?>
			<br /><br />
			<?php _e( 'You may now leave this page.', 'pl8app' ); ?>
		</p>
	</div>

	<div id="pl8app-migration-nav-warn" class="notice notice-info">
		<p>
			<?php _e( '<strong>Important:</strong> Please leave this screen open and do not navigate away until the process completes.', 'pl8app' ); ?>
		</p>
	</div>

	<style>
		.dashicons.dashicons-yes { display: none; color: rgb(0, 128, 0); vertical-align: middle; }
	</style>
	<script>
		jQuery( function($) {
			$(document).ready(function () {
				$(document).on("DOMNodeInserted", function (e) {
					var element = e.target;

					if (element.id === 'pl8app-batch-success') {
						element = $(element);

						element.parent().prev().find('.pl8app-migration.allowed').hide();
						element.parent().prev().find('.pl8app-migration.unavailable').show();
						var element_wrapper = element.parents().eq(4);
						element_wrapper.find('.dashicons.dashicons-yes').show();

						var next_step_wrapper = element_wrapper.next();
						if (next_step_wrapper.find('.postbox').length) {
							next_step_wrapper.find('.pl8app-migration.allowed').show();
							next_step_wrapper.find('.pl8app-migration.unavailable').hide();

							if (auto_start_next_step) {
								next_step_wrapper.find('.pl8app-export-form').submit();
							}
						} else {
							$('#pl8app-migration-nav-warn').hide();
							$('#pl8app-migration-ready').slideDown();
						}

					}
				});
			});
		});
	</script>

	<div class="metabox-holder">
		<div class="postbox">
			<h2 class="hndle">
				<span><?php _e( 'Update file menuitem logs', 'pl8app' ); ?></span>
				<span class="dashicons dashicons-yes"></span>
			</h2>
			<div class="inside migrate-file-menuitem-logs-control">
				<p>
					<?php _e( 'This will update the file menuitem logs to remove some <abbr title="Personally Identifiable Information">PII</abbr> and make file menuitem counts more accurate.', 'pl8app' ); ?>
				</p>
				<form method="post" id="pl8app-fix-file-menuitem-logs-form" class="pl8app-export-form pl8app-import-export-form">
			<span class="step-instructions-wrapper">

				<?php wp_nonce_field( 'pl8app_ajax_export', 'pl8app_ajax_export' ); ?>

				<?php if ( ! $migration_complete ) : ?>
					<span class="pl8app-migration allowed">
						<input type="submit" id="migrate-logs-submit" value="<?php _e( 'Update File Download Logs', 'pl8app' ); ?>" class="button-primary"/>
					</span>
				<?php else: ?>
					<input type="submit" disabled="disabled" id="migrate-logs-submit" value="<?php _e( 'Update File Download Logs', 'pl8app' ); ?>" class="button-secondary"/>
					&mdash; <?php _e( 'File menuitem logs have already been updated.', 'pl8app' ); ?>
				<?php endif; ?>

				<input type="hidden" name="pl8app-export-class" value="pl8app_File_Download_Log_Migration" />
				<span class="spinner"></span>

			</span>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div>

	<?php
}

function pl8app_register_batch_file_menuitem_log_migration() {
	add_action( 'pl8app_batch_export_class_include', 'pl8app_include_file_menuitem_log_migration_batch_processor', 10, 1 );
}
add_action( 'pl8app_register_batch_exporter', 'pl8app_register_batch_file_menuitem_log_migration', 10 );


function pl8app_include_file_menuitem_log_migration_batch_processor( $class ) {

	if ( 'pl8app_File_Download_Log_Migration' === $class ) {
		require_once PL8_PLUGIN_DIR . 'includes/admin/upgrades/classes/class-file-menuitem-log-migration.php';
	}

}
