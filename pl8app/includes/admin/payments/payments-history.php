<?php
/**
 * Admin Payment History
 *
 * @package     pl8app
 * @subpackage  Admin/Payments
 * @copyright
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Payment History Page
 *
 * Renders the payment history page contents.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/
function pl8app_payment_history_page() {
	
	$pl8app_payment = get_post_type_object( 'pl8app_payment' );

	if ( isset( $_GET['view'] ) && 'view-order-details' == $_GET['view'] ) {
		require_once PL8_PLUGIN_DIR . 'includes/admin/payments/view-order-details.php';
	} else {
		require_once PL8_PLUGIN_DIR . 'includes/admin/payments/class-payments-table.php';
		$payments_table = new pl8app_Payment_History_Table();
		$payments_table->prepare_items();
	?>
	<div class="wrap">
		<h1><?php echo $pl8app_payment->labels->menu_name ?></h1>
		<?php do_action( 'pl8app_payments_page_top' ); ?>
		<form id="pl8app-payments-filter" method="get" action="<?php echo admin_url( 'admin.php?page=pl8app-payment-history' ); ?>">

			<input type="hidden" name="page" value="pl8app-payment-history" />
			<?php $payments_table->views() ?>
			<?php $payments_table->advanced_filters(); ?>

			<?php $payments_table->display() ?>
		</form>
		<?php do_action( 'pl8app_payments_page_bottom' ); ?>
	</div>
<?php
	}
}


/**
 * Payment History admin titles
 *
 * @since  1.0.0
 *
 * @param $admin_title
 * @param $title
 * @return string
 */
function pl8app_view_order_details_title( $admin_title, $title ) {

	if ( 'orders_page_pl8app-payment-history' != get_current_screen()->base )
		return $admin_title;

	if( ! isset( $_GET['pl8app-action'] ) )
		return $admin_title;

	switch( $_GET['pl8app-action'] ) :

		case 'view-order-details' :
			$title = __( 'View Order Details', 'pl8app' ) . ' - ' . $admin_title;
			break;
		case 'edit-payment' :
			$title = __( 'Edit Payment', 'pl8app' ) . ' - ' . $admin_title;
			break;
		default:
			$title = $admin_title;
			break;
	endswitch;

	return $title;
}
add_filter( 'admin_title', 'pl8app_view_order_details_title', 10, 2 );

/**
 * Intercept default Edit post links for pl8app payments and rewrite them to the View Order Details screen
 *
 * @since 1.0.4
 *
 * @param $url
 * @param $post_id
 * @param $context
 * @return string
 */
function pl8app_override_edit_post_for_payment_link( $url, $post_id = 0, $context ) {

	$post = get_post( $post_id );
	if( ! $post )
		return $url;

	if( 'pl8app_payment' != $post->post_type )
		return $url;

	$url = admin_url( 'admin.php?page=pl8app-payment-history&view=view-order-details&id=' . $post_id );

	return $url;
}
add_filter( 'get_edit_post_link', 'pl8app_override_edit_post_for_payment_link', 10, 3 );
