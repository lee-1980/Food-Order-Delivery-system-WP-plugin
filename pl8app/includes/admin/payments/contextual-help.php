<?php
/**
 * Contextual Help
 *
 * @package     pl8app
 * @subpackage  Admin/Payments
 * @copyright
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since  1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Payments contextual help.
 *
 * @access      private
 * @since  1.0.0
 * @return      void
 */
function pl8app_payments_contextual_help() {
	
	$screen = get_current_screen();

	if ( $screen->id != 'orders_page_pl8app-payment-history' )
		return;

	do_action( 'pl8app_payments_contextual_help', $screen );
}
add_action( 'load-orders_page_pl8app-payment-history', 'pl8app_payments_contextual_help' );
